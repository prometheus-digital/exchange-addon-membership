<?php
/**
 * Delay rule interface.
 *
 * @since   1.18
 * @license GPLv2
 */

/**
 * Interface IT_Exchange_Membership_Delay_Rule
 *
 * Delay rules are saved against the individual post object.
 */
interface IT_Exchange_Membership_Delay_Rule extends IT_Exchange_Membership_Rule {

	/**
	 * Get the availability date for this rule.
	 *
	 * Null can be returned to indicate that the subscription will never
	 * have access to this content.
	 *
	 * @since    1.18
	 *
	 * @param IT_Exchange_User_Membership $user_membership
	 *
	 * @return DateTime|null
	 */
	public function get_availability_date( IT_Exchange_User_Membership $user_membership );
}