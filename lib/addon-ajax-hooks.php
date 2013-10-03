<?php
/**
 * iThemes Exchange Membership Add-on
 * @package IT_Exchange_Addon_Membership
 * @since 1.0.0
*/

/**
 * AJAX function called to add new content access rule rows
 *
 * @since 1.0.0
 * @return string HTML output of content access rule row div
*/
function it_exchange_membership_addon_ajax_add_content_access_rule() {
	
	$return = '';
	
	if ( isset( $_REQUEST['count'] ) ) { //use isset() in case count is 0
		
		$count = $_REQUEST['count'];
		
		$return  = '<div class="it-exchange-membership-content-access-rule columns-wrapper" data-count="' . $count . '">';
		
		$return .= '<div class="it-exchange-membership-addon-sort-content-access-rule column"></div>';
		
		$return .= it_exchange_membership_addon_get_selections( 0, NULL, $count );
		
		$return .= '<div class="it-exchange-content-access-content column"><div class="it-exchange-membership-content-type-terms hidden">';
		$return .= '</div></div>';
		
		$return .= '<div class="it-exchange-content-access-delay column"><div class="it-exchange-membership-content-type-drip hidden">';
		$return .= it_exchange_membership_addon_build_drip_rules( false, $count );
		$return .= '</div></div>';
		
		$return .= '<div class="it-exchange-membership-addon-remove-content-access-rule column">';
		$return .= '<a href="#">×</a>';
		$return .= '</div>';
		
		$return .= '</div>';
	
	}
	
	die( $return );
}
add_action( 'wp_ajax_it-exchange-membership-addon-add-content-access-rule', 'it_exchange_membership_addon_ajax_add_content_access_rule' );

/**
 * AJAX function called to add new content type terms
 *
 * @since 1.0.0
 * @return string HTML output of content type terms
*/
function it_exchange_membership_addon_ajax_get_content_type_term() {
	
	$return = '';
	
	if ( !empty( $_REQUEST['type'] ) && !empty( $_REQUEST['value'] ) ) {
			
		$type  = $_REQUEST['type'];
		$value = $_REQUEST['value'];
		$count = $_REQUEST['count'];
		$options = '';
	
		switch( $type ) {
			
			case 'posts':
				$posts = get_posts( array( 'post_type' => $value, 'posts_per_page' => -1 ) );
				foreach ( $posts as $post ) {
					$options .= '<option value="' . $post->ID . '">' . $post->post_title . '</option>';	
				}
				break;
			
			case 'post_types':
				$hidden_post_types = apply_filters( 'it_exchange_membership_addon_hidden_post_types', array( 'attachment', 'revision', 'nav_menu_item', 'it_exchange_tran', 'it_exchange_coupon', 'it_exchange_prod', 'it_exchange_download', 'page' ) );
				$post_types = get_post_types( array(), 'objects' );
				foreach ( $post_types as $post_type ) {
					if ( in_array( $post_type->name, $hidden_post_types ) ) 
						continue;
						
					$options .= '<option value="' . $post_type->name . '">' . $post_type->label . '</option>';	
				}
				break;
			
			case 'taxonomy':
				$terms = get_terms( $value, array( 'hide_empty' => false ) );
				foreach ( $terms as $term ) {
					$options .= '<option value="' . $term->term_id . '">' . $term->name . '</option>';	
				}
				break;
			
		}

		$return .= '<input type="hidden" value="' . $type . '" name="it_exchange_content_access_rules[' . $count . '][selected]" />';
		$return .= '<select class="it-exchange-membership-content-type-term" name="it_exchange_content_access_rules[' . $count . '][term]">';
		$return .= $options;
		$return .= '</select>';
		
	}

	die( $return );
	
}
add_action( 'wp_ajax_it-exchange-membership-addon-content-type-terms', 'it_exchange_membership_addon_ajax_get_content_type_term' );

/**
 * AJAX function called to add new content access rules to a WordPress $post
 *
 * @since 1.0.0
 * @return string HTML output of content access rules
*/
function it_exchange_membership_addon_ajax_add_content_access_rule_to_post() {
	
	$return  = '<div class="it-exchange-new-membership-rule-post it-exchange-new-membership-rule">';
	$return .= '<select class="it-exchange-membership-id" name="it_exchange_membership_id">';
	$membership_products = it_exchange_get_products( array( 'product_type' => 'membership-product-type' ) );
	foreach ( $membership_products as $membership ) {
		$return .= '<option type="checkbox" value="' . $membership->ID . '">' . get_the_title( $membership->ID ) . '</option>';
	}
	$return .= '</select>';
	$return .= '<span class="it-exchange-membership-remove-new-rule">&times;</span>';
	// $return .= '<div class="it-exchange-membership-rule-delay">' . __( 'Delay', 'LION' ) . '</div>';
	
	$return .= '<div class="it-exchange-membership-drip-rule">';
	$return .= '<input class="it-exchange-membership-drip-rule-interval" type="number" min="0" value="0" name="it_exchange_membership_drip_interval" />';
	$return .= '<select class="it-exchange-membership-drip-rule-duration" name="it_exchange_membership_drip_duration">';
	$durations = array(
		'days'   => __( 'Days', 'LION' ),
		'weeks'  => __( 'Weeks', 'LION' ),
		'months' => __( 'Months', 'LION' ),
		'years'  => __( 'Years', 'LION' ),
	);
	$durations = apply_filters( 'it-exchange-membership-drip-durations', $durations );
	foreach( $durations as $key => $string ) {
		$return .= '<option value="' . $key . '"' . selected( $key, apply_filters( 'it-exchange-membership-default-selected-drip-duration', 'days' ), false ) . '>' . $string . '</option>';
	}
	$return .= '</select>';
	$return .= '</div>';
	
	$return .= '<div class="it-exchange-add-new-restriction-ok-button">';
	$return .= '<a href class="button">' . __( 'OK', 'LION' ) . '</a>';
	$return .= '</div>';
	$return .= '</div>';
	
	die( $return );
}
add_action( 'wp_ajax_it-exchange-membership-addon-add-content-access-rule-to-post', 'it_exchange_membership_addon_ajax_add_content_access_rule_to_post' );

/**
 * AJAX function called to remove content access rules to a WordPress $post
 *
 * @since 1.0.0
 * @return string HTML output of content access rules
*/
function it_exchange_membership_addon_ajax_remove_rule_from_post() {
	
	$return = '';
	
	if ( !empty( $_REQUEST['membership_id'] ) && !empty( $_REQUEST['post_id'] ) ) {
		
		$post_id = $_REQUEST['post_id'];
		$membership_id = $_REQUEST['membership_id'];

		//remove from content rule
		if ( !( $rules = get_post_meta( $post_id, '_item-content-rule', true ) ) )
			$rules = array();
			
		if ( ( $key = array_search( $membership_id, $rules ) ) !== false ) {
			unset( $rules[$key] );
			update_post_meta( $post_id, '_item-content-rule', $rules );
		}
		
		//remove from exemptions
		if ( !( $exemptions = get_post_meta( $post_id, '_item-content-rule-exemptions', true ) ) )
			$exemptions = array();
		
		if ( !empty( $exemptions[$membership_id] ) ) {
			if ( ( $key = array_search( 'post', $exemptions[$membership_id] ) ) !== false ) {
				unset( $exemptions[$membership_id][$key] );
				if ( empty( $exemptions[$membership_id][$key] ) )
					unset( $exemptions[$membership_id] );
				if ( empty( $exemptions ) )
					delete_post_meta( $post_id, '_item-content-rule-exemptions' );
				else
					update_post_meta( $post_id, '_item-content-rule-exemptions', $exemptions );
			}
		}
		
		//Remove from Membership Product (we need to keep these in sync)
		$membership_product_feature = it_exchange_get_product_feature( $membership_id, 'membership-content-access-rules' );
		$value = array(
			'selection' => 'post',
			'selected'  => 'posts',
			'term'      => $post_id,
		);	
		if ( false !== $key = array_search( $value, $membership_product_feature ) ) {
			unset( $membership_product_feature[$key] );
			it_exchange_update_product_feature( $membership_id, 'membership-content-access-rules', $membership_product_feature );
		}
		
		$return = it_exchange_membership_addon_build_post_restriction_rules( $post_id );
	
	}
	
	die( $return );
}
add_action( 'wp_ajax_it-exchange-membership-addon-remove-rule-from-post', 'it_exchange_membership_addon_ajax_remove_rule_from_post' );

/**
 * AJAX function called to add new content access rules to a WordPress $post
 *
 * @since 1.0.0
 * @return string HTML output of content access rules
*/
function it_exchange_membership_addon_ajax_add_new_rule_to_post() {
	
	$return = '';
	
	if ( !empty( $_REQUEST['membership_id'] ) && !empty( $_REQUEST['post_id'] ) ) {
		
		$post_id = $_REQUEST['post_id'];
		$membership_id = $_REQUEST['membership_id'];

		if ( !( $rules = get_post_meta( $post_id, '_item-content-rule', true ) ) )
			$rules = array();
			
		if ( !in_array( $membership_id, $rules ) ) {
			$rules[] = $membership_id;
			update_post_meta( $post_id, '_item-content-rule', $rules );
		}
		
		$interval = !empty( $_REQUEST['interval'] ) ? $_REQUEST['interval'] : 0;
		$duration = !empty( $_REQUEST['duration'] ) ? $_REQUEST['duration'] : 'days';
		
		if ( !empty( $interval ) ) {
			update_post_meta( $post_id, '_item-content-rule-drip-interval-' . $membership_id, $interval );
			update_post_meta( $post_id, '_item-content-rule-drip-duration-' . $membership_id, $duration );
		}
		
		//Add details to Membership Product (we need to keep these in sync)
		$membership_product_feature = it_exchange_get_product_feature( $membership_id, 'membership-content-access-rules' );
		
		$value = array(
			'selection' => get_post_type( $post_id ),
			'selected'  => 'posts',
			'term'      => $post_id,
		);
		if ( false === array_search( $value, $membership_product_feature ) ) {
			$membership_product_feature[] = $value;
			it_exchange_update_product_feature( $membership_id, 'membership-content-access-rules', $membership_product_feature );
		}
		
		$return = it_exchange_membership_addon_build_post_restriction_rules( $post_id );
	
	}
	
	die( $return );
}
add_action( 'wp_ajax_it-exchange-membership-addon-add-new-rule-to-post', 'it_exchange_membership_addon_ajax_add_new_rule_to_post' );

/**
 * AJAX function called to set/unset restriction exemptions
 *
 * @since 1.0.0
 * @return void
*/
function it_exchange_membership_addon_ajax_modify_restrictions_exemptions() {
	
	$return = '';
		
	if ( !empty( $_REQUEST['post_id'] ) && !empty( $_REQUEST['membership_id'] ) && !empty( $_REQUEST['exemption'] ) && !empty( $_REQUEST['checked'] ) ) {
		$post_id       = $_REQUEST['post_id'];
		$membership_id = $_REQUEST['membership_id'];
		$exemption     = $_REQUEST['exemption'];
		$checked       = $_REQUEST['checked'];
		
		if ( 'false' === $checked ) {
			//add to exemptions
			if ( !( $exemptions = get_post_meta( $post_id, '_item-content-rule-exemptions', true ) ) )
				$exemptions = array();
				
			if ( !in_array( $exemption, $exemptions[$membership_id] ) ) {
				$exemptions[$membership_id][] = $exemption;
				update_post_meta( $post_id, '_item-content-rule-exemptions', $exemptions );
			}
		} else {
			//remove from exemptions
			if ( !( $exemptions = get_post_meta( $post_id, '_item-content-rule-exemptions', true ) ) )
				$exemptions = array();
			
			if ( !empty( $exemptions[$membership_id] ) ) {
				if ( ( $key = array_search( $exemption, $exemptions[$membership_id] ) ) !== false ) {
					unset( $exemptions[$membership_id][$key] );
					if ( empty( $exemptions[$membership_id][$key] ) )
						unset( $exemptions[$membership_id] );
					if ( empty( $exemptions ) )
						delete_post_meta( $post_id, '_item-content-rule-exemptions' );
					else
						update_post_meta( $post_id, '_item-content-rule-exemptions', $exemptions );
				}
			}
		}
	}
	
	die();
}
add_action( 'wp_ajax_it-exchange-membership-addon-modify-restrictions-exemptions', 'it_exchange_membership_addon_ajax_modify_restrictions_exemptions' );

/**
 * AJAX to update drips interval
 *
 * @since 1.0.0
 * @return void
*/
function it_exchange_membership_addon_ajax_update_interval() {
	
	$return = '';
		
	if ( !empty( $_REQUEST['post_id'] ) && !empty( $_REQUEST['membership_id'] ) && isset( $_REQUEST['interval'] ) ) {
		$post_id       = $_REQUEST['post_id'];
		$membership_id = $_REQUEST['membership_id'];
		$interval      = $_REQUEST['interval'];
		update_post_meta( $post_id, '_item-content-rule-drip-interval-' . $membership_id, absint( $interval ) );
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
	
	$return = '';
		
	if ( !empty( $_REQUEST['post_id'] ) && !empty( $_REQUEST['membership_id'] ) && !empty( $_REQUEST['duration'] ) ) {
		$post_id       = $_REQUEST['post_id'];
		$membership_id = $_REQUEST['membership_id'];
		$duration      = $_REQUEST['duration'];
		update_post_meta( $post_id, '_item-content-rule-drip-duration-' . $membership_id, $duration );
	}
	
	die();
}
add_action( 'wp_ajax_it-exchange-membership-addon-update-drip-rule-duration', 'it_exchange_membership_addon_ajax_update_duration' );