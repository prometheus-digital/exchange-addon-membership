<?php

use Mockery as m;

/**
 * Class IT_Exchange_Membership_Delay_Rule_Date_Test
 */
class IT_Exchange_Membership_Delay_Rule_Date_Test extends IT_Exchange_UnitTestCase {

	public function test_evaluate() {

		$membership      = m::mock( 'IT_Exchange_Membership' );
		$membership->ID  = 1;
		$user_membership = m::mock( 'IT_Exchange_User_Membership' );

		$delayable = m::mock( 'IT_Exchange_Membership_Rule_Delayable' );
		$delayable->shouldReceive( 'get_delay_meta' )->with( '_item-content-rule-date-1' )->andReturn( 'tomorrow' );

		$drip = new IT_Exchange_Membership_Delay_Rule_Date( $delayable, $membership );
		$this->assertFalse( $drip->evaluate( $user_membership ) );


		$delayable = m::mock( 'IT_Exchange_Membership_Rule_Delayable' );
		$delayable->shouldReceive( 'get_delay_meta' )->with( '_item-content-rule-date-1' )->andReturn( 'yesterday' );

		$drip = new IT_Exchange_Membership_Delay_Rule_Date( $delayable, $membership );
		$this->assertTrue( $drip->evaluate( $user_membership ) );
	}

	public function test_get_availability_date() {

		$membership     = m::mock( 'IT_Exchange_Membership' );
		$membership->ID = 1;

		$user_membership = m::mock( 'IT_Exchange_User_Membership' );

		$delayable = m::mock( 'IT_Exchange_Membership_Rule_Delayable' );
		$delayable->shouldReceive( 'get_delay_meta' )->with( '_item-content-rule-date-1' )->andReturn( '2016-01-01' );

		$drip = new IT_Exchange_Membership_Delay_Rule_Date( $delayable, $membership );
		$this->assertEquals( new DateTime( '2016-01-01' ), $drip->get_availability_date( $user_membership ) );
	}

	public function test_get_field_html() {

		$rule = new IT_Exchange_Membership_Delay_Rule_Date();

		$html = $rule->get_field_html( 'new' );

		$this->assertRegExp( '/id="new-date"/', $html, 'Input id attr not found.' );
		$this->assertRegExp( '/name="new\[date\]"/', $html, 'Input name attr not found.' );
	}

	public function test_save() {

		$membership     = m::mock( 'IT_Exchange_Membership' );
		$membership->ID = 1;

		$new_date = new DateTime( 'yesterday' );
		$new_date->setTimezone( new DateTimeZone( 'UTC' ) );

		$delayable = m::mock( 'IT_Exchange_Membership_Rule_Delayable' );
		$delayable->shouldReceive( 'get_delay_meta' )->with( '_item-content-rule-date-1' )->andReturn( '2016-01-01' );
		$delayable->shouldReceive( 'update_delay_meta' )->with( '_item-content-rule-date-1', $new_date->format( 'Y-m-d H:i:s' ) )
		          ->andReturn( true );

		$drip = new IT_Exchange_Membership_Delay_Rule_Date( $delayable, $membership );
		$this->assertTrue( $drip->save( array(
			'date' => $new_date->format( 'Y-m-d H:i:s' )
		) ) );
	}

	public function test_save_deletes_null_values() {

		$membership     = m::mock( 'IT_Exchange_Membership' );
		$membership->ID = 1;

		$delayable = m::mock( 'IT_Exchange_Membership_Rule_Delayable' );
		$delayable->shouldReceive( 'get_delay_meta' )->with( '_item-content-rule-date-1' )->andReturn( '2016-01-01' );
		$delayable->shouldReceive( 'delete_delay_meta' )->with( '_item-content-rule-date-1' )->andReturn( true );

		$drip = new IT_Exchange_Membership_Delay_Rule_Date( $delayable, $membership );
		$this->assertTrue( $drip->save( array(
			'date' => null
		) ) );
	}

	public function test_delete() {

		$membership     = m::mock( 'IT_Exchange_Membership' );
		$membership->ID = 1;

		$delayable = m::mock( 'IT_Exchange_Membership_Rule_Delayable' );
		$delayable->shouldReceive( 'get_delay_meta' )->with( '_item-content-rule-date-1' )->andReturn( '2016-01-01' );
		$delayable->shouldReceive( 'delete_delay_meta' )->with( '_item-content-rule-date-1' )->andReturn( true );

		$drip = new IT_Exchange_Membership_Delay_Rule_Date( $delayable, $membership );
		$this->assertTrue( $drip->delete() );
	}

	public function test_get_type() {
		$drip = new IT_Exchange_Membership_Delay_Rule_Date();
		$this->assertEquals( 'date', $drip->get_type() );
	}
}