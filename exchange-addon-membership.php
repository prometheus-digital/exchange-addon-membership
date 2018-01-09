<?php
/*
 * Plugin Name: ExchangeWP - Membership Add-on
 * Version: 1.19.20
 * Description: Adds the membership management to ExchangeWP
 * Plugin URI: https://exchangewp.com/downloads/membership/
 * Author: ExchangeWP
 * Author URI: https://exchangewp.com
 * ExchangeWP Package: exchange-addon-membership

 * Installation:
 * 1. Download and unzip the latest release zip file.
 * 2. If you use the WordPress plugin uploader to install this plugin skip to step 4.
 * 3. Upload the entire plugin directory to your `/wp-content/plugins/` directory.
 * 4. Activate the plugin through the 'Plugins' menu in WordPress Administration.
 *
*/

define( 'ITE_MEMBERSHIP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'ITE_MEMBERSHIP_PLUGIN_VERSION', '1.19.20' );

/**
 * This registers our plugin as a membership addon
 *
 * @since 1.0.0
 *
 * @return void
*/
function it_exchange_register_membership_addon() {

	require_once dirname( __FILE__ ) . '/lib/class.membership.php';

	$options = array(
		'name'              => __( 'Membership', 'LION' ),
		'description'       => __( 'Add Memberships levels to your customers.', 'LION' ),
		'author'            => 'ExchangeWP',
		'author_url'        => 'https://exchangewp.com/downloads/membership/',
		'icon'              => ITUtility::get_url_from_file( dirname( __FILE__ ) . '/lib/images/membership50px.png' ),
		'wizard-icon'       => ITUtility::get_url_from_file( dirname( __FILE__ ) . '/lib/images/wizard-membership.png' ),
		'file'              => dirname( __FILE__ ) . '/init.php',
		'category'          => 'product-type',
		'basename'          => plugin_basename( __FILE__ ),
		'labels'      => array(
			'singular_name' => __( 'Membership', 'LION' ),
		),
		'settings-callback' => 'it_exchange_membership_addon_settings_callback',
		'supports' => array(
			'sw-shortcode' => true
		),
		'options' => array(
			'class' => 'IT_Exchange_Membership'
		)
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

if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) && version_compare( PHP_VERSION, '5.3', '>=' ) ) {
	require_once dirname( __FILE__ ) . '/vendor/autoload.php';
	require_once dirname( __FILE__ ) . '/umbrella-memberships/load.php';
}

function exchange_membership_plugin_updater() {

	$license_check = get_transient( 'exchangewp_license_check' );

	if ($license_check->license == 'valid' ) {
		$license_key = it_exchange_get_option( 'exchangewp_licenses' );
		$license = $license_key['exchange_license'];

		$edd_updater = new EDD_SL_Plugin_Updater( 'https://exchangewp.com', __FILE__, array(
				'version' 		=> '1.19.20', 				// current version number
				'license' 		=> $license, 				// license key (used get_option above to retrieve from DB)
				'item_id'		 	=> 366,					 	  // name of this plugin
				'author' 	  	=> 'ExchangeWP',    // author of this plugin
				'url'       	=> home_url(),
				'wp_override' => true,
				'beta'		  	=> false
			)
		);
	}

}

add_action( 'admin_init', 'exchange_membership_plugin_updater', 0 );
