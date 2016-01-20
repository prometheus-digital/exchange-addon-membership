<?php
/**
 * Load umbrella memberships.
 *
 * @since   1.17
 * @license GPLv2
 */

add_action( 'plugins_loaded', function () {

	if ( class_exists( 'ITEGMS\Plugin' ) ) {

		require_once ABSPATH . '/wp-admin/includes/plugin.php';

		if ( is_plugin_active( 'exchange-addon-umbrella-memberships/exchange-addon-umbrella-memberships.php' ) ) {
			deactivate_plugins( array( 'exchange-addon-umbrella-memberships/exchange-addon-umbrella-memberships.php' ) );
		}
	} else {
		require_once __DIR__ . '/exchange-addon-umbrella-memberships.php';
	}
}, - 100 );