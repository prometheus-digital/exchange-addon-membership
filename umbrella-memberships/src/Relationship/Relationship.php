<?php
/**
 * Relationship model.
 *
 * @author      iThemes
 * @since       1.0
 * @copyright   2015 (c) Iron Bound Designs, 2016 iThemes.
 * @license     GPLv2
 */

namespace ITEGMS\Relationship;

use IronBound\DB\Exception;
use IronBound\DB\Manager;
use IronBound\DB\Model;
use IronBound\DB\Table\Table;
use ITEGMS\Purchase\Purchase;

/**
 * Class Relationship
 *
 * @package ITEGMS\Relationship
 */
class Relationship extends Model {

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var int
	 */
	private $purchase;

	/**
	 * @var int
	 */
	private $member;

	/**
	 * Constructor.
	 *
	 * @param \stdClass $data
	 */
	public function __construct( \stdClass $data ) {
		$this->init( $data );
	}

	/**
	 * Create a relationship model.
	 *
	 * @since 1.0
	 *
	 * @param Purchase              $purchase
	 * @param \IT_Exchange_Customer $member
	 *
	 * @return self|null
	 */
	public static function create( Purchase $purchase, \IT_Exchange_Customer $member ) {

		$model = self::insert( $purchase, $member );

		if ( $model ) {

			/**
			 * Fires when a relationship model is created.
			 *
			 * @since 1.0
			 *
			 * @param Relationship $model
			 */
			do_action( 'itegms_create_relationship', $model );
		}

		return $model;
	}

	/**
	 * Create a relationship model with an email address.
	 *
	 * @since 1.0
	 *
	 * @param Purchase $purchase
	 * @param string   $email
	 * @param string   $name
	 *
	 * @return self|null
	 */
	public static function create_with_email( Purchase $purchase, $email, $name ) {

		$pass = wp_generate_password( 24, true, true );

		$parts = explode( ' ', $name );

		switch ( count( $parts ) ) {
			case 1:
				$first = $parts[0];
				$last  = '';
				break;
			case 2:
				$first = $parts[0];
				$last  = $parts[1];
				break;
			default:
				$first = $parts[0];
				unset( $parts[0] );
				$last = implode( ' ', $parts );
				break;
		}

		$user_login = $name;

		$i = 1;

		while ( username_exists( $user_login ) ) {
			$i ++;
			$user_login = $name . $i;
		}

		$id = wp_insert_user( array(
			'user_email'   => $email,
			'user_login'   => $user_login,
			'user_pass'    => $pass,
			'display_name' => $name,
			'first_name'   => $first,
			'last_name'    => $last
		) );

		if ( is_wp_error( $id ) ) {
			throw new \UnexpectedValueException( $id->get_error_message() );
		}

		$member = it_exchange_get_customer( $id );

		$model = self::insert( $purchase, $member );

		if ( $model ) {

			/**
			 * Fires when a relationship model is created while creating
			 * a new WP user.
			 *
			 * @since 1.0
			 *
			 * @param Relationship $model
			 * @param string       $pass User's password
			 */
			do_action( 'itegms_create_relationship_new_user', $model, $pass );
		}

		return $model;
	}

	/**
	 * Insert the record.
	 *
	 * @since 1.0
	 *
	 * @param Purchase              $purchase
	 * @param \IT_Exchange_Customer $member
	 *
	 * @return Model|null
	 * @throws Exception
	 */
	private static function insert( Purchase $purchase, \IT_Exchange_Customer $member ) {

		$db = Manager::make_simple_query_object( 'itegms-relationships' );

		$id = $db->insert( array(
			'purchase' => $purchase->get_pk(),
			'member'   => $member->id
		) );

		if ( $id ) {
			$model = self::get( $id );

			/**
			 * Fires when a new relationship is inserted into the database.
			 *
			 * @since 1.0
			 *
			 * @param Relationship $model
			 */
			do_action( 'itegms_insert_relationship', $model );

			return $model;
		} else {
			return null;
		}
	}

	/**
	 * Get the unique pk for this record.
	 *
	 * @since 1.0
	 *
	 * @return mixed (generally int, but not necessarily).
	 */
	public function get_pk() {
		return $this->id;
	}

	/**
	 * Init an object.
	 *
	 * @since 1.0
	 *
	 * @param \stdClass $data
	 */
	protected function init( \stdClass $data ) {
		$this->id       = $data->id;
		$this->purchase = $data->purchase;
		$this->member   = $data->member;
	}

	/**
	 * Get the member.
	 *
	 * @since 1.0
	 *
	 * @return bool|\IT_Exchange_Customer
	 */
	public function get_member() {
		return it_exchange_get_customer( $this->member );
	}

	/**
	 * Get the purchase.
	 *
	 * @since 1.0
	 *
	 * @return Purchase|null
	 */
	public function get_purchase() {
		return Purchase::get( $this->purchase );
	}

	/**
	 * Set the purchase object.
	 *
	 * @since 1.0
	 *
	 * @param Purchase $purchase
	 */
	public function set_purchase( Purchase $purchase ) {

		if ( $purchase->get_remaining_seats() <= 0 ) {
			throw new \InvalidArgumentException( "Purchase does not have any remaining seats." );
		}

		$this->purchase = $purchase->get_pk();
		$this->update( 'purchase', $purchase->get_pk() );
	}

	/**
	 * Should be called when access to the membership has expired.
	 *
	 * This would typically be due to a lapse of payment.
	 *
	 * @since 1.0
	 */
	public function expire() {

		/**
		 * Fires when access to the membership has expired.
		 *
		 * @since 1.0
		 *
		 * @param Relationship $this
		 */
		do_action( 'itegms_expire_relationship', $this );
	}

	/**
	 * Delete this object.
	 *
	 * @since 1.0
	 *
	 * @throws Exception
	 */
	public function delete() {

		/**
		 * Fires prior to when a relationship is deleted.
		 *
		 * @since 1.0
		 *
		 * @param Relationship $this
		 */
		do_action( 'itegms_delete_relationship', $this );

		parent::delete();

		/**
		 * Fires after a relationship is deleted.
		 *
		 * @since 1.0
		 *
		 * @param Relationship $this
		 */
		do_action( 'itegms_deleted_relationship', $this );
	}

	/**
	 * Get the table object for this model.
	 *
	 * @since 1.0
	 *
	 * @returns Table
	 */
	protected static function get_table() {
		return Manager::get( 'itegms-relationships' );
	}
}