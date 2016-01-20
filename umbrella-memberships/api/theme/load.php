<?php
/**
 * Load the theme API modules.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2015 (c) Iron Bound Designs.
 * @license     GPLv2
 */

if ( is_admin() ) {
	return;
}

/**
 * Load the umbrella membership IT_Theme_API class
 */
require_once( \ITEGMS\Plugin::$dir . 'api/theme/class.umbrella-membership.php' );