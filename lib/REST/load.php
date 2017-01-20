<?php
/**
 * Load the REST API.
 *
 * @since   2.0.0
 * @license GPLv2
 */

use iThemes\Exchange\Membership\REST\Memberships\Membership;
use iThemes\Exchange\Membership\REST\Memberships\ProrateHelper;
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
		$requestor->register_provider( 'IT_Exchange_Subscription' );
		$requestor->register_provider( 'IT_Exchange_Transaction' );

		$helper = new ITE_Prorate_REST_Helper(
			it_exchange_object_type_registry()->get( 'membership' ),
			$requestor,
			$manager,
			$serializer,
			'membership_id'
		);

		$upgrades = new Upgrades( $serializer, $helper );
		$manager->register_route( $upgrades->set_parent( $membership ) );

		$downgrades = new Downgrades( $serializer, $helper );
		$manager->register_route( $downgrades->set_parent( $membership ) );
	}
} );