<?php
/**
 *
 *
 * @author      iThemes
 * @since       ?
 * @copyright   2015 (c) Iron Bound Designs, 2016 iThemes.
 * @license     GPLv2
 */

namespace ITEGMS\Purchase;

use IronBound\DB\Exception;
use IronBound\DB\Manager;
use IronBound\DB\Model;
use IronBound\DB\Table\Table;
use ITEGMS\Relationship\Relationship;
use ITEGMS\Relationship\Relationship_Query;

/**
 * Class Purchase
 *
 * @package ITEGMS\Purchase
 *
 * @property int  $id
 * @property int  $transaction
 * @property int  $customer
 * @property int  $membership
 * @property int  $seats
 * @property bool $active
 */
class Purchase extends Model {

	/**
	 * Create a purchase object.
	 *
	 * @since 1.0
	 *
	 * @param \IT_Exchange_Transaction $transaction
	 *
	 * @return Purchase|null
	 * @throws \IronBound\DB\Exception
	 */
	public static function create( \IT_Exchange_Transaction $transaction ) {

		$products = it_exchange_get_transaction_products( $transaction );

		foreach ( $products as $product ) {

			if ( ! it_exchange_product_has_feature( $product['product_id'], 'umbrella-membership' ) ) {
				continue;
			}

			if ( $product['count'] > 1 ) {

				$data = array(
					'transaction' => $transaction->ID,
					'customer'    => it_exchange_get_transaction_customer_id( $transaction ),
					'membership'  => $product['product_id'],
					'seats'       => $product['count'],
					'active'      => it_exchange_transaction_is_cleared_for_delivery( $transaction )
				);

				$model = static::_do_create( $data );

				if ( $model ) {

					/**
					 * Fires when a purchase object is created.
					 *
					 * @since 1.0
					 *
					 * @param Purchase $purchase
					 */
					do_action( 'itegms_create_purchase', $model );

					return $model;
				} else {
					return null;
				}
			}
		}

		throw new \InvalidArgumentException( "Invalid transaction." );
	}

	/**
	 * Activate a purchase.
	 *
	 * @since 1.0
	 */
	public function activate() {

		$this->active = true;
		$this->save();

		/**
		 * Fires when a purchase is activated.
		 *
		 * @since 1.0
		 *
		 * @param Purchase $this
		 */
		do_action( 'itegms_activate_purchase', $this );
	}

	/**
	 * Deactivate this purchase.
	 *
	 * @since 1.0
	 */
	public function deactivate() {

		$this->active = false;
		$this->save();

		/**
		 * Fires when a purchase is deactivated.
		 *
		 * @since 1.0
		 *
		 * @param Purchase $this
		 */
		do_action( 'itegms_deactivate_purchase', $this );
	}

	/**
	 * Get a list of the members being paid for by this purchase.
	 *
	 * @since 1.0
	 *
	 * @return Relationship[]
	 */
	public function get_members() {

		$query = new Relationship_Query( array(
			'purchase' => $this->get_pk()
		) );

		return $query->get_results();
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
	 * Get the transaction.
	 *
	 * @since 1.0
	 *
	 * @return \IT_Exchange_Transaction
	 */
	public function get_transaction() {
		return it_exchange_get_transaction( $this->transaction );
	}

	/**
	 * Get the purchasing customer.
	 *
	 * @since 1.0
	 *
	 * @return \IT_Exchange_Customer
	 */
	public function get_customer() {
		return it_exchange_get_customer( $this->customer );
	}

	/**
	 * Get the membership purchased.
	 *
	 * @since 1.0
	 *
	 * @return \IT_Exchange_Product
	 */
	public function get_membership() {
		return it_exchange_get_product( $this->membership );
	}

	/**
	 * Get the number of seats purchased.
	 *
	 * @since 1.0
	 *
	 * @return int
	 */
	public function get_seats() {
		return $this->seats;
	}

	/**
	 * Get the remaining seats.
	 *
	 * @since 1.0
	 *
	 * @return int
	 */
	public function get_remaining_seats() {
		return $this->get_seats() - count( $this->get_members() );
	}

	/**
	 * Is the purchase active.
	 *
	 * @since 1.0
	 *
	 * @return bool
	 */
	public function is_active() {
		return $this->active;
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
		 * Fires prior to when a purchase is deleted.
		 *
		 * @since 1.0
		 *
		 * @param Purchase $this
		 */
		do_action( 'itegms_delete_purchase', $this );

		foreach ( $this->get_members() as $relationship ) {
			$relationship->delete();
		}

		parent::delete();

		/**
		 * Fires after a purchase is deleted.
		 *
		 * @since 1.0
		 *
		 * @param Relationship $this
		 */
		do_action( 'itegms_deleted_purchase', $this );
	}


	/**
	 * Get the table object for this model.
	 *
	 * @since 1.0
	 *
	 * @returns Table
	 */
	protected static function get_table() {
		return Manager::get( 'itegms-purchases' );
	}
}