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
	 * @return IT_Exchange_Membership_Content_RuleInterface[]
	 */
	public function evaluate( WP_Post $post, IT_Exchange_Customer $customer = null ) {

		if ( ! $customer ) {
			return $this->factory->make_all_for_post( $post );
		}

		$subscriptions = it_exchange_get_customer_membership_subscriptions( $customer );

		$failed = array();

		foreach ( $subscriptions as $subscription ) {
			$rules = $this->factory->make_all_for_membership( $subscription->get_product() );

			foreach ( $rules as $rule ) {

				if ( ! $rule->matches_post( $post ) ) {
					continue;
				}

				if ( ! $rule->evaluate( $subscription, $post ) ) {
					$failed[] = $rule;
				}
			}
		}

		return $failed;
	}

	/**
	 * Evaluate drip rules.
	 *
	 * @since 1.18
	 *
	 * @param WP_Post              $post
	 * @param IT_Exchange_Customer $customer
	 *
	 * @return IT_Exchange_Membership_Delay_Rule_Drip[]
	 */
	public function evaluate_drip( WP_Post $post, IT_Exchange_Customer $customer ) {

		$subscriptions = it_exchange_get_customer_membership_subscriptions( $customer );
		$failed        = array();

		/** @var IT_Exchange_Subscription $subscription */
		foreach ( $subscriptions as $subscription ) {
			$rules = $this->factory->make_all_for_membership( $subscription->get_product() );

			foreach ( $rules as $rule ) {

				if ( ! $rule instanceof IT_Exchange_Membership_Content_Rule_Delayable ) {
					continue;
				}

				if ( ! $rule->matches_post( $post ) ) {
					continue;
				}

				if ( ! $rule->evaluate( $subscription, $post ) ) {
					continue;
				}

				$delay = $rule->get_delay_rule();

				if ( empty( $delay ) ) {
					continue;
				}

				if ( ! $delay->evaluate( $subscription, $post ) ) {
					$failed[] = $delay;
				}
			}
		}

		return $failed;
	}
}