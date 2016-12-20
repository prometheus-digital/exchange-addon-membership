<?php
/**
 * Nav Menu Roles integration.
 *
 * @since 1.20.0
 */

if ( ! class_exists( 'Nav_Menu_Roles' ) ) {
	return;
}

/**
 * Add memberships product to Nav Menu Roles.
 *
 * @since 1.20.0
 *
 * @param array $roles
 *
 * @return array
 */
function it_exchange_add_memberships_to_nav_menu_roles( $roles ) {

	$memberships = it_exchange_get_products( array(
		'product_type'   => 'membership-product-type',
		'posts_per_page' => - 1,
	) );

	foreach ( $memberships as $membership ) {
		$roles["it-exchange-{$membership->ID}"] = $membership->post_title;
	}

	return $roles;
}

add_filter( 'nav_menu_roles', 'it_exchange_add_memberships_to_nav_menu_roles' );

/**
 * Handle menu visibility for Nav Menu Roles.
 *
 * @since 1.20.0
 *
 * @param bool   $visible
 * @param object $item
 *
 * @return bool
 */
function it_exchange_memberships_nav_menu_roles_visibility( $visible, $item ) {

	if ( ! isset( $item->roles ) || ! is_array( $item->roles ) ) {
		return $visible;
	}

	$memberships = 0;

	foreach ( $item->roles as $role ) {

		preg_match( '/it-exchange-(\d+)/', $role, $matches );

		if ( ! $matches ) {
			continue;
		}

		$product = it_exchange_get_product( $matches[1] );

		if ( ! $product ) {
			continue;
		}

		$memberships ++;

		if ( it_exchange_membership_addon_is_customer_member_of( (int) $product->ID ) ) {
			return true;
		}
	}

	if ( ! $memberships ) {
		return $visible;
	}

	return false;
}

add_filter( 'nav_menu_roles_item_visibility', 'it_exchange_memberships_nav_menu_roles_visibility', 10, 2 );