<?php
/**
 * Load the REST API.
 *
 * @since   1.20.0
 * @license GPLv2
 */

use iThemes\Exchange\Membership\REST\Memberships\Membership;
use iThemes\Exchange\Membership\REST\Memberships\Serializer;
use iThemes\Exchange\Membership\REST\Memberships\Downgrades;
use iThemes\Exchange\Membership\REST\Memberships\Upgrades;
use iThemes\Exchange\RecurringPayments\REST\Subscriptions\ProrateSerializer;

add_action( 'it_exchange_register_rest_routes', function ( \iThemes\Exchange\REST\Manager $manager ) {

	$repository = new IT_Exchange_User_Membership_Repository();

	$membership = new Membership( new Serializer(), $repository );
	$manager->register_route( $membership );

	if ( class_exists( '\iThemes\Exchange\RecurringPayments\REST\Subscriptions\ProrateSerializer' ) ) {

		$serializer = new ProrateSerializer();
		$requestor  = new ITE_Prorate_Credit_Requestor( new ITE_Daily_Price_Calculator() );

		$upgrades = new Upgrades( $serializer, $requestor, $repository );
		$manager->register_route( $upgrades->set_parent( $membership ) );

		$downgrades = new Downgrades( $serializer, $requestor, $repository );
		$manager->register_route( $downgrades->set_parent( $membership ) );
	}
} );