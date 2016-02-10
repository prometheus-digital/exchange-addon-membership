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

		$return .= '<div class="it-exchange-content-access-content column"><div class="it-exchange-membership-content-type-terms hidden"></div></div>';

		$return .= '<div class="it-exchange-content-access-delay column">';
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

		$return .= '<input type="text" name="it_exchange_content_access_rules[' . $count . '][group]" class="it-exchange-membership-group-rule-title" value="" />';
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

		$return .= '<div class="columns-wrapper it-exchange-membership-content-access-group-content content-access-sortable" data-group-id="' . $group_id . '">';
		$return .= '<div class="nosort">' . __( 'Drag content items into this area to group them together.', 'LION' ) . '</div></div>';

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

		if ( $rule instanceof IT_Exchange_Membership_Rule_Layoutable ) {
			$return .= '<div class="group-layout-options">';
			$return .= '<span class="group-layout active-group-layout" data-type="grid">grid</span><span class="group-layout" data-type="list">list</span>';
			$return .= '<input type="hidden" class="group-layout-input" name="it_exchange_content_access_rules[' . $count . '][group_layout]" value="grid" />';
			$return .= '</div>';
		}
	}

	die( $return );

}

add_action( 'wp_ajax_it-exchange-membership-addon-content-type-terms', 'it_exchange_membership_addon_ajax_get_content_type_term' );

/**
 * AJAX function called to grab the HTML for the content delay rules.
 *
 * @since 1.18.0
 */
function it_exchange_membership_addon_ajax_get_content_delay_rules() {

	ob_start();

	$name      = 'it_exchange_content_access_rules';
	$count     = $_REQUEST['count'];
	$delayable = $_REQUEST['delayable'];

	$all_delay = it_exchange_membership_addon_get_delay_rules();
	?>

	<?php if ( $delayable === 'no' ): ?>
		<div class="it-exchange-content-access-delay-unavailable">
			<?php _e( 'Available for single posts or pages.', 'LION' ); ?>
		</div>
	<?php else: ?>
		<div class="it-exchange-membership-delay-rule-selection">
			<label for="<?php echo $name . "[$count]"; ?>-delay-type" class="screen-reader-text">
				<?php _e( 'Select a delay rule type.', 'LION' ); ?>
			</label>
			<select name="<?php echo $name . "[$count]"; ?>[delay-type]" id="<?php echo $name . "[$count]"; ?>-delay-type">
				<option value=""><?php _e( 'None', 'LION' ); ?></option>
				<?php foreach ( $all_delay as $delay ): ?>
					<option value="<?php echo $delay->get_type(); ?>">
						<?php echo $delay->get_type( true ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<?php foreach ( $all_delay as $delay ): ?>
			<div class="it-exchange-membership-content-delay-rule-<?php echo $delay->get_type(); ?> it-exchange-membership-content-delay-rule hidden">
				<?php echo $delay->get_field_html( $name . "[$count][delay]" ); ?>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>

	<?php

	die( ob_get_clean() );
}

add_action( 'wp_ajax_it-exchange-membership-addon-content-delay-rules', 'it_exchange_membership_addon_ajax_get_content_delay_rules' );

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

	$all_delay = it_exchange_membership_addon_get_delay_rules( $post );

	ob_start();
	?>
	<div class="it-exchange-membership-delay-rule-selection">
		<label for="it-exchange-membership-delay-type" class="screen-reader-text">
			<?php _e( 'Select a delay rule type.', 'LION' ); ?>
		</label>
		<select id="it-exchange-membership-delay-type">
			<option value=""><?php _e( 'None', 'LION' ); ?></option>
			<?php foreach ( $all_delay as $delay ): ?>
				<option value="<?php echo $delay->get_type(); ?>">
					<?php echo $delay->get_type( true ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>

	<?php
	$return .= ob_get_clean();

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

	if ( ! empty( $_REQUEST['membership_id'] ) && ! empty( $_REQUEST['post_id'] ) && ! empty( $_REQUEST['rule'] ) ) {

		$post_id       = $_REQUEST['post_id'];
		$membership_id = $_REQUEST['membership_id'];
		$rule_id       = $_REQUEST['rule'];

		$factory = new IT_Exchange_Membership_Rule_Factory();
		$rule    = $factory->make_content_rule_by_id( $rule_id, it_exchange_get_product( $membership_id ) );
		$rule->delete();

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

		//Add details to Membership Product (we need to keep these in sync)
		$membership_product_feature = it_exchange_get_product_feature( $membership_id, 'membership-content-access-rules' );

		$value = array(
			'selection'  => get_post_type( $post_id ),
			'selected'   => 'posts',
			'term'       => $post_id,
			'delay-type' => $_REQUEST['delay']
		);

		$rule = $value;

		$rule['group_layout']  = 'grid'; // set rule array to default options
		$rule['drip-interval'] = 0;
		$rule['drip-duration'] = IT_Exchange_Membership_Delay_Rule_Drip::D_DAYS;

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
 *  - $type       Delay rule type.
 *
 * @since 1.18
 */
function it_exchange_membership_addon_ajax_update_drip_rule() {

	if ( ! empty( $_REQUEST['post'] ) && ! empty( $_REQUEST['membership'] ) && ! empty( $_REQUEST['changes'] ) && ! empty( $_REQUEST['type'] ) ) {
		$post       = get_post( $_REQUEST['post'] );
		$membership = it_exchange_get_product( $_REQUEST['membership'] );
		$type       = $_REQUEST['type'];
		$changes    = $_REQUEST['changes'];

		$factory = new IT_Exchange_Membership_Rule_Factory();
		$delay   = $factory->make_delay_rule( $type, $membership, $post );

		if ( $delay ) {
			try {
				$delay->save( $changes );
			}
			catch ( Exception $e ) {

			}
		}
	}

	die();
}

add_action( 'wp_ajax_it-exchange-membership-update-delay-rule', 'it_exchange_membership_addon_ajax_update_drip_rule' );

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

