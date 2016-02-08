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
	 * @return IT_Exchange_Membership_Content_RuleInterface[]
	 */
	public function make_all_for_post( WP_Post $post ) {

		$memberships = $this->get_possible_membership_ids_for_post( $post );

		if ( ! $memberships ) {
			return array();
		}

		$rules      = array();
		$this->post = $post;

		foreach ( $memberships as $membership ) {
			$rules = array_merge( $rules, $this->make_all_for_membership( it_exchange_get_product( $membership ) ) );
		}

		$rules = array_filter( $rules, array( $this, '_filter' ) );

		$this->post = null;

		return $rules;
	}

	/**
	 * Get all possible membership IDs for a post.
	 *
	 * This iterates through the per-post rules, post type rules, and taxonomy rules.
	 *
	 * @since 1.18
	 *
	 * @param WP_Post $post
	 *
	 * @return array
	 */
	protected function get_possible_membership_ids_for_post( WP_Post $post ) {

		$IDs = get_post_meta( $post->ID, '_item-content-rule', true );
		$IDs = is_array( $IDs ) ? $IDs : array();

		$post_type = get_post_type( $post );

		$IDs = array_merge( $IDs, get_option( "_item-content-rule-post-type-$post_type", array() ) );

		$terms = wp_get_object_terms( $post->ID, get_taxonomies( array( 'public' => true ) ) );

		/** @var WP_Term $term */
		foreach ( $terms as $term ) {
			$IDs = array_merge( $IDs, get_option( "_item-content-rule-tax-$term->taxonomy-$term->term_id", array() ) );
		}

		return array_unique( $IDs );
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
	 * Make all memberships, grouped.
	 *
	 * This returns
	 *
	 * @since 1.18
	 *
	 * @param IT_Exchange_Membership $membership
	 *
	 * @return array
	 */
	public function make_all_for_membership_grouped( IT_Exchange_Membership $membership ) {

		$rules = $membership->get_feature( 'membership-content-access-rules' );

		$objects = array();

		if ( ! is_array( $rules ) ) {
			return array();
		}

		$groups = array();

		foreach ( $rules as $rule ) {
			if ( isset( $rule['group_id'] ) && ! isset( $groups[ $rule['group_id'] ] ) ) {

				$ID = $rule['group_id'];

				$groups[ $ID ] = new IT_Exchange_Membership_Content_Rule_Group( $rule['group'], $rule['group_layout'], $ID );
			}
		}

		$added_group_ids = array();

		$i = 0;

		foreach ( $rules as $rule ) {

			if ( empty( $rule['selected'] ) ) {
				continue;
			}

			$object = $this->make_content_rule( $rule['selected'], $rule, $membership );

			if ( isset( $rule['grouped_id'] ) && trim( $rule['grouped_id'] ) !== '' ) {

				$group_id = $rule['grouped_id'];

				if ( isset( $groups[ $group_id ] ) ) {
					$group = $groups[ $group_id ];
					/** @var IT_Exchange_Membership_Content_Rule_Group $group */
					$group->add_rule( $object );

					if ( ! in_array( $group_id, $added_group_ids ) ) {
						$objects[ $i ]     = $group;
						$added_group_ids[] = $group_id;
					}
				} else {
					$objects[ $i ] = $object;
				}
			} else {
				$objects[ $i ] = $object;
			}

			$i ++;
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
	public function make_content_rule( $type, $data, IT_Exchange_Membership $membership = null ) {

		switch ( $type ) {
			case 'posts':
				$rule = new IT_Exchange_Membership_Content_Rule_Post( $data['selection'], $membership, $data );

				if ( isset( $data['term'] ) ) {
					$this->attach_delay_rules( $rule, $membership, get_post( $data['term'] ) );
				}

				return $rule;
			case 'post_types':
				return new IT_Exchange_Membership_Content_Rule_Post_Type( $membership, $data );
			case 'taxonomy':
				return new IT_Exchange_Membership_Content_Rule_Term( $data['selection'], $membership, $data );
			default:

				/**
				 * Filter the rule for unknown types.
				 *
				 * @since 1.18
				 *
				 * @param IT_Exchange_Membership_Content_RuleInterface $rule
				 * @param string                                       $type
				 * @param array                                        $data
				 * @param IT_Exchange_Membership                       $membership
				 */
				$rule = apply_filters( 'it_exchange_membership_rule_factory_make_rule', null, $type, $data, $membership );

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
	 * @param IT_Exchange_Membership_Content_Rule_Delayable $rule
	 * @param IT_Exchange_Membership                       $membership
	 * @param WP_Post                                      $post
	 */
	protected function attach_delay_rules( IT_Exchange_Membership_Content_Rule_Delayable $rule, IT_Exchange_Membership $membership, WP_Post $post ) {

		$interval = get_post_meta( $post->ID, '_item-content-rule-drip-interval-' . $membership->ID, true );

		// we don't currently store the type of delay rule, so we just need to check that the metadata is there
		if ( $interval ) {
			$rule->set_delay_rule( new IT_Exchange_Membership_Delay_Rule_Drip( $post, $membership ) );
		}
	}

}