<?php
/**
 * Load and register all upgrade routines.
 *
 * @since   1.18
 * @license GPLv2
 */

require_once dirname( __FILE__ ) . '/repair-member-access.php';

/**
 * Register our upgrade routines against the iThemes Exchange upgrader.
 *
 * @since 1.18
 *
 * @param IT_Exchange_Upgrader $upgrader
 */
function it_exchange_membership_addon_register_upgrade_routines( IT_Exchange_Upgrader $upgrader ) {
	$upgrader->add_upgrade( new IT_Exchange_Memberships_Repair_Member_Access_Upgrade() );
}

add_action( 'it_exchange_register_upgrades', 'it_exchange_membership_addon_register_upgrade_routines' );