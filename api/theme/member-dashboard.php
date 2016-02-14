<?php

/**
 * Member Dashboard class for THEME API in Membership Add-on
 *
 * @since 1.0.0
 */
class IT_Theme_API_Member_Dashboard implements IT_Theme_API {

	/**
	 * API context
	 * @var string $_context
	 * @since 1.0.0
	 */
	private $_context = 'member-dashboard';

	/**
	 * Current customer being viewed
	 * @var string $_customer
	 * @since 1.0.0
	 */
	private $_customer = '';


	/**
	 * Current membership product being viewed
	 * @var string $_membership_product
	 * @since 1.0.0
	 */
	private $_membership_product = '';

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 1.0.0
	 */
	public $_tag_map = array(
		'welcomemessage'    => 'welcome_message',
		'membershipcontent' => 'membership_content',
	);

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	function __construct() {
		if ( is_user_logged_in() ) {
			$this->_customer = it_exchange_get_current_customer();
		}

		$this->_membership_product = it_exchange_membership_addon_get_current_membership();
	}

	/**
	 * Deprecated Constructor
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function IT_Theme_API_Member_Dashboard() {
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
	function welcome_message( $options = array() ) {

		if ( empty( $this->_membership_product ) || empty( $this->_membership_product->ID ) ) {
			return false;
		}

		// Return boolean if has flag was set
		if ( $options['supports'] ) {
			return it_exchange_product_supports_feature( $this->_membership_product->ID, 'membership-welcome-message' );
		}

		// Return boolean if has flag was set
		if ( $options['has'] ) {
			return it_exchange_product_has_feature( $this->_membership_product->ID, 'membership-welcome-message' );
		}

		// Repeats checks for when flags were not passed.
		if ( it_exchange_product_supports_feature( $this->_membership_product->ID, 'membership-welcome-message' )
		     && it_exchange_product_has_feature( $this->_membership_product->ID, 'membership-welcome-message' )
		) {
			$result   = false;
			$message  = it_exchange_get_product_feature( $this->_membership_product->ID, 'membership-welcome-message' );
			$defaults = array(
				'before' => '<div class="entry-content">',
				'after'  => '</div>',
				'title'  => __( 'Welcome', 'LION' ),
			);
			$options  = ITUtility::merge_defaults( $options, $defaults );

			$result .= '<h2>' . $options['title'] . '</h2>';
			$result .= $options['before'];
			$result .= $message;
			$result .= $options['after'];

			return $result;
		}

		return false;
	}

	/**
	 * @since 1.0.0
	 * @return string
	 */
	function membership_content( $options = array() ) {

		if ( ! empty( $this->_membership_product ) && 'itememberships' === $this->_membership_product ) {
			if ( $options['has'] ) {
				return true;
			}
			if ( $options['supports'] ) {
				return true;
			}

			$result = '<ul>';
			$result .= it_exchange_membership_addon_append_to_customer_menu_loop();
			$result .= '</ul>';

			return $result;
		}

		if ( empty( $this->_membership_product ) || empty( $this->_membership_product->ID ) ) {
			return false;
		}

		// Return boolean if has flag was set
		if ( $options['has'] ) {
			return it_exchange_product_has_feature( $this->_membership_product->ID, 'membership-content-access-rules' );
		}

		// Return boolean if has flag was set
		if ( $options['supports'] ) {
			return it_exchange_product_supports_feature( $this->_membership_product->ID, 'membership-content-access-rules' );
		}

		$product_id = $this->_membership_product->ID;

		$all_access = it_exchange_membership_addon_setup_recursive_member_access_array( array( $product_id => '' ) );

		if ( empty( $all_access ) ) {
			return false;
		}

		$result = '';

		$membership_settings = it_exchange_get_option( 'addon_membership' );

		$defaults = array(
			'before'             => '<div class="it-exchange-restricted-content">',
			'after'              => '</div>',
			'title'              => __( 'Membership Content', 'LION' ),
			'toggle'             => $membership_settings['memberships-group-toggle'],
			'layout'             => $membership_settings['memberships-dashboard-view'],
			'posts_per_grouping' => 5,
			'child_description'  => '<p class="description">' . sprintf( __( '(Included with %s)', 'LION' ), get_the_title( $product_id ) ) . '</p>',
		);

		$options = ITUtility::merge_defaults( $options, $defaults );

		$count        = 0;
		$memberships  = it_exchange_membership_addon_get_customer_memberships();
		$subscription = it_exchange_get_subscription_by_transaction( it_exchange_get_transaction( $memberships[ $product_id ] ) );
		$factory      = new IT_Exchange_Membership_Rule_Factory();

		foreach ( $all_access as $product_id => $ignore ) {

			$count ++;

			$product = it_exchange_get_product( $product_id );

			if ( ! $product->supports_feature( 'membership-content-access-rules' ) ) {
				continue;
			}

			if ( ! $product->has_feature( 'membership-content-access-rules' ) ) {
				continue;
			}

			$access_rules = $product->get_feature( 'membership-content-access-rules' );

			if ( empty( $access_rules ) ) {
				continue;
			}

			if ( 1 === $count ) {
				$result .= '<h2>' . $options['title'] . '</h2>';
			}

			$renderer       = new IT_Exchange_Membership_Front_Rule_Renderer( $access_rules, $subscription, $factory );
			$render_options = $options;

			$render_options['include_product_title'] = count( $all_access ) > 1;
			$render_options['as_child']              = $count >= 2;

			$result .= $renderer->render( $render_options, $product );
		}

		return $result;
	}
}
