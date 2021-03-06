<?php
/**
 * Interface representing cacheable objects.
 *
 * @author      ExchangeWP
 * @since       1.0
 * @copyright   2015 (c) Iron Bound Designs, 2016 iThemes, 2017 ExchangeWP.
 * @license     GPLv2
 */

namespace IronBound\Cache;

/**
 * Interface Cacheable
 * @package IronBound\Cache;
 */
interface Cacheable {

	/**
	 * Get the unique pk for this record.
	 *
	 * @since 1.0
	 *
	 * @return mixed (generally int, but not necessarily).
	 */
	public function get_pk();

	/**
	 * Get the data to cache.
	 *
	 * This data should be the same data used to construct this class.
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function get_data_to_cache();

	/**
	 * Get the group name this record should be stored in.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public static function get_cache_group();
}
