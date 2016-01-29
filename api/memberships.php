<?php
/**
 * Contains memberships API functions.
 *
 * @since   1.18
 * @license GPLv2
 */

/**
 * Checks if current content should be restricted
 * if admin - false
 * if member has access - false
 * if post|posttype|taxonomy has rule - true (unless above rule overrides)
 * if exemption exists - true
 *
 * An exemption basically tells the Membership addon that a member who has access to
 * specific content should not have access to it. For instance, say you have a post in
 * a restricted category and you have two membership levels who have access to that category
 * but you only want that post to be visible to one of the memberships. By adding the
 * exemption for the other membership, they will no longer have access to that content.
 *
 * @since 1.0.0
 *
 * @param WP_Post $post
 *
 * @return bool
 */
function it_exchange_membership_addon_is_content_restricted( $post = null ) {

	if ( ! $post ) {
		global $post;
	}

	$restriction = false;

	if ( current_user_can( 'administrator' ) )
		return false;

	$member_access = it_exchange_membership_addon_get_customer_memberships();

	if ( !empty( $post ) ) {

		$restriction_exemptions = get_post_meta( $post->ID, '_item-content-rule-exemptions', true );

		if ( 'it_exchange_prod' !== $post->post_type ) {
			$post_rules = get_post_meta( $post->ID, '_item-content-rule', true );
			if ( !empty( $post_rules ) ) {
				if ( !empty( $member_access ) ) {
					foreach( $member_access as $product_id => $txn_id ) {
						if ( in_array( $product_id, $post_rules ) ) {
							return false;
						}
					}
				}
				foreach( $post_rules as $product_id ) {
					if ( !empty( $restriction_exemptions[$product_id] ) && in_array( $post->post_type, $restriction_exemptions[$product_id] ) ) {
						$restriction = false;
					} else {
						$restriction = true;
					}
				}
			}
		}

		$post_type_rules = get_option( '_item-content-rule-post-type-' . $post->post_type, array() );
		if ( !empty( $post_type_rules ) ) {
			if ( !empty( $member_access ) ) {
				foreach( $member_access as $product_id => $txn_id ) {
					if ( in_array( $product_id, $post_type_rules ) ) {
						return false;
					}
				}
			}
			foreach( $post_type_rules as $product_id ) {
				if ( !empty( $restriction_exemptions[$product_id] ) && in_array( 'posttype', $restriction_exemptions[$product_id] ) ) {
					$restriction = false;
				} else {
					$restriction = true;
				}
			}
		}

		$taxonomy_rules = array();
		$taxonomies = get_object_taxonomies( $post->post_type );
		$terms = wp_get_object_terms( $post->ID, $taxonomies );
		foreach( $terms as $term ) {
			$term_rules = get_option( '_item-content-rule-tax-' . $term->taxonomy . '-' . $term->term_id, array() );
			if ( !empty( $term_rules ) ) {
				if ( !empty( $member_access ) ) {
					foreach( $member_access as $product_id => $txn_id ) {
						if ( in_array( $product_id, $term_rules ) )
							return false;
					}
				}
				foreach( $term_rules as $product_id ) {
					if ( !empty( $restriction_exemptions[$product_id] ) && in_array( sprintf( 'taxonomy|%s|%d', $term->taxonomy,  $term->term_id ), $restriction_exemptions[$product_id] ) ) {
						$restriction = false;
					} else {
						$restriction = true;
					}
				}
			}
		}

	}

	return apply_filters( 'it_exchange_membership_addon_is_content_restricted', $restriction, $member_access );
}

/**
 * Checks if current product should be restricted
 * if admin - false
 * if member has access - false
 * if product has rule - true (unless above rule overrides)
 * if exemption exists - true
 *
 * An exemption basically tells the Membership addon that a member who has access to
 * specific product should not have access to it. For instance, say you have a post in
 * a restricted category and you have two membership levels who have access to that category
 * but you only want that post to be visible to one of the memberships. By adding the
 * exemption for the other membership, they will no longer have access to that content.
 *
 * @since 1.0.0
 *
 * @param WP_Post $post
 *
 * @return bool
 */
function it_exchange_membership_addon_is_product_restricted( $post = null ) {

	if ( ! $post ) {
		global $post;
	}

	$restriction = false;

	if ( current_user_can( 'administrator' ) )
		return false;

	$member_access = it_exchange_membership_addon_get_customer_memberships();

	if ( !empty( $post ) && 'it_exchange_prod' === $post->post_type ) {
		$restriction_exemptions = get_post_meta( $post->ID, '_item-content-rule-exemptions', true );

		$post_rules = get_post_meta( $post->ID, '_item-content-rule', true );
		if ( !empty( $post_rules ) ) {
			if ( !empty( $member_access ) ) {
				foreach( $member_access as $product_id => $txn_id ) {
					if ( in_array( $product_id, $post_rules ) )
						return false;
				}
			}
			foreach( $post_rules as $product_id ) {
				if ( !empty( $restriction_exemptions[$product_id] ) && in_array( $post->post_type, $restriction_exemptions[$product_id] ) ) {
					$restriction = false;
				} else {
					$restriction = true;
				}
			}
		}
	}

	return apply_filters( 'it_exchange_membership_addon_is_product_restricted', $restriction, $member_access );
}

/**
 * Checks if current content should be dripped
 * if admin - false
 * if member has access - check if content is dripped, otherwise false
 * Dripped content is basically published content that you want to arbitrarily delay for
 * your members. Say you have a class and you want to release 1 class a week to your membership
 * this will allow you to do that. Simply set your content to the appropriate timeline and new members
 * will have access to the classes based on the set schedule.
 *
 * @since 1.0.0
 *
 * @param WP_Post $post
 *
 * @return bool
 */
function it_exchange_membership_addon_is_content_dripped( $post = null ) {

	if ( ! $post ) {
		global $post;
	}

	$dripped = false;

	if ( current_user_can( 'administrator' ) )
		return false;

	$member_access = it_exchange_membership_addon_get_customer_memberships();

	if ( !empty( $post ) ) {

		foreach( $member_access as $product_id => $txn_id  ) {
			$interval = get_post_meta( $post->ID, '_item-content-rule-drip-interval-' . $product_id, true );
			$interval = !empty( $interval ) ? $interval : 0;
			$duration = get_post_meta( $post->ID, '_item-content-rule-drip-duration-' . $product_id, true );
			$duration = !empty( $duration ) ? $duration : 'days';
			if ( 0 < $interval ) {
				$purchase_time = strtotime( 'midnight', get_post_time( 'U', true, $txn_id ) );
				$dripping = strtotime( $interval . ' ' . $duration, $purchase_time );
				$now = time();

				if ( $dripping < $now )
					return false; // we can return here because they should have access to this content with this membership
				else
					$dripped = true; // we don't want to return here, because other memberships might have access to content sooner
			}
		}

	}

	return apply_filters( 'it_exchange_membership_addon_is_content_dripped', $dripped, $member_access );
}

/**
 * Checks if current product should be dripped
 * if admin - false
 * if member has access - check if content is dripped, otherwise false
 * Dripped product is basically published product that you want to arbitrarily delay for
 * your members. Say you have a class and you want to release 1 class a week to your membership
 * this will allow you to do that. Simply set your content to the appropriate timeline and new members
 * will have access to the classes based on the set schedule.
 *
 * @since 1.0.0
 *
 * @param WP_Post $post
 *
 * @return bool
 */
function it_exchange_membership_addon_is_product_dripped( $post = null ) {

	if ( ! $post ) {
		global $post;
	}

	$dripped = false;

	if ( current_user_can( 'administrator' ) )
		return false;

	$member_access = it_exchange_membership_addon_get_customer_memberships();

	if ( !empty( $post ) && 'it_exchange_prod' === $post->post_type ) {
		foreach( $member_access as $product_id => $txn_id  ) {
			$interval = get_post_meta( $post->ID, '_item-content-rule-drip-interval-' . $product_id, true );
			$interval = !empty( $interval ) ? $interval : 0;
			$duration = get_post_meta( $post->ID, '_item-content-rule-drip-duration-' . $product_id, true );
			$duration = !empty( $duration ) ? $duration : 'days';
			if ( 0 < $interval ) {
				$purchase_time = strtotime( 'midnight', get_post_time( 'U', true, $txn_id ) );
				$dripping = strtotime( $interval . ' ' . $duration, $purchase_time );
				$now = time();

				if ( $dripping < $now )
					return false; // we can return here because they should have access to this content with this membership
				else
					$dripped = true; // we don't want to return here, because other memberships might have access to content sooner
			}
		}
	}
	return apply_filters( 'it_exchange_membership_addon_is_product_dripped', $dripped, $member_access );
}
/**
 * Gets a customer's memberships.
 *
 * This is an array of product IDs mapped to transaction IDs.
 *
 * @since 1.2.16
 *
 * @param int|bool $customer_id Customer's User ID
 *
 * @return array|bool
 */
function it_exchange_membership_addon_get_customer_memberships( $customer_id = false ) {

	$memberships = array();

	if ( empty( $customer_id ) ) {
		if ( is_user_logged_in() ) {
			$customer_id = it_exchange_get_current_customer_id();
			$memberships = it_exchange_get_session_data( 'member_access' );
		}
	} else {
		$customer = new IT_Exchange_Customer( $customer_id );
		$member_access = $customer->get_customer_meta( 'member_access' );
		if ( !empty( $member_access ) ) {

			$flip_member_access = array();

			foreach( $member_access as $txn_id => $product_id_array ) {
				// we want the transaction ID to be the value to help us determine child access relations to transaction IDs
				// Can't use array_flip because product_id_array is an array -- now :)
				foreach ( (array) $product_id_array as $product_id ) {
					$flip_member_access[$product_id] = $txn_id;
				}
			}

			$memberships = it_exchange_membership_addon_setup_recursive_member_access_array( $flip_member_access );
		}
	}

	/**
	 * Filter the Memberships a customer has access to.
	 *
	 * @since 1.17.0
	 *
	 * @param array|bool $memberships
	 * @param int        $customer_id
	 */
	$memberships = apply_filters( 'it_exchange_get_customer_memberships', $memberships, $customer_id );

	return $memberships;
}

/**
 * Gets a customer's memberships
 *
 * @since 1.2.16
 *
 * @param int $membership Member's Product/Post ID
 * @param int|bool $customer_id Customer's User ID
 *
 * @return array|bool
 */
function it_exchange_membership_addon_is_customer_member_of( $membership, $customer_id = false ) {
	$member_access = it_exchange_membership_addon_get_customer_memberships( $customer_id );

	if ( is_int( $membership ) ) {
		$membership_id = $membership;
	} else {
		$args = array(
			'name' => $membership,
			'post_type' => 'it_exchange_pro',
			'post_status' => 'publish',
			'numberposts' => 1
		);
		$products = get_posts( $args );
		if ( !empty( $products ) ) {
			$membership_id = $products[0]->ID;
		} else {
			return false;
		}
	}

	return !empty( $member_access[$membership_id] );
}

/**
 * Check if a customer is eligible for a trial.
 *
 * By default, a customer cannot signup for a trial if they are current members of the product,
 * or any other in the hierarchy.
 *
 * @since 1.17
 *
 * @param IT_Exchange_Product       $membership
 * @param IT_Exchange_Customer|null $customer
 *
 * @return bool
 */
function it_exchange_is_customer_eligible_for_trial( IT_Exchange_Product $membership, IT_Exchange_Customer $customer = null ) {

	$membership_id = (int) $membership->ID;

	$member_access = it_exchange_membership_addon_get_customer_memberships( $customer ? $customer->id : false );

	$children      = (array) it_exchange_membership_addon_get_all_the_children( $membership_id );
	$parents       = (array) it_exchange_membership_addon_get_all_the_parents( $membership_id );

	foreach ( $member_access as $prod_id => $txn_id ) {
		if ( $prod_id === $membership_id || in_array( $prod_id, $children ) || in_array( $prod_id, $parents ) ) {
			return false;
		}
	}

	return true;
}