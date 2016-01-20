<?php
/**
 * Purchase related functions.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2015 (c) Iron Bound Designs.
 * @license     GPLv2
 */

/**
 * Get the person paying for a membership for a member.
 *
 * @since 1.0
 *
 * @param IT_Exchange_Customer $member
 * @param IT_Exchange_Product  $membership
 *
 * @return \ITEGMS\Purchase\Purchase|null
 */
function itegms_get_purchase_for_members_membership( IT_Exchange_Customer $member, IT_Exchange_Product $membership ) {

	$sub = 'SELECT purchase FROM wp_itegms_relationships WHERE member = %d';
	$sql = "SELECT * FROM wp_itegms_purchases WHERE membership = %d AND id IN ( $sub );";

	global $wpdb;

	$results = $wpdb->get_row( $wpdb->prepare( $sql, $membership->ID, $member->id ) );

	if ( $results ) {
		return new \ITEGMS\Purchase\Purchase( $results );
	} else {
		return null;
	}
}

/**
 * Get a purchase record by a transaction.
 *
 * @since 1.0
 *
 * @param IT_Exchange_Transaction $transaction
 *
 * @return \ITEGMS\Purchase\Purchase|null
 */
function itegms_get_purchase_by_transaction( IT_Exchange_Transaction $transaction ) {

	$query = new \ITEGMS\Purchase\Purchase_Query( array(
		'transaction' => $transaction->ID
	) );

	$purchases = $query->get_results();

	if ( empty( $purchases ) ) {
		return null;
	}

	return reset( $purchases );
}