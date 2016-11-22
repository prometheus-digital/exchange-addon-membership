<?php
/**
 * Relationships table between memberships and customers.
 *
 * @author      iThemes
 * @since       1.0
 * @copyright   2015 (c) Iron Bound Designs, 2016 iThemes.
 * @license     GPLv2
 */

namespace ITEGMS\DB;

use IronBound\DB\Table\BaseTable;
use IronBound\DB\Table\Column\IntegerBased;

/**
 * Class Relationships
 *
 * @package ITEGMS\DB
 */
class Relationships extends BaseTable {

	/** @var array */
	private $columns = array();

	/**
	 * @inheritdoc
	 */
	public function get_table_name( \wpdb $wpdb ) {
		return "{$wpdb->prefix}itegms_relationships";
	}

	/**
	 * @inheritdoc
	 */
	public function get_slug() {
		return 'itegms-relationships';
	}

	/**
	 * @inheritdoc
	 */
	public function get_columns() {

		if ( $this->columns ) {
			return $this->columns;
		}

		$this->columns = array(
			'id'       =>
				new IntegerBased( 'BIGINT', 'id', array( 'unsigned', 'auto_increment', 'NOT NULL' ), array( 20 ) ),
			'member'   => new IntegerBased( 'BIGINT', 'member', array( 'unsigned', 'NOT NULL' ), array( 20 ) ),
			'purchase' => new IntegerBased( 'BIGINT', 'purchase', array( 'unsigned', 'NOT NULL' ), array( 20 ) ),
		);

		return $this->columns;
	}

	/**
	 * @inheritdoc
	 */
	public function get_column_defaults() {
		return array(
			'id'       => 0,
			'member'   => 0,
			'purchase' => 0
		);
	}

	/**
	 * @inheritdoc
	 */
	public function get_primary_key() {
		return 'id';
	}

	/**
	 * @inheritDoc
	 */
	protected function get_keys() {
		$keys   = parent::get_keys();
		$keys[] = 'UNIQUE KEY purchase__member (purchase,member)';

		return $keys;
	}

	/**
	 * @inheritdoc
	 */
	public function get_version() {
		return 1;
	}
}