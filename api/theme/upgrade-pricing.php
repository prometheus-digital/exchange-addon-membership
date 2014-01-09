<?php
/**
 * Upgrade Pricing class for THEME API
 *
 * @since 1.0.0
*/

class IT_Theme_API_Upgrade_Pricing implements IT_Theme_API {
	
	/**
	 * API context
	 * @var string $_context
	 * @since 1.0.0
	*/
	private $_context = 'upgrade-pricing';

	/**u
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 1.0.0
	*/
	var $_tag_map = array(
		'baseprice'        => 'base_price',
	);

	/**
	 * Current product in iThemes Exchange Global
	 * @var object $product
	 * @since 1.0.0
	*/
	private $product;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @return void
	*/
	function IT_Theme_API_Upgrade_Pricing() {
		// Set the current global product as a property
		$this->product = empty( $GLOBALS['it_exchange']['product'] ) ? false : $GLOBALS['it_exchange']['product'];
	}

	/**
	 * Returns the context. Also helps to confirm we are an iThemes Exchange theme API class
	 *
	 * @since 1.0.0
	 * 
	 * @return string
	*/
	function get_api_context() {
		return $this->_context;
	}

	/**
	 * The product base price
	 *
	 * @since 1.0.0
	 * @return mixed
	*/
	function base_price( $options=array() ) {

		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'membership-hierarchy' );
			
		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'membership-hierarchy' );

		if ( it_exchange_product_supports_feature( $this->product->ID, 'membership-hierarchy' )
				&& it_exchange_product_has_feature( $this->product->ID, 'membership-hierarchy' ) {
			
			$defaults   = array(
				'before'        => '<span class="it-exchange-base-price">',
				'after'         => '</span>',
				'format'        => 'html',
				'free-label'    => __( 'Free', 'LION' ),
			);
			$options = ITUtility::merge_defaults( $options, $defaults );
				
			$child_ids = setup_recursive_member_access_array( array( $this->product->ID ) );
			
			if ( !empty( $child_ids ) ) {
			
				$base_price = it_exchange_get_product_feature( $this->product->ID, 'base-price' );
				$prorated_price = 0;
				
				$parent_memberships = it_exchange_get_session_data( 'parent_access' );
				
				foreach( $parent_memberships as $product_id ) {
					if ( in_array( $product_id, $child_ids ) ) {
						
						break;
					}
				}
						
				$addon_settings = it_exchange_get_option( 'addon_customer_pricing' );
	
				$result     = '';
				$db_price = 0;
				$price_options = it_exchange_get_product_feature( $this->product->ID, 'customer-pricing', array( 'setting' => 'pricing-options' ) );
				$nyop_enabled = it_exchange_get_product_feature( $this->product->ID, 'customer-pricing', array( 'setting' => 'nyop_enabled' ) );
				
				if ( 'yes' === $nyop_enabled ) {
					$nyop_min_price = it_exchange_convert_from_database_number( it_exchange_get_product_feature( $this->product->ID, 'customer-pricing', array( 'setting' => 'nyop_min' ) ) );
					if ( 0 == $base_price || $nyop_min_price < $base_price )
						$base_price = $nyop_min_price;
				}
				
				if ( !empty( $price_options ) ) {
					foreach( $price_options as $price_option ) {
						$db_price = $price_option['price'];
						$price = it_exchange_convert_from_database_number( $price_option['price'] );
						if ( 0 == $base_price || $price < $base_price )
							$base_price = $price;
						if ( 'checked' === $price_option['default'] ) {
							$base_price = $price;
							break;
						}
					}
				}
				
				$price    = empty( $base_price ) ? '<span class="free-label">' . $options['free-label'] . '</span>' : it_exchange_format_price( $base_price );
				$price    = ( empty( $options['free-label'] ) && empty( $base_price ) ) ? it_exchange_format_price( $base_price ) : $price;
	
				if ( 'html' == $options['format'] )
					$result .= $options['before'];
	
				$result .= $price . ( $options['show-or-more'] ? ' <span class="customer-pricing-or-more-label">' . $options['or-more-label'] . '</span>' : '' );
	
				if ( 'html' == $options['format'] )
					$result .= $options['after'];
				$result .= '<input type="hidden" class="it-exchange-customer-pricing-new-base-price" name="it-exchange-customer-pricing-new-base-price" value="' . $db_price . '">';
	
				return $result;
			}
		
		}

		return false;
	}
}
