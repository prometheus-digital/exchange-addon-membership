<?php
/**
 * User Membership Repository.
 *
 * @since   1.18
 * @license GPLv2
 */

/**
 * Class IT_Exchange_User_Membership_Repository
 */
class IT_Exchange_User_Membership_Repository {

	/**
	 * Retrieve a user's memberships.
	 *
	 * @since 1.18
	 *
	 * @param IT_Exchange_Customer|null $customer
	 *
	 * @return IT_Exchange_User_Membership[]
	 */
	public function get_user_memberships( IT_Exchange_Customer $customer = null ) {

		$membership_ids = it_exchange_membership_addon_get_customer_memberships( $customer ? $customer->ID : false );

		$memberships = array();

		foreach ( $membership_ids as $product_id => $transaction_id ) {

			$txn  = it_exchange_get_transaction( $transaction_id );
			$prod = it_exchange_get_product( $product_id );

			if ( ! $txn || ! $prod instanceof IT_Exchange_Membership ) {
				continue;
			}

			if ( function_exists( 'it_exchange_get_subscription_by_transaction' ) ) {

				try {
					$subscription = it_exchange_get_subscription_by_transaction( $txn, $prod );

					if ( $subscription ) {
						$memberships[] = new IT_Exchange_User_Membership_Subscription_Driver( $subscription );

						continue;
					}
				} catch ( Exception $e ) {

				}
			}

			$memberships[] = new IT_Exchange_User_Membership_Transaction_Driver( $txn, $prod );
		}

		return $memberships;
	}

	/**
	 * Get a membership by an ID.
	 *
	 * @since 1.20.0
	 *
	 * @param string $id
	 *
	 * @return IT_Exchange_User_Membership|null
	 */
	public function get_membership_by_id( $id ) {

		if ( class_exists( '\IT_Exchange_Subscription' ) ) {
			try {
				$subscription = IT_Exchange_Subscription::get( $id );

				if ( $subscription ) {
					return new IT_Exchange_User_Membership_Subscription_Driver( $subscription );
				}

			} catch ( InvalidArgumentException $e ) {

			}
		}

		$parts = explode( ':', $id );

		if ( count( $parts ) !== 2 ) {
			return null;
		}

		$transaction = it_exchange_get_transaction( $parts[0] );
		$product     = it_exchange_get_product( $parts[1] );

		if ( ! $transaction || ! $product instanceof IT_Exchange_Membership ) {
			return null;
		}

		return new IT_Exchange_User_Membership_Transaction_Driver( $transaction, $product );
	}
}