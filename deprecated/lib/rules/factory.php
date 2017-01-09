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
	 * @param array   $args
	 *
	 * @return IT_Exchange_Membership_Content_Rule[]
	 */
	public function make_all_for_post( WP_Post $post, array $args = array() ) {

		$args = wp_parse_args( $args, array(
			'include_non_published_memberships' => false
		) );

		$memberships = $this->get_possible_membership_ids_for_post( $post );

		if ( ! $memberships ) {
			return array();
		}

		$rules      = array();
		$this->post = $post;

		foreach ( $memberships as $membership ) {

			if ( ! $args['include_non_published_memberships'] && get_post_status( $membership ) !== 'publish' ) {
				continue;
			}

			$membership = it_exchange_get_product( $membership );

			if ( $membership instanceof IT_Exchange_Membership ) {
				$rules = array_merge( $rules, $this->make_all_for_membership( $membership ) );
			}
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
			$IDs = array_merge( $IDs, get_option( "_item-content-rule-tax-{$term->taxonomy}-{$term->term_id}", array() ) );
		}

		return array_unique( $IDs );
	}

	/**
	 * Filter callback.
	 *
	 * This will get removed when moved to PHP 5.3. This is an implementation detail, do not rely upon it.
	 *
	 * @internal
	 *
	 * @param IT_Exchange_Membership_Content_Rule $rule
	 *
	 * @return bool
	 */
	public function _filter( IT_Exchange_Membership_Content_Rule $rule = null ) {
		return $rule && $rule->matches_post( $this->post );
	}

	/**
	 * Make all rules for a membership.
	 *
	 * @since 1.18
	 *
	 * @param IT_Exchange_Membership $membership
	 * @param string                 $type
	 *
	 * @return IT_Exchange_Membership_Content_Rule[]
	 */
	public function make_all_for_membership( IT_Exchange_Membership $membership, $type = '' ) {

		$rules = $membership->get_feature( 'membership-content-access-rules' );

		if ( ! is_array( $rules ) ) {
			return array();
		}

		$objects = array();

		foreach ( $rules as $rule ) {

			if ( empty( $rule['selected'] ) ) {
				continue;
			}

			if ( ! empty( $type ) && $rule['selected'] !== $type ) {
				continue;
			}

			$object = $this->make_content_rule( $rule['selected'], $rule, $membership );

			if ( $object ) {
				$objects[] = $object;
			}
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

			if ( ! $object ) {
				continue;
			}

			if ( isset( $rule['grouped_id'] ) && trim( $rule['grouped_id'] ) !== '' ) {

				$group_id = $rule['grouped_id'];

				if ( isset( $groups[ $group_id ] ) ) {

					/** @var IT_Exchange_Membership_Content_Rule_Group $group */
					$group = $groups[ $group_id ];
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

		// ensure indexes are 0-based and continuous
		return array_values( $objects );
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
	 * @return IT_Exchange_Membership_Content_Rule
	 */
	public function make_content_rule( $type, $data, IT_Exchange_Membership $membership = null ) {

		switch ( $type ) {
			case 'posts':
				$rule = new IT_Exchange_Membership_Content_Rule_Post( $data['selection'], $membership, $data );
				break;
			case 'post_types':
				$rule = new IT_Exchange_Membership_Content_Rule_Post_Type( $membership, $data );
				break;
			case 'taxonomy':
				$rule = new IT_Exchange_Membership_Content_Rule_Term( $data['selection'], $membership, $data );
				break;
			default:

				/**
				 * Filter the rule for unknown types.
				 *
				 * @since 1.18
				 *
				 * @param IT_Exchange_Membership_Content_Rule $rule
				 * @param string                              $type
				 * @param array                               $data
				 * @param IT_Exchange_Membership              $membership
				 */
				$rule = apply_filters( 'it_exchange_membership_rule_factory_make_rule', null, $type, $data, $membership );

				if ( $rule && ! $rule instanceof IT_Exchange_Membership_Content_Rule ) {
					throw new UnexpectedValueException( 'Invalid class type for new rule.' );
				}
				break;
		}

		if ( $rule && $membership && $rule instanceof IT_Exchange_Membership_Rule_Delayable ) {
			$this->attach_delay_rules( $rule, $membership, $data );
		}

		return $rule;
	}

	/**
	 * Make a content rule by its ID.
	 *
	 * @since 1.18
	 *
	 * @param string                 $id
	 * @param IT_Exchange_Membership $membership
	 *
	 * @return IT_Exchange_Membership_Content_Rule|null
	 */
	public function make_content_rule_by_id( $id, IT_Exchange_Membership $membership ) {

		$rules = $membership->get_feature( 'membership-content-access-rules' );

		foreach ( $rules as $rule ) {
			if ( isset( $rule['id'] ) && $rule['id'] === $id ) {
				return $this->make_content_rule( $rule['selected'], $rule, $membership );
			}
		}

		return null;
	}

	/**
	 * Attach delay rules to a content rule.
	 *
	 * @since 1.18
	 *
	 * @param IT_Exchange_Membership_Rule_Delayable $rule
	 * @param IT_Exchange_Membership                $membership
	 * @param array                                 $data
	 */
	protected function attach_delay_rules( IT_Exchange_Membership_Rule_Delayable $rule, IT_Exchange_Membership $membership, $data ) {

		if ( ! isset( $data['delay-type'] ) ) {
			if ( $rule->get_delay_meta( '_item-content-rule-drip-interval-' . $membership->ID ) ) {
				$type = 'drip';
			} else {
				$type = '';
			}
		} else {
			$type = $data['delay-type'];
		}

		$delay = $this->make_delay_rule( $type, $membership, $rule );

		if ( $delay ) {
			$rule->set_delay_rule( $delay );
		}
	}

	/**
	 * Make a delay rule object.
	 *
	 * @since 1.18
	 *
	 * @param string                                $type
	 * @param IT_Exchange_Membership                $membership
	 * @param IT_Exchange_Membership_Rule_Delayable $rule
	 *
	 * @return IT_Exchange_Membership_Delay_Rule|null
	 */
	public function make_delay_rule( $type, IT_Exchange_Membership $membership, IT_Exchange_Membership_Rule_Delayable $rule ) {

		switch ( $type ) {
			case 'drip':
				return new IT_Exchange_Membership_Delay_Rule_Drip( $rule, $membership );
			case 'date':
				return new IT_Exchange_Membership_Delay_Rule_Date( $rule, $membership );
			default:

				/**
				 * Filter the delay rule for unknown types.
				 *
				 * @since 1.18
				 *
				 * @param IT_Exchange_Membership_Content_Rule   $rule
				 * @param string                                $type
				 * @param IT_Exchange_Membership                $membership
				 * @param IT_Exchange_Membership_Rule_Delayable $rule
				 */
				$rule = apply_filters( 'it_exchange_membership_rule_factory_make_delay_rule', null, $type, $membership, $rule );

				if ( $rule && ! $rule instanceof IT_Exchange_Membership_Delay_Rule ) {
					throw new UnexpectedValueException( 'Invalid class type for new delay rule.' );
				}

				return $rule;
		}
	}

}