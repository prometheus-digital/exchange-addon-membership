<?php
/**
 * This will control membership status
 *
 * @since 1.0.0
 * @package IT_Exchange_Addon_Membership
*/


class IT_Exchange_Addon_Membership_Product_Feature_Trial_Period {

	/**
	 * Constructor. Registers hooks
	 *
	 * @since 1.0.0
	 * @return void
	*/
	function IT_Exchange_Addon_Membership_Product_Feature_Trial_Period() {
		if ( is_admin() ) {
			add_action( 'load-post-new.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'load-post.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'it_exchange_save_product', array( $this, 'save_feature_on_product_save' ) );
		}
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'add_feature_support_to_product_types' ) );
		add_action( 'it_exchange_update_product_feature_membership-trial-period', array( $this, 'save_feature' ), 9, 3 );
		add_filter( 'it_exchange_get_product_feature_membership-trial-period', array( $this, 'get_feature' ), 9, 3 );
		add_filter( 'it_exchange_product_has_feature_membership-trial-period', array( $this, 'product_has_feature') , 9, 2 );
		add_filter( 'it_exchange_product_supports_feature_membership-trial-period', array( $this, 'product_supports_feature') , 9, 2 );
		add_filter( 'it_exchange_default_field_names', array( $this, 'set_membership_trial_period_vars' ) );
	}

	/**
	 * Register the product feature and add it to enabled product-type addons
	 *
	 * @since 0.4.0
	*/
	function add_feature_support_to_product_types() {
		// Register the product feature
		$slug        = 'membership-trial-period';
		$description = __( 'What is the free trial period allowed for new members?', 'LION' );
		it_exchange_register_product_feature( $slug, $description );

		it_exchange_add_feature_support_to_product_type( 'membership-trial-period', 'membership' );
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
			if ( !empty( $product_type ) &&  it_exchange_product_type_supports_feature( $product_type, 'membership-trial-period' ) )
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
		add_meta_box( 'it-exchange-product-membership-trial-period', __( 'Membership Trial Period', 'LION' ), array( $this, 'print_metabox' ), 'it_exchange_prod', 'normal' );
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
		$product_feature_time = it_exchange_get_product_feature( $product->ID, 'membership-trial-period', array( 'setting' => 'time' ) );
		$product_feature_type = it_exchange_get_product_feature( $product->ID, 'membership-trial-period', array( 'setting' => 'type' ) );

		?>
			<p class="it-exchange-duration">
				<?php _e( 'How long does the trial period last?', 'LION' ); ?>
			</p>
            <input type="text" name="it-exchange-product-membership-trial-period-time" value="<?php esc_attr_e( $product_feature_time ); ?>" />
            <select name="it-exchange-product-membership-trial-period-type">
            	<option value="day" <?php selected( 'day', $product_feature_type ); ?>><?php _e( 'Day(s)', 'LION' ); ?></option>
            	<option value="week" <?php selected( 'week', $product_feature_type ); ?>><?php _e( 'Week(s)', 'LION' ); ?></option>
            	<option value="year" <?php selected( 'year', $product_feature_type ); ?>><?php _e( 'Year(s)', 'LION' ); ?></option>
            </select>
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
	function set_membership_trial_period_vars( $vars ) {
		$vars['product_membership_trial_period_time'] = 'it-exchange-product-membership-trial-period-time';
		$vars['product_membership_trial_period_type'] = 'it-exchange-product-membership-trial-period-type';
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
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'membership-trial-period' ) )
			return;

		// If the value is empty (0), delete the key, otherwise save
		if ( empty( $_POST['it-exchange-product-membership-trial-period-time'] ) )
			delete_post_meta( $product_id, '_it-exchange-product-membership-trial-period-time' );
		else
			it_exchange_update_product_feature( $product_id, 'membership-trial-period', absint( $_POST['it-exchange-product-membership-trial-period-time'] ), array( 'setting' => 'time' ) );
			
		it_exchange_update_product_feature( $product_id, 'membership-trial-period', $_POST['it-exchange-product-membership-trial-period-type'], array( 'setting' => 'type' ) );
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
			'setting' => 'time',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
		
		switch( $options['setting'] ) {
		
			case 'type':
				return update_post_meta( $product_id, '_it-exchange-product-membership-trial-period-type', $new_value );
		
			case 'time':
			default:
				$new_value = empty( $new_value ) && !is_numeric( $new_value ) ? '' : absint( $new_value );
				return update_post_meta( $product_id, '_it-exchange-product-membership-trial-period-time', $new_value );
			
		}
	}

	/**
	 * Return the product's features
	 *
	 * @since 0.4.0
	 * @param mixed $existing the values passed in by the WP Filter API. Ignored here.
	 * @param integer product_id the WordPress post ID
	 * @return string product feature
	*/
	function get_feature( $existing, $product_id, $options=array() ) {
		// Using options to determine if we're getting the download limit or adding/updating files
        $defaults = array(
            'setting' => 'time',
        );
        $options = ITUtility::merge_defaults( $options, $defaults );
		
		return get_post_meta( $product_id, '_it-exchange-product-membership-trial-period-' . $options['setting'], true );
	}

	/**
	 * Does the product have the feature?
	 *
	 * @since 0.4.0
	 * @param mixed $result Not used by core
	 * @param integer $product_id
	 * @return boolean
	*/
	function product_has_feature( $result, $product_id, $options=array() ) {
		$defaults['setting'] = 'time';
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
	 * @since 0.4.0
	 * @param mixed $result Not used by core
	 * @param integer $product_id
	 * @return boolean
	*/
	function product_supports_feature( $result, $product_id ) {
		// Does this product type support this feature?
		$product_type = it_exchange_get_product_type( $product_id );
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'membership-trial-period' ) )
			return false;

		return true;
	}
}
$IT_Exchange_Addon_Membership_Product_Feature_Trial_Period = new IT_Exchange_Addon_Membership_Product_Feature_Trial_Period();