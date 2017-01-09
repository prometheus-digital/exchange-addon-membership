<?php
/**
 * Proratable User Membership interface.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Interface ITE_Proratable_User_Membership
 */
interface ITE_Proratable_User_Membership extends IT_Exchange_User_Membership {

	/**
	 * Get all available upgrades for this membership.
	 *
	 * @since 2.0.0
	 *
	 * @return ITE_Prorate_Credit_Request[]
	 */
	public function get_available_upgrades();

	/**
	 * Get all available downgrades for this membership.
	 *
	 * @since 2.0.0
	 *
	 * @return ITE_Prorate_Credit_Request[]
	 */
	public function get_available_downgrades();
}