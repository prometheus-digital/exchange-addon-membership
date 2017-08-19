<?php
/**
 * ExchangeWP Recurring Payments Add-on
 * load theme API functions
 * @package exchange-addon-recurring-payments
 * @since 1.0.0
*/

if ( ! is_admin() ) {

	// Frontend only
	include( 'theme.php' );
}

require_once dirname( __FILE__ ) . '/memberships.php';
