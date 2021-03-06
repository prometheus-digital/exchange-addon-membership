<?php
/**
 * Queue interface.
 *
 * @author      ExchangeWP
 * @since       1.0
 *
 * @copyright   Copyright (c) 2015, Iron Bound Designs, Inc, 2017 ExchangeWP.
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License v2 or later
 */

namespace IronBound\WP_Notifications\Queue;

use IronBound\WP_Notifications\Contract;
use IronBound\WP_Notifications\Strategy\Strategy;

/**
 * Interface Queue
 * @package IronBound\WP_Notifications\Queue
 */
interface Queue {

	/**
	 * Process a batch of notifications.
	 *
	 * @since 1.0
	 *
	 * @param Contract[] $notifications
	 * @param Strategy   $strategy
	 *
	 * @throws \Exception
	 */
	public function process( array $notifications, Strategy $strategy );
}
