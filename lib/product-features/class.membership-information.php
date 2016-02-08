<?php

/**
 * This will control membership welcome messages on the frontend membership dashboard
 *
 * @since   1.1.0
 * @package IT_Exchange_Addon_Membership
 */
class IT_Exchange_Addon_Membership_Product_Feature_Membership_Information {

	/**
	 * Constructor. Registers hooks
	 *
	 * @since 1.1.0
	 */
	function __construct() {
		if ( is_admin() ) {
			add_action( 'load-post-new.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'load-post.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'it_exchange_save_product', array( $this, 'save_feature_on_product_save' ) );
		}
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'add_feature_support_to_product_types' ) );
		add_action( 'it_exchange_update_product_feature_membership-information', array( $this, 'save_feature' ), 9, 3 );
		add_filter( 'it_exchange_get_product_feature_membership-information', array( $this, 'get_feature' ), 9, 3 );
		add_filter( 'it_exchange_product_has_feature_membership-information', array(
			$this,
			'product_has_feature'
		), 9, 3 );
		add_filter( 'it_exchange_product_supports_feature_membership-information', array(
			$this,
			'product_supports_feature'
		), 9, 2 );
	}

	/**
	 * Deprecated Constructor. Registers hooks
	 *
	 * @since 1.1.0
	 */
	function IT_Exchange_Addon_Membership_Product_Feature_Membership_Information() {
		self::__construct();
	}

	/**
	 * Register the product feature and add it to enabled product-type addons
	 *
	 * @since 1.1.0
	 */
	function add_feature_support_to_product_types() {
		// Register the product feature
		$slug        = 'membership-information';
		$description = __( "This displays the intended audience message for each Membership type on the member's product page", 'LION' );
		it_exchange_register_product_feature( $slug, $description );

		it_exchange_add_feature_support_to_product_type( 'membership-information', 'membership-product-type' );
	}

	/**
	 * Register's the metabox for any product type that supports the feature
	 *
	 * @since 1.1.0
	 */
	function init_feature_metaboxes() {

		global $post;

		if ( isset( $_REQUEST['post_type'] ) ) {
			$post_type = $_REQUEST['post_type'];
		} else {
			if ( isset( $_REQUEST['post'] ) ) {
				$post_id = (int) $_REQUEST['post'];
			} elseif ( isset( $_REQUEST['post_ID'] ) ) {
				$post_id = (int) $_REQUEST['post_ID'];
			} else {
				$post_id = 0;
			}

			if ( $post_id ) {
				$post = get_post( $post_id );
			}

			if ( isset( $post ) && ! empty( $post ) ) {
				$post_type = $post->post_type;
			}
		}

		if ( ! empty( $_REQUEST['it-exchange-product-type'] ) ) {
			$product_type = $_REQUEST['it-exchange-product-type'];
		} else {
			$product_type = it_exchange_get_product_type( $post );
		}

		if ( ! empty( $post_type ) && 'it_exchange_prod' === $post_type ) {
			if ( ! empty( $product_type ) && it_exchange_product_type_supports_feature( $product_type, 'membership-information' ) ) {
				add_action( 'it_exchange_product_metabox_callback_' . $product_type, array(
					$this,
					'register_metabox'
				), 1 );
			} //we want this to appear first in Membership product types
		}

	}

	/**
	 * Registers the feature metabox for a specific product type
	 *
	 * Hooked to it_exchange_product_metabox_callback_[product-type] where product type supports the feature
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	function register_metabox() {
		add_meta_box( 'it-exchange-product-membership-information', __( 'Membership Information', 'LION' ), array(
			$this,
			'print_metabox'
		), 'it_exchange_prod', 'it_exchange_advanced' );
	}

	/**
	 * This echos the feature metabox.
	 *
	 * @since 1.1.0
	 *
	 * @param WP_Post $post
	 */
	function print_metabox( $post ) {
		// Grab the iThemes Exchange Product object from the WP $post object
		$product  = it_exchange_get_product( $post );
		$defaults = it_exchange_get_option( 'addon_membership' );

		$content_restricted = $product->get_feature( 'membership-information', array( 'setting' => 'content-restricted' ) );
		$has_restricted     = $product->has_feature( 'membership-information', array( 'setting' => 'content-restricted' ) );
		$content_delayed    = $product->get_feature( 'membership-information', array( 'setting' => 'content-delayed' ) );
		$has_delayed        = $product->has_feature( 'membership-information', array( 'setting' => 'content-delayed' ) );

		$intended_audience = $product->get_feature( 'membership-information', array( 'setting' => 'intended-audience' ) );
		$has_audience      = $product->has_feature( 'membership-information', array( 'setting' => 'intended-audience' ) );

		$objectives     = $product->get_feature( 'membership-information', array( 'setting' => 'objectives' ) );
		$has_objectives = $product->has_feature( 'membership-information', array( 'setting' => 'objectives' ) );

		$prerequisites     = $product->get_feature( 'membership-information', array( 'setting' => 'prerequisites' ) );
		$has_prerequisites = $product->has_feature( 'membership-information', array( 'setting' => 'prerequisites' ) );
		?>

		<p>
			<label for="it-exchange-show-override-content-restricted">

				<input type="checkbox" id="it-exchange-show-override-content-restricted" name="it-exchange-show-override-content-restricted" <?php checked( $has_restricted ); ?>>&nbsp;

				<?php _e( 'Override the Restricted Content Message', 'LION' ); ?>
			</label>
		</p>

		<div <?php echo ! $has_restricted ? 'class="hide-if-js"' : ''; ?> id="it-exchange-override-content-restricted">
			<?php wp_editor( $content_restricted, 'membership-information-content-restricted-template', array(
				'textarea_name' => 'it-exchange-membership-information-content-restricted-template',
				'textarea_rows' => 5,
				'textarea_cols' => 30,
				'editor_class'  => 'large-text',
				'teeny'         => true
			) ); ?>
		</div>

		<p>
			<label for="it-exchange-show-override-content-delayed">

				<input type="checkbox" id="it-exchange-show-override-content-delayed" name="it-exchange-show-override-content-delayed" <?php checked( $has_delayed ); ?>>&nbsp;

				<?php _e( 'Override the Content Delayed Message', 'LION' ); ?>
			</label>
		</p>

		<div <?php echo ! $has_delayed ? 'class="hide-if-js"' : ''; ?> id="it-exchange-override-content-delayed">
			<?php wp_editor( $content_delayed, 'membership-information-content-delayed-template', array(
				'textarea_name' => 'it-exchange-membership-information-content-delayed-template',
				'textarea_rows' => 5,
				'textarea_cols' => 30,
				'editor_class'  => 'large-text',
				'teeny'         => true
			) ); ?>

			<p class="description">
				<?php _e( 'Use %d to represent the number of days until the delayed content will be available.', 'LION' ); ?>
			</p>
		</div>

		<p>
			<label for="it-exchange-show-intended-audience" class="customer-information-label">

				<input type="checkbox" id="it-exchange-show-intended-audience" name="it-exchange-show-intended-audience" <?php checked( $has_audience ); ?>>&nbsp;

				<?php printf( __( 'Display %s', 'LION' ), $defaults['membership-intended-audience-label'] ); ?>
			</label>
		</p>

		<div <?php echo ! $has_audience ? 'class="hide-if-js"' : ''; ?> id="it-exchange-intended-audience">
			<?php wp_editor( $intended_audience, 'membership-information-intended-audience-template', array(
				'textarea_name' => 'it-exchange-membership-information-intended-audience-template',
				'textarea_rows' => 5,
				'textarea_cols' => 30,
				'editor_class'  => 'large-text',
				'teeny'         => true
			) ); ?>
		</div>

		<p>
			<label for="it-exchange-show-objectives" class="customer-information-label">

				<input type="checkbox" id="it-exchange-show-objectives" name="it-exchange-show-objectives" <?php checked( $has_objectives ); ?>>&nbsp;

				<?php printf( __( 'Display %s', 'LION' ), $defaults['membership-objectives-label'] ); ?>
			</label>
		</p>

		<div <?php echo ! $has_objectives ? 'class="hide-if-js"' : ''; ?> id="it-exchange-objectives">
			<?php wp_editor( $objectives, 'membership-information-objectives-template', array(
				'textarea_name' => 'it-exchange-membership-information-objectives-template',
				'textarea_rows' => 5,
				'textarea_cols' => 30,
				'editor_class'  => 'large-text',
				'teeny'         => true
			) ); ?>
		</div>

		<p>
			<label for="it-exchange-show-prerequisites" class="customer-information-label">

				<input type="checkbox" id="it-exchange-show-prerequisites" name="it-exchange-show-prerequisites" <?php checked( $has_prerequisites ); ?>>&nbsp;

				<?php printf( __( 'Display %s', 'LION' ), $defaults['membership-prerequisites-label'] ); ?>
			</label>
		</p>

		<div <?php echo ! $has_prerequisites ? 'class="hide-if-js"' : ''; ?> id="it-exchange-prerequisites">
			<?php wp_editor( $prerequisites, 'membership-information-prerequisites-template', array(
				'textarea_name' => 'it-exchange-membership-information-prerequisites-template',
				'textarea_rows' => 5,
				'textarea_cols' => 30,
				'editor_class'  => 'large-text',
				'teeny'         => true
			) ); ?>
		</div>

		<?php
	}

	/**
	 * This saves the value
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	function save_feature_on_product_save() {
		// Abort if we can't determine a product type
		if ( ! $product_type = it_exchange_get_product_type() ) {
			return;
		}

		// Abort if we don't have a product ID
		$product_id = empty( $_POST['ID'] ) ? false : $_POST['ID'];
		if ( ! $product_id ) {
			return;
		}

		// Abort if this product type doesn't support this feature 
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'membership-information' ) ) {
			return;
		}

		it_exchange_update_product_feature( $product_id, 'membership-information', ! empty( $_POST['it-exchange-show-override-content-restricted'] ), array(
			'setting' => 'content-restricted',
			'enabled' => true
		) );
		it_exchange_update_product_feature( $product_id, 'membership-information', ! empty( $_POST['it-exchange-show-override-content-delayed'] ), array(
			'setting' => 'content-delayed',
			'enabled' => true
		) );
		it_exchange_update_product_feature( $product_id, 'membership-information', ! empty( $_POST['it-exchange-show-intended-audience'] ), array(
			'setting' => 'intended-audience',
			'enabled' => true
		) );
		it_exchange_update_product_feature( $product_id, 'membership-information', ! empty( $_POST['it-exchange-show-objectives'] ), array(
			'setting' => 'objectives',
			'enabled' => true
		) );
		it_exchange_update_product_feature( $product_id, 'membership-information', ! empty( $_POST['it-exchange-show-prerequisites'] ), array(
			'setting' => 'prerequisites',
			'enabled' => true
		) );

		if ( empty( $_POST['it-exchange-membership-information-content-restricted-template'] ) ) {
			delete_post_meta( $product_id, '_it-exchange-product-membership-content-restricted' );
		} else {
			it_exchange_update_product_feature( $product_id, 'membership-information', $_POST['it-exchange-membership-information-content-restricted-template'], array( 'setting' => 'content-restricted' ) );
		}

		if ( empty( $_POST['it-exchange-membership-information-content-delayed-template'] ) ) {
			delete_post_meta( $product_id, '_it-exchange-product-membership-content-delayed' );
		} else {
			it_exchange_update_product_feature( $product_id, 'membership-information', $_POST['it-exchange-membership-information-content-delayed-template'], array( 'setting' => 'content-delayed' ) );
		}

		if ( empty( $_POST['it-exchange-membership-information-intended-audience-template'] ) ) {
			delete_post_meta( $product_id, '_it-exchange-product-membership-intended-audience' );
		} else {
			it_exchange_update_product_feature( $product_id, 'membership-information', $_POST['it-exchange-membership-information-intended-audience-template'], array( 'setting' => 'intended-audience' ) );
		}

		if ( empty( $_POST['it-exchange-membership-information-objectives-template'] ) ) {
			delete_post_meta( $product_id, '_it-exchange-product-membership-objectives' );
		} else {
			it_exchange_update_product_feature( $product_id, 'membership-information', $_POST['it-exchange-membership-information-objectives-template'], array( 'setting' => 'objectives' ) );
		}

		if ( empty( $_POST['it-exchange-membership-information-prerequisites-template'] ) ) {
			delete_post_meta( $product_id, '_it-exchange-product-membership-prerequisites' );
		} else {
			it_exchange_update_product_feature( $product_id, 'membership-information', $_POST['it-exchange-membership-information-prerequisites-template'], array( 'setting' => 'prerequisites' ) );
		}
	}

	/**
	 * Return the product's features
	 *
	 * @since 1.1.0
	 *
	 * @param integer $product_id the WordPress post ID
	 * @param mixed   $new_value
	 * @param array   $options
	 *
	 * @return string product feature
	 */
	function save_feature( $product_id, $new_value, $options = array() ) {

		$meta_key = $this->get_feature_meta_key( $options['setting'], ! empty( $options['enabled'] ) );

		if ( ! $meta_key ) {
			return false;
		}

		update_post_meta( $product_id, $meta_key, $new_value );

		return true;
	}

	/**
	 * Return the product's features
	 *
	 * @since 1.1.0
	 *
	 * @param mixed $existing   the values passed in by the WP Filter API. Ignored here.
	 * @param int   $product_id the WordPress post ID
	 * @param array $options
	 *
	 * @return string product feature
	 */
	function get_feature( $existing, $product_id, $options = array() ) {

		$meta_key = $this->get_feature_meta_key( $options['setting'], ! empty( $options['enabled'] ) );

		if ( ! $meta_key ) {
			return $existing;
		}

		return get_post_meta( $product_id, $meta_key, true );
	}

	/**
	 * Does the product have the feature?
	 *
	 * @since 1.1.0
	 *
	 * @param mixed $result Not used by core
	 * @param int   $product_id
	 * @param array $options
	 *
	 * @return boolean
	 */
	function product_has_feature( $result, $product_id, $options = array() ) {
		// Does this product type support this feature?
		if ( false === $this->product_supports_feature( false, $product_id ) ) {
			return false;
		}

		$options['enabled'] = true;

		switch ( $options['setting'] ) {
			case 'intended-audience':
			case 'objectives':
			case 'prerequisites':

				$has_meta_key = $this->get_feature_meta_key( $options['setting'], true );
				$meta_key     = $this->get_feature_meta_key( $options['setting'], false );

				if ( ! metadata_exists( 'post', $product_id, $has_meta_key ) ) {
					update_post_meta( $product_id, $has_meta_key, (bool) get_post_meta( $product_id, $meta_key, true ) );
				}

				break;
		}

		// If it does support, does it have it?
		return (boolean) $this->get_feature( false, $product_id, $options );
	}

	/**
	 * @param string $feature
	 * @param bool   $check_enabled
	 *
	 * @return false|string
	 */
	protected function get_feature_meta_key( $feature, $check_enabled = false ) {

		switch ( $feature ) {

			case 'content-restricted' :
				$meta_key = '_it-exchange-product-membership-content-restricted';
				break;

			case 'content-delayed' :
				$meta_key = '_it-exchange-product-membership-content-delayed';
				break;

			case 'intended-audience':
				$meta_key = '_it-exchange-product-membership-intended-audience';
				break;
			case 'objectives':
				$meta_key = '_it-exchange-product-membership-objectives';
				break;
			case 'prerequisites':
				$meta_key = '_it-exchange-product-membership-prerequisites';
				break;
			default:
				return false;
		}

		if ( $check_enabled ) {
			$meta_key .= '-enabled';
		}

		return $meta_key;
	}


	/**
	 * Does the product support this feature?
	 *
	 * This is different than if it has the feature, a product can
	 * support a feature but might not have the feature set.
	 *
	 * @since 1.1.0
	 *
	 * @param mixed $result Not used by core
	 * @param int   $product_id
	 *
	 * @return boolean
	 */
	function product_supports_feature( $result, $product_id ) {
		// Does this product type support this feature?
		$product_type = it_exchange_get_product_type( $product_id );
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'membership-information' ) ) {
			return false;
		}

		return true;
	}
}

$IT_Exchange_Addon_Membership_Product_Feature_Membership_Information = new IT_Exchange_Addon_Membership_Product_Feature_Membership_Information();