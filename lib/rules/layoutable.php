<?php
/**
 * File Description
 *
 * @author    Iron Bound Designs
 * @since     1.0
 * @license   AGPL
 * @copyright Iron Bound Designs, 2016.
 */

/**
 * Interface IT_Exchange_Membership_Rule_Layoutable
 */
interface IT_Exchange_Membership_Rule_Layoutable {

	const L_GRID = 'grid';
	const L_LIST = 'list';

	public function get_layout();
}