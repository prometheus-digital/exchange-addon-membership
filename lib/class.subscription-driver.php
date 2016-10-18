<?php
/**
 * Contains the subscription driver for a user membership.
 *
 * @since   1.18
 * @license GPLv2
 */

/**
 * Class IT_Exchange_User_Membership_Subscription_Driver
 */
class IT_Exchange_User_Membership_Subscription_Driver implements ITE_Proratable_User_Membership {

	/**
	 * @var IT_Exchange_Subscription
	 */
	private $subscription;

	/**
	 * IT_Exchange_User_Membership_Subscription_Driver constructor.
	 *
	 * @param IT_Exchange_Subscription $subscription
	 */
	public function __construct( IT_Exchange_Subscription $subscription ) {
		$this->subscription = $subscription;
	}

	/**
	 * Get the user associated with this membership.
	 *
	 * This isn't necessarily the payer.
	 *
	 * @since 1.18
	 *
	 * @return WP_User
	 */
	public function get_user() {
		return $this->subscription->get_beneficiary()->wp_user;
	}

	/**
	 * Get the membership plan backing the user's membership.
	 *
	 * @since 1.18
	 *
	 * @return IT_Exchange_Membership
	 */
	public function get_membership() {
		return $this->subscription->get_product();
	}

	/**
	 * Get the membership start date.
	 *
	 * @since 1.18
	 *
	 * @return DateTime
	 */
	public function get_start_date() {
		return $this->subscription->get_start_date();
	}

	/**
	 * Get the end date of this membership.
	 *
	 * If null, the membership access never terminates.
	 *
	 * @since 1.18
	 *
	 * @return DateTime|null
	 */
	public function get_end_date() {
		return $this->subscription->get_expiry_date();
	}

	/**
	 * Is this membership auto-renewing.
	 *
	 * @since 1.18
	 *
	 * @return bool
	 */
	public function is_auto_renewing() {
		return $this->subscription->is_auto_renewing();
	}

	/**
	 * Get the status of this membership.
	 *
	 * @since 1.18
	 *
	 * @param bool $label
	 *
	 * @return string
	 */
	public function get_status( $label = false ) {
		return $this->subscription->get_status( $label );
	}

	/**
	 * Set the membership status.
	 *
	 * @since 1.18
	 *
	 * @param string $new_status
	 */
	public function set_status( $new_status ) {
		$this->subscription->set_status( $new_status );
	}

	/**
	 * Check if this membership's current status grants access to protected content.
	 *
	 * @since 1.18
	 *
	 * @return bool
	 */
	public function current_status_grants_access() {
		return $this->get_status() === IT_Exchange_Subscription::STATUS_ACTIVE;
	}

	/**
	 * @inheritDoc
	 */
	public function get_available_upgrades() { return $this->subscription->get_available_upgrades(); }

	/**
	 * @inheritDoc
	 */
	public function get_available_downgrades() { return $this->subscription->get_available_downgrades(); }

}