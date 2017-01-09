<?php
/*
 * Plugin Name: iThemes Exchange - Membership Add-on
 * Version: 2.0.0
 * Description: Adds the membership management to iThemes Exchange
 * Plugin URI: http://ithemes.com/exchange/membership/
 * Author: iThemes
 * Author URI: http://ithemes.com
 * iThemes Package: exchange-addon-membership
 
 * Installation:
 * 1. Download and unzip the latest release zip file.
 * 2. If you use the WordPress plugin uploader to install this plugin skip to step 4.
 * 3. Upload the entire plugin directory to your `/wp-content/plugins/` directory.
 * 4. Activate the plugin through the 'Plugins' menu in WordPress Administration.
 *
*/

/**
 * Load the Memberships plugin.
 *
 * @since 2.0.0
 */
function it_exchange_load_memberships() {
	if ( ! function_exists( 'it_exchange_load_deprecated' ) || it_exchange_load_deprecated() ) {
		require_once dirname( __FILE__ ) . '/deprecated/exchange-addon-membership.php';
	} else {
		require_once dirname( __FILE__ ) . '/plugin.php';
	}
}

add_action( 'plugins_loaded', 'it_exchange_load_memberships' );

/**
 * Registers Plugin with iThemes updater class
 *
 * @since 1.0.0
 *
 * @param object $updater ithemes updater object
 * @return void
 */
function ithemes_exchange_addon_membership_updater_register( $updater ) {
	$updater->register( 'exchange-addon-membership', __FILE__ );
}
add_action( 'ithemes_updater_register', 'ithemes_exchange_addon_membership_updater_register' );
require( dirname( __FILE__ ) . '/lib/updater/load.php' );

/**
 * When addon is activated, copy bundled-addons folders to plugins directory
 *
 * @since 1.0.0
 */
function it_exchange_membership_addon_activation() {
	if ( apply_filters( 'it_exchange_membership_addon_activation', true ) ) {

		if ( file_exists( WP_PLUGIN_DIR . '/exchange-addon-recurring-payments/exchange-addon-recurring-payments.php' ) ) {
			add_action( 'activated_plugin', 'it_exchange_membership_addon_activated_bundled_addons', 10, 2 );
		} elseif ( WP_Filesystem( 'Direct', plugin_dir_path( __FILE__ ) ) ) {
			copy_dir( plugin_dir_path( __FILE__ ) . 'bundled-addons/', WP_PLUGIN_DIR );
			add_action( 'activated_plugin', 'it_exchange_membership_addon_activated_bundled_addons', 10, 2 );
		}
	}
	update_option( 'it-exchange-membership-addon-version', ITE_MEMBERSHIP_PLUGIN_VERSION );
}
register_activation_hook( __FILE__, 'it_exchange_membership_addon_activation' );

/**
 * Action to activate bundled addons w/ parent addon is activated
 *
 * @since 1.0.0
 * @param string $plugin Current plugin being activated (should be this plugin)
 * @param bool $network_wide Whether or not the plugin being activated is being activated Network Wid
 * @return void
 */
function it_exchange_membership_addon_activated_bundled_addons( $plugin, $network_wide ) {
	wp_cache_delete( 'plugins', 'plugins' );
	if ( basename( __FILE__ ) === basename( $plugin ) ) {
		foreach ( glob( plugin_dir_path( __FILE__ ) . 'bundled-addons/*' ) as $file_path ) {
			$file = basename( $file_path );
			$new_plugin = "$file/$file.php";
			if ( is_plugin_inactive( $new_plugin ) )
				$output = activate_plugin( $new_plugin, '', $network_wide );
		}
	}
}
