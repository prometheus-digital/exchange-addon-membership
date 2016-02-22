<?php

use Mockery as m;

/**
 * Class IT_Exchange_Membership_Content_Rule_Post_Type_Test
 */
class IT_Exchange_Membership_Content_Rule_Post_Type_Test extends IT_Exchange_UnitTestCase {

	public function test_get_matching_posts() {

		$post = $this->factory()->post->create_and_get();
		$this->factory()->post->create( array( 'post_type' => 'page' ) );

		$rule = new IT_Exchange_Membership_Content_Rule_Post_Type( null, array( 'term' => 'post' ) );
		$this->assertEquals( array( $post ), $rule->get_matching_posts() );
	}

	public function test_has_no_more_content_url() {
		$rule = new IT_Exchange_Membership_Content_Rule_Post_Type( null, array( 'term' => 'post' ) );
		$this->assertNotEmpty( $rule->get_more_content_url() );
	}

	public function test_get_field_html() {

		$rule = new IT_Exchange_Membership_Content_Rule_Post_Type();

		$html = $rule->get_field_html( 'new' );

		$this->assertRegExp( '/id="new-post-types"/', $html, 'Select id attr not found.' );
		$this->assertRegExp( '/name="new\[term\]"/', $html, 'Select name attr not found.' );
	}

	public function test_save_creates_rule() {

		$post       = $this->factory()->post->create_and_get();
		$membership = $this->product_factory->create_and_get( array( 'type' => 'membership-product-type' ) );

		$rule = new IT_Exchange_Membership_Content_Rule_Post_Type( $membership );
		$rule->save( array(
			'term' => 'post'
		) );

		$this->assertNotEmpty( $rule->get_rule_id() );

		$factory = new IT_Exchange_Membership_Rule_Factory();
		$rule    = $factory->make_content_rule_by_id( $rule->get_rule_id(), $membership );

		$this->assertNotEmpty( $rule );
		$this->assertEquals( 'post', $rule->get_term() );

		$post_rules = $factory->make_all_for_post( $post );
		$this->assertNotEmpty( array_filter( $post_rules, function ( IT_Exchange_Membership_Content_Rule $other ) use ( $rule ) {
			return $other->get_rule_id() == $rule->get_rule_id();
		} ) );
	}

	public function test_save_updates_rule() {

		register_post_type( 'my-test-type' );

		$membership = $this->product_factory->create_and_get( array( 'type' => 'membership-product-type' ) );

		$rule = new IT_Exchange_Membership_Content_Rule_Post_Type( $membership );
		$rule->save( array(
			'term' => 'post'
		) );

		$rule->save( array(
			'term' => 'my-test-type'
		) );

		$this->assertEquals( 'my-test-type', $rule->get_term() );

		$factory = new IT_Exchange_Membership_Rule_Factory();
		$rule    = $factory->make_content_rule_by_id( $rule->get_rule_id(), $membership );

		$this->assertEquals( 'my-test-type', $rule->get_term() );

		_unregister_post_type( 'my-test-type' );
	}

	public function test_delete_rule() {

		$membership = $this->product_factory->create_and_get( array( 'type' => 'membership-product-type' ) );

		$rule = new IT_Exchange_Membership_Content_Rule_Post_Type( $membership );
		$rule->save( array(
			'term' => 'post'
		) );
		$rule->delete();

		$factory = new IT_Exchange_Membership_Rule_Factory();
		$this->assertEmpty( $factory->make_content_rule_by_id( $rule->get_rule_id(), $membership ) );
	}

	public function test_get_type() {
		$rule = new IT_Exchange_Membership_Content_Rule_Post_Type();
		$this->assertEquals( 'post_types', $rule->get_type() ); // simple test to catch typos
	}
}