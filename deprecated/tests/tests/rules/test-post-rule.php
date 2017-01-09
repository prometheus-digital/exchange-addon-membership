<?php

use Mockery as m;

/**
 * Class IT_Exchange_Membership_Content_Rule_Post_Test
 */
class IT_Exchange_Membership_Content_Rule_Post_Test extends IT_Exchange_UnitTestCase {
	/**
	 * Teardown the test case.
	 */
	function tearDown() {
		parent::tearDown();

		m::close();
	}

	public function test_delay_meta() {

		$rule = new IT_Exchange_Membership_Content_Rule_Post( 'post', null, array( 'term' => 1 ) );
		$rule->update_delay_meta( 'my-meta', 'my-value' );

		$this->assertEquals( 'my-value', $rule->get_delay_meta( 'my-meta' ) );

		$rule = new IT_Exchange_Membership_Content_Rule_Post( 'post', null, array( 'term' => 1 ) );
		$this->assertEquals( 'my-value', $rule->get_delay_meta( 'my-meta' ) );

		$rule->delete_delay_meta( 'my-meta' );
		$this->assertEmpty( $rule->get_delay_meta( 'my-meta' ) );
	}

	public function test_matches_post() {

		$rule = new IT_Exchange_Membership_Content_Rule_Post( 'post', null, array( 'term' => 1 ) );

		$this->assertTrue( $rule->matches_post( new WP_Post( (object) array( 'ID' => 1 ) ) ) );
		$this->assertFalse( $rule->matches_post( new WP_Post( (object) array( 'ID' => 2 ) ) ) );
	}

	public function test_get_matching_posts() {

		$rule = new IT_Exchange_Membership_Content_Rule_Post( 'post', null, array( 'term' => 1 ) );
		$this->assertEquals( array( get_post( 1 ) ), $rule->get_matching_posts() );
	}

	public function test_has_no_more_content_url() {
		$rule = new IT_Exchange_Membership_Content_Rule_Post( 'post', null, array( 'term' => 1 ) );
		$this->assertEmpty( $rule->get_more_content_url() );
	}

	public function test_get_field_html() {

		$rule = new IT_Exchange_Membership_Content_Rule_Post( 'post' );

		$html = $rule->get_field_html( 'new' );

		$this->assertRegExp( '/id="new-post"/', $html, 'Select id attr not found.' );
		$this->assertRegExp( '/name="new\[term\]"/', $html, 'Select name attr not found.' );
	}

	public function test_save_creates_rule() {

		$post       = $this->factory()->post->create_and_get();
		$membership = $this->product_factory->create_and_get( array( 'type' => 'membership-product-type' ) );

		$rule = new IT_Exchange_Membership_Content_Rule_Post( 'post', $membership );
		$rule->save( array(
			'term' => $post->ID
		) );

		$this->assertNotEmpty( $rule->get_rule_id() );

		$factory = new IT_Exchange_Membership_Rule_Factory();
		$this->assertNotEmpty( $factory->make_content_rule_by_id( $rule->get_rule_id(), $membership ) );

		$post_rules = $factory->make_all_for_post( $post );
		$this->assertNotEmpty( array_filter( $post_rules, function ( IT_Exchange_Membership_Content_Rule $other ) use ( $rule ) {
			return $other->get_rule_id() == $rule->get_rule_id();
		} ) );
	}

	public function test_save_updates_rule() {

		$post1 = $this->factory()->post->create_and_get();
		$post2 = $this->factory()->post->create_and_get();

		$membership = $this->product_factory->create_and_get( array( 'type' => 'membership-product-type' ) );

		$rule = new IT_Exchange_Membership_Content_Rule_Post( 'post', $membership );
		$rule->save( array(
			'term' => $post1->ID
		) );

		$rule->save( array(
			'term' => $post2->ID
		) );

		$this->assertEquals( $post2->ID, $rule->get_term() );

		$factory = new IT_Exchange_Membership_Rule_Factory();
		$rule    = $factory->make_content_rule_by_id( $rule->get_rule_id(), $membership );

		$this->assertEquals( $post2->ID, $rule->get_term() );
	}

	public function test_delete_rule() {

		$post       = $this->factory()->post->create_and_get();
		$membership = $this->product_factory->create_and_get( array( 'type' => 'membership-product-type' ) );

		$rule = new IT_Exchange_Membership_Content_Rule_Post( 'post', $membership );
		$rule->save( array(
			'term' => $post->ID
		) );
		$rule->delete();

		$factory = new IT_Exchange_Membership_Rule_Factory();
		$this->assertEmpty( $factory->make_content_rule_by_id( $rule->get_rule_id(), $membership ) );
	}

	public function tset_delete_rule_deletes_delay_rule() {

		$delay = m::mock( 'IT_Exchange_Membership_Delay_Rule' );
		$delay->shouldReceive( 'delete' );

		$membership = $this->product_factory->create_and_get( array( 'type' => 'membership-product-type' ) );

		$rule = new IT_Exchange_Membership_Content_Rule_Post( 'post', $membership, array( 'term' => 1 ) );
		$rule->set_delay_rule( $delay );
		$rule->delete();
	}

	public function test_get_type() {
		$rule = new IT_Exchange_Membership_Content_Rule_Post( 'post' );
		$this->assertEquals( 'posts', $rule->get_type() ); // simple test to catch typos
	}
}