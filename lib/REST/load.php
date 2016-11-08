<?php
/**
 * Load the REST API.
 *
 * @since   1.20.0
 * @license GPLv2
 */

use iThemes\Exchange\Membership\REST\Memberships\Membership;
use iThemes\Exchange\Membership\REST\Memberships\Serializer;

add_action( 'it_exchange_register_rest_routes', function ( \iThemes\Exchange\REST\Manager $manager ) {

	$membership = new Membership( new Serializer(), new IT_Exchange_User_Membership_Repository() );
	$manager->register_route( $membership );
} );