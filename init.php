<?php
/**
 * iThemes Exchange Membership Add-on
 * @package IT_Exchange_Addon_Membership
 * @since   1.0.0
 */

/**
 * New API functions.
 */
require_once( dirname( __FILE__ ) . '/api/load.php' );

/**
 * Exchange will build your add-on's settings page for you and link to it from our add-on
 * screen. You are free to link from it elsewhere as well if you'd like... or to not use our API
 * at all. This file has all the functions related to registering the page, printing the form, and saving
 * the options. This includes the wizard settings. Additionally, we use the Exchange storage API to
 * save / retreive options. Add-ons are not required to do this.
 */
require_once( dirname( __FILE__ ) . '/lib/addon-settings.php' );

/**
 * The following file contains utility functions specific to our membership add-on
 * If you're building your own addon, it's likely that you will
 * need to do similar things.
 */
require_once( dirname( __FILE__ ) . '/lib/addon-functions.php' );

/**
 * The following file contains shortcodes specific to our membership add-on
 * If you're building your own addon, it's likely that you will
 * need to do similar things.
 */
require_once( dirname( __FILE__ ) . '/lib/addon-shortcodes.php' );

/**
 * Our own Members table... basic the Users table but just members
 */
require_once( dirname( __FILE__ ) . '/lib/addon-member-table.php' );

/**
 * Exchange Add-ons require several hooks in order to work properly.
 * We've placed them all in one file to help add-on devs identify them more easily
 */
require_once( dirname( __FILE__ ) . '/lib/required-hooks.php' );

/**
 * We decided to place all AJAX hooked functions into this file, just for ease of use
 */
require_once( dirname( __FILE__ ) . '/lib/addon-ajax-hooks.php' );

/**
 * The following file adds a new metabox are to non-iThemes Exchange posttypes
 */
require_once( dirname( __FILE__ ) . '/lib/addon-metaboxes.php' );

/**
 * Custom integrations.
 */
include_once( dirname( __FILE__ ) . '/lib/integrations/load.php' );

/**
 * New Product Features added by the Exchange Membership Add-on.
 */
require_once( dirname( __FILE__ ) . '/lib/product-features/load.php' );

require_once( dirname( __FILE__ ) . '/lib/class.user-membership-repository.php' );
require_once( dirname( __FILE__ ) . '/lib/interface.user-membership.php' );
require_once( dirname( __FILE__ ) . '/lib/interface.proratable-user-membership.php' );
require_once( dirname( __FILE__ ) . '/lib/class.subscription-driver.php' );
require_once( dirname( __FILE__ ) . '/lib/class.transaction-driver.php' );

require_once( dirname( __FILE__ ) . '/lib/class.emails.php' );
require_once( dirname( __FILE__ ) . '/lib/REST/load.php' );

require_once( dirname( __FILE__ ) . '/lib/deprecated.php' );

require_once( dirname( __FILE__ ) . '/lib/rules/load.php' );
require_once( dirname( __FILE__ ) . '/lib/upgrades/load.php' );

$current_version = get_option( 'exchange_mmebership_version', '1.17.0' );

if ( $current_version != ITE_MEMBERSHIP_PLUGIN_VERSION ) {

	/**
	 * Runs when the version upgrades.
	 *
	 * @since 1.17.0
	 *
	 * @param string $current_version
	 * @param string $new_version
	 */
	do_action( 'it_exchange_addon_membership_upgrade', $current_version, ITE_MEMBERSHIP_PLUGIN_VERSION );

	update_option( 'exchange_mmebership_version', ITE_MEMBERSHIP_PLUGIN_VERSION );

	//update_option( 'it_exchange_show_upgrades_nag', true );
}
