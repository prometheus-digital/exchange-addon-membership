<?php
/**
 * Load the DB component.
 *
 * @author      ExchangeWP
 * @since       1.0
 * @copyright   2015 (c) Iron Bound Designs, 2016 iThemes, 2017 ExchangeWP.
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
