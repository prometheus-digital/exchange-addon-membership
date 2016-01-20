<?php
/**
 * Complex query tool for relationship objects.
 *
 * @author      iThemes
 * @since       1.0
 * @copyright   2015 (c) Iron Bound Designs, 2016 iThemes.
 * @license     GPLv2
 */

namespace ITEGMS\Relationship;

use IronBound\DB\Manager;
use IronBound\DB\Query\Builder;
use IronBound\DB\Query\Complex_Query;
use IronBound\DB\Query\Tag\From;
use IronBound\DB\Query\Tag\Where;

/**
 * Class Query
 *
 * @package ITEGMS\Relationship
 */
class Relationship_Query extends Complex_Query {

	/**
	 * Constructor.
	 *
	 * @param array $args
	 */
	public function __construct( array $args = array() ) {
		parent::__construct( Manager::get( 'itegms-relationships' ), $args );
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
			'purchase'            => '',
			'purchase__in'        => array(),
			'purchase__not_in'    => array(),
			'member'              => '',
			'member__in'          => array(),
			'member__not_in'      => array()
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
	 * @return object
	 */
	protected function make_object( \stdClass $data ) {
		return new Relationship( $data );
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

		if ( ( $purchase = $this->parse_purchase() ) !== null ) {
			$where->qAnd( $purchase );
		}

		if ( ( $member = $this->parse_member() ) !== null ) {
			$where->qAnd( $member );
		}

		$order = $this->parse_order();
		$limit = $this->parse_pagination();

		$builder->append( $select )->append( $from );

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
	 * Parse the purchase where.
	 *
	 * @since 1.0
	 *
	 * @return Where|null
	 */
	protected function parse_purchase() {

		if ( ! empty( $this->args['purchase'] ) ) {
			$this->args['purchase__in'] = array( $this->args['purchase'] );
		}

		return $this->parse_in_or_not_in_query( 'purchase', $this->args['purchase__in'], $this->args['purchase__not_in'] );
	}

	/**
	 * Parse the member where.
	 *
	 * @since 1.0
	 *
	 * @return Where|null
	 */
	protected function parse_member() {

		if ( ! empty( $this->args['member'] ) ) {
			$this->args['member__in'] = array( $this->args['member'] );
		}

		return $this->parse_in_or_not_in_query( 'member', $this->args['member__in'], $this->args['member__not_in'] );
	}
}