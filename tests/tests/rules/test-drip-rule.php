<?php

use Mockery as m;

/**
 * Class IT_Exchange_Membership_Delay_Rule_Drip_Test
 */
class IT_Exchange_Membership_Delay_Rule_Drip_Test extends IT_Exchange_UnitTestCase {

	public function test_evaluate() {

		$membership     = m::mock( 'IT_Exchange_Membership' );
		$membership->ID = 1;

		$delayable = m::mock( 'IT_Exchange_Membership_Rule_Delayable' );
		$delayable->shouldReceive( 'get_delay_meta' )->with( '_item-content-rule-drip-interval-1' )->andReturn( '5' );
		$delayable->shouldReceive( 'get_delay_meta' )->with( '_item-content-rule-drip-duration-1' )->andReturn( 'days' );

		$drip = new IT_Exchange_Membership_Delay_Rule_Drip( $delayable, $membership );

		$user_membership = m::mock( 'IT_Exchange_User_Membership' );
		$user_membership->shouldReceive( 'get_start_date' )->andReturn( new DateTime( 'yesterday' ) );
		$this->assertFalse( $drip->evaluate( $user_membership ) );

		$user_membership = m::mock( 'IT_Exchange_User_Membership' );
		$user_membership->shouldReceive( 'get_start_date' )->andReturn( new DateTime( 'last week' ) );
		$this->assertTrue( $drip->evaluate( $user_membership ) );
	}

	public function test_get_availability_date() {

		$membership     = m::mock( 'IT_Exchange_Membership' );
		$membership->ID = 1;

		$delayable = m::mock( 'IT_Exchange_Membership_Rule_Delayable' );
		$delayable->shouldReceive( 'get_delay_meta' )->with( '_item-content-rule-drip-interval-1' )->andReturn( '5' );
		$delayable->shouldReceive( 'get_delay_meta' )->with( '_item-content-rule-drip-duration-1' )->andReturn( 'days' );

		$drip = new IT_Exchange_Membership_Delay_Rule_Drip( $delayable, $membership );

		$user_membership = m::mock( 'IT_Exchange_User_Membership' );
		$user_membership->shouldReceive( 'get_start_date' )->andReturn( new DateTime( '2015-01-01' ) );

		$available = new DateTime( '2015-01-06' );

		$this->assertEquals( $available, $drip->get_availability_date( $user_membership ) );
	}

	public function test_get_field_html() {

		$rule = new IT_Exchange_Membership_Delay_Rule_Drip();

		$html = $rule->get_field_html( 'new' );

		$this->assertRegExp( '/id="new-interval"/', $html, 'Input id attr not found.' );
		$this->assertRegExp( '/name="new\[interval\]"/', $html, 'Input name attr not found.' );

		$this->assertRegExp( '/id="new-drip"/', $html, 'Select id attr not found.' );
		$this->assertRegExp( '/name="new\[duration\]"/', $html, 'Select name attr not found.' );
	}

	public function test_save() {

		$membership     = m::mock( 'IT_Exchange_Membership' );
		$membership->ID = 1;

		$delayable = m::mock( 'IT_Exchange_Membership_Rule_Delayable' );
		$delayable->shouldReceive( 'get_delay_meta' )->with( '_item-content-rule-drip-interval-1' )->andReturn( 1 );
		$delayable->shouldReceive( 'get_delay_meta' )->with( '_item-content-rule-drip-duration-1' )->andReturn( 'days' );
		$delayable->shouldReceive( 'update_delay_meta' )->with( '_item-content-rule-drip-interval-1', 5 )->andReturn( true );
		$delayable->shouldReceive( 'update_delay_meta' )->with( '_item-content-rule-drip-duration-1', 'months' )->andReturn( true );

		$drip = new IT_Exchange_Membership_Delay_Rule_Drip( $delayable, $membership );
		$this->assertTrue( $drip->save( array(
			'interval' => 5,
			'duration' => 'months'
		) ) );
	}

	public function test_save_deletes_null_values() {

		$membership     = m::mock( 'IT_Exchange_Membership' );
		$membership->ID = 1;

		$delayable = m::mock( 'IT_Exchange_Membership_Rule_Delayable' );
		$delayable->shouldReceive( 'get_delay_meta' )->with( '_item-content-rule-drip-interval-1' )->andReturn( 1 );
		$delayable->shouldReceive( 'get_delay_meta' )->with( '_item-content-rule-drip-duration-1' )->andReturn( 'days' );
		$delayable->shouldReceive( 'delete_delay_meta' )->with( '_item-content-rule-drip-interval-1' )->andReturn( true );
		$delayable->shouldReceive( 'delete_delay_meta' )->with( '_item-content-rule-drip-duration-1' )->andReturn( true );

		$drip = new IT_Exchange_Membership_Delay_Rule_Drip( $delayable, $membership );
		$this->assertTrue( $drip->save( array(
			'interval' => null,
			'duration' => null
		) ) );
	}

	public function test_save_rejects_invalid_durations() {

		$membership     = m::mock( 'IT_Exchange_Membership' );
		$membership->ID = 1;

		$delayable = m::mock( 'IT_Exchange_Membership_Rule_Delayable' );
		$delayable->shouldReceive( 'get_delay_meta' )->with( '_item-content-rule-drip-interval-1' )->andReturn( 1 );
		$delayable->shouldReceive( 'get_delay_meta' )->with( '_item-content-rule-drip-duration-1' )->andReturn( 'days' );
		$delayable->shouldNotReceive( 'delete_delay_meta' )->with( '_item-content-rule-drip-duration-1' );

		$this->setExpectedException( 'InvalidArgumentException' );

		$drip = new IT_Exchange_Membership_Delay_Rule_Drip( $delayable, $membership );
		$drip->save( array(
			'duration' => 'centuries'
		) );
	}

	public function test_delete() {

		$membership     = m::mock( 'IT_Exchange_Membership' );
		$membership->ID = 1;

		$delayable = m::mock( 'IT_Exchange_Membership_Rule_Delayable' );
		$delayable->shouldReceive( 'get_delay_meta' )->with( '_item-content-rule-drip-interval-1' )->andReturn( 1 );
		$delayable->shouldReceive( 'get_delay_meta' )->with( '_item-content-rule-drip-duration-1' )->andReturn( 'days' );
		$delayable->shouldReceive( 'delete_delay_meta' )->with( '_item-content-rule-drip-interval-1' )->andReturn( true );
		$delayable->shouldReceive( 'delete_delay_meta' )->with( '_item-content-rule-drip-duration-1' )->andReturn( true );

		$drip = new IT_Exchange_Membership_Delay_Rule_Drip( $delayable, $membership );
		$this->assertTrue( $drip->delete() );
	}

	public function test_get_type() {
		$drip = new IT_Exchange_Membership_Delay_Rule_Drip();
		$this->assertEquals( 'drip', $drip->get_type() );
	}

	public function test_get_durations() {

		$durations = IT_Exchange_Membership_Delay_Rule_Drip::get_durations();

		$this->assertArrayHasKey( 'days', $durations );
		$this->assertArrayHasKey( 'weeks', $durations );
		$this->assertArrayHasKey( 'months', $durations );
		$this->assertArrayHasKey( 'years', $durations );
	}


}