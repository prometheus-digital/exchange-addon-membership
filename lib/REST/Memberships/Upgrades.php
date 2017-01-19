<?php
/**
 * Prorate Upgrades.
 *
 * @since   1.9.0
 * @license GPLv2
 */

namespace iThemes\Exchange\Membership\REST\Memberships;

use iThemes\Exchange\RecurringPayments\REST\Subscriptions\ProrateSerializer;
use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Postable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;
use iThemes\Exchange\REST\Route\Cart\Item;

class Upgrades extends Base implements Getable, Postable {

	/** @var ProrateSerializer */
	private $serializer;

	/** @var \ITE_Prorate_Credit_Requestor */
	private $requestor;

	/** @var \IT_Exchange_User_Membership_Repository */
	private $repository;

	/**
	 * Upgrades constructor.
	 *
	 * @param ProrateSerializer                       $serializer
	 * @param \ITE_Prorate_Credit_Requestor           $requestor
	 * @param \IT_Exchange_User_Membership_Repository $repository
	 */
	public function __construct(
		ProrateSerializer $serializer,
		\ITE_Prorate_Credit_Requestor $requestor,
		\IT_Exchange_User_Membership_Repository $repository
	) {
		$this->serializer = $serializer;
		$this->requestor  = $requestor;
		$this->repository = $repository;
	}

	/**
	 * @inheritDoc
	 */
	public function handle_get( Request $request ) {

		/** @var \ITE_Proratable_User_Membership $membership */
		$membership = $this->repository->get_membership_by_id(
			rawurldecode( $request->get_param( 'membership_id', 'URL' ) )
		);

		$options = $membership->get_available_upgrades();
		$data    = array();

		foreach ( $options as $option ) {
			if ( $this->requestor->request_upgrade( $option, false ) ) {
				$data[] = $this->serializer->serialize( $option );
			}
		}

		return new \WP_REST_Response( $data );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( Request $request, \IT_Exchange_Customer $user = null ) {

		$membership = $this->repository->get_membership_by_id(
			rawurldecode( $request->get_param( 'membership_id', 'URL' ) )
		);

		if ( ! $membership instanceof \ITE_Proratable_User_Membership ) {
			return new \WP_Error(
				'it_exchange_rest_invalid_prorate_request',
				__( 'Unable to prorate request.', 'LION' ),
				array( 'status' => \WP_Http::BAD_REQUEST )
			);
		}

		return true; // Cascades to accessing a subscription
	}

	/**
	 * @inheritDoc
	 */
	public function handle_post( Request $request ) {

		$membership = $this->repository->get_membership_by_id(
			rawurldecode( $request->get_param( 'membership_id', 'URL' ) )
		);

		if ( ! $membership instanceof \ITE_Proratable_User_Membership ) {
			return new \WP_Error(
				'it_exchange_rest_invalid_prorate_request',
				__( 'Invalid prorate request.', 'LION' ),
				array( 'status' => \WP_Http::BAD_REQUEST )
			);
		}

		$product_id = $request['product'];

		$all_available   = $membership->get_available_upgrades();
		$prorate_request = null;
		$found           = false;

		foreach ( $all_available as $available ) {

			if ( $available->get_product_receiving_credit()->ID == $product_id ) {
				$prorate_request = $available;
				$found           = true;
				break;
			}
		}

		if ( ! $found ) {
			return new \WP_Error(
				'it_exchange_rest_invalid_prorate_request',
				__( 'Invalid prorate request. That product cannot be upgraded to.', 'LION' ),
				array( 'status' => \WP_Http::BAD_REQUEST )
			);
		}

		if ( ! $request['cart_id'] || ! $cart = it_exchange_get_cart( $request['cart_id'] ) ) {
			return new \WP_Error(
				'it_exchange_rest_invalid_prorate_request',
				__( 'Invalid prorate request. Cart is required.', 'LION' ),
				array( 'status' => \WP_Http::BAD_REQUEST )
			);
		}

		if ( ! $item = $cart->get_items( 'product' )->filter( function ( \ITE_Cart_Product $cart_product ) use ( $product_id ) {
			return $cart_product->get_product()->ID == $product_id;
		} )->first()
		) {
			$item = \ITE_Cart_Product::create( it_exchange_get_product( $product_id ) );
			$cart->add_item( $item );
		}

		$prorate_request->set_cart( $cart );

		if ( ! $this->requestor->request_upgrade( $prorate_request ) ) {
			return new \WP_Error(
				'it_exchange_rest_unable_to_prorate',
				__( 'Unable to complete the prorate request.', 'LION' ),
				array( 'status' => \WP_Http::INTERNAL_SERVER_ERROR )
			);
		}

		/** @var Item $route */
		foreach ( $this->get_manager()->get_routes_by_class( 'iThemes\Exchange\REST\Route\Cart\Item' ) as $route ) {
			if ( $route->get_type()->get_type() === 'product' ) {

				$response = new \WP_REST_Response( null, \WP_Http::SEE_OTHER );
				$url      = \iThemes\Exchange\REST\get_rest_url( $route, array(
					'cart_id' => $cart->get_id(),
					'item_id' => $item->get_id()
				) );
				$response->header( 'Location', $url );

				return $response;
			}
		}

		return new \WP_REST_Response( null, \WP_Http::NO_CONTENT );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_post( Request $request, \IT_Exchange_Customer $user = null ) {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function get_version() { return 1; }

	/**
	 * @inheritDoc
	 */
	public function get_path() { return 'upgrades/'; }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() { return array(); }

	/**
	 * @inheritDoc
	 */
	public function get_schema() { return $this->serializer->get_schema(); }
}