<?php
/**
 * This will control membership content access
 *
 * @since 1.0.0
 * @package IT_Exchange_Addon_Membership
*/


class IT_Exchange_Addon_Membership_Product_Feature_Content_Access {

	/**
	 * Constructor. Registers hooks
	 *
	 * @since 1.0.0
	 * @return void
	*/
	function IT_Exchange_Addon_Membership_Product_Feature_Content_Access() {
		if ( is_admin() ) {
			add_action( 'load-post-new.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'load-post.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'it_exchange_save_product', array( $this, 'save_feature_on_product_save' ) );
		}
		add_action( 'it_exchange_enabled_addons_loaded', array( $this, 'add_feature_support_to_product_types' ) );
		add_action( 'it_exchange_update_product_feature_membership-content-access-rules', array( $this, 'save_feature' ), 9, 3 );
		add_filter( 'it_exchange_get_product_feature_membership-content-access-rules', array( $this, 'get_feature' ), 9, 3 );
		add_filter( 'it_exchange_product_has_feature_membership-content-access-rules', array( $this, 'product_has_feature') , 9, 2 );
		add_filter( 'it_exchange_product_supports_feature_membership-content-access-rules', array( $this, 'product_supports_feature') , 9, 2 );
	}

	/**
	 * Register the product feature and add it to enabled product-type addons
	 *
	 * @since 0.4.0
	*/
	function add_feature_support_to_product_types() {
		// Register the product feature
		$slug        = 'membership-content-access';
		$description = __( 'How long a membership should last.', 'LION' );
		it_exchange_register_product_feature( $slug, $description );

		it_exchange_add_feature_support_to_product_type( 'membership-content-access', 'membership-product-type' );
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
			if ( !empty( $product_type ) &&  it_exchange_product_type_supports_feature( $product_type, 'membership-content-access' ) )
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
		add_meta_box( 'it-exchange-product-membership-content-access', __( 'Membership Content Access', 'LION' ), array( $this, 'print_metabox' ), 'it_exchange_prod', 'it_exchange_normal', 'low'  );
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
		$access_rules = it_exchange_get_product_feature( $product->ID, 'membership-content-access-rules' );
		?>
		<div class="it-exchange-content-access-header">
	        <div class="it-exchange-content-access-label-add">
	            <label><?php _e( 'Content Access', 'LION' ); ?> <span class="tip" title="<?php _e( 'Restrict access to your content by membership.', 'LION' ); ?>">i</span></label>
	        </div>
	        <div class="it-exchange-membership-content-access-add-new-rule left">
	            <a href class="button"><?php _e( 'Add New Rule', 'LION' ); ?></a>
	        </div>
		</div>
        <div class="it-exchange-content-access-list-wrapper">
			<div class="it-exchange-content-access-list-titles">
				<div class="it-exchange-content-access-item columns-wrapper">
					<div class="column col-1_4-12"></div>
					<div class="it-exchange-content-access-type column col-3-12">
						<span><?php _e( 'Type', 'LION' ); ?></span>
					</div>
					<div class="it-exchange-content-access-content column col-3-12">
						<span><?php _e( 'Content', 'LION' ); ?></span>
					</div>
				</div>
			</div>
        	<?php $count = 0; ?>
            <div class="it-exchange-membership-addon-content-access-rules">
            <?php
			if ( !empty( $access_rules ) ) {
				foreach( $access_rules as $rule ) {
					
					echo it_exchange_membership_addon_build_content_rule( $rule['selected'], $rule['selection'], $rule['term'], $count++ );
					
				}
			}
			?>
            </div>
        </div>
		<script type="text/javascript" charset="utf-8">
            var it_exchange_membership_addon_content_access_interation = <?php echo $count; ?>;
        </script>
		<?php
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
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'membership-content-access' ) )
			return;
		
		$existing_access_rules = it_exchange_get_product_feature( $product_id, 'membership-content-access-rules' );
		
		if ( ! empty( $_REQUEST['it_exchange_content_access_rules'] ) ) {
			
			foreach( $_REQUEST['it_exchange_content_access_rules'] as $key => $rule ) {
				
				if ( !empty( $rule['selected'] ) && !empty( $rule['selection'] ) && !empty( $rule['term'] ) ) {
				
					switch( $rule['selected'] ) {
					
						case 'posts':
							if ( !( $rules = get_post_meta( $rule['term'], '_item-content-rule', true ) ) )
								$rules = array();
								
							if ( !in_array( $product_id, $rules ) ) {
								$rules[] = $product_id;
								update_post_meta( $rule['term'], '_item-content-rule', $rules );
							}
							break;
							
						case 'post_types':
							if ( !( $rules = get_option( '_item-content-rule-post-type-' . $rule['term'] ) ) )
								$rules = array();
	
							if ( !in_array( $product_id, $rules ) ) {
								$rules[] = $product_id;
								update_option( '_item-content-rule-post-type-' . $rule['term'],  $rules );
							}
							break;
							
						case 'taxonomy':
							if ( !( $rules = get_option( '_item-content-rule-tax-' . $rule['selection'] . '-' . $rule['term'] ) ) )
								$rules = array();
								
							if ( !in_array( $product_id, $rules ) ) {
								$rules[] = $product_id;
								update_option( '_item-content-rule-tax-' . $rule['selection'] . '-' . $rule['term'],  $rules );
							}
							break;
						
					}
				
				} else {
				
					//This should only happen if the user adds a new rule but doesn't make a selection
					unset( $_REQUEST['it_exchange_content_access_rules'][$key] );
					
				}
				
			}
			
			it_exchange_update_product_feature( $product_id, 'membership-content-access-rules', $_REQUEST['it_exchange_content_access_rules'] );
			
		} else {
			
			it_exchange_update_product_feature( $product_id, 'membership-content-access-rules', array() );
			
		}
			
		if ( !empty( $existing_access_rules ) ) {
			
			$updated_access_rules = it_exchange_get_product_feature( $product_id, 'membership-content-access-rules' );
			$diff_access_rules = array();
			
			foreach ( $existing_access_rules as $existing_access_rule ) {
				
				$found = false;
			
				foreach ( $updated_access_rules as $updated_access_rule ) {
				
					if ( $existing_access_rule['selection'] === $updated_access_rule['selection']
						&& $existing_access_rule['selected'] === $updated_access_rule['selected']
						&& $existing_access_rule['term'] === $updated_access_rule['term'] ) {
						$found = true;
						continue;
					}
				
				}
				
				if ( !$found )
					$diff_access_rules[] = $existing_access_rule;
				
			}
						
			if ( ! empty( $diff_access_rules ) ) {
				
				foreach( $diff_access_rules as $rule ) {
				
					switch( $rule['selected'] ) {
					
						case 'posts':
							if ( !( $rules = get_post_meta( $rule['term'], '_item-content-rule', true ) ) )
								$rules = array();
								
							if( false !== $key = array_search( $product_id, $rules ) ) {
								unset( $rules[$key] );
								if ( empty( $rules ) )
									delete_post_meta(  $rule['term'], '_item-content-rule' );
								else
									update_post_meta( $rule['term'], '_item-content-rule', $rules );
							}	
							break;
							
						case 'post_types':
							if ( !( $rules = get_option( '_item-content-rule-post-type-' . $rule['term'] ) ) )
								$rules = array();
								
							if( false !== $key = array_search( $product_id, $rules ) ) {
								unset( $rules[$key] );
								if ( empty( $rules ) )
									delete_option( '_item-content-rule-post-type-' . $rule['term'] );
								else
									update_option( '_item-content-rule-post-type-' . $rule['term'],  $rules );
							}
							break;
							
						case 'taxonomy':
							if ( !( $rules = get_option( '_item-content-rule-tax-' . $rule['selection'] . '-' . $rule['term'] ) ) )
								$rules = array();
								
							if( false !==  $key = array_search( $product_id, $rules ) ) {
								unset( $rules[$key] );
								if ( empty( $rules ) )
									delete_option( '_item-content-rule-tax-' . $rule['selection'] . '-' . $rule['term'] );
								else
									update_option( '_item-content-rule-tax-' . $rule['selection'] . '-' . $rule['term'],  $rules );
							}
							break;
						
					}
					
				}
				
			}

		}
		
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
	function save_feature( $product_id, $new_value ) {
		if ( ! it_exchange_get_product( $product_id ) )
			return false;
			
		update_post_meta( $product_id, '_it-exchange-membership-addon-content-access-meta', $new_value );
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
		return get_post_meta( $product_id, '_it-exchange-membership-addon-content-access-meta', true );
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

		// If it does support, does it have it?
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
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'membership-content-access' ) )
			return false;

		return true;
	}
}
$IT_Exchange_Addon_Membership_Product_Feature_Content_Access = new IT_Exchange_Addon_Membership_Product_Feature_Content_Access();