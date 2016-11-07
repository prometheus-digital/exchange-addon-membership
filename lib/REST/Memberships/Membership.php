<?php
/**
 * Single Membership Route.
 *
 * @since   1.20.0
 * @license GPLv2
 */

namespace iThemes\Exchange\Memberships\REST\Memberships;

use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Memberships\Serializer;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route;

/**
 * Class Membership
 *
 * @package iThemes\Exchange\Memberships\REST\Memberships
 */
class Membership extends Route\Base implements Getable {

	/** @var Serializer */
	private $serializer;

	/**
	 * Membership constructor.
	 *
	 * @param Serializer $serializer
	 */
	public function __construct( Serializer $serializer ) { $this->serializer = $serializer; }

	/**
	 * @inheritDoc
	 */
	public function handle_get( Request $request ) {
		// TODO: Implement handle_get() method.
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( Request $request, \IT_Exchange_Customer $user = null ) {
		// TODO: Implement user_can_get() method.
	}

	/**
	 * @inheritDoc
	 */
	public function get_version() { return 1; }

	/**
	 * @inheritDoc
	 */
	public function get_path() { return 'memberships/membership/'; }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() { return array(); }

	/**
	 * @inheritDoc
	 */
	public function get_schema() { return $this->serializer->get_schema(); }
}