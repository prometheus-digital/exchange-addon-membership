<?php
/**
 * Contains deprecated functions.
 *
 * @since   1.18
 * @license GPLv2
 */

/**
 * AJAX to update drips interval
 *
 * @since      1.0.0
 * @deprecated 1.18
 *
 * @return void
 */
function it_exchange_membership_addon_ajax_update_interval() {

	_deprecated_function( __FUNCTION__, '1.18', 'it_exchange_membership_addon_ajax_update_drip_rule' );

	if ( ! empty( $_REQUEST['post_id'] ) && ! empty( $_REQUEST['membership_id'] ) && isset( $_REQUEST['interval'] ) ) {
		$post       = get_post( $_REQUEST['post_id'] );
		$membership = it_exchange_get_product( $_REQUEST['membership_id'] );
		$interval   = $_REQUEST['interval'];

		$drip = new IT_Exchange_Membership_Delay_Rule_Drip( $post, $membership );
		$drip->save( array(
			'interval' => $interval
		) );
	}

	die();
}

add_action( 'wp_ajax_it-exchange-membership-addon-update-drip-rule-interval', 'it_exchange_membership_addon_ajax_update_interval' );

/**
 * AJAX to update drips duration
 *
 * @since 1.0.0
 * @return void
 */
function it_exchange_membership_addon_ajax_update_duration() {

	_deprecated_function( __FUNCTION__, '1.18', 'it_exchange_membership_addon_ajax_update_drip_rule' );

	if ( ! empty( $_REQUEST['post_id'] ) && ! empty( $_REQUEST['membership_id'] ) && ! empty( $_REQUEST['duration'] ) ) {
		$post       = get_post( $_REQUEST['post_id'] );
		$membership = it_exchange_get_product( $_REQUEST['membership_id'] );
		$duration   = $_REQUEST['duration'];

		$drip = new IT_Exchange_Membership_Delay_Rule_Drip( $post, $membership );
		$drip->save( array(
			'duration' => $duration
		) );
	}

	die();
}

add_action( 'wp_ajax_it-exchange-membership-addon-update-drip-rule-duration', 'it_exchange_membership_addon_ajax_update_duration' );
