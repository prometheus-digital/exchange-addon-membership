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
	 * IT_Exchange_Membership_Rule_Evaluator_Service constructor.
	 *
	 * @param IT_Exchange_Membership_Rule_Factory $factory
	 */
	public function __construct( IT_Exchange_Membership_Rule_Factory $factory ) {
		$this->factory = $factory;
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

		if ( ! $customer ) {
			return $this->factory->make_all_for_post( $post );
		}

		$user_memberships = it_exchange_get_user_memberships( $customer );

		if ( empty( $user_memberships ) ) {
			return $this->factory->make_all_for_post( $post );
		}

		$failed = array();

		foreach ( $user_memberships as $user_membership ) {
			$rules = $this->factory->make_all_for_membership( $user_membership->get_membership() );

			foreach ( $rules as $rule ) {

				if ( ! $rule->matches_post( $post ) ) {
					continue;
				}

				if ( ! $rule->evaluate( $user_membership, $post ) ) {
					$failed[] = $rule;
				}
			}
		}

		return $failed;
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
	 *                    'subscription' containing associated subscription.
	 */
	public function evaluate_drip( WP_Post $post, IT_Exchange_Customer $customer ) {

		$user_memberships = it_exchange_get_user_memberships( $customer );

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
}