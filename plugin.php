<?php
/**
 * Load the memberships plugin.
 */

define( 'ITE_MEMBERSHIP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'ITE_MEMBERSHIP_PLUGIN_VERSION', '2.0.0' );

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
it_exchange_membership_set_textdomain();

require_once dirname( __FILE__ ) . '/vendor/autoload.php';
require_once dirname( __FILE__ ) . '/umbrella-memberships/load.php';