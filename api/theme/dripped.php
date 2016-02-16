<?php

/**
 * Dripped class for THEME API in Membership Add-on
 *
 * @since 1.0.0
 */
class IT_Theme_API_Dripped implements IT_Theme_API {

	/**
	 * API context
	 * @var string $_context
	 * @since 1.0.0
	 */
	private $_context = 'dripped';

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 * @since 1.0.0
	 */
	public $_tag_map = array(
		'content' => 'content',
		'excerpt' => 'excerpt',
		'product' => 'product',
	);

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	function __construct() {
	}

	/**
	 * Deprecated Constructor
	 *
	 * @since 1.0.0
	 */
	function IT_Theme_API_Dripped() {
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
	 * Replace the content with the drip message.
	 *
	 * @since 1.0.0
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	function content( $options = array() ) {

		$failed  = it_exchange_get_global( 'membership_failed_delay' );
		$message = $this->get_message( is_array( $failed ) ? $failed : array(), false );

		$defaults = array(
			'before'  => '',
			'after'   => '',
			'message' => $message,
			'class'   => 'it-exchange-membership-restricted-content',
		);
		$options  = ITUtility::merge_defaults( $options, $defaults );

		$content = $options['before'];
		$content .= '<p class="' . $options['class'] . '">' . $options['message'] . '</p>';
		$content .= $options['after'];

		return $content;
	}

	/**
	 * Replace the excerpt with the drip message.
	 *
	 * @since 1.0.0
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	function excerpt( $options = array() ) {

		$failed  = it_exchange_get_global( 'membership_failed_delay' );
		$message = $this->get_message( is_array( $failed ) ? $failed : array(), false );

		$defaults = array(
			'before'  => '',
			'after'   => '',
			'message' => $message,
			'class'   => 'it-exchange-membership-restricted-excerpt',
		);
		$options  = ITUtility::merge_defaults( $options, $defaults );

		$content = $options['before'];
		$content .= '<p class="' . $options['class'] . '">' . $options['message'] . '</p>';
		$content .= $options['after'];

		return $content;
	}

	/**
	 * Replace the product with the drip message.
	 *
	 * @since 1.0.0
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	function product( $options = array() ) {

		$failed  = it_exchange_get_global( 'membership_failed_delay' );
		$message = $this->get_message( is_array( $failed ) ? $failed : array(), false );

		$defaults = array(
			'before'  => '',
			'after'   => '',
			'message' => $message,
			'class'   => 'it-exchange-membership-restricted-product',
		);
		$options  = ITUtility::merge_defaults( $options, $defaults );

		$content = $options['before'];
		$content .= '<p class="' . $options['class'] . '">' . $options['message'] . '</p>';
		$content .= $options['after'];

		return $content;
	}

	/**
	 * Get the restriction message.
	 *
	 * @since 1.18
	 *
	 * @param array $failed
	 * @param bool  $product Get message for a product.
	 *
	 * @return string
	 */
	protected function get_message( array $failed, $product = false ) {

		$membership_settings = it_exchange_get_option( 'addon_membership' );

		/** @var IT_Exchange_User_MembershipInterface $user_membership */
		$user_membership = $failed['membership'];

		/** @var IT_Exchange_Membership_Delay_RuleInterface $rule */
		$rule = $failed['rule'];

		if ( ! $rule->get_availability_date( $user_membership ) ) {
			return __( 'Your membership is not eligible to receive this content.', 'LION' );
		}

		if ( $product ) {
			if ( ! empty( $membership_settings['dripped-product-message'] ) ) {
				$message = $membership_settings['dripped-product-message'];
			} else {
				$message = $membership_settings['membership-dripped-product-message'];
			}
		} else {
			if ( ! empty( $membership_settings['dripped-content-message'] ) ) {
				$message = $membership_settings['dripped-content-message'];
			} else {
				$message = $membership_settings['membership-dripped-content-message'];
			}
		}

		if ( ! $product && $user_membership->get_membership()->has_feature( 'membership-information', array( 'setting' => 'content-delayed' ) ) ) {
			$message = $user_membership->get_membership()->get_feature( 'membership-information', array( 'setting' => 'content-delayed' ) );
		}

		$available = $rule->get_availability_date( $user_membership );

		if ( strpos( $message, '{available_date}' ) !== false ) {
			$df      = get_option( 'date_format' );
			$message = str_replace( '{available_date}', $available->format( $df ), $message );
		}

		if ( strpos( $message, '{time_until_available}' ) !== false ) {
			$message = str_replace( '{time_until_available}', human_time_diff( $available->format( 'U' ) ), $message );
		}

		if ( strpos( $message, '%d' ) !== false ) {
			$message = sprintf( $message, ceil( ( (int) $available->format( 'U' ) - time() ) / DAY_IN_SECONDS ) );
		}

		return $message;
	}

}
