<?php
/**
 * Contains the user membership interface.
 *
 * @since   1.18
 * @license GPLv2
 */

/**
 * Interface IT_Exchange_User_Membership
 */
interface IT_Exchange_User_Membership {

	/**
	 * Get the user associated with this membership.
	 *
	 * This isn't necessarily the payer.
	 *
	 * @since 1.18
	 *
	 * @return WP_User
	 */
	public function get_user();

	/**
	 * Get the membership plan backing the user's membership.
	 *
	 * @since 1.18
	 *
	 * @return IT_Exchange_Membership
	 */
	public function get_membership();

	/**
	 * Get the membership start date.
	 *
	 * @since 1.18
	 *
	 * @return DateTime
	 */
	public function get_start_date();

	/**
	 * Get the end date of this membership.
	 *
	 * If null, the membership access never terminates.
	 *
	 * @since 1.18
	 *
	 * @return DateTime|null
	 */
	public function get_end_date();

	/**
	 * Is this membership auto-renewing.
	 *
	 * @since 1.18
	 *
	 * @return bool
	 */
	public function is_auto_renewing();

	/**
	 * Get the status of this membership.
	 *
	 * @since 1.18
	 *
	 * @param bool $label
	 *
	 * @return string
	 */
	public function get_status( $label = false );

	/**
	 * Set the membership status.
	 *
	 * @since 1.18
	 *
	 * @param string $new_status
	 */
	public function set_status( $new_status );

	/**
	 * Check if this membership's current status grants access to protected content.
	 *
	 * @since 1.18
	 *
	 * @return bool
	 */
	public function current_status_grants_access();
}
