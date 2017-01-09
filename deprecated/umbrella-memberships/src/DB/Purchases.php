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

use IronBound\DB\Table\Table;

/**
 * Class Purchases
 *
 * @package ITEGMS\DB
 */
class Purchases implements Table {

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
		return array(
			'id'          => '%d',
			'transaction' => '%d',
			'customer'    => '%d',
			'membership'  => '%d',
			'seats'       => '%d',
			'active'      => '%d'
		);
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
	 * Get creation SQL.
	 *
	 * @since 1.0
	 *
	 * @param \wpdb $wpdb
	 *
	 * @return string
	 */
	public function get_creation_sql( \wpdb $wpdb ) {

		$tn = $this->get_table_name( $wpdb );

		return "CREATE TABLE {$tn} (
		id bigint(20) unsigned auto_increment NOT NULL,
		transaction bigint(20) unsigned NOT NULL,
		customer bigint(20) unsigned NOT NULL,
		membership bigint(20) unsigned NOT NULL,
		seats int unsigned NOT NULL,
		active tinyint(1) unsigned NOT NULL DEFAULT 1,
		PRIMARY KEY  (id),
		UNIQUE KEY transaction (transaction),
		UNIQUE KEY customer__membership__transaction (customer,membership,transaction)
		) {$wpdb->get_charset_collate()};";
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