<?php
/**
 * Having Clause
 *
 * @author      ExchangeWP
 * @since       1.0
 * @copyright   2015 (c) Iron Bound Designs, 2016 iThemes, 2017 ExchangeWP.
 * @license     GPLv2
 */

namespace IronBound\DB\Query\Tag;

/**
 * Class Having
 * @package IronBound\DB\Query\Tag
 */
class Having extends Generic {

	/**
	 * Constructor.
	 *
	 * @param Where $where
	 */
	public function __construct( Where $where ) {
		parent::__construct( 'HAVING', $where->get_value() );
	}
}
