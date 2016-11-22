<?php
/**
 * Table for representing umbrella membership purchases.
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
 * Class Purchases
 *
 * @package ITEGMS\DB
 */
class Purchases extends BaseTable {

	/** @var array */
	private $columns = array();

	/**
	 * Retrieve the name of the database table.
	 *
	 * @since 1.0
	 *
	 * @param \wpdb $wpdb
	 *
	 * @return string
	 */
	public function get_table_name( \wpdb $wpdb ) {
		return "{$wpdb->prefix}itegms_purchases";
	}

	/**
	 * Get the slug of this table.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public function get_slug() {
		return 'itegms-purchases';
	}

	/**
	 * Columns in the table.
	 *
	 * key => sprintf field type
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function get_columns() {

		if ( $this->columns ) {
			return $this->columns;
		}

		$this->columns = array(
			'id'          =>
				new IntegerBased( 'BIGINT', 'id', array( 'unsigned', 'auto_increment', 'NOT NULL' ), array( 20 ) ),
			'transaction' => new IntegerBased( 'BIGINT', 'transaction', array( 'unsigned', 'NOT NULL' ), array( 20 ) ),
			'customer'    => new IntegerBased( 'BIGINT', 'customer', array( 'unsigned', 'NOT NULL' ), array( 20 ) ),
			'membership'  => new IntegerBased( 'BIGINT', 'membership', array( 'unsigned', 'NOT NULL' ), array( 20 ) ),
			'seats'       => new IntegerBased( 'INT', 'seats', array( 'unsigned', 'NOT NULL' ) ),
			'active'      =>
				new IntegerBased( 'TINYINT', 'active', array( 'unsigned', 'NOT NULL', 'DEFAULT 1' ), array( 1 ) ),
		);

		return $this->columns;
	}

	/**
	 * Default column values.
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function get_column_defaults() {
		return array(
			'id'          => 0,
			'transaction' => 0,
			'customer'    => 0,
			'membership'  => 0,
			'seats'       => 1,
			'active'      => true
		);
	}

	/**
	 * Retrieve the name of the primary key.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public function get_primary_key() {
		return 'id';
	}

	/**
	 * @inheritDoc
	 */
	protected function get_keys() {
		$keys = parent::get_keys();

		$keys[] = 'UNIQUE KEY transaction (transaction)';
		$keys[] = 'UNIQUE KEY customer__membership__transaction (customer,membership,transaction)';

		return $keys;
	}

	/**
	 * Retrieve the version number of the current table schema as written.
	 *
	 * The version should be incremented by 1 for each change.
	 *
	 * @since 1.0
	 *
	 * @return int
	 */
	public function get_version() {
		return 1;
	}
}