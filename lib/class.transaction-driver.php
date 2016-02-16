<?php
/**
 * Transaction driver for the User_MembershipInterface
 *
 * @since   1.18
 * @license GPLv2
 */

/**
 * Class IT_Exchange_User_Membership_Transaction_Driver
 */
class IT_Exchange_User_Membership_Transaction_Driver implements IT_Exchange_User_MembershipInterface {

	/**
	 * @var IT_Exchange_Transaction
	 */
	private $transaction;

	/**
	 * @var IT_Exchange_Membership
	 */
	private $membership;

	/**
	 * IT_Exchange_User_Membership_Transaction_Driver constructor.
	 *
	 * @param IT_Exchange_Transaction $transaction
	 * @param IT_Exchange_Membership  $membership
	 */
	public function __construct( IT_Exchange_Transaction $transaction, IT_Exchange_Membership $membership ) {
		$this->transaction = $transaction;
		$this->membership  = $membership;
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
		return it_exchange_get_transaction_customer( $this->transaction )->wp_user;
	}

	/**
	 * Get the membership plan backing the user's membership.
	 *
	 * @since 1.18
	 *
	 * @return IT_Exchange_Membership
	 */
	public function get_membership() {
		return $this->membership;
	}

	/**
	 * Get the membership start date.
	 *
	 * @since 1.18
	 *
	 * @return DateTime
	 */
	public function get_start_date() {
		return new DateTime( $this->transaction->post_date_gmt, new DateTimeZone( 'UTC' ) );
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
		return null;
	}

	/**
	 * Is this membership auto-renewing.
	 *
	 * @since 1.18
	 *
	 * @return bool
	 */
	public function is_auto_renewing() {
		return false;
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

		if ( $this->current_status_grants_access() ) {
			return $label ? __( 'Active', 'LION' ) : 'active';
		}

		return $label ? __( 'Cancelled', 'LION' ) : 'cancelled';
	}

	/**
	 * Set the membership status.
	 *
	 * @since 1.18
	 *
	 * @param string $new_status
	 */
	public function set_status( $new_status ) {

		$customer      = it_exchange_get_customer( $this->get_user()->ID );
		$member_access = $customer->get_customer_meta( 'member_access' );

		if ( $new_status === 'active' ) {

			if ( ! isset( $member_access[ $this->transaction->ID ] ) ) {
				$member_access[ $this->transaction->ID ] = array( $this->get_membership()->ID );
			} elseif ( ! in_array( $this->get_membership()->ID, $member_access[ $this->transaction->ID ] ) ) {
				$member_access[ $this->transaction->ID ][] = $this->get_membership()->ID;
			}
		} else {

			if ( isset( $member_access[ $this->transaction->ID ] ) ) {

				$i = array_search( $this->get_membership()->ID, $member_access[ $this->transaction->ID ] );

				if ( $i !== false ) {
					unset( $member_access[ $this->transaction->ID ][ $i ] );

					if ( empty( $member_access[ $this->transaction->ID ] ) ) {
						unset( $member_access[ $this->transaction->ID ] );
					}
				}
			}
		}

		$customer->update_customer_meta( 'member_access', $member_access );
	}

	/**
	 * Check if this membership's current status grants access to protected content.
	 *
	 * @since 1.18
	 *
	 * @return bool
	 */
	public function current_status_grants_access() {

		$customer      = it_exchange_get_customer( $this->get_user()->ID );
		$member_access = $customer->get_customer_meta( 'member_access' );

		if ( empty( $member_access[ $this->transaction->ID ] ) ) {
			return false;
		}

		return false !== array_search( $this->get_membership()->ID, $member_access[ $this->transaction->ID ] );
	}
}
