<?php
/**
 * Main Umbrella Membership product feature.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2015 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace ITEGMS\Product_Feature;

use ITEGMS\Plugin;

/**
 * Class Umbrella_Membership
 *
 * @package ITEGMS\Product_Feature
 */
class Umbrella_Membership extends \IT_Exchange_Product_Feature_Abstract {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$args = array(
			'slug'          => 'umbrella-membership',
			'description'   => __( "Allow for customers to purchase a multi-seat membership.", 'LION' ),
			'metabox_title' => __( "Umbrella Memberships", 'LION' ),
			'product_types' => array( 'membership-product-type' )
		);

		parent::__construct( $args );
	}

	/**
	 * This echos the feature metabox.
	 *
	 * @since 1.0
	 *
	 * @param \WP_Post $post
	 */
	function print_metabox( $post ) {
		$data = it_exchange_get_product_feature( isset( $post->ID ) ? $post->ID : 0, $this->slug );
		?>

		<p><?php echo $this->description; ?></p>

		<p>
			<input type="checkbox" id="itegms-discount-disable" name="itegms[enable]" <?php checked( true, $data['enable'] ); ?>>
			<label for="itegms-discount-disable"><?php _e( "Enable umbrella memberships for this product." ); ?></label>
		</p>

		<?php
	}

	/**
	 * This saves the value.
	 *
	 * @since 1.0
	 */
	public function save_feature_on_product_save() {

		// Abort if we don't have a product ID
		$product_id = empty( $_POST['ID'] ) ? false : $_POST['ID'];

		if ( ! $product_id ) {
			return;
		}

		$data = $_POST['itegms'];

		$data['enable'] = isset( $data['enable'] ) ? it_exchange_str_true( $data['enable'] ) : false;

		it_exchange_update_product_feature( $product_id, $this->slug, $data );
	}

	/**
	 * This updates the feature for a product
	 *
	 * @since 1.0
	 *
	 * @param integer $product_id the product id
	 * @param array   $new_value  the new value
	 * @param array   $options
	 *
	 * @return boolean
	 */
	public function save_feature( $product_id, $new_value, $options = array() ) {

		$prev_values = it_exchange_get_product_feature( $product_id, $this->slug );
		$values      = \ITUtility::merge_defaults( $new_value, $prev_values );

		$values['enable'] = (bool) $values['enable'];

		return update_post_meta( $product_id, '_it_exchange_itegms_renewal_discount', $values );
	}

	/**
	 * Return the product's features
	 *
	 * @since 1.0
	 *
	 * @param mixed   $existing   the values passed in by the WP Filter API.
	 *                            Ignored here.
	 * @param integer $product_id the WordPress post ID
	 * @param array   $options
	 *
	 * @return string product feature
	 */
	public function get_feature( $existing, $product_id, $options = array() ) {
		$defaults = array(
			'enable' => false
		);

		$values   = get_post_meta( $product_id, '_it_exchange_itegms_renewal_discount', true );
		$raw_meta = \ITUtility::merge_defaults( $values, $defaults );

		if ( ! isset( $options['field'] ) ) { // if we aren't looking for a particular field
			return $raw_meta;
		}

		$field = $options['field'];

		if ( isset( $raw_meta[ $field ] ) ) { // if the field exists with that name just return it
			return $raw_meta[ $field ];
		} else if ( strpos( $field, "." ) !== false ) { // if the field name was passed using array dot notation
			$pieces  = explode( '.', $field );
			$context = $raw_meta;
			foreach ( $pieces as $piece ) {
				if ( ! is_array( $context ) || ! array_key_exists( $piece, $context ) ) {
					// error occurred
					return null;
				}
				$context = &$context[ $piece ];
			}

			return $context;
		} else {
			return null; // we didn't find the data specified
		}
	}

	/**
	 * Does the product have the feature?
	 *
	 * @since 1.0
	 *
	 * @param mixed   $result Not used by core
	 * @param integer $product_id
	 * @param array   $options
	 *
	 * @return boolean
	 */
	public function product_has_feature( $result, $product_id, $options = array() ) {
		return (bool) it_exchange_get_product_feature( $product_id, $this->slug, array( 'field' => 'enable' ) );
	}

	/**
	 * Does the product support this feature?
	 *
	 * This is different than if it has the feature, a product can
	 * support a feature but might not have the feature set.
	 *
	 * @since 1.0
	 *
	 * @param mixed   $result Not used by core
	 * @param integer $product_id
	 * @param array   $options
	 *
	 * @return boolean
	 */
	public function product_supports_feature( $result, $product_id, $options = array() ) {
		$product_type = it_exchange_get_product_type( $product_id );

		return it_exchange_product_type_supports_feature( $product_type, $this->slug );
	}
}