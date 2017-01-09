<?php

use Mockery as m;

/**
 * Class IT_Exchange_Memberships_Repair_Member_Access_Upgrade_Test
 *
 * @group repair-access-upgrade
 */
class IT_Exchange_Memberships_Repair_Member_Access_Upgrade_Test extends IT_Exchange_UnitTestCase {

	/**
	 * Get a transaction for testing.
	 *
	 * @param stdClass $object
	 * @param int      $customer
	 * @param string   $status
	 *
	 * @return int
	 */
	protected function _get_txn( $object = null, $customer = 1, $status = 'pending' ) {

		static $count = 0;

		if ( ! $object ) {
			$object = new stdClass();
		}

		if ( empty( $object->cart_id ) ) {
			$object->cart_id = "test-cart-id-$count";
		}

		$id = it_exchange_add_transaction( 'offline-payments', "test-method-id-{$count}", $status, $customer, $object );

		$count ++;

		return $id;
	}

	public function test_cleared_txn_restores_member_access() {

		$product = $this->product_factory->create_and_get( array( 'type' => 'membership-product-type' ) );

		$object = (object) array(
			'products' => array(
				"{$product->ID}-test-product-hash" => array(
					'product_id' => $product->ID
				)
			)
		);

		$txn      = $this->_get_txn( $object );
		$customer = it_exchange_get_customer( 1 );
		$customer->update_customer_meta( 'member_access', array() );

		// mark as paid, without triggering actions
		update_post_meta( $txn, '_it_exchange_transaction_status', 'paid' );

		$upgrade = new IT_Exchange_Memberships_Repair_Member_Access_Upgrade();
		$upgrade->upgrade( new IT_Exchange_Upgrade_Config( 1, 1, false ), new IT_Exchange_Upgrade_Skin_Ajax() );

		$member_access = $customer->get_customer_meta( 'member_access' );

		$this->assertArrayHasKey( $txn, $member_access );
		$this->assertNotFalse( array_search( $product->ID, $member_access[ $txn ] ) );
	}

	public function test_not_cleared_txn_revokes_member_access() {

		$product = $this->product_factory->create_and_get( array( 'type' => 'membership-product-type' ) );

		$object = (object) array(
			'products' => array(
				"{$product->ID}-test-product-hash" => array(
					'product_id' => $product->ID
				)
			)
		);

		$txn      = $this->_get_txn( $object, 1, 'paid' );
		$customer = it_exchange_get_customer( 1 );
		$customer->update_customer_meta( 'member_access', array(
			$txn => array( $product->ID )
		) );

		// mark as paid, without triggering actions
		update_post_meta( $txn, '_it_exchange_transaction_status', 'pending' );

		$upgrade = new IT_Exchange_Memberships_Repair_Member_Access_Upgrade();
		$upgrade->upgrade( new IT_Exchange_Upgrade_Config( 1, 1, false ), new IT_Exchange_Upgrade_Skin_Ajax() );

		$member_access = $customer->get_customer_meta( 'member_access' );

		$this->assertArrayNotHasKey( $txn, $member_access );
	}
}