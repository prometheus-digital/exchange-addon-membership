<?php
/**
 * Single Membership Route.
 *
 * @since   2.0.0
 * @license GPLv2
 */

namespace iThemes\Exchange\Membership\REST\Memberships;

use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route;

/**
 * Class Membership
 *
 * @package iThemes\Exchange\Membership\REST\Memberships
 */
class Membership extends Route\Base implements Getable {

	/** @var Serializer */
	private $serializer;

	/** @var \IT_Exchange_User_Membership_Repository */
	private $repository;

	/**
	 * Membership constructor.
	 *
	 * @param Serializer                              $serializer
	 * @param \IT_Exchange_User_Membership_Repository $repository
	 */
	public function __construct( Serializer $serializer, \IT_Exchange_User_Membership_Repository $repository ) {
		$this->serializer = $serializer;
		$this->repository = $repository;
	}

	/**
	 * @inheritDoc
	 */
	public function handle_get( Request $request ) {

		$membership = $this->repository->get_membership_by_id(
			rawurldecode( $request->get_param( 'membership_id', 'URL' ) )
		);

		$data = $this->serializer->serialize( $membership );

		$response = new \WP_REST_Response( $data );

		if ( $membership->get_user() ) {
			$response->add_link(
				'beneficiary',
				\iThemes\Exchange\REST\get_rest_url(
					$this->get_manager()->get_first_route( 'iThemes\Exchange\REST\Route\Customer\Customer' ),
					array( 'customer_id' => $membership->get_user()->ID )
				),
				array( 'embeddable' => true )
			);
		}

		if ( $membership instanceof \IT_Exchange_User_Membership_Transaction_Driver ) {
			$response->add_link(
				'transaction',
				\iThemes\Exchange\REST\get_rest_url(
					$this->get_manager()->get_first_route( 'iThemes\Exchange\REST\Route\Transaction\Transaction' ),
					array( 'transaction_id' => $membership->get_transaction()->get_ID() )
				),
				array( 'embeddable' => true )
			);
		}

		if ( $membership instanceof \IT_Exchange_User_Membership_Subscription_Driver ) {
			$response->add_link(
				'subscription',
				\iThemes\Exchange\REST\get_rest_url(
					$this->get_manager()->get_first_route( 'iThemes\Exchange\RecurringPayments\REST\Subscriptions\Subscription' ),
					array( 'subscription_id' => $membership->get_subscription()->get_id() )
				),
				array( 'embeddable' => true )
			);
		}

		return $response;
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( Request $request, \IT_Exchange_Customer $user = null ) {

		$membership = $this->repository->get_membership_by_id(
			rawurldecode( $request->get_param( 'membership_id', 'URL' ) )
		);

		if ( ! $membership ) {
			return new \WP_Error(
				'it_exchange_rest_not_found',
				__( 'Membership not found.', 'LION' ),
				array( 'status' => \WP_Http::NOT_FOUND )
			);
		}

		if ( ! $user || ! user_can( $user->wp_user, 'edit_user', $membership->get_user()->ID ) ) {
			return new \WP_Error(
				'it_exchange_rest_invalid_context',
				__( 'Sorry, you are not allowed to view that membership.', 'LION' ),
				array( 'status' => \WP_Http::FORBIDDEN )
			);
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function get_version() { return 1; }

	/**
	 * @inheritDoc
	 */
	public function get_path() { return 'memberships/(?P<membership_id>\d+(?:\:|\%3A)\d+)/'; }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() { return array(); }

	/**
	 * @inheritDoc
	 */
	public function get_schema() { return $this->serializer->get_schema(); }
}