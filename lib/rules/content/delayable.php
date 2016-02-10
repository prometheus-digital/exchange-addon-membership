<?php
/**
 * Delayable interface.
 *
 * @since   1.18
 * @license GPLv2
 */

/**
 * Interface IT_Exchange_Membership_Content_Rule_Delayable
 */
interface IT_Exchange_Membership_Content_Rule_Delayable extends IT_Exchange_Membership_Content_RuleInterface {

	/**
	 * Set this content rule's delay rule.
	 *
	 * @since 1.18
	 *
	 * @param IT_Exchange_Membership_Delay_RuleInterface $delay_rule
	 *
	 * @return self
	 */
	public function set_delay_rule( IT_Exchange_Membership_Delay_RuleInterface $delay_rule );

	/**
	 * Retrieve this content rule's delay rule.
	 *
	 * @since 1.18
	 *
	 * @return IT_Exchange_Membership_Delay_RuleInterface
	 */
	public function get_delay_rule();

	/**
	 * Retrieve the post for a delay rule.
	 *
	 * @since 1.18
	 *
	 * @return WP_Post
	 */
	public function get_post_for_delay();
}