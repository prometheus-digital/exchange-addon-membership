<?php
/**
 * Query for purchases.
 *
 * @author      ExchangeWP
 * @since       1.0
 * @copyright   2015 (c) Iron Bound Designs, 2016 iThemes, 2017 ExchangeWP.
 * @license     GPLv2
 */

namespace ITEGMS\Purchase;

use IronBound\DB\Manager;
use IronBound\DB\Model;
use IronBound\DB\Query\Builder;
use IronBound\DB\Query\Complex_Query;
use IronBound\DB\Query\Tag\From;
use IronBound\DB\Query\Tag\Join;
use IronBound\DB\Query\Tag\Select;
use IronBound\DB\Query\Tag\Where;
use IronBound\DB\Query\Tag\Where_Raw;
use ITEGMS\Relationship\Relationship;

/**
 * Class Purchase_Query
 *
 * @package ITEGMS\Purchase
 */
class Purchase_Query extends Complex_Query {

	/**
	 * Constructor.
	 *
	 * @param array $args
	 */
	public function __construct( array $args = array() ) {
		parent::__construct( Manager::get( 'itegms-purchases' ), $args );
	}

	/**
	 * Get the default args.
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	protected function get_default_args() {
		$existing = parent::get_default_args();

		$new = array(
			'id'                  => '',
			'id__in'              => array(),
			'id__not_in'          => array(),
			'transaction'         => '',
			'transaction__in'     => array(),
			'transaction__not_in' => array(),
			'customer'            => '',
			'customer__in'        => array(),
			'customer__not_in'    => array(),
			'membership'          => '',
			'membership__in'      => array(),
			'membership__not_in'  => array(),
			'seats'               => '',
			'seats_gt'            => '',
			'seats_lt'            => '',
			'active'              => ''
		);

		return wp_parse_args( $new, $existing );
	}

	/**
	 * Convert data to its object.
	 *
	 * @since 1.0
	 *
	 * @param \stdClass $data
	 *
	 * @return Model
	 */
	protected function make_object( \stdClass $data ) {
		return new Purchase( $data );
	}

	/**
	 * Parse the results returned from the DB.
	 *
	 * @since 1.0
	 *
	 * @param array $results
	 *
	 * @return array
	 */
	protected function parse_results( $results ) {

		if ( $this->args['return_value'] == 'relationships' ) {

			$objects = array();

			foreach ( $results as $result ) {
				$objects[ $result->id ] = new Relationship( $result );
			}

			return $objects;

		} else {
			return parent::parse_results( $results );
		}
	}

	/**
	 * Build the select query.
	 *
	 * @since 1.0
	 *
	 * @param string $alias
	 *
	 * @return Select
	 */
	protected function parse_select( $alias = 'q' ) {

		if ( $this->args['return_value'] == 'relationships' ) {
			return new Select( Select::ALL, $alias );
		} else {
			return parent::parse_select( $alias );
		}
	}

	/**
	 * Build the sql query.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	protected function build_sql() {

		$builder = new Builder();

		$select = $this->parse_select();
		$from   = new From( $this->table->get_table_name( $GLOBALS['wpdb'] ), 'q' );

		$where = new Where( 1, true, 1 );

		if ( ( $id = $this->parse_id() ) !== null ) {
			$where->qAnd( $id );
		}

		if ( ( $transaction = $this->parse_transaction() ) !== null ) {
			$where->qAnd( $transaction );
		}

		if ( ( $customer = $this->parse_customer() ) !== null ) {
			$where->qAnd( $customer );
		}

		if ( ( $membership = $this->parse_membership() ) !== null ) {
			$where->qAnd( $membership );
		}

		if ( ( $seats = $this->parse_seats() ) !== null ) {
			$where->qAnd( $seats );
		}

		if ( ( $seats_gt = $this->parse_seats_gt() ) !== null ) {
			$where->qAnd( $seats_gt );
		}

		if ( ( $seats_lt = $this->parse_seats_lt() ) !== null ) {
			$where->qAnd( $seats_lt );
		}

		if ( ( $active = $this->parse_active() ) !== null ) {
			$where->qAnd( $active );
		}

		$order = $this->parse_order();
		$limit = $this->parse_pagination();

		if ( $this->args['return_value'] == 'relationships' ) {

			$match = new Where_Raw( 'r.purchase = q.id' );
			$match->qAnd( $where );

			$select = $this->parse_select( 'r' );
			$join   = new Join( $from, $match );

			$from  = new From(
				Manager::get( 'itegms-relationships' )->get_table_name( $GLOBALS['wpdb'] ), 'r'
			);
			$where = $join;
		}

		$builder->append( $select );
		$builder->append( $from );
		$builder->append( $where );
		$builder->append( $order );

		if ( $limit !== null ) {
			$builder->append( $limit );
		}

		return $builder->build();
	}

	/**
	 * Parse the ID where.
	 *
	 * @since 1.0
	 *
	 * @return Where|null
	 */
	protected function parse_id() {

		if ( ! empty( $this->args['id'] ) ) {
			$this->args['id__in'] = array( $this->args['id'] );
		}

		return $this->parse_in_or_not_in_query( 'id', $this->args['id__in'], $this->args['id__not_in'] );
	}

	/**
	 * Parse the transaction where.
	 *
	 * @since 1.0
	 *
	 * @return Where|null
	 */
	protected function parse_transaction() {

		if ( ! empty( $this->args['transaction'] ) ) {
			$this->args['transaction__in'] = array( $this->args['transaction'] );
		}

		return $this->parse_in_or_not_in_query( 'transaction', $this->args['transaction__in'], $this->args['transaction__not_in'] );
	}

	/**
	 * Parse the customer where.
	 *
	 * @since 1.0
	 *
	 * @return Where|null
	 */
	protected function parse_customer() {

		if ( ! empty( $this->args['customer'] ) ) {
			$this->args['customer__in'] = array( $this->args['customer'] );
		}

		return $this->parse_in_or_not_in_query( 'customer', $this->args['customer__in'], $this->args['customer__not_in'] );
	}

	/**
	 * Parse the membership where.
	 *
	 * @since 1.0
	 *
	 * @return Where|null
	 */
	protected function parse_membership() {

		if ( ! empty( $this->args['membership'] ) ) {
			$this->args['membership__in'] = array( $this->args['membership'] );
		}

		return $this->parse_in_or_not_in_query( 'membership', $this->args['membership__in'], $this->args['membership__not_in'] );
	}

	/**
	 * Parse the seats where.
	 *
	 * @since 1.0
	 *
	 * @return Where|null
	 */
	protected function parse_seats() {

		if ( $this->args['seats'] === '' ) {
			return null;
		}

		if ( $this->args['seats'] < 1 ) {
			throw new \InvalidArgumentException( "Usage: `seats` >= 1" );
		}

		return new Where( 'seats', true, (int) $this->args['seats'] );
	}

	/**
	 * Parse the seats greater than where.
	 *
	 * @since 1.0
	 *
	 * @return Where|null
	 */
	protected function parse_seats_gt() {

		if ( $this->args['seats_gt'] === '' ) {
			return null;
		}

		if ( $this->args['seats_gt'] < 1 ) {
			throw new \InvalidArgumentException( "Usage: `seats_gt` >= 1" );
		}

		return new Where( 'seats', '>', (int) $this->args['seats_gt'] );
	}

	/**
	 * Parse the seats greater than where.
	 *
	 * @since 1.0
	 *
	 * @return Where|null
	 */
	protected function parse_seats_lt() {

		if ( $this->args['seats_lt'] === '' ) {
			return null;
		}

		if ( $this->args['seats_lt'] < 1 ) {
			throw new \InvalidArgumentException( "Usage: `seats_lt` >= 1" );
		}

		return new Where( 'seats', '<', (int) $this->args['seats_lt'] );
	}

	/**
	 * Parse the active where.
	 *
	 * @since 1.0
	 *
	 * @return Where|null
	 */
	protected function parse_active() {

		if ( $this->args['active'] === '' ) {
			return null;
		}

		if ( ! is_bool( $this->args['active'] ) ) {
			throw new \InvalidArgumentException( "Usage: `active` === true || false" );
		}

		return new Where( 'active', true, (int) $this->args['active'] );
	}
}
