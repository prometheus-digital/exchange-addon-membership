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
	 * @since    1.18
	 *
	 * @param string $context Context to preface field name attributes.
	 * @param array  $data
	 *
	 * @return string
	 * @internal param IT_Exchange_Membership|null $membership
	 */
	public function get_field_html( $context, array $data = array() );

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

	/**
	 * Check if tis content type is groupable.
	 *
	 * @since 1.18
	 *
	 * @return bool
	 */
	public function is_groupable();

	/**
	 * Get the value this content rule instance represents.
	 *
	 * This is used to build the content access type dropdown.
	 *
	 * @since 1.18
	 *
	 * @return string
	 */
	public function get_value();

	/**
	 * Get the label this content rule instance represents.
	 *
	 * This is used to build the content access type dropdown.
	 *
	 * @since 1.18
	 *
	 * @return string
	 */
	public function get_label();

	/**
	 * Get the type of this restriction.
	 *
	 * @since 1.18
	 *
	 * @param bool $label
	 *
	 * @return string
	 */
	public function get_type( $label = false );
}