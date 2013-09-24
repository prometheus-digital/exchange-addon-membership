<?php
/*
 * Plugin Name: iThemes Exchange - Membership Add-on
 * Version: 1.0.0
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

/**
 * This registers our plugin as a membership addon
 *
 * @since 0.1.0
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
	load_plugin_textdomain( 'LION', false, plugin_basename( __FILE__  ) . '/lang/' );
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
/*
function ithemes_exchange_addon_stripe_updater_register( $updater ) { 
	    $updater->register( 'exchange-addon-membership', __FILE__ );
}
add_action( 'ithemes_updater_register', 'ithemes_exchange_addon_membership_updater_register' );
require( dirname( __FILE__ ) . '/lib/updater/load.php' );
*/