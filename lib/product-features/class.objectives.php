<?php
/**
 * This will control membership welcome messages on the frontend membership dashboard
 *
 * @since 1.1.0
 * @package IT_Exchange_Addon_Membership
*/


class IT_Exchange_Addon_Membership_Product_Feature_Membership_Objectives {

	/**
	 * Constructor. Registers hooks
	 *
	 * @since 1.1.0
	 * @return void
	*/
	function IT_Exchange_Addon_Membership_Product_Feature_Membership_Objectives() {
		if ( is_admin() ) {
			add_action( 'load-post-new.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'load-post.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'it_exchange_save_product', array( $this, 'save_feature_on_product_save' ) );
		}
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'add_feature_support_to_product_types' ) );
		add_action( 'it_exchange_update_product_feature_membership-objectives', array( $this, 'save_feature' ), 9, 3 );
		add_filter( 'it_exchange_get_product_feature_membership-objectives', array( $this, 'get_feature' ), 9, 3 );
		add_filter( 'it_exchange_product_has_feature_membership-objectives', array( $this, 'product_has_feature') , 9, 2 );
		add_filter( 'it_exchange_product_supports_feature_membership-objectives', array( $this, 'product_supports_feature') , 9, 2 );
	}

	/**
	 * Register the product feature and add it to enabled product-type addons
	 *
	 * @since 1.1.0
	*/
	function add_feature_support_to_product_types() {
		// Register the product feature
		$slug        = 'membership-objectives';
		$description = __( "This displays the objectives message for each Membership type on the member's product page", 'LION' );
		it_exchange_register_product_feature( $slug, $description );

		it_exchange_add_feature_support_to_product_type( 'membership-objectives', 'membership-product-type' );
	}

	/**
	 * Register's the metabox for any product type that supports the feature
	 *
	 * @since 1.1.0
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
			if ( !empty( $product_type ) &&  it_exchange_product_type_supports_feature( $product_type, 'membership-objectives' ) )
				add_action( 'it_exchange_product_metabox_callback_' . $product_type, array( $this, 'register_metabox' ), 1 ); //we want this to appear first in Membership product types
		}
		
	}

	/**
	 * Registers the feature metabox for a specific product type
	 *
	 * Hooked to it_exchange_product_metabox_callback_[product-type] where product type supports the feature 
	 *
	 * @since 1.1.0
	 * @return void
	*/
	function register_metabox() {
		add_meta_box( 'it-exchange-product-membership-objectives', __( 'Membership Objectives', 'LION' ), array( $this, 'print_metabox' ), 'it_exchange_prod', 'it_exchange_advanced' );
	}

	/**
	 * This echos the feature metabox.
	 *
	 * @since 1.1.0
	 * @return void
	*/
	function print_metabox( $post ) {
		// Grab the iThemes Exchange Product object from the WP $post object
		$product = it_exchange_get_product( $post );
        $defaults = it_exchange_get_option( 'addon_membership' );
		
		$prerequisites_label = it_exchange_get_product_feature( $product->ID, 'membership-objectives', array( 'setting' => 'label' ) );
		$prerequisites_label = false === $prerequisites_label ? $defaults['membership-objectives-label'] : $prerequisites_label;
		$prerequisites_description = it_exchange_get_product_feature( $product->ID, 'membership-objectives', array( 'setting' => 'description' ) );
		
		$description = __( "This feature should be used to list the intended audience for this membership.", 'LION' );
		$description = apply_filters( 'it_exchange_membership_addon_product_membership-objectives_metabox_description', $description );

		if ( $description ) {
			echo '<p class="intro-description">' . $description . '</p>';
		}
	
		?>
		<p class="it-exchange-product-membership-objectives-label">
        <label for="it-exchange-product-membership-objectives-label"><?php _e( 'Objective Title', 'LION' ); ?></label>
		<input type="text" id="it-exchange-product-membership-objectives-label"  class="it-exchange-product-membership-objectives-label" name="it-exchange-product-membership-objectives-label" value="<?php esc_attr_e( $prerequisites_label ); ?>" />
		</p>
        <?php
        
        echo wp_editor( $prerequisites_description, 'membership-objectives-template', array( 'textarea_name' => 'it-exchange-product-membership-objectives-description', 'textarea_rows' => 10, 'textarea_cols' => 30, 'editor_class' => 'large-text' ) );
	}

	/**
	 * This saves the value
	 *
	 * @since 1.1.0
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
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'membership-objectives' ) )
			return;

		// If the value is empty (0), delete the key, otherwise save
		if ( empty( $_POST['it-exchange-product-membership-objectives-label'] ) )
			delete_post_meta( $product_id, '_it-exchange-product-membership-prereq-label' );
		else
			it_exchange_update_product_feature( $product_id, 'membership-objectives', $_POST['it-exchange-product-membership-objectives-label'], array( 'setting' => 'label' ) );
			
		// If the value is empty (0), delete the key, otherwise save
		if ( empty( $_POST['it-exchange-product-membership-objectives-description'] ) )
			delete_post_meta( $product_id, '_it-exchange-product-membership-objectives' );
		else
			it_exchange_update_product_feature( $product_id, 'membership-objectives', $_POST['it-exchange-product-membership-objectives-description'], array( 'setting' => 'description' ) );
	}

	/**
	 * Return the product's features
	 *
	 * @since 1.1.0
	 * @param mixed $existing the values passed in by the WP Filter API. Ignored here.
	 * @param integer product_id the WordPress post ID
	 * @return string product feature
	*/
	function save_feature( $product_id, $new_value, $options=array() ) {
		$defaults['setting'] = 'description';
		$options = ITUtility::merge_defaults( $options, $defaults );
		
		switch ( $options['setting'] ) {
			
			case 'label':
				update_post_meta( $product_id, '_it-exchange-membership-objectives-label', $new_value );
				break;
			case 'description':
				update_post_meta( $product_id, '_it-exchange-membership-objectives-description', $new_value );
				break;
			
		}
		return true;
	}

	/**
	 * Return the product's features
	 *
	 * @since 1.1.0
	 *
	 * @param mixed $existing the values passed in by the WP Filter API. Ignored here.
	 * @param integer product_id the WordPress post ID
	 * @return string product feature
	*/
	function get_feature( $existing, $product_id, $options=array() ) {
		$defaults['setting'] = 'description';
		$options = ITUtility::merge_defaults( $options, $defaults );
		
		switch ( $options['setting'] ) {
			
			case 'label':
				return get_post_meta( $product_id, '_it-exchange-membership-objectives-label', true );
				break;
			case 'description':
				return get_post_meta( $product_id, '_it-exchange-membership-objectives-description', true );
				break;
				
		}
		
		return false;
	}

	/**
	 * Does the product have the feature?
	 *
	 * @since 1.1.0
	 * @param mixed $result Not used by core
	 * @param integer $product_id
	 * @return boolean
	*/
	function product_has_feature( $result, $product_id, $options=array() ) {
		$defaults['setting'] = 'description';
		$options = ITUtility::merge_defaults( $options, $defaults );

		// Does this product type support this feature?
		if ( false === $this->product_supports_feature( false, $product_id, $options ) )
			return false;

		// If it does support, does it have it?
		return (boolean) $this->get_feature( false, $product_id, $options );
	}

	/**
	 * Does the product support this feature?
	 *
	 * This is different than if it has the feature, a product can 
	 * support a feature but might not have the feature set.
	 *
	 * @since 1.1.0
	 * @param mixed $result Not used by core
	 * @param integer $product_id
	 * @return boolean
	*/
	function product_supports_feature( $result, $product_id ) {
		// Does this product type support this feature?
		$product_type = it_exchange_get_product_type( $product_id );
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'membership-objectives' ) )
			return false;
			
		// Determine if this product has a prerequisites 
		if ( 'no' == it_exchange_get_product_feature( $product_id, 'membership-objectives', array( 'setting' => 'description' ) ) ) 
			return false;

		return true;
	}
}
$IT_Exchange_Addon_Membership_Product_Feature_Membership_Objectives = new IT_Exchange_Addon_Membership_Product_Feature_Membership_Objectives();