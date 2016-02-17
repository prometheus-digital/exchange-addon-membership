<?php

/**
 * This will control membership content access
 *
 * @since   1.0.0
 * @package IT_Exchange_Addon_Membership
 */
class IT_Exchange_Addon_Membership_Product_Feature_Content_Access {

	/**
	 * @var string
	 */
	private $slug = 'membership-content-access-rules';

	/**
	 * Constructor. Registers hooks
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		if ( is_admin() ) {
			add_action( 'load-post-new.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'load-post.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'it_exchange_save_product', array( $this, 'save_feature_on_product_save' ) );
		}

		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'add_feature_support_to_product_types' ) );
		add_action( 'it_exchange_update_product_feature_' . $this->slug, array( $this, 'save_feature' ), 9, 3 );
		add_filter( 'it_exchange_get_product_feature_' . $this->slug, array( $this, 'get_feature' ), 9, 3 );
		add_filter( 'it_exchange_product_has_feature_' . $this->slug, array( $this, 'product_has_feature' ), 9, 2 );
		add_filter( 'it_exchange_product_supports_feature_' . $this->slug, array(
			$this,
			'product_supports_feature'
		), 9, 2 );
	}

	/**
	 * Deprecated Constructor. Registers hooks
	 *
	 * @since 1.0.0
	 */
	public function IT_Exchange_Addon_Membership_Product_Feature_Content_Access() {
		self::__construct();
	}

	/**
	 * Register the product feature and add it to enabled product-type addons
	 *
	 * @since 1.0.0
	 */
	public function add_feature_support_to_product_types() {
		// Register the product feature
		$slug        = 'membership-content-access-rules';
		$description = __( 'How long a membership should last.', 'LION' );
		it_exchange_register_product_feature( $slug, $description );

		it_exchange_add_feature_support_to_product_type( 'membership-content-access-rules', 'membership-product-type' );
	}

	/**
	 * Register's the metabox for any product type that supports the feature
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init_feature_metaboxes() {

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
			if ( ! empty( $product_type ) && it_exchange_product_type_supports_feature( $product_type, 'membership-content-access-rules' ) ) {
				add_action( 'it_exchange_product_metabox_callback_' . $product_type, array(
					$this,
					'register_metabox'
				), 1 );
			}
		}
	}

	/**
	 * Registers the feature metabox for a specific product type
	 *
	 * Hooked to it_exchange_product_metabox_callback_[product-type] where product type supports the feature
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_metabox() {
		add_meta_box( 'it-exchange-product-membership-content-access-rules', __( 'Membership Content Access', 'LION' ),
			array( $this, 'print_metabox' ), 'it_exchange_prod', 'it_exchange_normal', 'low' );
	}

	/**
	 * This echos the feature metabox.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post
	 *
	 * @return void
	 */
	public function print_metabox( $post ) {
		// Grab the iThemes Exchange Product object from the WP $post object
		$product = it_exchange_get_product( $post );

		// Set the value of the feature for this product
		$access_rules = it_exchange_get_product_feature( $product->ID, 'membership-content-access-rules' );
		?>
		<div class="it-exchange-content-access-header">
			<div class="it-exchange-content-access-label-add">
				<label><?php _e( 'Content Access', 'LION' ); ?>
					<span class="tip" title="<?php _e( 'Content Access settings restrict access to content for this membership.  Note: Delay Access settings can only be applied to individual posts or pages.', 'LION' ); ?>">i</span></label>
			</div>
		</div>
		<div class="it-exchange-content-access-list-wrapper">
			<?php
			if ( ! empty( $access_rules ) ) {
				$hidden_access_list_class = '';
				$hidden_no_rules_class    = 'hidden';
			} else {
				$hidden_access_list_class = 'hidden';
				$hidden_no_rules_class    = '';
			}
			?>
			<div class="it-exchange-content-access-list <?php echo $hidden_access_list_class; ?>">
				<div class="it-exchange-content-access-list-titles">
					<div class="it-exchange-content-access-item columns-wrapper">
						<div class="column"></div>
						<div class="it-exchange-content-access-type column">
							<span><?php _e( 'Type', 'LION' ); ?></span>
						</div>
						<div class="it-exchange-content-access-content column">
							<span><?php _e( 'Content', 'LION' ); ?></span>
						</div>
						<div class="it-exchange-content-access-delay column">
							<span><?php _e( 'Delay Access', 'LION' ); ?>
								<span class="tip" title="<?php _e( 'This setting can only be applied to individual posts or pages.', 'LION' ); ?>">i</span></span>
						</div>
					</div>
				</div>
				<?php echo it_exchange_membership_addon_build_content_rules( $access_rules, $product->ID ); ?>
			</div>
			<div class="it-exchange-content-no-rules it-exchange-membership-content-access-add-new-rule <?php echo $hidden_no_rules_class; ?>"><?php _e( 'No content access rules added to this membership yet. <a href="">Add New Rule</a>', 'LION' ); ?></div>
		</div>
		<div class="it-exchange-content-access-footer">
			<div class="it-exchange-membership-content-access-add-new-rule left">
				<a href class="button"><?php _e( 'Add New Rule', 'LION' ); ?></a>
			</div>
			<div class="it-exchange-membership-content-access-add-new-group left">
				<a href class="button"><?php _e( 'Add New Group', 'LION' ); ?></a>
			</div>
		</div>
		<?php
	}

	/**
	 * This saves the value
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function save_feature_on_product_save() {
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
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'membership-content-access-rules' ) ) {
			return;
		}

		$factory    = new IT_Exchange_Membership_Rule_Factory();
		$membership = it_exchange_get_product( $product_id );

		$existing_rules = $factory->make_all_for_membership( $membership );

		$saved_ids = array();
		$errors    = array();

		if ( ! empty( $_REQUEST['it_exchange_content_access_rules'] ) ) {

			foreach ( $_REQUEST['it_exchange_content_access_rules'] as $key => $rule ) {

				if ( ! empty( $rule['selected'] ) && ! empty( $rule['selection'] ) && ! empty( $rule['term'] ) ) {

					$delay_data = isset( $rule['delay'] ) ? $rule['delay'] : array();
					unset( $rule['delay'] );
					unset( $_REQUEST['it_exchange_content_access_rules'][ $key ]['delay'] );

					$rule_model = $factory->make_content_rule( $rule['selected'], $rule, $membership );
					$rule_model->save();

					$saved_ids[] = $rule_model->get_rule_id();

					if ( $rule_model instanceof IT_Exchange_Membership_Rule_Delayable && $delay_data ) {

						$delay = $factory->make_delay_rule( $rule['delay-type'], $membership, $rule_model );

						if ( $delay ) {
							try {
								$delay->save( $delay_data );
							}
							catch ( Exception $e ) {
								$errors[] = $e->getMessage();
							}
						}
					}

					do_action( 'it_exchange_membership_addon_update_content_access_rules_options', $product_id, $rule['selected'], $rule['selection'], $rule['term'] );

				} elseif ( ! isset( $rule['group'] ) || ! isset( $rule['group_id'] ) ) {
					//This should only happen if the user adds a new rule but doesn't make a selection
					unset( $_REQUEST['it_exchange_content_access_rules'][ $key ] );
				}
			}

			it_exchange_update_product_feature( $product_id, 'membership-content-access-rules', $_REQUEST['it_exchange_content_access_rules'] );

		} else {
			it_exchange_update_product_feature( $product_id, 'membership-content-access-rules', array() );
		}

		foreach ( $existing_rules as $rule ) {
			if ( in_array( $rule->get_rule_id(), $saved_ids ) ) {
				continue;
			}

			$rule->delete();

			do_action( 'it_exchange_membership_addon_update_content_access_diff_rules_options', $product_id, $rule->get_type(), $rule->get_selection(), $rule->get_term() );
		}
	}

	/**
	 * This updates the feature for a product
	 *
	 * @since 1.0.0
	 *
	 * @param integer $product_id the product id
	 * @param mixed   $new_value  the new value
	 */
	public function save_feature( $product_id, $new_value ) {

		if ( ! it_exchange_get_product( $product_id ) ) {
			return;
		}

		update_post_meta( $product_id, '_it-exchange-membership-addon-content-access-meta', $new_value );
	}

	/**
	 * Return the product's features
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $existing   the values passed in by the WP Filter API. Ignored here.
	 * @param int   $product_id the WordPress post ID
	 *
	 * @return string product feature
	 */
	public function get_feature( $existing, $product_id ) {
		$content_access = get_post_meta( $product_id, '_it-exchange-membership-addon-content-access-meta', true );

		if ( empty( $content_access ) ) {
			return array();
		}

		$resave = false;

		foreach ( $content_access as &$rule ) {
			if ( ! isset( $rule['id'] ) ) {
				$rule['id'] = md5( serialize( $rule ) . uniqid() ); // this isn't meant to be secure, just an easy way to get a random ID
				$resave     = true;
			}
		}

		if ( $resave ) {
			$this->save_feature( $product_id, $content_access );
		}

		return $content_access;
	}

	/**
	 * Does the product have the feature?
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $result Not used by core
	 * @param int   $product_id
	 *
	 * @return boolean
	 */
	public function product_has_feature( $result, $product_id ) {
		// Does this product type support this feature?
		if ( false === $this->product_supports_feature( false, $product_id ) ) {
			return false;
		}

		// If it does support, does it have it?
		return (boolean) $this->get_feature( false, $product_id );
	}

	/**
	 * Does the product support this feature?
	 *
	 * This is different than if it has the feature, a product can
	 * support a feature but might not have the feature set.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $result Not used by core
	 * @param int   $product_id
	 *
	 * @return boolean
	 */
	public function product_supports_feature( $result, $product_id ) {
		// Does this product type support this feature?
		$product_type = it_exchange_get_product_type( $product_id );
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'membership-content-access-rules' ) ) {
			return false;
		}

		return true;
	}
}

$IT_Exchange_Addon_Membership_Product_Feature_Content_Access = new IT_Exchange_Addon_Membership_Product_Feature_Content_Access();
