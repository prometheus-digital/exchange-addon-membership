<?php
/**
 * iThemes Exchange Membership Add-on
 * @package IT_Exchange_Addon_Membership
 * @since   1.0.0
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

		$return = '<div class="it-exchange-membership-addon-content-access-rule columns-wrapper" data-count="' . $count . '">';

		$return .= '<div class="it-exchange-membership-addon-sort-content-access-rule column"></div>';

		$return .= it_exchange_membership_addon_get_selections( 0, null, $count );

		$return .= '<div class="it-exchange-content-access-content column"><div class="it-exchange-membership-content-type-terms hidden">';
		$return .= '</div></div>';

		$return .= '<div class="it-exchange-content-access-delay column">';
		$return .= '<div class="it-exchange-membership-content-type-drip hidden">';
		$return .= it_exchange_membership_addon_build_drip_rules( array(), $count );
		$return .= '</div>';
		$return .= '<div class="it-exchange-content-access-delay-unavailable hidden">';
		$return .= __( 'Available for single posts or pages', 'LION' );
		$return .= '</div>';
		$return .= '</div>';

		$return .= '<div class="it-exchange-membership-addon-remove-content-access-rule column">';
		$return .= '<a href="#">×</a>';
		$return .= '</div>';

		$return .= '<input type="hidden" class="it-exchange-content-access-group" name="it_exchange_content_access_rules[' . $count . '][grouped_id]" value="" />';

		$return .= '</div>';

	}

	die( $return );
}

add_action( 'wp_ajax_it-exchange-membership-addon-add-content-access-rule', 'it_exchange_membership_addon_ajax_add_content_access_rule' );


/**
 * Adds group to content access lists
 *
 * @since 1.0.7
 * @return string
 */
function it_exchange_membership_addon_ajax_add_content_access_group() {

	$return = '';

	if ( isset( $_REQUEST['count'] ) && isset( $_REQUEST['group_count'] ) ) { //use isset() in case count is 0

		$count    = $_REQUEST['count'];
		$group_id = $_REQUEST['group_count'];

		$return = '<div class="it-exchange-membership-addon-content-access-rule it-exchange-membership-addon-content-access-group columns-wrapper" data-count="' . $count . '">';

		$return .= '<div class="it-exchange-membership-addon-sort-content-access-rule column"></div>';

		$return .= '<input type="text" name="it_exchange_content_access_rules[' . $count . '][group]" value="" />';
		$return .= '<input type="hidden" name="it_exchange_content_access_rules[' . $count . '][group_id]" value="' . $group_id . '" />';

		$return .= '<div class="group-layout-options">';
		$return .= '<span class="group-layout active-group-layout" data-type="grid">grid</span><span class="group-layout" data-type="list">list</span>';
		$return .= '<input type="hidden" class="group-layout-input" name="it_exchange_content_access_rules[' . $count . '][group_layout]" value="grid" />';
		$return .= '</div>';

		$return .= '<div class="it-exchange-membership-addon-group-action-wrapper">';
		$return .= '<div class="it-exchange-membership-addon-group-action">ACTION</div>';
		$return .= '<div class="it-exchange-membership-addon-group-actions">';
		$return .= '	<div class="it-exchange-membership-addon-ungroup-content-access-group column">';
		$return .= '		<a href="#">' . __( 'Ungroup', 'LION' ) . '</a>';
		$return .= '	</div>';
		$return .= '	<div class="it-exchange-membership-addon-remove-content-access-group column">';
		$return .= '		<a href="#">' . __( 'Delete Group', 'LION' ) . '</a>';
		$return .= '	</div>';
		$return .= '</div>';
		$return .= '</div>';

		$return .= '<input type="hidden" class="it-exchange-content-access-group" name="it_exchange_content_access_rules[' . $count . '][grouped_id]" value="" />';

		$return .= '<div class="columns-wrapper it-exchange-membership-content-access-group-content content-access-sortable" data-group-id="' . $group_id . '"><div class="nosort">' . __( 'Drag content items into this area to group them together.', 'LION' ) . '</div></div>';

		$return .= '</div>';

	}

	die( $return );
}

add_action( 'wp_ajax_it-exchange-membership-addon-add-content-access-group', 'it_exchange_membership_addon_ajax_add_content_access_group' );

/**
 * AJAX function called to add new content type terms
 *
 * @since 1.0.0
 * @return string HTML output of content type terms
 */
function it_exchange_membership_addon_ajax_get_content_type_term() {

	$return = '';

	if ( ! empty( $_REQUEST['type'] ) && ! empty( $_REQUEST['value'] ) ) {

		$type    = $_REQUEST['type'];
		$value   = $_REQUEST['value'];
		$count   = $_REQUEST['count'];
		$options = '';

		$data = array(
			'selection' => $value
		);

		$factory = new IT_Exchange_Membership_Rule_Factory();
		$rule    = $factory->make_content_rule( $type, $data );

		if ( ! $rule ) {
			$options = apply_filters( 'it_exchange_membership_addon_get_custom_selected_options', $options, $value, $type );

			$return .= '<input type="hidden" value="' . $type . '" name="it_exchange_content_access_rules[' . $count . '][selected]" />';
			$return .= '<select class="it-exchange-membership-content-type-term" name="it_exchange_content_access_rules[' . $count . '][term]">';
			$return .= $options;
			$return .= '</select>';

			die( $return );
		}

		$return .= '<input type="hidden" value="' . $type . '" name="it_exchange_content_access_rules[' . $count . '][selected]" />';
		$return .= $rule->get_field_html( "it_exchange_content_access_rules[$count]" );

		if ( $rule->is_layout_configurable() ) {
			$return .= '<div class="group-layout-options">';
			$return .= '<span class="group-layout active-group-layout" data-type="grid">grid</span><span class="group-layout"data-type="list">list</span>';
			$return .= '<input type="hidden" class="group-layout-input" name="it_exchange_content_access_rules[' . $count . '][group_layout]" value="grid" />';
			$return .= '</div>';
		}
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

	$post = get_post( $_POST['ID'] );

	$return = '<div class="it-exchange-new-membership-rule-post it-exchange-new-membership-rule">';
	$return .= '<select class="it-exchange-membership-id" name="it_exchange_membership_id">';
	$membership_products = it_exchange_get_products( array(
		'product_type' => 'membership-product-type',
		'numberposts'  => - 1,
		'show_hidden'  => true
	) );
	foreach ( $membership_products as $membership ) {
		$return .= '<option value="' . $membership->ID . '">' . get_the_title( $membership->ID ) . '</option>';
	}
	$return .= '</select>';
	$return .= '<span class="it-exchange-membership-remove-new-rule">&times;</span>';

	$return .= '<div class="it-exchange-membership-rule-delay">' . __( 'Delay', 'LION' ) . '</div>';
	$return .= '<div class="it-exchange-membership-drip-rule">';
	$delay = new IT_Exchange_Membership_Delay_Rule_Drip( $post );
	$return .= $delay->get_field_html( 'new' );
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

	if ( ! empty( $_REQUEST['membership_id'] ) && ! empty( $_REQUEST['post_id'] ) ) {

		$post_id       = $_REQUEST['post_id'];
		$membership_id = $_REQUEST['membership_id'];

		//remove from content rule
		if ( ! ( $rules = get_post_meta( $post_id, '_item-content-rule', true ) ) ) {
			$rules = array();
		}

		if ( ( $key = array_search( $membership_id, $rules ) ) !== false ) {

			/**
			 * Fires when a post of any type is removed from the protection rules.
			 *
			 * @since 1.9
			 *
			 * @param int   $membership_id
			 * @param int   $post_id
			 * @param array $rule
			 */
			do_action( 'it_exchange_membership_remove_post_rule', $membership_id, $post_id, $rules[ $key ] );

			unset( $rules[ $key ] );
			update_post_meta( $post_id, '_item-content-rule', $rules );
		}

		//remove from exemptions
		if ( ! ( $exemptions = get_post_meta( $post_id, '_item-content-rule-exemptions', true ) ) ) {
			$exemptions = array();
		}

		if ( ! empty( $exemptions[ $membership_id ] ) ) {
			if ( ( $key = array_search( 'post', $exemptions[ $membership_id ] ) ) !== false ) {

				/**
				 * Fires when an exemption is removed from the protection rules.
				 *
				 * @since 1.0
				 *
				 * @param int    $membership_id
				 * @param int    $post_id
				 * @param string $exemption
				 */
				do_action( 'it_exchange_membership_remove_exemption', $membership_id, $post_id, $exemptions[ $membership_id ][ $key ] );

				unset( $exemptions[ $membership_id ][ $key ] );

				// clean up arrays if empty
				if ( empty( $exemptions[ $membership_id ][ $key ] ) ) {
					unset( $exemptions[ $membership_id ] );
				}
				if ( empty( $exemptions ) ) {
					delete_post_meta( $post_id, '_item-content-rule-exemptions' );
				} else {
					update_post_meta( $post_id, '_item-content-rule-exemptions', $exemptions );
				}
			}
		}

		//Remove from Membership Product (we need to keep these in sync)
		$membership_product_feature = it_exchange_get_product_feature( $membership_id, 'membership-content-access-rules' );
		$value                      = array(
			'selection' => 'post',
			'selected'  => 'posts',
			'term'      => $post_id,
		);
		if ( false !== $key = array_search( $value, $membership_product_feature ) ) {
			unset( $membership_product_feature[ $key ] );
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

	if ( ! empty( $_REQUEST['membership_id'] ) && ! empty( $_REQUEST['post_id'] ) ) {

		$post_id       = $_REQUEST['post_id'];
		$membership_id = $_REQUEST['membership_id'];

		if ( ! ( $rules = get_post_meta( $post_id, '_item-content-rule', true ) ) ) {
			$rules = array();
		}

		if ( ! in_array( $membership_id, $rules ) ) {
			$rules[] = $membership_id;
			update_post_meta( $post_id, '_item-content-rule', $rules );
		}

		$interval = ! empty( $_REQUEST['interval'] ) ? $_REQUEST['interval'] : 0;
		$duration = ! empty( $_REQUEST['duration'] ) ? $_REQUEST['duration'] : 'days';

		if ( ! empty( $interval ) ) {
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

		$rule                  = $value;
		$rule['group_layout']  = 'grid'; // set rule array to default options
		$rule['drip-interval'] = $interval;
		$rule['drip-duration'] = $duration;

		/**
		 * Fires when a post of any type is added to the protection rules.
		 *
		 * @since 1.9
		 *
		 * @param int   $membership_id
		 * @param int   $post_id
		 * @param array $rule
		 */
		do_action( 'it_exchange_membership_add_post_rule', $membership_id, $post_id, $rule );

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

	if ( ! empty( $_REQUEST['post_id'] ) && ! empty( $_REQUEST['membership_id'] ) && ! empty( $_REQUEST['rule_data'] ) && ! empty( $_REQUEST['checked'] ) ) {
		$post_id       = $_REQUEST['post_id'];
		$membership_id = $_REQUEST['membership_id'];
		$type          = $_REQUEST['type'];
		$checked       = $_REQUEST['checked'];
		$post          = get_post( $post_id );
		$membership    = it_exchange_get_product( $membership_id );
		$rule_data     = (array) $_REQUEST['rule_data'];

		$factory = new IT_Exchange_Membership_Rule_Factory();

		$rule = $factory->make_content_rule( $type, $rule_data, $membership );
		$rule->set_post_exempt( $post, $checked === 'false' );

		if ( $type === 'taxonomy' ) {
			$exemption = "taxonomy|{$rule->get_selection()}|{$rule->get_term()}";
		} else {
			$exemption = $rule->get_term();
		}

		if ( 'false' === $checked ) {

			/**
			 * Fires when an exemption is added to the protection rules.
			 *
			 * @since 1.9
			 *
			 * @param int    $membership_id
			 * @param int    $post_id
			 * @param string $exemption
			 */
			do_action( 'it_exchange_membership_add_exemption', $membership_id, $post_id, $exemption );
		} else {

			/**
			 * Fires when an exemption is removed from the protection rules.
			 *
			 * @since 1.9
			 *
			 * @param int    $membership_id
			 * @param int    $post_id
			 * @param string $exemption
			 */
			do_action( 'it_exchange_membership_remove_exemption', $membership_id, $post_id, $exemption );
		}
	}

	die();
}

add_action( 'wp_ajax_it-exchange-membership-addon-modify-restrictions-exemptions', 'it_exchange_membership_addon_ajax_modify_restrictions_exemptions' );

/**
 * AJAX callback to update a drip rule.
 *
 * Passes:
 *  - $post       The post ID of the content
 *  - $membership The membership ID for dripping
 *  - $changes    Array of changes to be saved. Keyed with 'interval', and 'duration'.
 *
 * @since 1.18
 */
function it_exchange_membership_addon_ajax_update_drip_rule() {

	if ( ! empty( $_REQUEST['post'] ) && ! empty( $_REQUEST['membership'] ) && ! empty( $_REQUEST['changes'] ) ) {
		$post       = get_post( $_REQUEST['post'] );
		$membership = it_exchange_get_product( $_REQUEST['membership'] );
		$changes    = $_REQUEST['changes'];

		$drip = new IT_Exchange_Membership_Delay_Rule_Drip( $post, $membership );
		$drip->save( $changes );
	}

	die();
}

add_action( 'wp_ajax_it-exchange-membership-update-drip-rule', 'it_exchange_membership_addon_ajax_update_drip_rule' );

function it_exchange_membership_addon_ajax_add_membership_child() {

	$return = '';

	if ( ! empty( $_REQUEST['post_id'] ) && ! empty( $_REQUEST['product_id'] ) ) {
		$child_ids = array();

		if ( ! empty( $_REQUEST['child_ids'] ) ) {
			foreach ( $_REQUEST['child_ids'] as $child_id ) {
				if ( 'it-exchange-membership-child-ids[]' === $child_id['name'] ) {
					$child_ids[] = $child_id['value'];
				}
			}
		}

		if ( ! in_array( $_REQUEST['product_id'], $child_ids ) ) {
			$child_ids[] = $_REQUEST['product_id'];
		}

		$return = it_exchange_membership_addon_display_membership_hierarchy( $child_ids, array( 'echo' => false ) );
	}

	die( $return );
}

add_action( 'wp_ajax_it-exchange-membership-addon-add-membership-child', 'it_exchange_membership_addon_ajax_add_membership_child' );

/**
 * AJAX to add new member relatives
 *
 * @since 1.0.0
 * @return void
 */
function it_exchange_membership_addon_ajax_add_membership_parent() {

	$return = '';

	if ( ! empty( $_REQUEST['post_id'] ) && ! empty( $_REQUEST['product_id'] ) ) {
		$parent_ids = array();
		if ( ! empty( $_REQUEST['parent_ids'] ) ) {
			foreach ( $_REQUEST['parent_ids'] as $parent_id ) {
				if ( 'it-exchange-membership-parent-ids[]' === $parent_id['name'] ) {
					$parent_ids[] = $parent_id['value'];
				}
			}
		}

		if ( ! in_array( $_REQUEST['product_id'], $parent_ids ) ) {
			$parent_ids[] = $_REQUEST['product_id'];
		}

		$return .= '<ul>';
		foreach ( $parent_ids as $parent_id ) {
			$return .= '<li data-parent-id="' . $parent_id . '">';
			$return .= '<div class="inner-wrapper">' . get_the_title( $parent_id ) . ' <a data-membership-id="' . $parent_id . '" class="it-exchange-membership-addon-delete-membership-parent it-exchange-remove-item">x</a>';
			$return .= '<input type="hidden" name="it-exchange-membership-parent-ids[]" value="' . $parent_id . '" /></div>';
			$return .= '</li>';
		}
		$return .= '</ul>';
	}

	die( $return );
}

add_action( 'wp_ajax_it-exchange-membership-addon-add-membership-parent', 'it_exchange_membership_addon_ajax_add_membership_parent' );

