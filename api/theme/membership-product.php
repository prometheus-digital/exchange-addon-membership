<?php

/**
 * Member Dashboard class for THEME API in Membership Add-on
 *
 * @since 1.0.0
 */
class IT_Theme_API_Membership_Product implements IT_Theme_API {

	/**
	 * API context
	 * @var string $_context
	 * @since 1.0.0
	 */
	private $_context = 'membership-product';

	/**
	 * Current product in iThemes Exchange Global
	 * @var IT_Exchange_Product $product
	 * @since 0.4.0
	 */
	private $product;

	/**
	 * @var ITE_Prorate_Credit_Requestor
	 */
	private $requestor;

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 1.0.0
	 */
	public $_tag_map = array(
		'intendedaudience' => 'intended_audience',
		'objectives'       => 'objectives',
		'prerequisites'    => 'prerequisites',
		'upgradedetails'   => 'upgrade_details',
		'downgradedetails' => 'downgrade_details',
	);

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	function __construct() {
		// Set the current global product as a property
		$this->product = empty( $GLOBALS['it_exchange']['product'] ) ? false : $GLOBALS['it_exchange']['product'];

		$requestor = new ITE_Prorate_Credit_Requestor( new ITE_Daily_Price_Calculator() );
		$requestor->register_provider( 'IT_Exchange_Subscription' );
		$requestor->register_provider( 'IT_Exchange_Transaction' );

		$this->requestor = $requestor;
	}

	/**
	 * Deprecated Constructor
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function IT_Theme_API_Membership_Product() {
		self::__construct();
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
	 * @since 1.0.0
	 * @return string
	 */
	function intended_audience( $options = array() ) {
		$result              = '';
		$membership_settings = it_exchange_get_option( 'addon_membership' );

		$defaults = array(
			'before'       => '',
			'after'        => '',
			'label'        => $membership_settings['membership-intended-audience-label'],
			'before_label' => '<h3>',
			'after_label'  => '</h3>',
			'before_desc'  => '<p>',
			'after_desc'   => '</p>',
		);
		$options  = ITUtility::merge_defaults( $options, $defaults );

		// Return boolean if has flag was set
		if ( $options['supports'] ) {
			return it_exchange_product_supports_feature( $this->product->ID, 'membership-information' );
		}

		// Return boolean if has flag was set
		if ( $options['has'] ) {
			return it_exchange_product_has_feature( $this->product->ID, 'membership-information', array( 'setting' => 'intended-audience' ) );
		}

		// Repeats checks for when flags were not passed.
		if ( it_exchange_product_supports_feature( $this->product->ID, 'membership-information' )
		     && it_exchange_product_has_feature( $this->product->ID, 'membership-information', array( 'setting' => 'intended-audience' ) )
		) {

			$description = it_exchange_get_product_feature( $this->product->ID, 'membership-information', array( 'setting' => 'intended-audience' ) );

			if ( ! empty( $description ) ) {

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
	 * @since 1.0.0
	 * @return string
	 */
	function objectives( $options = array() ) {
		$result              = '';
		$membership_settings = it_exchange_get_option( 'addon_membership' );

		$defaults = array(
			'before'       => '',
			'after'        => '',
			'label'        => $membership_settings['membership-objectives-label'],
			'before_label' => '<h3>',
			'after_label'  => '</h3>',
			'before_desc'  => '<p>',
			'after_desc'   => '</p>',
		);
		$options  = ITUtility::merge_defaults( $options, $defaults );

		// Return boolean if has flag was set
		if ( $options['supports'] ) {
			return it_exchange_product_supports_feature( $this->product->ID, 'membership-information' );
		}

		// Return boolean if has flag was set
		if ( $options['has'] ) {
			return it_exchange_product_has_feature( $this->product->ID, 'membership-information', array( 'setting' => 'objectives' ) );
		}

		// Repeats checks for when flags were not passed.
		if ( it_exchange_product_supports_feature( $this->product->ID, 'membership-information' )
		     && it_exchange_product_has_feature( $this->product->ID, 'membership-information', array( 'setting' => 'objectives' ) )
		) {

			$description = it_exchange_get_product_feature( $this->product->ID, 'membership-information', array( 'setting' => 'objectives' ) );

			if ( ! empty( $description ) ) {

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
	 * @since 1.0.0
	 * @return string
	 */
	function prerequisites( $options = array() ) {
		$result              = '';
		$membership_settings = it_exchange_get_option( 'addon_membership' );

		$defaults = array(
			'before'       => '',
			'after'        => '',
			'label'        => $membership_settings['membership-prerequisites-label'],
			'before_label' => '<h3>',
			'after_label'  => '</h3>',
			'before_desc'  => '<p>',
			'after_desc'   => '</p>',
		);
		$options  = ITUtility::merge_defaults( $options, $defaults );

		// Return boolean if has flag was set
		if ( $options['supports'] ) {
			return it_exchange_product_supports_feature( $this->product->ID, 'membership-information' );
		}

		// Return boolean if has flag was set
		if ( $options['has'] ) {
			return it_exchange_product_has_feature( $this->product->ID, 'membership-information', array( 'setting' => 'prerequisites' ) );
		}

		// Repeats checks for when flags were not passed.
		if ( it_exchange_product_supports_feature( $this->product->ID, 'membership-information' )
		     && it_exchange_product_has_feature( $this->product->ID, 'membership-information', array( 'setting' => 'prerequisites' ) )
		) {

			$description = it_exchange_get_product_feature( $this->product->ID, 'membership-information', array( 'setting' => 'prerequisites' ) );

			if ( ! empty( $description ) ) {

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
	function upgrade_details( $options = array() ) {

		$result = '';

		$defaults = array(
			'before'      => '',
			'after'       => '',
			'before_desc' => '<p class="description">',
			'after_desc'  => '</p>',
		);
		$options  = ITUtility::merge_defaults( $options, $defaults );

		// Return boolean if has flag was set
		if ( $options['supports'] ) {
			return it_exchange_product_supports_feature( $this->product->ID, 'membership-hierarchy' );
		}

		// Return boolean if has flag was set
		if ( $options['has'] ) {
			return it_exchange_product_has_feature( $this->product->ID, 'membership-hierarchy', array( 'setting' => 'children' ) );
		}

		// Repeats checks for when flags were not passed.
		if ( ! $this->product->supports_feature( 'membership-hierarchy' ) || ! $this->product->has_feature( 'membership-hierarchy', array( 'setting' => 'children' ) ) ) {
			return $result;
		}

		$child_ids = it_exchange_membership_addon_setup_recursive_member_access_array( array( $this->product->ID => '' ) );

		if ( empty( $child_ids ) ) {
			return $result;
		}

		$most_priciest        = 0;
		$most_priciest_txn_id = 0;
		$most_producty        = null;

		$parent_memberships = it_exchange_get_session_data( 'parent_access' );

		foreach ( $parent_memberships as $parent_id => $txn_id ) {
			if ( $parent_id != $this->product->ID && isset( $child_ids[ $parent_id ] ) ) {
				$product                   = it_exchange_get_product( $parent_id );
				$parent_product_base_price = it_exchange_get_product_feature( $parent_id, 'base-price' );
				$db_price                  = it_exchange_convert_to_database_number( $parent_product_base_price );

				if ( $db_price > $most_priciest ) {
					$most_priciest        = $db_price;
					$most_priciest_txn_id = $txn_id;
					$most_producty        = $product;
				}
			}
		}

		if ( empty( $most_priciest_txn_id ) || empty( $most_producty ) ) {
			return $result;
		}

		$transaction = it_exchange_get_transaction( $most_priciest_txn_id );

		try {
			$subscription = it_exchange_get_subscription_by_transaction( $transaction, $most_producty );

			if ( $subscription ) {
				$request = new ITE_Prorate_Subscription_Credit_Request( $subscription, $this->product );
			}
		}
		catch ( InvalidArgumentException $e ) {
		}

		if ( empty( $request ) ) {
			$request = new ITE_Prorate_Forever_Credit_Request( $most_producty, $this->product, $transaction );
		}

		$this->requestor->request_upgrade( $request );

		if ( $request->get_upgrade_type() === 'days' ) {
			$days   = $request->get_free_days();
			$result = sprintf( _n( '%d days free, then regular price', '%d day free, then regular price', $days, 'LION' ), $days );
		} else if ( $request->get_upgrade_type() === 'credit' ) {
			$credit = it_exchange_format_price( $request->get_credit() );
			$result = sprintf( __( '%s upgrade credit, then regular price', 'LION' ), $credit );
		}

		if ( trim( $result ) !== '' ) {
			$result = $options['before_desc'] . $result . $options['after_desc'];
		}

		return trim( $result );
	}

	/**
	 * @since CHANGEME
	 * @return string
	 */
	function downgrade_details( $options = array() ) {

		$result = '';

		$defaults = array(
			'before'      => '',
			'after'       => '',
			'before_desc' => '<p class="description">',
			'after_desc'  => '</p>',
		);
		$options  = ITUtility::merge_defaults( $options, $defaults );

		// Return boolean if has flag was set
		if ( $options['supports'] ) {
			return it_exchange_product_supports_feature( $this->product->ID, 'membership-hierarchy' );
		}

		// Return boolean if has flag was set
		if ( $options['has'] ) {
			return it_exchange_product_has_feature( $this->product->ID, 'membership-hierarchy', array( 'setting' => 'parents' ) );
		}

		if ( ! $this->product->supports_feature( 'membership-hierarchy' ) || ! $this->product->has_feature( 'membership-hierarchy', array( 'setting' => 'parents' ) ) ) {
			return $result;
		}

		$parent_access_session = it_exchange_get_session_data( 'parent_access' );

		if ( empty( $parent_access_session ) || array_key_exists( $this->product->ID, $parent_access_session ) ) {
			return $result;
		}

		$most_parent = it_exchange_membership_addon_get_most_parent_from_member_access( $this->product->ID, $parent_access_session );

		if ( false === $most_parent ) {
			return $result;
		}

		$most_priciest_txn_id = $parent_access_session[ $most_parent ];
		$most_producty        = it_exchange_get_product( $most_parent );

		if ( empty( $most_priciest_txn_id ) ) {
			return $result;
		}

		$transaction = it_exchange_get_transaction( $most_priciest_txn_id );

		try {
			$subscription = it_exchange_get_subscription_by_transaction( $transaction, $most_producty );

			if ( $subscription ) {
				$request = new ITE_Prorate_Subscription_Credit_Request( $subscription, $this->product );
			}
		}
		catch ( InvalidArgumentException $e ) {
		}

		if ( empty( $request ) ) {
			$request = new ITE_Prorate_Forever_Credit_Request( $most_producty, $this->product, $transaction );
		}

		$this->requestor->request_downgrade( $request );

		if ( $request->get_upgrade_type() === 'days' ) {
			$days   = $request->get_free_days();
			$result = sprintf( _n( '%d days free, then regular price', '%d day free, then regular price', $days, 'LION' ), $days );
		} else if ( $request->get_upgrade_type() === 'credit' ) {
			$credit = it_exchange_format_price( $request->get_credit() );
			$result = sprintf( __( '%s downgrade credit, then regular price', 'LION' ), $credit );
		}

		if ( trim( $result ) !== '' ) {
			$result = $options['before_desc'] . $result . $options['after_desc'];
		}

		return trim( $result );
	}
}
