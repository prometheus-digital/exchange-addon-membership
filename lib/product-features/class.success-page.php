<?php
/**
 * This will control membership status
 *
 * @since 1.0.0
 * @package IT_Exchange_Addon_Membership
*/


class IT_Exchange_Addon_Membership_Product_Feature_Success_Page {

	/**
	 * Constructor. Registers hooks
	 *
	 * @since 1.0.0
	 * @return void
	*/
	function IT_Exchange_Addon_Membership_Product_Feature_Success_Page() {
		if ( is_admin() ) {
			add_action( 'load-post-new.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'load-post.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'it_exchange_save_product', array( $this, 'save_feature_on_product_save' ) );
		}
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'add_feature_support_to_product_types' ) );
		add_action( 'it_exchange_update_product_feature_membership-success-page', array( $this, 'save_feature' ), 9, 3 );
		add_filter( 'it_exchange_get_product_feature_membership-success-page', array( $this, 'get_feature' ), 9, 3 );
		add_filter( 'it_exchange_product_has_feature_membership-success-page', array( $this, 'product_has_feature') , 9, 2 );
		add_filter( 'it_exchange_product_supports_feature_membership-success-page', array( $this, 'product_supports_feature') , 9, 2 );
		add_filter( 'it_exchange_default_field_names', array( $this, 'set_membership_success_page_vars' ) );
	}

	/**
	 * Register the product feature and add it to enabled product-type addons
	 *
	 * @since 0.4.0
	*/
	function add_feature_support_to_product_types() {
		// Register the product feature
		$slug        = 'membership-success-page';
		$description = __( 'The WordPress page you want this membership level to redirect to upon successfully registering. Defaults to the Exchange Profile page.', 'LION' );
		it_exchange_register_product_feature( $slug, $description );

		it_exchange_add_feature_support_to_product_type( 'membership-success-page', 'membership' );
	}

	/**
	 * Register's the metabox for any product type that supports the feature
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function init_feature_metaboxes() {
		
		global $post;
		
		if ( isset( $_REQUEST['post_type'] ) ) {
			$post_type = $_REQUEST['post_type'];
		} else {
			if ( isset( $_REQUEST['post'] ) )
				$post_id = (int) $_REQUEST['post'];
			elseif ( isset( $_REQUEST['post_ID'] ) )
				$post_id = (int) $_REQUEST['post_ID'];
			else
				$post_id = 0;

			if ( $post_id )
				$post = get_post( $post_id );

			if ( isset( $post ) && !empty( $post ) )
				$post_type = $post->post_type;
		}
			
		if ( !empty( $_REQUEST['it-exchange-product-type'] ) )
			$product_type = $_REQUEST['it-exchange-product-type'];
		else
			$product_type = it_exchange_get_product_type( $post );
		
		if ( !empty( $post_type ) && 'it_exchange_prod' === $post_type ) {
			if ( !empty( $product_type ) &&  it_exchange_product_type_supports_feature( $product_type, 'membership-success-page' ) )
				add_action( 'it_exchange_product_metabox_callback_' . $product_type, array( $this, 'register_metabox' ) );
		}
		
	}

	/**
	 * Registers the feature metabox for a specific product type
	 *
	 * Hooked to it_exchange_product_metabox_callback_[product-type] where product type supports the feature 
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function register_metabox() {
		add_meta_box( 'it-exchange-product-membership-success-page', __( 'Membership Success Page', 'LION' ), array( $this, 'print_metabox' ), 'it_exchange_prod', 'normal' );
	}

	/**
	 * This echos the feature metabox.
	 *
	 * @since 0.4.0
	 * @return void
	*/
	function print_metabox( $post ) {
		// Grab the iThemes Exchange Product object from the WP $post object
		$product = it_exchange_get_product( $post );

		// Set the value of the feature for this product
		$product_feature_value = it_exchange_get_product_feature( $product->ID, 'membership-success-page' );
		
		?>
			<p class="it-exchange-membership-status">
				<?php _e( 'Success Page', 'LION' ); ?>
			</p>  
            <?php echo wp_dropdown_pages( array( 'name' => 'it-exchange-product-membership-success-page', 'echo' => 0, 'show_option_none' => __( '&mdash; Select &mdash;' ), 'option_none_value' => '0', 'selected' => $product_feature_value ) ); ?>
		<?php
	}

	/**
	 * Sets the purchase quantity query_var
	 *
	 * @since 0.4.0
	 *
	 * @param array $vars sent in through filter
	 * @return array
	*/
	function set_membership_success_page_vars( $vars ) {
		$vars['product_membership_success_page']     = 'it-exchange-product-membership-success-page';
		return $vars;
	}

	/**
	 * This saves the value
	 *
	 * @since 0.3.8
	 *
	 * @param object $post wp post object
	 * @return void
	*/
	function save_feature_on_product_save() {
		// Abort if we can't determine a product type
		if ( ! $product_type = it_exchange_get_product_type() )
			return;

		// Abort if we don't have a product ID
		$product_id = empty( $_POST['ID'] ) ? false : $_POST['ID'];
		if ( ! $product_id )
			return;

		// Abort if this product type doesn't support this feature 
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'membership-success-page' ) || empty( $_POST['it-exchange-product-membership-success-page']  ))
			return;

		// If the value is empty (0), delete the key, otherwise save
		if ( empty( $_POST['it-exchange-product-membership-success-page'] ) )
			delete_post_meta( $product_id, '_it-exchange-product-membership-success-page' );
		else
			it_exchange_update_product_feature( $product_id, 'membership-success-page', absint( $_POST['it-exchange-product-membership-success-page'] ) );
	}
/**
	 * This updates the feature for a product
	 *
	 * @since 0.4.0
	 *
	 * @param integer $product_id the product id
	 * @param mixed $new_value the new value 
	 * @return bolean
	*/
	function save_feature( $product_id, $new_value, $options=array() ) {
		// Using options to determine if we're setting the enabled setting or the actual max_number setting
		$defaults = array(
			'setting' => 'membership-success-page',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
		
		$new_value = empty( $new_value ) && !is_numeric( $new_value ) ? '' : absint( $new_value );
		update_post_meta( $product_id, '_it-exchange-product-membership-success-page', $new_value );
		return true;
	}

	/**
	 * Return the product's features
	 *
	 * @since 0.4.0
	 * @param mixed $existing the values passed in by the WP Filter API. Ignored here.
	 * @param integer product_id the WordPress post ID
	 * @return string product feature
	*/
	function get_feature( $existing, $product_id ) {
		// Is the the add / edit product page?
		$current_screen = is_admin() ? get_current_screen(): false;
		$editing_product = ( ! empty( $current_screen->id ) && 'it_exchange_prod' == $current_screen->id );

		// Return the value if supported or on add/edit screen
		if ( it_exchange_product_supports_feature( $product_id, 'membership-success-page' ) || $editing_product )
			return get_post_meta( $product_id, '_it-exchange-product-membership-success-page', true );

		return false;
	}

	/**
	 * Does the product have the feature?
	 *
	 * @since 0.4.0
	 * @param mixed $result Not used by core
	 * @param integer $product_id
	 * @return boolean
	*/
	function product_has_feature( $result, $product_id ) {
		// Does this product type support this feature?
		if ( false === $this->product_supports_feature( false, $product_id ) )
			return false;
		return (boolean) $this->get_feature( false, $product_id );
	}

	/**
	 * Does the product support this feature?
	 *
	 * This is different than if it has the feature, a product can 
	 * support a feature but might not have the feature set.
	 *
	 * @since 0.4.0
	 * @param mixed $result Not used by core
	 * @param integer $product_id
	 * @return boolean
	*/
	function product_supports_feature( $result, $product_id ) {
		// Does this product type support this feature?
		$product_type = it_exchange_get_product_type( $product_id );
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'membership-success-page' ) )
			return false;

		return true;
	}
}
$IT_Exchange_Addon_Membership_Product_Feature_Success_Page = new IT_Exchange_Addon_Membership_Product_Feature_Success_Page();