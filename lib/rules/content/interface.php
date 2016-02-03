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
interface IT_Exchange_Membership_Content_RuleInterface extends IT_Exchange_Membership_RuleInterface {

	/**
	 * Add a delay rule to this content rule.
	 *
	 * @since 1.18
	 *
	 * @param IT_Exchange_Membership_Delay_RuleInterface $delay_rule
	 *
	 * @return self
	 */
	public function add_delay_rule( IT_Exchange_Membership_Delay_RuleInterface $delay_rule );

	/**
	 * Check if this content rule matches a post.
	 *
	 * @since 1.18
	 *
	 * @param WP_Post $post
	 *
	 * @return bool
	 */
	public function matches_post( WP_Post $post );

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
	 * Get the short description for this rule.
	 *
	 * Ex. Category "Protected"
	 *
	 * @since 1.18
	 *
	 * @return string
	 */
	public function get_short_description();

	/**
	 * Get all the delay rules.
	 *
	 * @since 1.18
	 *
	 * @return IT_Exchange_Membership_Delay_RuleInterface[]
	 */
	public function get_delay_rules();
}