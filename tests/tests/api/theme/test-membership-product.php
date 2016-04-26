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
		$date->sub( new DateInterval( "P{$days_ago}D" ) );

		$txn_id = $this->transaction_factory->create( array(
			'cart_object' => (object) $cart,
			'post_date'   => $date->format( 'Y-m-d H:i:s' ),
			'method'      => 'offline-payments',
			'status'      => 'paid'
		) );

		it_exchange_membership_addon_setup_customer_session();

		return $txn_id;
	}

	public function test_monthly_to_monthly_upgrade() {

		/** @var $monthly_5 IT_Exchange_Product * */
		$monthly_5 = $this->product_factory->create_and_get( array(
			'type'       => 'membership-product-type',
			'base-price' => "5.00"
		) );

		$monthly_5->update_feature( 'recurring-payments', 'on' );
		$monthly_5->update_feature( 'recurring-payments', 1, array( 'setting' => 'interval-count' ) );
		$monthly_5->update_feature( 'recurring-payments', 'month', array( 'setting' => 'interval' ) );
		$monthly_5->update_feature( 'recurring-payments', 'on', array( 'setting' => 'auto-renew' ) );

		$this->signup( $monthly_5->ID, 7 );

		/** @var $monthly_10 IT_Exchange_Product * */
		$monthly_10 = $this->product_factory->create_and_get( array(
			'type'       => 'membership-product-type',
			'base-price' => "10.00"
		) );

		$monthly_10->update_feature( 'recurring-payments', 'on' );
		$monthly_10->update_feature( 'recurring-payments', 1, array( 'setting' => 'interval-count' ) );
		$monthly_10->update_feature( 'recurring-payments', 'month', array( 'setting' => 'interval' ) );
		$monthly_10->update_feature( 'recurring-payments', 'on', array( 'setting' => 'auto-renew' ) );
		$monthly_10->update_feature( 'membership-hierarchy', array( $monthly_5->ID ), array( 'setting' => 'children' ) );

		$this->assertContains( $monthly_10->ID, it_exchange_membership_addon_get_all_the_parents( $monthly_5->ID ) );

		$GLOBALS['it_exchange']['product'] = $monthly_10;

		$api         = new IT_Theme_API_Membership_Product();
		$description = $api->upgrade_details( array(
			'supports'    => false,
			'has'         => false,
			'before_desc' => '',
			'after_desc'  => ''
		) );


		$session = it_exchange_get_session_data( 'updowngrade_details' );

		$this->assertInternalType( 'array', $session );
		$this->assertArrayHasKey( $monthly_10->ID, $session );
		$this->assertEquals( '8.95', $session[ $monthly_10->ID ]['credit'] );
	}

}