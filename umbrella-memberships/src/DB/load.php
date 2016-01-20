<?php
/**
 * Load the DB component.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2015 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace ITEGMS\DB;

use IronBound\DB\Manager;

Manager::register( new Relationships() );
Manager::register( new Purchases() );

add_action( 'itegms_upgrade', function () {
	Manager::maybe_install_table( new Relationships() );
	Manager::maybe_install_table( new Purchases() );
} );