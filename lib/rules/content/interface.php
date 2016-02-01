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
}