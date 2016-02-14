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
 * @param null    $failed_rules Will be set to the failed rules upon completion.
 *
 * @return bool
 */
function it_exchange_membership_addon_is_content_restricted( $post = null, &$failed_rules = null ) {

	if ( current_user_can( 'administrator' ) ) {
		return false;
	}

	if ( ! $post ) {
		global $post;
	}

	if ( $post->post_type === 'it_exchange_prod' ) {
		return false;
	}

	$evaluator    = new IT_Exchange_Membership_Rule_Evaluator_Service( new IT_Exchange_Membership_Rule_Factory() );
	$customer     = it_exchange_get_current_customer();
	$failed_rules = $evaluator->evaluate( $post, $customer ? $customer : null );

	$memberships = it_exchange_membership_addon_get_customer_memberships();

	return apply_filters( 'it_exchange_membership_addon_is_content_restricted', ! empty( $failed_rules ), $memberships );
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
 * @param null    $failed_rules Will be set to the failed rules upon completion.
 *
 * @return bool
 */
function it_exchange_membership_addon_is_product_restricted( $post = null, &$failed_rules = null ) {

	if ( ! $post ) {
		global $post;
	}

	if ( current_user_can( 'administrator' ) ) {
		return false;
	}

	if ( $post->post_type !== 'it_exchange_prod' ) {
		return false;
	}

	$evaluator    = new IT_Exchange_Membership_Rule_Evaluator_Service( new IT_Exchange_Membership_Rule_Factory() );
	$customer     = it_exchange_get_current_customer();
	$failed_rules = $evaluator->evaluate( $post, $customer ? $customer : null );

	$memberships = it_exchange_membership_addon_get_customer_memberships();

	return apply_filters( 'it_exchange_membership_addon_is_product_restricted', ! empty( $failed_rules ), $memberships );
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
 * @param null    $failed_rules Will be set to the failed rules upon completion.
 *
 * @return bool
 */
function it_exchange_membership_addon_is_content_dripped( $post = null, &$failed_rules = null ) {

	if ( ! $post ) {
		global $post;
	}

	if ( current_user_can( 'administrator' ) ) {
		return false;
	}

	if ( $post->post_type === 'it_exchange_prod' ) {
		return false;
	}

	$evaluator = new IT_Exchange_Membership_Rule_Evaluator_Service( new IT_Exchange_Membership_Rule_Factory() );
	$customer  = it_exchange_get_current_customer();

	if ( ! $customer ) {
		return false;
	}

	$failed_rules = $evaluator->evaluate_drip( $post, $customer );
	$memberships  = it_exchange_membership_addon_get_customer_memberships();

	return apply_filters( 'it_exchange_membership_addon_is_content_dripped', ! empty( $failed_rules ), $memberships );
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
 * @param null    $failed_rules Will be set to the failed rules upon completion.
 *
 * @return bool
 */
function it_exchange_membership_addon_is_product_dripped( $post = null, &$failed_rules = null ) {

	if ( ! $post ) {
		global $post;
	}

	if ( current_user_can( 'administrator' ) ) {
		return false;
	}

	if ( $post->post_type !== 'it_exchange_prod' ) {
		return false;
	}

	$evaluator = new IT_Exchange_Membership_Rule_Evaluator_Service( new IT_Exchange_Membership_Rule_Factory() );
	$customer  = it_exchange_get_current_customer();

	if ( ! $customer ) {
		return false;
	}

	$failed_rules = $evaluator->evaluate_drip( $post, $customer );
	$memberships  = it_exchange_membership_addon_get_customer_memberships();

	return apply_filters( 'it_exchange_membership_addon_is_product_dripped', ! empty( $failed_rules ), $memberships );
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
		$customer      = new IT_Exchange_Customer( $customer_id );
		$member_access = $customer->get_customer_meta( 'member_access' );
		if ( ! empty( $member_access ) ) {

			$flip_member_access = array();

			foreach ( $member_access as $txn_id => $product_id_array ) {
				// we want the transaction ID to be the value to help us determine child access relations to transaction IDs
				// Can't use array_flip because product_id_array is an array -- now :)
				foreach ( (array) $product_id_array as $product_id ) {
					$flip_member_access[ $product_id ] = $txn_id;
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
 * Get a customer's membership subscriptions.
 *
 * @since 1.18
 *
 * @param IT_Exchange_Customer|null $customer
 *
 * @return IT_Exchange_Subscription[]
 */
function it_exchange_get_customer_membership_subscriptions( IT_Exchange_Customer $customer = null ) {

	$memberships = it_exchange_membership_addon_get_customer_memberships( $customer ? $customer->ID : false );

	$subs = array();

	foreach ( $memberships as $product_id => $transaction_id ) {

		$txn  = it_exchange_get_transaction( $transaction_id );
		$prod = it_exchange_get_product( $product_id );

		try {
			$subs[] = it_exchange_get_subscription( $txn, $prod );
		}
		catch ( Exception $e ) {

		}
	}

	return $subs;
}

/**
 * Gets a customer's memberships
 *
 * @since 1.2.16
 *
 * @param int      $membership  Member's Product/Post ID
 * @param int|bool $customer_id Customer's User ID
 *
 * @return array|bool
 */
function it_exchange_membership_addon_is_customer_member_of( $membership, $customer_id = false ) {
	$member_access = it_exchange_membership_addon_get_customer_memberships( $customer_id );

	if ( is_int( $membership ) ) {
		$membership_id = $membership;
	} else {
		$args     = array(
			'name'        => $membership,
			'post_type'   => 'it_exchange_pro',
			'post_status' => 'publish',
			'numberposts' => 1
		);
		$products = get_posts( $args );
		if ( ! empty( $products ) ) {
			$membership_id = $products[0]->ID;
		} else {
			return false;
		}
	}

	return ! empty( $member_access[ $membership_id ] );
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

	$children = (array) it_exchange_membership_addon_get_all_the_children( $membership_id );
	$parents  = (array) it_exchange_membership_addon_get_all_the_parents( $membership_id );

	foreach ( $member_access as $prod_id => $txn_id ) {
		if ( $prod_id === $membership_id || in_array( $prod_id, $children ) || in_array( $prod_id, $parents ) ) {
			return false;
		}
	}

	return true;
}