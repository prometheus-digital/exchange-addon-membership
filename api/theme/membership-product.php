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
		
		$defaults      = array(
			'before' => '',
			'after'  => '',
			'before_label' => '<h3>',
			'after_label'  => '</h3>',
			'before_desc' => '<p>',
			'after_desc'  => '</p>',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
				
		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'membership-intended-audience' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'membership-intended-audience' );

		// Repeats checks for when flags were not passed.
		if ( it_exchange_product_supports_feature( $this->product->ID, 'membership-intended-audience' )	
				&& it_exchange_product_has_feature( $this->product->ID, 'membership-intended-audience' ) ) {
			
			$label = it_exchange_get_product_feature( $this->product->ID, 'membership-intended-audience', array( 'setting' => 'label' ) );
			$description = it_exchange_get_product_feature( $this->product->ID, 'membership-intended-audience', array( 'setting' => 'description' ) );
			
			if ( !empty( $description ) ) {
			
				$result .= $options['before'];
				$result .= $options['before_label'] . $label . $options['after_label'];
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
		
		$defaults      = array(
			'before' => '',
			'after'  => '',
			'before_label' => '<h3>',
			'after_label'  => '</h3>',
			'before_desc' => '<p>',
			'after_desc'  => '</p>',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
				
		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'membership-objectives' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'membership-objectives' );

		// Repeats checks for when flags were not passed.
		if ( it_exchange_product_supports_feature( $this->product->ID, 'membership-objectives' )	
				&& it_exchange_product_has_feature( $this->product->ID, 'membership-objectives' ) ) {
			
			$label = it_exchange_get_product_feature( $this->product->ID, 'membership-objectives', array( 'setting' => 'label' ) );
			$description = it_exchange_get_product_feature( $this->product->ID, 'membership-objectives', array( 'setting' => 'description' ) );
			
			if ( !empty( $description ) ) {
			
				$result .= $options['before'];
				$result .= $options['before_label'] . $label . $options['after_label'];
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
		
		$defaults      = array(
			'before' => '',
			'after'  => '',
			'before_label' => '<h3>',
			'after_label'  => '</h3>',
			'before_desc' => '<p>',
			'after_desc'  => '</p>',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );
				
		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'membership-prerequisites' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'membership-prerequisites' );

		// Repeats checks for when flags were not passed.
		if ( it_exchange_product_supports_feature( $this->product->ID, 'membership-prerequisites' )	
				&& it_exchange_product_has_feature( $this->product->ID, 'membership-prerequisites' ) ) {
			
			$label = it_exchange_get_product_feature( $this->product->ID, 'membership-prerequisites', array( 'setting' => 'label' ) );
			$description = it_exchange_get_product_feature( $this->product->ID, 'membership-prerequisites', array( 'setting' => 'description' ) );
			
			if ( !empty( $description ) ) {
			
				$result .= $options['before'];
				$result .= $options['before_label'] . $label . $options['after_label'];
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
