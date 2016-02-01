<?php
/**
 * Delay rule interface.
 *
 * @since   1.18
 * @license GPLv2
 */

/**
 * Interface IT_Exchange_Membership_Delay_RuleInterface
 *
 * Delay rules are saved against the individual post object.
 */
interface IT_Exchange_Membership_Delay_RuleInterface extends IT_Exchange_Membership_RuleInterface {

	/**
	 * Evaluate the rule.
	 *
	 * @since 1.18
	 *
	 * @param IT_Exchange_Subscription $subscription
	 * @param WP_Post                  $post
	 *
	 * @return bool True if readable
	 */
	public function evaluate( IT_Exchange_Subscription $subscription, WP_Post $post = null );

	/**
	 * Save the data to the post.
	 *
	 * @since 1.18
	 *
	 * @param array $data
	 *
	 * @return bool
	 *
	 * @throws UnexpectedValueException
	 * @throws InvalidArgumentException
	 */
	public function save( array $data );
}