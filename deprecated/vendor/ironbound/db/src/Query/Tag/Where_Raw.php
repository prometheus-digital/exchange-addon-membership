<?php
/**
 * Allows for Raw where statements.
 *
 * @author      iThemes
 * @since       1.0
 * @copyright   2015 (c) Iron Bound Designs, 2016 iThemes.
 * @license     GPLv2
 */

namespace IronBound\DB\Query\Tag;

/**
 * Class Where_Raw
 * @package IronBound\DB\Query\Tag
 */
class Where_Raw extends Where {

	/**
	 * Constructor.
	 *
	 * @since 1.0
	 *
	 * @param string $sql
	 */
	public function __construct( $sql ) {
		parent::__construct( null, null, $sql );
	}

	/**
	 * Get the raw sql as the comparsion data.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	protected function get_comparison() {
		return $this->value;
	}
}