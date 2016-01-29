<?php
/**
 * Contains rule interface.
 *
 * @since   1.18
 * @license GPLv2
 */

/**
 * Interface IT_Exchange_Membership_Content_RuleInterface
 */
interface IT_Exchange_Membership_Content_RuleInterface {

	/**
	 * IT_Exchange_Membership_Content_RuleInterface constructor.
	 *
	 * @param IT_Exchange_Membership $membership
	 */
	public function __construct( IT_Exchange_Membership $membership );

	/**
	 * Evaluate the rule.
	 *
	 * @since 1.18
	 *
	 * @param WP_Post                  $post
	 * @param IT_Exchange_Subscription $subscription
	 *
	 * @return bool True if readable
	 */
	public function evaluate( WP_Post $post, IT_Exchange_Subscription $subscription );

	/**
	 * Get HTML to render the necessary form fields.
	 *
	 * @since 1.18
	 *
	 * @param string $context Context to preface field name attributes.
	 *
	 * @return string
	 */
	public function get_field_html( $context );

	/**
	 * Save the fields.
	 *
	 * @since 1.18
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function save( $data );

	/**
	 * String representation of this rule.
	 *
	 * Ex. This content will be accessible in 5 days.
	 *
	 * @since 1.18
	 *
	 * @return string
	 */
	public function __toString();
}