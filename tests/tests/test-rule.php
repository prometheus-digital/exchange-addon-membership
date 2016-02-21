<?php

/**
 * Rule factory unit tests.
 *
 * @since   1.18
 * @license GPLv2
 */
class IT_Exchange_Membership_Rule_Factory_Test extends IT_Exchange_UnitTestCase {

	private static $membership;

	/**
	 * @var IT_Exchange_Membership_Rule_Factory
	 */
	private static $factory;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$factory    = new IT_Exchange_Membership_Rule_Factory();
		self::$membership = self::product_factory()->create_and_get( array(
			'product-type' => 'membership-product-type'
		) );
	}

	public function test_make_content_rule_posts() {

		$data = array(
			'selected'  => 'posts',
			'selection' => 'post'
		);

		$this->assertInstanceOf( 'IT_Exchange_Membership_Content_Rule_Post', self::$factory->make_content_rule( 'posts', $data ) );
	}

	public function test_make_content_rule_post_types() {

		$data = array(
			'selected' => 'post_types'
		);

		$this->assertInstanceOf( 'IT_Exchange_Membership_Content_Rule_Post_Type', self::$factory->make_content_rule( 'post_types', $data ) );
	}

	public function test_make_content_rule_term() {

		$data = array(
			'selected'  => 'taxonomy',
			'selection' => 'category'
		);

		$this->assertInstanceOf( 'IT_Exchange_Membership_Content_Rule_Term', self::$factory->make_content_rule( 'taxonomy', $data ) );
	}

	public function test_make_content_rule_by_id() {

		$membership = $this->getMockBuilder( 'IT_Exchange_Membership' )->disableOriginalConstructor()->getMock();
		$membership->method( 'get_feature' )->with( 'membership-content-access-rules' )->willReturn( array(
			array(
				'id'        => 'my-fav-id',
				'selected'  => 'posts',
				'selection' => 'post'
			)
		) );

		$rule = self::$factory->make_content_rule_by_id( 'my-fav-id', $membership );

		$this->assertInstanceOf( 'IT_Exchange_Membership_Content_Rule_Post', $rule );
		$this->assertEquals( 'my-fav-id', $rule->get_rule_id() );
	}

	public function test_make_delay_rule_drip() {

		$membership = $this->getMockBuilder( 'IT_Exchange_Membership' )->disableOriginalConstructor()->getMock();
		$rule       = $this->getMock( 'IT_Exchange_Membership_Rule_Delayable' );

		$delay = self::$factory->make_delay_rule( 'drip', $membership, $rule );

		$this->assertInstanceOf( 'IT_Exchange_Membership_Delay_Rule_Drip', $delay );
	}

	public function test_make_delay_rule_date() {

		$membership = $this->getMockBuilder( 'IT_Exchange_Membership' )->disableOriginalConstructor()->getMock();
		$rule       = $this->getMock( 'IT_Exchange_Membership_Rule_Delayable' );

		$delay = self::$factory->make_delay_rule( 'date', $membership, $rule );

		$this->assertInstanceOf( 'IT_Exchange_Membership_Delay_Rule_Date', $delay );
	}

	public function test_make_content_rule_attaches_delay_rules() {

		$data = array(
			'selected'   => 'posts',
			'selection'  => 'post',
			'delay-type' => 'date'
		);

		$membership = $this->getMockBuilder( 'IT_Exchange_Membership' )->disableOriginalConstructor()->getMock();

		$rule = self::$factory->make_content_rule( 'posts', $data, $membership );

		$this->assertInstanceOf( 'IT_Exchange_Membership_Content_Rule_Post', $rule );
		$this->assertInstanceOf( 'IT_Exchange_Membership_Delay_Rule_Date', $rule->get_delay_rule() );
	}

	public function test_make_all_for_membership_returns_empty_array() {

		$mock = $this->getMockBuilder( 'IT_Exchange_Membership' )->disableOriginalConstructor()->getMock();
		$mock->method( 'get_feature' )->willReturn( false );

		$rules = self::$factory->make_all_for_membership( $mock );

		$this->assertInternalType( 'array', $rules );
		$this->assertEmpty( $rules );
	}

	public function test_make_all_for_membership_skips_rule_without_selected_value() {

		$mock = $this->getMockBuilder( 'IT_Exchange_Membership' )->disableOriginalConstructor()->getMock();
		$mock->method( 'get_feature' )->with( 'membership-content-access-rules' )->willReturn( array(
			array(
				'selection' => 'posttype'
			)
		) );

		$rules = self::$factory->make_all_for_membership( $mock );

		$this->assertInternalType( 'array', $rules );
		$this->assertEmpty( $rules );
	}

	public function test_make_all_for_membership() {

		$mock = $this->getMockBuilder( 'IT_Exchange_Membership' )->disableOriginalConstructor()->getMock();
		$mock->method( 'get_feature' )->with( 'membership-content-access-rules' )->willReturn( array(
			array(
				'selected'  => 'posts',
				'selection' => 'post'
			),
			array(
				'selected' => 'post_types'
			),
			array(
				'selected'  => 'taxonomy',
				'selection' => 'category'
			)
		) );

		$rules = self::$factory->make_all_for_membership( $mock );

		$this->assertContainsOnlyInstancesOf( 'IT_Exchange_Membership_Content_Rule', $rules );
		$this->assertEquals( 3, count( $rules ) );
	}

	public function test_make_all_for_membership_grouped() {

		$mock = $this->getMockBuilder( 'IT_Exchange_Membership' )->disableOriginalConstructor()->getMock();
		$mock->method( 'get_feature' )->with( 'membership-content-access-rules' )->willReturn( array(
			array(
				'selected'   => 'posts',
				'selection'  => 'post',
				'id'         => 'my-id-1',
				'grouped_id' => ''
			),
			array(
				'selected'   => 'posts',
				'selection'  => 'page',
				'id'         => 'my-id-2',
				'grouped_id' => ''
			),
			array(
				'group_id'     => 0,
				'group'        => 'My Group 0',
				'group_layout' => 'grid'
			),
			array(
				'selected'   => 'posts',
				'selection'  => 'page',
				'id'         => 'my-id-3',
				'grouped_id' => 0
			),
			array(
				'selected'   => 'posts',
				'selection'  => 'post',
				'id'         => 'my-id-4',
				'grouped_id' => ''
			),
			array(
				'group_id'     => 1,
				'group'        => 'My Group 1',
				'group_layout' => 'list'
			),
			array(
				'selected'   => 'posts',
				'selection'  => 'page',
				'id'         => 'my-id-5',
				'grouped_id' => 1
			),
			array(
				'selected'   => 'posts',
				'selection'  => 'post',
				'id'         => 'my-id-6',
				'grouped_id' => 1
			),
			array(
				'selected'   => 'posts',
				'selection'  => 'post',
				'id'         => 'my-id-7',
				'grouped_id' => ''
			)
		) );

		$grouped = self::$factory->make_all_for_membership_grouped( $mock );

		$this->assertInternalType( 'array', $grouped );
		$this->assertEquals( 6, count( $grouped ) );

		$this->assertEquals( 'my-id-1', $grouped[0]->get_rule_id() );
		$this->assertEquals( 'my-id-2', $grouped[1]->get_rule_id() );
		$this->assertInstanceOf( 'IT_Exchange_Membership_Content_Rule_Group', $grouped[2] );

		/** @var IT_Exchange_Membership_Content_Rule_Group $group_0 */
		$group_0       = $grouped[2];
		$group_0_rules = $group_0->get_rules();

		$this->assertEquals( 'My Group 0', $group_0->get_name() );
		$this->assertEquals( 1, count( $group_0_rules ) );
		$this->assertEquals( 'my-id-3', $group_0_rules[0]->get_rule_id() );

		$this->assertEquals( 'my-id-4', $grouped[3]->get_rule_id() );

		/** @var IT_Exchange_Membership_Content_Rule_Group $group_1 */
		$group_1       = $grouped[4];
		$group_1_rules = $group_1->get_rules();

		$this->assertEquals( 'My Group 1', $group_1->get_name() );
		$this->assertEquals( 2, count( $group_1_rules ) );
		$this->assertEquals( 'my-id-5', $group_1_rules[0]->get_rule_id() );
		$this->assertEquals( 'my-id-6', $group_1_rules[1]->get_rule_id() );

		$this->assertEquals( 'my-id-7', $grouped[5]->get_rule_id() );
	}

}