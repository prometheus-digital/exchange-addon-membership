<?php
/**
 * Delayable interface.
 *
 * @since   1.18
 * @license GPLv2
 */

/**
 * Interface IT_Exchange_Membership_Rule_Delayable
 */
interface IT_Exchange_Membership_Rule_Delayable extends IT_Exchange_Membership_RuleInterface {

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
	 * Get delay meta for a given key.
	 *
	 * @since 1.18
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function get_delay_meta( $key );

	/**
	 * Update delay meta for a given key.
	 *
	 * @since 1.18
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return bool
	 */
	public function update_delay_meta( $key, $value );

	/**
	 * Delete delay meta for a given key.
	 *
	 * @since 1.18
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function delete_delay_meta( $key );
}