<?php

use Mockery as m;

/**
 * Class IT_Exchange_Membership_Content_Rule_Term_Test
 */
class IT_Exchange_Membership_Content_Rule_Term_Test extends IT_Exchange_UnitTestCase {

	/**
	 * Teardown the test case.
	 */
	function tearDown() {
		parent::tearDown();

		m::close();
	}

	public function test_delay_meta() {

		$rule = new IT_Exchange_Membership_Content_Rule_Term( 'category', null, array( 'term' => 1 ) );
		$rule->update_delay_meta( 'my-meta', 'my-value' );

		$this->assertEquals( 'my-value', $rule->get_delay_meta( 'my-meta' ) );

		$rule = new IT_Exchange_Membership_Content_Rule_Term( 'category', null, array( 'term' => 1 ) );
		$this->assertEquals( 'my-value', $rule->get_delay_meta( 'my-meta' ) );

		$rule->delete_delay_meta( 'my-meta' );
		$this->assertEmpty( $rule->get_delay_meta( 'my-meta' ) );
	}

	public function test_matches_post() {

		wp_set_current_user( 1 );

		$term  = $this->factory()->term->create_and_get( array( 'taxonomy' => 'category' ) );
		$post  = $this->factory()->post->create_and_get( array( 'tax_input' => array( 'category' => $term->term_id ) ) );
		$post2 = $this->factory()->post->create_and_get();

		$rule = new IT_Exchange_Membership_Content_Rule_Term( 'category', null, array( 'term' => $term->term_id ) );

		$this->assertTrue( $rule->matches_post( $post ) );
		$this->assertFalse( $rule->matches_post( $post2 ) );
	}

	public function test_get_matching_posts() {

		wp_set_current_user( 1 );

		$term = $this->factory()->term->create_and_get( array( 'taxonomy' => 'category' ) );
		$post = $this->factory()->post->create_and_get( array( 'tax_input' => array( 'category' => $term->term_id ) ) );
		$this->factory()->post->create();

		$rule = new IT_Exchange_Membership_Content_Rule_Term( 'category', null, array( 'term' => $term->term_id ) );
		$this->assertEquals( array( $post ), $rule->get_matching_posts() );
	}

	public function test_has_no_more_content_url() {

		$term = $this->factory()->term->create_and_get( array( 'taxonomy' => 'category' ) );

		$rule = new IT_Exchange_Membership_Content_Rule_Term( 'category', null, array( 'term' => $term->term_id ) );
		$this->assertNotEmpty( $rule->get_more_content_url() );
	}

	public function test_get_field_html() {

		$rule = new IT_Exchange_Membership_Content_Rule_Term( 'category' );

		$html = $rule->get_field_html( 'new' );

		$this->assertRegExp( '/id="new-term"/', $html, 'Select id attr not found.' );
		$this->assertRegExp( '/name="new\[term\]"/', $html, 'Select name attr not found.' );
	}

	public function test_save_creates_rule() {

		wp_set_current_user( 1 );

		$term = $this->factory()->term->create_and_get( array( 'taxonomy' => 'category' ) );
		$post = $this->factory()->post->create_and_get( array( 'tax_input' => array( 'category' => $term->term_id ) ) );

		$membership = $this->product_factory->create_and_get( array( 'type' => 'membership-product-type' ) );

		$rule = new IT_Exchange_Membership_Content_Rule_Term( 'category', $membership );
		$rule->save( array(
			'term' => $term->term_id
		) );

		$this->assertNotEmpty( $rule->get_rule_id() );

		$factory = new IT_Exchange_Membership_Rule_Factory();
		$rule    = $factory->make_content_rule_by_id( $rule->get_rule_id(), $membership );

		$this->assertNotEmpty( $rule );
		$this->assertEquals( $term->term_id, $rule->get_term() );

		$post_rules = $factory->make_all_for_post( $post );
		$this->assertNotEmpty( array_filter( $post_rules, function ( IT_Exchange_Membership_Content_Rule $other ) use ( $rule ) {
			return $other->get_rule_id() == $rule->get_rule_id();
		} ) );
	}

	public function test_save_updates_rule() {

		$term1 = $this->factory()->term->create_and_get( array( 'taxonomy' => 'category' ) );
		$term2 = $this->factory()->term->create_and_get( array( 'taxonomy' => 'category' ) );

		$membership = $this->product_factory->create_and_get( array( 'type' => 'membership-product-type' ) );

		$rule = new IT_Exchange_Membership_Content_Rule_Term( 'category', $membership );
		$rule->save( array(
			'term' => $term1->term_id
		) );

		$rule->save( array(
			'term' => $term2->term_id
		) );

		$this->assertEquals( $term2->term_id, $rule->get_term() );

		$factory = new IT_Exchange_Membership_Rule_Factory();
		$rule    = $factory->make_content_rule_by_id( $rule->get_rule_id(), $membership );

		$this->assertEquals( $term2->term_id, $rule->get_term() );
	}

	public function test_delete_rule() {

		$term       = $this->factory()->term->create_and_get();
		$membership = $this->product_factory->create_and_get( array( 'type' => 'membership-product-type' ) );

		$rule = new IT_Exchange_Membership_Content_Rule_Term( 'category', $membership );
		$rule->save( array(
			'term' => $term->term_id
		) );
		$rule->delete();

		$factory = new IT_Exchange_Membership_Rule_Factory();
		$this->assertEmpty( $factory->make_content_rule_by_id( $rule->get_rule_id(), $membership ) );
	}

	public function tset_delete_rule_deletes_delay_rule() {

		$delay = m::mock( 'IT_Exchange_Membership_Delay_Rule' );
		$delay->shouldReceive( 'delete' );

		$membership = $this->product_factory->create_and_get( array( 'type' => 'membership-product-type' ) );

		$rule = new IT_Exchange_Membership_Content_Rule_Term( 'category', $membership, array( 'term' => 1 ) );
		$rule->set_delay_rule( $delay );
		$rule->delete();
	}

	public function test_get_type() {
		$rule = new IT_Exchange_Membership_Content_Rule_Term( 'category' );
		$this->assertEquals( 'taxonomy', $rule->get_type() ); // simple test to catch typos
	}
}