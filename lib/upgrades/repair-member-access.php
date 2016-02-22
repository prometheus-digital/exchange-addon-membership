<?php
/**
 * Contains the upgrade routine for repairing member access.
 *
 * @since   1.18
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Memberships_Repair_Member_Access_Upgrade
 */
class IT_Exchange_Memberships_Repair_Member_Access_Upgrade implements IT_Exchange_UpgradeInterface {

	/**
	 * Get the iThemes Exchange version this upgrade applies to.
	 *
	 * @since 1.33
	 *
	 * @return string
	 */
	public function get_version() {
		return '1.18';
	}

	/**
	 * Get the name of this upgrade.
	 *
	 * @since 1.33
	 *
	 * @return string
	 */
	public function get_name() {
		return __( 'Repair Member Access', 'LION' );
	}

	/**
	 * Get the slug for this upgrade. This should be globally unique.
	 *
	 * @since 1.33
	 *
	 * @return string
	 */
	public function get_slug() {
		return 'memberships-repair-member-access';
	}

	/**
	 * Get the description for this upgrade. 1-3 sentences.
	 *
	 * @since 1.33
	 *
	 * @return string
	 */
	public function get_description() {
		return __( 'Repairs member access to match payment and subscription statuses.', 'LION' );
	}

	/**
	 * Get the group this upgrade belongs to.
	 *
	 * Example 'Core' or 'Membership'.
	 *
	 * @since 1.33
	 *
	 * @return string
	 */
	public function get_group() {
		return __( 'Membership', 'LION' );
	}

	/**
	 * Get the total records needed to be processed for this upgrade.
	 *
	 * This is used to build the upgrade UI.
	 *
	 * @since 1.34
	 *
	 * @return int
	 */
	public function get_total_records_to_process() {
		return count( $this->get_transactions( - 1, 1, true ) );
	}

	/**
	 * Get all coupons we need to upgrade.
	 *
	 * @since 1.18
	 *
	 * @param int  $number
	 * @param int  $page
	 * @param bool $ids Only return IDs
	 *
	 * @return IT_Exchange_Transaction[]
	 */
	protected function get_transactions( $number = - 1, $page = 1, $ids = false ) {

		$args = array(
			'posts_per_page' => $number,
			'page'           => $page,
			'post_parent'    => 0,
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'     => '_upgrade_completed',
					'compare' => 'NOT EXISTS'
				),
				array(
					'key'     => '_upgrade_completed',
					'value'   => $this->get_slug(),
					'compare' => '!='
				)
			)
		);

		if ( $ids ) {
			$args['fields'] = 'ids';
		}

		return it_exchange_get_transactions( $args );
	}

	/**
	 * Upgrade a single transaction.
	 *
	 * We are looking for transactions that contain membership products.
	 *
	 * There are two cases to examine.
	 *
	 *  1. A transaction is cleared for delivery, and if a subscription is present its active,
	 *     then if the customer does not have member access, we re-add the member access.
	 *
	 *  2. A transaction is not cleared for delivery, and the subscription status is active,
	 *     we cancel the subscription and remove the member's access.
	 *
	 * @since 1.18
	 *
	 * @param IT_Exchange_Transaction           $transaction
	 * @param IT_Exchange_Upgrade_SkinInterface $skin
	 * @param bool                              $verbose
	 */
	protected function upgrade_transaction( IT_Exchange_Transaction $transaction, IT_Exchange_Upgrade_SkinInterface $skin, $verbose ) {

		if ( $verbose ) {
			$skin->debug( 'Upgrading Txn: ' . $transaction->ID );
		}

		$membership_ids = $this->get_transaction_memberships( $transaction );

		if ( empty( $membership_ids ) ) {

			if ( $verbose ) {
				$skin->debug( 'Skipped Txn: ' . $transaction->ID . '. No memberships found.' );
				$skin->debug( '' );
			}

			return;
		}

		$customer = it_exchange_get_transaction_customer( $transaction );
		$cleared  = it_exchange_transaction_is_cleared_for_delivery( $transaction );
		$subs     = $this->get_transaction_subscriptions( $transaction );

		if ( $cleared ) {

			$member_access = $customer->get_customer_meta( 'member_access' );

			foreach ( $subs as $sub ) {

				$i = array_search( $sub->get_product()->ID, $membership_ids );

				if ( $i !== false ) {
					unset( $membership_ids[ $i ] );
				}

				if ( $sub->get_status() === IT_Exchange_Subscription::STATUS_ACTIVE ) {

					if ( empty( $member_access[ $transaction->ID ] ) ) {
						$member_access[ $transaction->ID ] = array();
					}

					if ( false === array_search( $sub->get_product()->ID, $member_access[ $transaction->ID ] ) ) {
						$member_access[ $transaction->ID ][] = $sub->get_product()->ID;

						if ( $verbose ) {
							$skin->debug(
								"Transaction cleared for delivery & subscription active. " .
								"Adding member access customer: {$customer->ID} product: {$sub->get_product()->ID}"
							);
						}
					}
				} else {

					if ( empty( $member_access[ $transaction->ID ] ) ) {
						continue;
					}

					$i = array_search( $sub->get_product()->ID, $member_access[ $transaction->ID ] );

					if ( $i === false ) {
						continue;
					}

					unset( $member_access[ $transaction->ID ][ $i ] );

					if ( empty( $member_access[ $transaction->ID ] ) ) {
						unset( $member_access[ $transaction->ID ] );
					}

					if ( $verbose ) {
						$skin->debug(
							"Transaction cleared for delivery & subscription not-active ({$sub->get_status()}). " .
							"Removing member access. customer: {$customer->ID} product: {$sub->get_product()->ID}"
						);
					}
				}
			}

			foreach ( $membership_ids as $membership_id ) {
				if ( empty( $member_access[ $transaction->ID ] ) ) {
					$member_access[ $transaction->ID ] = array();
				}


				if ( false === array_search( $membership_id, $member_access[ $transaction->ID ] ) ) {
					$member_access[ $transaction->ID ][] = $membership_id;

					if ( $verbose ) {
						$skin->debug(
							"Transaction cleared for delivery & no subscription. " .
							"Adding member access. customer: {$customer->ID} product: {$membership_id}"
						);
					}
				}
			}

			$customer->update_customer_meta( 'member_access', $member_access );
		} else {

			foreach ( $subs as $sub ) {

				$i = array_search( $sub->get_product()->ID, $membership_ids );

				if ( $i !== false ) {
					unset( $membership_ids[ $i ] );
				}

				// only active subscriptions need to be cancelled.
				if ( $sub->get_status() !== IT_Exchange_Subscription::STATUS_ACTIVE ) {
					continue;
				}

				// this will handle changing the member access
				$sub->set_status( IT_Exchange_Subscription::STATUS_CANCELLED );

				if ( $verbose ) {
					$skin->debug(
						"Transaction not cleared for delivery. Cancelling subscription and " .
						"removing member access. customer: {$customer->ID} product: {$sub->get_product()->ID}"
					);
				}
			}

			$member_access = $customer->get_customer_meta( 'member_access' );

			foreach ( $membership_ids as $membership_id ) {

				if ( empty( $member_access[ $transaction->ID ] ) ) {
					continue;
				}

				$i = array_search( $membership_id, $member_access[ $transaction->ID ] );

				if ( $i !== false ) {
					unset( $member_access[ $transaction->ID ][ $i ] );
				}

				if ( empty( $member_access[ $transaction->ID ] ) ) {
					unset( $member_access[ $transaction->ID ] );
				}

				if ( $verbose ) {
					$skin->debug(
						"Transaction not cleared for delivery & no subscription. " .
						"Removing member access. customer: {$customer->ID} product: {$membership_id}"
					);
				}
			}

			$customer->update_customer_meta( 'member_access', $member_access );
		}

		update_post_meta( $transaction->ID, '_upgrade_completed', $this->get_slug() );

		if ( $verbose ) {
			$skin->debug( 'Upgraded Txn: ' . $transaction->ID );
			$skin->debug( '' );
		}
	}

	/**
	 * Get all memberships for a transaction.
	 *
	 * @since 1.18
	 *
	 * @param IT_Exchange_Transaction $transaction
	 *
	 * @return int[]
	 */
	private function get_transaction_memberships( IT_Exchange_Transaction $transaction ) {

		$memberships = array();

		foreach ( $transaction->get_products() as $product ) {
			if ( it_exchange_get_product_type( $product['product_id'] ) === 'membership-product-type' ) {
				$memberships[] = $product['product_id'];
			}
		}

		return $memberships;
	}

	/**
	 * Get a transaction's subscriptions.
	 *
	 * @since 1.18
	 *
	 * @param IT_Exchange_Transaction $transaction
	 *
	 * @return IT_Exchange_Subscription[]
	 */
	private function get_transaction_subscriptions( IT_Exchange_Transaction $transaction ) {

		if ( ! function_exists( 'it_exchange_get_transaction_subscriptions' ) ) {
			return array();
		}

		return it_exchange_get_transaction_subscriptions( $transaction );
	}

	/**
	 * Get the suggested rate at which the upgrade routine should be processed.
	 *
	 * The rate refers to how many items are upgraded in one step.
	 *
	 * @since 1.33
	 *
	 * @return int
	 */
	public function get_suggested_rate() {
		return 30;
	}

	/**
	 * Perform the upgrade according to the given configuration.
	 *
	 * Throwing an upgrade exception will halt the upgrade process and notify the user.
	 *
	 * @param IT_Exchange_Upgrade_Config        $config
	 * @param IT_Exchange_Upgrade_SkinInterface $skin
	 *
	 * @return void
	 *
	 * @throws IT_Exchange_Upgrade_Exception
	 */
	public function upgrade( IT_Exchange_Upgrade_Config $config, IT_Exchange_Upgrade_SkinInterface $skin ) {

		$transactions = $this->get_transactions( $config->get_number(), $config->get_step() );

		foreach ( $transactions as $coupon ) {
			$this->upgrade_transaction( $coupon, $skin, $config->is_verbose() );
			$skin->tick();
		}
	}
}