<?php
/**
 * Initialize the plugin.
 *
 * @author      ExchangeWP
 * @since       1.0
 * @copyright   2015 (c) Iron Bound Designs, 2016 iThemes, 2017 ExchangeWP.
 * @license     GPLv2
 */

namespace ITEGMS;

/**
 * Initialize the settings.
 */
Settings::init();

/**
 * Load the DB component.
 */
require_once( Plugin::$dir . '/src/DB/load.php' );

/**
 * Load the Product Feature component.
 */
require_once( Plugin::$dir . '/src/Product_Feature/load.php' );

/**
 * Load the API functions.
 */
require_once( Plugin::$dir . 'api/load.php' );

/**
 * Load the theme API.
 */
require_once( Plugin::$dir . 'api/theme/load.php' );

/**
 * Load the main plugin hooks.
 */
new Hooks();

/**
 * Load the emails hooks.
 */
new Emails();

if ( is_admin() ) {
	Plugin::upgrade();
}
