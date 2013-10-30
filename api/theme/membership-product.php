<?php
/**
 * Member Dashboard class for THEME API in Membership Add-on
 *
 * @since CHANGEME
*/

class IT_Theme_API_Membership_Product implements IT_Theme_API {
	
	/**
	 * API context
	 * @var string $_context
	 * @since CHANGEME
	*/
	private $_context = 'membership-product';

	/**
	 * Current product in iThemes Exchange Global
	 * @var object $product
	 * @since 0.4.0
	*/
	private $product;
	
	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since CHANGEME
	*/
	public $_tag_map = array(
		'intendedaudience' => 'intended_audience',
		'objectives'       => 'objectives',
		'prerequisites'    => 'prerequisites',
	);

	/**
	 * Constructor
	 *
	 * @since CHANGEME
	 *
	 * @return void
	*/
	function IT_Theme_API_Membership_Product() {
		// Set the current global product as a property
		$this->product = empty( $GLOBALS['it_exchange']['product'] ) ? false : $GLOBALS['it_exchange']['product'];
	}

	/**
	 * Returns the context. Also helps to confirm we are an iThemes Exchange theme API class
	 *
	 * @since CHANGEME
	 * 
	 * @return string
	*/
	function get_api_context() {
		return $this->_context;
	}

	/**
	 * @since CHANGEME
	 * @return string
	*/
	function intended_audience( $options=array() ) {
		$result = '';
		$membership_settings = it_exchange_get_option( 'addon_membership' );
		
		$defaults      = array(
			'before'       => '',
			'after'        => '',
			'label'        => $membership_settings['membership-intended-audience-label'],
			'before_label' => '<h3>',
			'after_label'  => '</h3>',
			'before_desc'  => '<p>',
			'after_desc'   => '</p>',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
				
		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'membership-information' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'membership-information', array( 'setting' => 'intended-audience' ) );

		// Repeats checks for when flags were not passed.
		if ( it_exchange_product_supports_feature( $this->product->ID, 'membership-information' )	
				&& it_exchange_product_has_feature( $this->product->ID, 'membership-information', array( 'setting' => 'intended-audience' ) ) ) {
			
			$description = it_exchange_get_product_feature( $this->product->ID, 'membership-information', array( 'setting' => 'intended-audience' ) );
			
			if ( !empty( $description ) ) {
			
				$result .= $options['before'];
				$result .= $options['before_label'] . $options['label'] . $options['after_label'];
				$description = wpautop( $description );
				$description = shortcode_unautop( $description );
				$description = do_shortcode( $description );
				$result .= $options['before_desc'] . $description . $options['after_desc'];
				$result .= $options['after'];
				
			}
			
		}
		
		return $result;
	}

	/**
	 * @since CHANGEME
	 * @return string
	*/
	function objectives( $options=array() ) {
		$result = '';
		$membership_settings = it_exchange_get_option( 'addon_membership' );
		
		$defaults      = array(
			'before'       => '',
			'after'        => '',
			'label'        => $membership_settings['membership-objectives-label'],
			'before_label' => '<h3>',
			'after_label'  => '</h3>',
			'before_desc'  => '<p>',
			'after_desc'   => '</p>',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
				
		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'membership-information' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'membership-information', array( 'setting' => 'objectives' ) );

		// Repeats checks for when flags were not passed.
		if ( it_exchange_product_supports_feature( $this->product->ID, 'membership-information' )	
				&& it_exchange_product_has_feature( $this->product->ID, 'membership-information', array( 'setting' => 'objectives' ) ) ) {
			
			$description = it_exchange_get_product_feature( $this->product->ID, 'membership-information', array( 'setting' => 'objectives' ) );
			
			if ( !empty( $description ) ) {
			
				$result .= $options['before'];
				$result .= $options['before_label'] . $options['label'] . $options['after_label'];
				$description = wpautop( $description );
				$description = shortcode_unautop( $description );
				$description = do_shortcode( $description );
				$result .= $options['before_desc'] . $description . $options['after_desc'];
				$result .= $options['after'];
				
			}
			
		}
		
		return $result;
	}

	/**
	 * @since CHANGEME
	 * @return string
	*/
	function prerequisites( $options=array() ) {
		$result = '';
		$membership_settings = it_exchange_get_option( 'addon_membership' );
		
		$defaults      = array(
			'before'       => '',
			'after'        => '',
			'label'        => $membership_settings['membership-prerequisites-label'],
			'before_label' => '<h3>',
			'after_label'  => '</h3>',
			'before_desc'  => '<p>',
			'after_desc'   => '</p>',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
				
		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'membership-information' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'membership-information', array( 'setting' => 'prerequisites' ) );

		// Repeats checks for when flags were not passed.
		if ( it_exchange_product_supports_feature( $this->product->ID, 'membership-information' )	
				&& it_exchange_product_has_feature( $this->product->ID, 'membership-information', array( 'setting' => 'prerequisites' ) ) ) {
			
			$description = it_exchange_get_product_feature( $this->product->ID, 'membership-information', array( 'setting' => 'prerequisites' ) );
			
			if ( !empty( $description ) ) {
			
				$result .= $options['before'];
				$result .= $options['before_label'] . $options['label'] . $options['after_label'];
				$description = wpautop( $description );
				$description = shortcode_unautop( $description );
				$description = do_shortcode( $description );
				$result .= $options['before_desc'] . $description . $options['after_desc'];
				$result .= $options['after'];
				
			}
			
		}
		
		return $result;
	}
}
