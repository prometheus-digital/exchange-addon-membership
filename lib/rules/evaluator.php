<?php
/**
 * Contains the rule evaluator service class.
 *
 * @since   1.18
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Membership_Rule_Evaluator_Service
 */
class IT_Exchange_Membership_Rule_Evaluator_Service {

	/**
	 * @var IT_Exchange_Membership_Rule_Factory
	 */
	private $factory;

	/**
	 * @var IT_Exchange_User_Membership_Repository
	 */
	private $repository;

	/**
	 * IT_Exchange_Membership_Rule_Evaluator_Service constructor.
	 *
	 * @param IT_Exchange_Membership_Rule_Factory    $factory
	 * @param IT_Exchange_User_Membership_Repository $repository
	 */
	public function __construct( IT_Exchange_Membership_Rule_Factory $factory, IT_Exchange_User_Membership_Repository $repository ) {
		$this->factory    = $factory;
		$this->repository = $repository;
	}

	/**
	 * Evaluate customer access.
	 *
	 * @since 1.18
	 *
	 * @param WP_Post              $post
	 * @param IT_Exchange_Customer $customer
	 *
	 * @return IT_Exchange_Membership_Content_Rule[]
	 */
	public function evaluate_content( WP_Post $post, IT_Exchange_Customer $customer = null ) {

		$post_rules       = $this->factory->make_all_for_post( $post );
		$user_memberships = $this->repository->get_user_memberships( $customer );

		$wrong_membership_rules = array();
		$exempted_rules         = array();
		$passed_rules           = array();
		$failed_rules           = array();

		if ( empty( $user_memberships ) ) {
			$failed_rules = $post_rules;
		}

		foreach ( $post_rules as $post_rule ) {

			foreach ( $user_memberships as $user_membership ) {
				if ( $post_rule->get_membership()->ID != $user_membership->get_membership()->ID ) {
					$wrong_membership_rules[] = $post_rule;
				} elseif ( $post_rule->is_post_exempt( $post ) ) {
					$exempted_rules[] = $post_rule;
				} elseif ( $post_rule->evaluate( $user_membership, $post ) ) {
					$passed_rules[] = $post_rule;
				} else {
					$failed_rules[] = $post_rule;
				}
			}

			if ( empty( $user_memberships ) && $post_rule->is_post_exempt( $post ) ) {
				$exempted_rules[] = $post_rule;
			}
		}

		if ( empty( $passed_rules ) && empty( $failed_rules ) ) {
			return array_values( array_udiff( $wrong_membership_rules, $exempted_rules, __CLASS__ . '::_udiff' ) );
		}

		return array_values( array_udiff( $failed_rules, $exempted_rules, __CLASS__ . '::_udiff' ) );
	}

	/**
	 * Evaluate drip rules.
	 *
	 * A customer only needs one drip rule to pass to be granted access.
	 *
	 * @since 1.18
	 *
	 * @param WP_Post              $post
	 * @param IT_Exchange_Customer $customer
	 *
	 * @return array|null Null if customer has immediate access,
	 *                    or array with 'rule' containing rule granting earliest access and
	 *                    'membership' containing associated user membership.
	 */
	public function evaluate_drip( WP_Post $post, IT_Exchange_Customer $customer ) {

		$user_memberships = $this->repository->get_user_memberships( $customer );

		/** @var IT_Exchange_Membership_Delay_Rule $failed */
		$failed            = null;
		$failed_membership = null;

		foreach ( $user_memberships as $user_membership ) {
			$rules = $this->factory->make_all_for_membership( $user_membership->get_membership() );

			foreach ( $rules as $rule ) {

				if ( ! $rule instanceof IT_Exchange_Membership_Rule_Delayable ) {
					continue;
				}

				if ( ! $rule->matches_post( $post ) ) {
					continue;
				}

				if ( ! $rule->evaluate( $user_membership, $post ) ) {
					continue;
				}

				$delay = $rule->get_delay_rule();

				if ( empty( $delay ) ) {
					continue;
				}

				if ( $delay->evaluate( $user_membership, $post ) ) {
					return null;
				}

				$available = $delay->get_availability_date( $user_membership );

				if ( is_null( $failed ) ) {
					$failed            = $delay;
					$failed_membership = $user_membership;
				} elseif ( ! $failed->get_availability_date( $user_membership ) ) {
					$failed            = $delay;
					$failed_membership = $user_membership;
				} elseif ( $available && $failed->get_availability_date( $user_membership ) > $available ) {
					$failed            = $delay;
					$failed_membership = $user_membership;
				}
			}
		}

		if ( is_null( $failed ) ) {
			return null;
		}

		return array(
			'rule'       => $failed,
			'membership' => $failed_membership
		);
	}

	/**
	 * Udiff for rule objects.
	 *
	 * @since 1.18
	 *
	 * @param IT_Exchange_Membership_Content_Rule $a
	 * @param IT_Exchange_Membership_Content_Rule $b
	 *
	 * @return int
	 */
	public static function _udiff( IT_Exchange_Membership_Content_Rule $a, IT_Exchange_Membership_Content_Rule $b ) {
		return strcmp( $a->get_rule_id(), $b->get_rule_id() );
	}
}