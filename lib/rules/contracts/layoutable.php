<?php
/**
 * Contains the layoutable interface.
 *
 * @since   1.18
 * @license GPLv2
 */

/**
 * Interface IT_Exchange_Membership_Rule_Layoutable
 */
interface IT_Exchange_Membership_Rule_Layoutable {

	const L_GRID = 'grid';
	const L_LIST = 'list';

	/**
	 * Retrieve the layout. Must be one of L_GRID and L_LIST.
	 *
	 * @since 1.18
	 *
	 * @return string
	 */
	public function get_layout();
}