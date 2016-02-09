<?php

/**
 * Restricted class for THEME API in Membership Add-on
 *
 * @since 1.0.0
 */
class IT_Theme_API_Restricted implements IT_Theme_API {

	/**
	 * API context
	 * @var string $_context
	 * @since 1.0.0
	 */
	private $_context = 'restricted';

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
	function IT_Theme_API_Restricted() {
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
	 * Override the post content with the restriction message.
	 *
	 * If enabled, includes the post excerpt before the message.
	 *
	 * @since 1.0.0
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function content( $options = array() ) {

		global $post;

		$membership_settings = it_exchange_get_option( 'addon_membership' );

		$rules   = it_exchange_get_global( 'membership_failed_rules' );
		$message = $this->get_message( is_array( $rules ) ? $rules : array() );

		$defaults = array(
			'before'  => '',
			'after'   => '',
			'message' => $message,
			'class'   => 'it-exchange-membership-restricted-content',
		);
		$options  = ITUtility::merge_defaults( $options, $defaults );

		$content = $options['before'];

		if ( $membership_settings['membership-restricted-show-excerpt'] ) {

			if ( ! empty( $post->post_excerpt ) ) {
				$excerpt = $post->post_excerpt;
			} else if ( ! empty( $post->post_content ) ) {
				$excerpt        = $post->post_content;
				$excerpt        = str_replace( ']]>', ']]&gt;', $excerpt );
				$excerpt_length = apply_filters( 'excerpt_length', 55 );
				$excerpt_more   = apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );
				$excerpt        = wp_trim_words( $excerpt, $excerpt_length, $excerpt_more );
			}

			if ( ! empty( $excerpt ) ) {
				$excerpt = wp_trim_excerpt( $excerpt );
			} else {
				$excerpt = '';
			}

			$content .= '<p class="it-exchange-membership-content-excerpt">';
			$content .= $excerpt;
			$content .= '</p>';
		}

		$content .= '<p class="' . $options['class'] . '">' . $options['message'] . '</p>';
		$content .= $options['after'];

		return $content;
	}

	/**
	 * Override the post excerpt with the restriction message.
	 *
	 * If enabled, includes the post excerpt.
	 *
	 * @since 1.0.0
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function excerpt( $options = array() ) {

		global $post;

		$membership_settings = it_exchange_get_option( 'addon_membership' );

		$rules   = it_exchange_get_global( 'membership_failed_rules' );
		$message = $this->get_message( is_array( $rules ) ? $rules : array() );

		$defaults = array(
			'before'  => '',
			'after'   => '',
			'message' => $message,
			'class'   => 'it-exchange-membership-restricted-excerpt',
		);
		$options  = ITUtility::merge_defaults( $options, $defaults );

		$content = $options['before'];

		if ( $membership_settings['membership-restricted-show-excerpt'] ) {
			if ( ! empty( $post->post_excerpt ) ) {
				$excerpt = $post->post_excerpt;
			} else if ( ! empty( $post->post_content ) ) {
				$excerpt        = $post->post_content;
				$excerpt        = str_replace( ']]>', ']]&gt;', $excerpt );
				$excerpt_length = apply_filters( 'excerpt_length', 55 );
				$excerpt_more   = apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );
				$excerpt        = wp_trim_words( $excerpt, $excerpt_length, $excerpt_more );
			}
			if ( ! empty( $excerpt ) ) {
				$excerpt = wp_trim_excerpt( $excerpt );
			} else {
				$excerpt = '';
			}
			$content .= '<p class="it-exchange-membership-content-excerpt">';
			$content .= $excerpt;
			$content .= '</p>';
		}

		$content .= '<p class="' . $options['class'] . '">' . $options['message'] . '</p>';
		$content .= $options['after'];

		return $content;
	}

	/**
	 * Retrieve the product restriction message.
	 *
	 * @since 1.0.0
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function product( $options = array() ) {

		$rules   = it_exchange_get_global( 'membership_failed_rules' );
		$message = $this->get_message( is_array( $rules ) ? $rules : array(), true );

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
	 * Get a unique list of products from the given rules.
	 *
	 * @since 1.18
	 *
	 * @param IT_Exchange_Membership_Content_RuleInterface[] $rules
	 *
	 * @return IT_Exchange_Membership[]
	 */
	protected function get_products( array $rules ) {
		$products = array();

		foreach ( $rules as $rule ) {
			if ( $rule->get_membership() ) {
				$products[ $rule->get_membership()->ID ] = $rule->get_membership();
			}
		}

		return $products;
	}

	/**
	 * Get the restriction message.
	 *
	 * @since 1.18
	 *
	 * @param IT_Exchange_Membership_Content_RuleInterface[] $rules
	 * @param bool                                           $product Return the product message.
	 *
	 * @return string
	 */
	protected function get_message( array $rules, $product = false ) {

		$membership_settings = it_exchange_get_option( 'addon_membership' );

		$products = $this->get_products( $rules );

		if ( $product ) {
			if ( ! empty( $membership_settings['restricted-product-message'] ) ) {
				$message = $membership_settings['restricted-product-message'];
			} else {
				$message = $membership_settings['membership-restricted-product-message'];
			}
		} else {
			if ( ! empty( $membership_settings['restricted-content-message'] ) ) {
				$message = $membership_settings['restricted-content-message'];
			} else {
				$message = $membership_settings['membership-restricted-content-message'];
			}
		}

		// if more than one product, we use the global message.
		if ( count( $products ) == 1 && ! $product ) {

			/** @var IT_Exchange_Membership $membership */
			$membership = reset( $products );

			if ( $membership->has_feature( 'membership-information', array( 'setting' => 'content-restricted' ) ) ) {
				$message = $membership->get_feature( 'membership-information', array( 'setting' => 'content-restricted' ) );
			}
		}

		if ( strpos( $message, '{products}' ) !== false ) {
			$message = str_replace( '{products}', $this->product_replacement( $products ), $message );
		}

		return $message;
	}

	/**
	 * Replace the product tag.
	 *
	 * @since 1.18
	 *
	 * @param IT_Exchange_Membership[] $products
	 *
	 * @return string
	 */
	protected function product_replacement( array $products ) {

		$links = array();

		foreach ( $products as $product ) {
			$links[] = '<a href="' . get_permalink( $product->ID ) . '">' . get_the_title( $product->ID ) . '</a>';
		}

		return implode( ', ', $links );
	}
}
