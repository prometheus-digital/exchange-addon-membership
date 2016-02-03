<?php
/**
 * Rule factory.
 *
 * @since   1.18
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Membership_Rule_Factory
 */
class IT_Exchange_Membership_Rule_Factory {

	/**
	 * @var WP_Post
	 */
	private $post;

	/**
	 * Make all rules for a post.
	 *
	 * @since 1.18
	 *
	 * @param WP_Post $post
	 *
	 * @return array
	 */
	public function make_all_for_post( WP_Post $post ) {

		$memberships = get_post_meta( $post->ID, '_item-content-rule', true );

		if ( ! $memberships ) {
			return array();
		}

		$rules      = array();
		$this->post = $post;

		foreach ( $memberships as $membership ) {

			$membership_rules = $this->make_all_for_membership( it_exchange_get_product( $membership ) );
			$membership_rules = array_filter( $membership_rules, array( $this, '_filter' ) );

			$rules[ $membership ] = $membership_rules;
		}

		$this->post = null;

		return $rules;
	}

	/**
	 * Filter callback.
	 *
	 * @internal
	 *
	 * @param IT_Exchange_Membership_Content_RuleInterface $rule
	 *
	 * @return bool
	 */
	public function _filter( IT_Exchange_Membership_Content_RuleInterface $rule ) {
		return $rule->matches_post( $this->post );
	}

	/**
	 * Make all rules for a membership.
	 *
	 * @since 1.18
	 *
	 * @param IT_Exchange_Membership $membership
	 *
	 * @return IT_Exchange_Membership_Content_RuleInterface[]
	 */
	public function make_all_for_membership( IT_Exchange_Membership $membership ) {

		$rules = $membership->get_feature( 'membership-content-access-rules' );

		if ( ! is_array( $rules ) ) {
			return array();
		}

		$objects = array();

		foreach ( $rules as $rule ) {

			if ( empty( $rule['selected'] ) ) {
				continue;
			}

			$objects[] = $this->make_content_rule( $rule['selected'], $rule, $membership );
		}

		return $objects;
	}

	/**
	 * Make an individual content rule.
	 *
	 * @since 1.18
	 *
	 * @param string                 $type
	 * @param array                  $data
	 * @param IT_Exchange_Membership $membership
	 *
	 * @return IT_Exchange_Membership_Content_RuleInterface
	 */
	public function make_content_rule( $type, $data, IT_Exchange_Membership $membership ) {

		switch ( $type ) {
			case 'posts':
				$rule = new IT_Exchange_Membership_Content_Rule_Post( $data['selection'], $data );
				$this->attach_delay_rules( $rule, $membership, get_post( $data['term'] ) );

				return $rule;
			case 'post_types':
				return new IT_Exchange_Membership_Content_Rule_Post_Type( $data );
			case 'taxonomy':
				return new IT_Exchange_Membership_Content_Rule_Term( $data['selection'], $data );
			default:

				/**
				 * Filter the rule for unknown types.
				 *
				 * @since 1.18
				 *
				 * @param IT_Exchange_Membership_Content_RuleInterface $rule
				 * @param string                                       $type
				 * @param array                                        $data
				 */
				$rule = apply_filters( 'it_exchange_membership_rule_factory_make_rule', null, $type, $data );

				if ( $rule && ! $rule instanceof IT_Exchange_Membership_Content_RuleInterface ) {
					throw new UnexpectedValueException( 'Invalid class type for new rule.' );
				}

				return $rule;
		}
	}

	/**
	 * Attach delay rules to a content rule.
	 *
	 * @since 1.18
	 *
	 * @param IT_Exchange_Membership_Content_RuleInterface $rule
	 * @param IT_Exchange_Membership                       $membership
	 * @param WP_Post                                      $post
	 */
	protected function attach_delay_rules( IT_Exchange_Membership_Content_RuleInterface $rule, IT_Exchange_Membership $membership, WP_Post $post ) {

		$interval = get_post_meta( $post->ID, '_item-content-rule-drip-interval-' . $membership->ID, true );

		// we don't currently store the type of delay rule, so we just need to check that the metadata is there
		if ( $interval ) {
			$rule->add_delay_rule( new IT_Exchange_Membership_Delay_Rule_Drip( $post, $membership ) );
		}
	}

}