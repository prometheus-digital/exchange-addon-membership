<?php
/*
 * Plugin Name: iThemes Exchange - Membership Add-on
 * Version: 1.5.0
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

define( 'ITE_MEMBERSHIP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'ITE_MEMBERSHIP_PLUGIN_VERSION', '1.6.0' );

/**
 * Setup the plugin 
 *
 * @uses update_option()
 * @since 1.6.0
 * @return void
*/
function it_exchange_membership_addon_loaded() {
	$version = get_option( 'it-exchange-membership-addon-version', true );
	if ( version_compare( $version, '1.6.0', '<' ) ) { //Updated membership redirect rules in 1.6.0
		update_option( '_it-exchange-flush-rewrites', true );
	}
	update_option( 'it-exchange-membership-addon-version', ITE_MEMBERSHIP_PLUGIN_VERSION );
}
add_action( 'it_exchange_loaded', 'it_exchange_membership_addon_loaded' );

/**
 * This registers our plugin as a membership addon
 *
 * @since 1.0.0
 *
 * @return void
*/
function it_exchange_register_membership_addon() {
	$options = array(
		'name'              => __( 'Membership', 'LION' ),
		'description'       => __( 'Add Memberships levels to your customers.', 'LION' ),
		'author'            => 'iThemes',
		'author_url'        => 'http://ithemes.com/exchange/membership/',
		'icon'              => ITUtility::get_url_from_file( dirname( __FILE__ ) . '/lib/images/membership50px.png' ),
		'wizard-icon'       => ITUtility::get_url_from_file( dirname( __FILE__ ) . '/lib/images/wizard-membership.png' ),
		'file'              => dirname( __FILE__ ) . '/init.php',
		'category'          => 'product-type',
		'basename'          => plugin_basename( __FILE__ ),
		'labels'      => array(
			'singular_name' => __( 'Membership', 'LION' ),
		),
		'settings-callback' => 'it_exchange_membership_addon_settings_callback',	
	);
	it_exchange_register_addon( 'membership-product-type', $options );
}
add_action( 'it_exchange_register_addons', 'it_exchange_register_membership_addon' );

/**
 * Loads the translation data for WordPress
 *
 * @uses load_plugin_textdomain()
 * @since 1.0.3
 * @return void
*/
function it_exchange_membership_set_textdomain() {
	load_plugin_textdomain( 'LION', false, dirname( plugin_basename( __FILE__  ) ) . '/lang/' );
}
add_action( 'plugins_loaded', 'it_exchange_membership_set_textdomain' );

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
		if ( WP_Filesystem( 'Direct', plugin_dir_path( __FILE__ ) ) ) {
			copy_dir( plugin_dir_path( __FILE__ ) . 'bundled-addons/', WP_PLUGIN_DIR );
			add_action( 'activated_plugin', 'it_exchange_membership_addon_activated_bundled_addons', 10, 2 );
		}
	}
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