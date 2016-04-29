<?php
/**
 * Contains tests for the membership product theme API.
 *
 * @since   1.19.11
 * @license GPLv2
 */

/**
 * Class Test_IT_Theme_API_Membership_Product
 *
 * @group updowngrade
 */
class Test_IT_Theme_API_Membership_Product_Upgrade_Downgrades extends IT_Exchange_UnitTestCase {

	/**
	 * Do custom initialization.
	 */
	public function setUp() {
		parent::setUp();

		wp_set_current_user( 1 );

		$settings = it_exchange_get_option( 'addon_offline_payments' );

		$settings['offline-payments-default-status'] = 'paid';
		it_exchange_save_option( 'addon_offline_payments', $settings );
		it_exchange_clear_option_cache( 'addon_offline_payments' );

		$GLOBALS['it_exchange']['session'] = new IT_Exchange_Mock_Session();

		if ( ! class_exists( 'IT_Exchange_Subscription' ) ) {
			$this->markTestSkipped( 'Recurring Payments Required' );
		}
	}

	/**
	 * Teardown the test case.
	 */
	function tearDown() {
		parent::tearDown();

		it_exchange_clear_session();
	}

	/**
	 * Signup the user for a membership.
	 *
	 * @param int $membership_id
	 * @param int $days_ago
	 *
	 * @return int
	 */
	protected function signup( $membership_id, $days_ago = 0 ) {

		$cart = array(
			'products'  => array(
				"$membership_id-hash" => array(
					'product_id'         => $membership_id,
					'count'              => 1,
					'product_base_price' => it_exchange_get_product_feature( $membership_id, 'base-price' ),
					'product_subtotal'   => it_exchange_get_product_feature( $membership_id, 'base-price' ),
					'product_cart_id'    => "$membership_id-hash"
				)
			),
			'sub_total' => it_exchange_get_product_feature( $membership_id, 'base-price' ),
			'total'     => it_exchange_get_product_feature( $membership_id, 'base-price' )
		);

		$date = new DateTime();

		if ( $days_ago ) {
			$date->sub( new DateInterval( "P{$days_ago}D" ) );
		}

		$txn_id = $this->transaction_factory->create( array(
			'cart_object' => (object) $cart,
			'post_date'   => $date->format( 'Y-m-d H:i:s' ),
			'method'      => 'offline-payments',
			'status'      => 'paid'
		) );

		it_exchange_membership_addon_setup_customer_session();

		return $txn_id;
	}

	/**
	 * @dataProvider _dp_upgrade_auto_renew_to_auto_renew
	 *
	 * @param $i1
	 * @param $p1
	 * @param $days_ago
	 * @param $i2
	 * @param $p2
	 * @param $credit
	 * @param $free_days
	 */
	public function test_upgrade_auto_renew_to_auto_renew( $i1, $p1, $days_ago, $i2, $p2, $credit, $free_days ) {

		/** @var $membership_1 IT_Exchange_Product * */
		$membership_1 = $this->product_factory->create_and_get( array(
			'type'       => 'membership-product-type',
			'base-price' => $p1
		) );

		$membership_1->update_feature( 'recurring-payments', 'on' );
		$membership_1->update_feature( 'recurring-payments', 1, array( 'setting' => 'interval-count' ) );
		$membership_1->update_feature( 'recurring-payments', $i1, array( 'setting' => 'interval' ) );
		$membership_1->update_feature( 'recurring-payments', 'on', array( 'setting' => 'auto-renew' ) );

		$this->signup( $membership_1->ID, $days_ago );

		/** @var $membership_2 IT_Exchange_Product * */
		$membership_2 = $this->product_factory->create_and_get( array(
			'type'       => 'membership-product-type',
			'base-price' => $p2
		) );

		$membership_2->update_feature( 'recurring-payments', 'on' );
		$membership_2->update_feature( 'recurring-payments', 1, array( 'setting' => 'interval-count' ) );
		$membership_2->update_feature( 'recurring-payments', $i2, array( 'setting' => 'interval' ) );
		$membership_2->update_feature( 'recurring-payments', 'on', array( 'setting' => 'auto-renew' ) );
		$membership_2->update_feature( 'membership-hierarchy', array( $membership_1->ID ), array( 'setting' => 'children' ) );

		$this->assertContains( $membership_2->ID, it_exchange_membership_addon_get_all_the_parents( $membership_1->ID ) );

		$GLOBALS['it_exchange']['product'] = $membership_2;

		$api         = new IT_Theme_API_Membership_Product();
		$description = $api->upgrade_details( array(
			'supports'    => false,
			'has'         => false,
			'before_desc' => '',
			'after_desc'  => ''
		) );

		$session = it_exchange_get_session_data( 'updowngrade_details' );

		$this->assertInternalType( 'array', $session );

		if ( empty( $credit ) ) {
			$this->assertArrayNotHasKey( $membership_2->ID, $session );
		} else {
			$this->assertArrayHasKey( $membership_2->ID, $session );
			$this->assertEquals( $credit, $session[ $membership_2->ID ]['credit'], "Credit doesn't match", 0.01 );
			$this->assertEquals( $free_days, $session[ $membership_2->ID ]['free_days'], "Free days doesn't match" );
		}
	}

	public function _dp_upgrade_auto_renew_to_auto_renew() {
		return array(
			array( 'month', '5.00', 7, 'month', '10.00', '3.78', 12 ),
			array( 'month', '75.00', 3, 'month', '250.00', '66.58', 27 ),
			array( 'month', '750.00', 3, 'month', '1250.00', '665.75', 27 ),
			array( 'month', '5.00', 0, 'month', '10.00', '5.00', 15 ),
			array( 'month', '5.00', 30, 'month', '10.00', 0, 0 ),
			array( 'month', '5.00', 15, 'year', '20.00', '2.47', 45 ),
			array( 'year', '5.00', 90, 'year', '20.00', '3.77', 69 ),
			array( 'year', '5.00', 240, 'month', '20.00', '1.71', 3 ),
		);
	}

	/**
	 * @dataProvider _dp_upgrade_life_to_life
	 *
	 * @param $p1
	 * @param $p2
	 * @param $days_ago
	 * @param $credit
	 */
	public function test_upgrade_life_to_life( $p1, $days_ago, $p2, $credit ) {

		/** @var $membership_1 IT_Exchange_Product * */
		$membership_1 = $this->product_factory->create_and_get( array(
			'type'       => 'membership-product-type',
			'base-price' => $p1
		) );

		$membership_1->update_feature( 'recurring-payments', 'off' );
		$membership_1->update_feature( 'recurring-payments', 'off', array( 'setting' => 'auto-renew' ) );

		$this->signup( $membership_1->ID, $days_ago );

		/** @var $membership_2 IT_Exchange_Product * */
		$membership_2 = $this->product_factory->create_and_get( array(
			'type'       => 'membership-product-type',
			'base-price' => $p2
		) );

		$membership_2->update_feature( 'recurring-payments', 'off' );
		$membership_2->update_feature( 'recurring-payments', 'off', array( 'setting' => 'auto-renew' ) );
		$membership_2->update_feature( 'membership-hierarchy', array( $membership_1->ID ), array( 'setting' => 'children' ) );

		$this->assertContains( $membership_2->ID, it_exchange_membership_addon_get_all_the_parents( $membership_1->ID ) );

		$GLOBALS['it_exchange']['product'] = $membership_2;

		$api         = new IT_Theme_API_Membership_Product();
		$description = $api->upgrade_details( array(
			'supports'    => false,
			'has'         => false,
			'before_desc' => '',
			'after_desc'  => ''
		) );

		$session = it_exchange_get_session_data( 'updowngrade_details' );

		$this->assertInternalType( 'array', $session );

		if ( empty( $credit ) ) {
			$this->assertArrayNotHasKey( $membership_2->ID, $session );
		} else {
			$this->assertArrayHasKey( $membership_2->ID, $session );
			$this->assertEquals( $credit, $session[ $membership_2->ID ]['credit'], "Credit doesn't match", 0.01 );
			$this->assertEmpty( $session[ $membership_2->ID ]['free_days'] );
		}
	}

	public function _dp_upgrade_life_to_life() {
		return array(
			array( '5.00', 30, '15.00', '5.00' )
		);
	}

	/**
	 * @dataProvider _dp_upgrade_life_to_auto_renew
	 *
	 * @param $p1
	 * @param $days_ago
	 * @param $p2
	 * @param $i2
	 * @param $credit
	 * @param $free_days
	 */
	public function test_upgrade_life_to_auto_renew( $p1, $days_ago, $p2, $i2, $credit, $free_days ) {

		/** @var $membership_1 IT_Exchange_Product * */
		$membership_1 = $this->product_factory->create_and_get( array(
			'type'       => 'membership-product-type',
			'base-price' => $p1
		) );

		$membership_1->update_feature( 'recurring-payments', 'off' );
		$membership_1->update_feature( 'recurring-payments', 'off', array( 'setting' => 'auto-renew' ) );

		$this->signup( $membership_1->ID, $days_ago );

		/** @var $membership_2 IT_Exchange_Product * */
		$membership_2 = $this->product_factory->create_and_get( array(
			'type'       => 'membership-product-type',
			'base-price' => $p2
		) );

		$membership_2->update_feature( 'recurring-payments', 'on' );
		$membership_2->update_feature( 'recurring-payments', 1, array( 'setting' => 'interval-count' ) );
		$membership_2->update_feature( 'recurring-payments', $i2, array( 'setting' => 'interval' ) );
		$membership_2->update_feature( 'recurring-payments', 'on', array( 'setting' => 'auto-renew' ) );
		$membership_2->update_feature( 'membership-hierarchy', array( $membership_1->ID ), array( 'setting' => 'children' ) );

		$this->assertContains( $membership_2->ID, it_exchange_membership_addon_get_all_the_parents( $membership_1->ID ) );

		$GLOBALS['it_exchange']['product'] = $membership_2;

		$api         = new IT_Theme_API_Membership_Product();
		$description = $api->upgrade_details( array(
			'supports'    => false,
			'has'         => false,
			'before_desc' => '',
			'after_desc'  => ''
		) );

		$session = it_exchange_get_session_data( 'updowngrade_details' );

		$this->assertInternalType( 'array', $session );

		if ( empty( $credit ) ) {
			$this->assertArrayNotHasKey( $membership_2->ID, $session );
		} else {
			$this->assertArrayHasKey( $membership_2->ID, $session );
			$this->assertEquals( $credit, $session[ $membership_2->ID ]['credit'], "Credit doesn't match", 0.01 );
			$this->assertEquals( $free_days, $session[ $membership_2->ID ]['free_days'], "Free days doesn't match" );
		}
	}

	public function _dp_upgrade_life_to_auto_renew() {
		return array(
			array( '5.00', 60, '15.00', 'monthly', '5.00', 10 )
		);
	}

	/**
	 * @dataProvider _dp_upgrade_auto_renew_to_life
	 *
	 * @param $p1
	 * @param $i1
	 * @param $days_ago
	 * @param $p2
	 * @param $credit
	 */
	public function test_upgrade_auto_renew_to_life( $p1, $i1, $days_ago, $p2, $credit ) {

		/** @var $membership_1 IT_Exchange_Product * */
		$membership_1 = $this->product_factory->create_and_get( array(
			'type'       => 'membership-product-type',
			'base-price' => $p1
		) );

		$membership_1->update_feature( 'recurring-payments', 'on' );
		$membership_1->update_feature( 'recurring-payments', 1, array( 'setting' => 'interval-count' ) );
		$membership_1->update_feature( 'recurring-payments', $i1, array( 'setting' => 'interval' ) );
		$membership_1->update_feature( 'recurring-payments', 'on', array( 'setting' => 'auto-renew' ) );

		$this->signup( $membership_1->ID, $days_ago );

		/** @var $membership_2 IT_Exchange_Product * */
		$membership_2 = $this->product_factory->create_and_get( array(
			'type'       => 'membership-product-type',
			'base-price' => $p2
		) );

		$membership_2->update_feature( 'recurring-payments', 'off' );
		$membership_2->update_feature( 'recurring-payments', 'off', array( 'setting' => 'auto-renew' ) );
		$membership_2->update_feature( 'membership-hierarchy', array( $membership_1->ID ), array( 'setting' => 'children' ) );

		$this->assertContains( $membership_2->ID, it_exchange_membership_addon_get_all_the_parents( $membership_1->ID ) );

		$GLOBALS['it_exchange']['product'] = $membership_2;

		$api         = new IT_Theme_API_Membership_Product();
		$description = $api->upgrade_details( array(
			'supports'    => false,
			'has'         => false,
			'before_desc' => '',
			'after_desc'  => ''
		) );

		$session = it_exchange_get_session_data( 'updowngrade_details' );

		$this->assertInternalType( 'array', $session );

		if ( empty( $credit ) ) {
			$this->assertArrayNotHasKey( $membership_2->ID, $session );
		} else {
			$this->assertArrayHasKey( $membership_2->ID, $session );
			$this->assertEquals( $credit, $session[ $membership_2->ID ]['credit'], '', 0.01 );
			$this->assertEmpty( $session[ $membership_2->ID ]['free_days'] );
		}
	}

	public function _dp_upgrade_auto_renew_to_life() {
		return array(
			array( 'month', '5.00', 7, '10.00', '3.78' ),
			array( 'month', '5.00', 0, '10.00', '5.00' ),
			array( 'month', '5.00', 30, '10.00', 0 ),
			array( 'month', '5.00', 15, '20.00', '2.47' ),
			array( 'year', '5.00', 90, '20.00', '3.77' ),
			array( 'year', '5.00', 240, '20.00', '1.71' ),
		);
	}
}