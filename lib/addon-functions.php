<?php
/**
 * iThemes Exchange Membership Add-on
 * @package IT_Exchange_Addon_Membership
 * @since   1.0.0
 */

/**
 * The following file contains utility functions specific to our membership add-on
 * If you're building your own product-type addon, it's likely that you will
 * need to do similar things. This includes enqueueing scripts, formatting data for stripe, etc.
 */

/**
 * Returns HTML w/ selection options for content access rule builder
 *
 * @since 1.0.0
 *
 * @param int    $selection      current selection (if it exists)
 * @param string $selection_type current selection type (if it exists)
 * @param int    $count          current row count, used for JavaScript/AJAX
 * @param object $post_types     WordPress Post Types
 * @param object $taxonomies     WordPress Taxonomies
 *
 * @return string HTML output of selections row div
 */
function it_exchange_membership_addon_get_selections( $selection = 0, $selection_type = null, $count, $post_types = null, $taxonomies = null ) {

	$return = '<div class="it-exchange-content-access-type column">';
	$return .= '<select class="it-exchange-membership-content-type-selections" name="it_exchange_content_access_rules[' . $count . '][selection]">';
	$return .= '<option value="">' . __( 'Select Content', 'LION' ) . '</option>';

	$rule_groups = it_exchange_membership_addon_get_content_rules( false );
	$other       = array();

	foreach ( $rule_groups as $rules ) {

		$rule = reset( $rules );

		if ( count( $rules ) > 1 ) {
			$return .= "<optgroup label='{$rule->get_type( true )}'>";
		} else {
			$other[] = $rule;
			continue;
		}

		/** @var IT_Exchange_Membership_Content_RuleInterface $rule */
		foreach ( $rules as $rule ) {
			$selected = selected( $rule->get_value(), $selection, false );
			$return .= "<option value='{$rule->get_value()}' data-type='{$rule->get_type()}' $selected>";
			$return .= $rule->get_label();
			$return .= "</option>";
		}

		if ( count( $rules ) > 1 ) {
			$return .= '</optgroup>';
		}
	}

	if ( $other ) {

		$return .= '<optgroup label="' . __( 'Other', 'LION' ) . '">';

		foreach ( $other as $rule ) {

			$selected = selected( $rule->get_value(), $selection, false );
			$return .= "<option value='{$rule->get_value()}' data-type='{$rule->get_type()}' $selected>";
			$return .= $rule->get_label();
			$return .= "</option>";
		}
	}

	$return .= apply_filters( 'it_exchange_membership_addon_get_selections', '', $selection, $selection_type );

	$return .= '</select>';
	$return .= '</div>';

	return $return;
}

/**
 * Build the content rules restriction HTML.
 *
 * @since 1.0
 *
 * @param array $rules
 * @param int   $product_id
 *
 * @return string
 */
function it_exchange_membership_addon_build_content_rules( $rules, $product_id ) {
	$count        = 0;
	$group_count  = 0;
	$groupings    = array();
	$post_types   = get_post_types( array( 'public' => true ), 'objects' );
	$taxonomies   = get_taxonomies( array( 'public' => true ), 'objects' );
	$cache        = new stdClass();
	$cache->posts = new stdClass();
	$cache->terms = new stdClass();

	$return = '<div class="it-exchange-membership-addon-content-access-rules content-access-sortable">';

	if ( ! empty( $rules ) ) {

		foreach ( $rules as $rule ) {
			$options = '';

			$current_grouped_id = isset( $rule['grouped_id'] ) ? $rule['grouped_id'] : false;

			if ( ! empty( $groupings ) && $current_grouped_id !== end( $groupings ) ) {

				$return .= '</div></div>'; //this is ending the divs from the group opening in it_exchange_membership_addon_build_content_rule()
				array_pop( $groupings );

			} else if ( false === $current_grouped_id && ! empty( $groupings ) ) {

				foreach ( $groupings as $group ) {
					$return .= '</div></div>'; //this is ending the divs from the group opening in it_exchange_membership_addon_build_content_rule()
				}
				$groupings = array();

			}

			$selection    = ! empty( $rule['selection'] ) ? $rule['selection'] : false; //Content Types (e.g. post_types or taxonomies)
			$selected     = ! empty( $rule['selected'] ) ? $rule['selected'] : false;  //Content Type (e.g. posts post_type, or category taxonomy)
			$value        = ! empty( $rule['term'] ) ? $rule['term'] : false;      //Content (e.g. specific post or category)
			$group        = isset( $rule['group'] ) ? $rule['group'] : null;
			$group_layout = ! empty( $rule['group_layout'] ) ? $rule['group_layout'] : 'grid';
			$group_id     = isset( $rule['group_id'] ) ? $rule['group_id'] : null;
			$grouped_id   = isset( $rule['grouped_id'] ) ? $rule['grouped_id'] : null;

			if ( isset( $group ) && isset( $group_id ) ) {
				$group_class = 'it-exchange-membership-addon-content-access-group';
			} else {
				$group_class = '';
			}

			$return .= '<div class="it-exchange-membership-addon-content-access-rule ' . $group_class . ' columns-wrapper" data-count="' . $count . '">';
			$return .= '<div class="it-exchange-membership-addon-sort-content-access-rule column col-1_4-12"></div>';

			if ( isset( $group_id ) ) {

				$return .= '<input type="text" name="it_exchange_content_access_rules[' . $count . '][group]" value="' . $group . '" />';
				$return .= '<input type="hidden" name="it_exchange_content_access_rules[' . $count . '][group_id]" value="' . $group_id . '" />';

				$return .= '<div class="group-layout-options">';
				$return .= '<span class="group-layout ' . ( 'grid' === $group_layout ? 'active-group-layout' : '' ) . '" data-type="grid">grid</span><span class="group-layout ' . ( 'list' === $group_layout ? 'active-group-layout' : '' ) . '" data-type="list">list</span>';
				$return .= '<input type="hidden" class="group-layout-input" name="it_exchange_content_access_rules[' . $count . '][group_layout]" value="' . $group_layout . '" />';
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

				$return .= '<input type="hidden" class="it-exchange-content-access-group" name="it_exchange_content_access_rules[' . $count . '][grouped_id]" value="' . $grouped_id . '" />';

				$return .= '<div class="columns-wrapper it-exchange-membership-content-access-group-content content-access-sortable" data-group-id="' . $group_id . '">';
				//we don't want to end the <div> yet, because the next bunch of rules are grouped under this
				//we only want to end the <div> when a new group_id is set
				//or the div above

			} else {

				$return .= it_exchange_membership_addon_get_selections( $selection, $selected, $count, $post_types, $taxonomies );
				$return .= '<div class="it-exchange-content-access-content column col-6-12"><div class="it-exchange-membership-content-type-terms">';

				switch ( $selected ) {

					case 'posts':
						$rule_obj = new IT_Exchange_Membership_Content_Rule_Post( $selection, $rule );
						break;

					case 'post_types':
						$rule_obj = new IT_Exchange_Membership_Content_Rule_Post_Type( $rule );
						break;

					case 'taxonomy':
						$rule_obj = new IT_Exchange_Membership_Content_Rule_Term( $selection, $rule );
						break;
				}

				$return .= '<input type="hidden" value="' . $selected . '" name="it_exchange_content_access_rules[' . $count . '][selected]" />';

				if ( isset( $rule_obj ) ) {
					$return .= $rule_obj->get_field_html( "it_exchange_content_access_rules[$count]" );

					if ( $rule_obj->is_groupable() && false ) {
						$return .= '<div class="group-layout-options">';
						$return .= '<span class="group-layout active-group-layout" data-type="grid">grid</span><span class="group-layout"data-type="list">list</span>';
						$return .= '<input type="hidden" class="group-layout-input" name="it_exchange_content_access_rules[' . $count . '][group_layout]" value="grid" />';
						$return .= '</div>';
					}
				} else {

					$options = apply_filters( 'it_exchange_membership_addon_get_custom_selected_options', $options, $value, $selected );

					$return .= '<input type="hidden" value="' . $selected . '" name="it_exchange_content_access_rules[' . $count . '][selected]" />';
					$return .= '<select class="it-exchange-membership-content-type-term" name="it_exchange_content_access_rules[' . $count . '][term]">';
					$return .= $options;
					$return .= '</select>';
				}

				$return .= '</div></div>';

				if ( 'posts' === $selected ) {
					$drip_hidden    = '';
					$unavail_hidden = 'hidden';
				} else {
					$drip_hidden    = 'hidden';
					$unavail_hidden = '';
				}

				$return .= '<div class="it-exchange-content-access-delay column col-3-12 column-reduce-padding">';
				$return .= '<div class="it-exchange-membership-content-type-drip ' . $drip_hidden . '">';
				$return .= it_exchange_membership_addon_build_drip_rules( $rule, $count, $product_id );
				$return .= '</div>';
				$return .= '<div class="it-exchange-content-access-delay-unavailable ' . $unavail_hidden . '">';
				$return .= __( 'Available for single posts or pages', 'LION' );
				$return .= '</div>';
				$return .= '</div>';

				$return .= '<div class="it-exchange-membership-addon-remove-content-access-rule column col-3_4-12">';
				$return .= '<a href="#">×</a>';
				$return .= '</div>';

				$return .= '<input type="hidden" class="it-exchange-content-access-group" name="it_exchange_content_access_rules[' . $count . '][grouped_id]" value="' . $grouped_id . '" />';


				$return .= '</div>';
			}

			$current_group_id = isset( $rule['group_id'] ) ? $rule['group_id'] : false;

			if ( false !== $current_group_id && ! in_array( $current_group_id, $groupings ) ) {
				$groupings[] = $current_group_id;
			}

			if ( false !== $current_group_id && $group_count >= $current_group_id ) {
				$group_count = $rule['group_id'] + 1;
			}

			$count ++;

		}

		if ( ! empty( $groupings ) ) {
			foreach ( $groupings as $group ) {
				$return .= '</div></div>'; //this is ending the divs from the group opening in it_exchange_membership_addon_build_content_rule()
			}
		}

	}

	$return .= '</div>';

	$return .= '<script type="text/javascript" charset="utf-8">';
	$return .= '	var it_exchange_membership_addon_content_access_iteration = ' . $count . ';';
	$return .= '	var it_exchange_membership_addon_content_access_group_iteration = ' . $group_count . ';';
	$return .= '</script>';

	return $return;
}

/**
 * Builds the actual drip rule HTML
 *
 * @since 1.0.0
 *
 * @param array    $rule       A Memberships rule
 * @param int      $count      current row count, used for JavaScript/AJAX
 * @param int|bool $product_id Memberhip's product ID
 *
 * @return string HTML output of drip rule div
 */
function it_exchange_membership_addon_build_drip_rules( $rule = array(), $count, $product_id = false ) {

	$return = '';

	if ( ! empty( $product_id ) && ! empty( $rule['selected'] ) && 'posts' === $rule['selected'] && ! empty( $rule['term'] ) ) {
		$drip_interval = get_post_meta( $rule['term'], '_item-content-rule-drip-interval-' . $product_id, true );
	} else {
		$drip_interval = 0;
	}

	if ( 0 < $drip_interval ) {
		$drip_duration = get_post_meta( $rule['term'], '_item-content-rule-drip-duration-' . $product_id, true );
		$drip_duration = ! empty( $drip_duration ) ? $drip_duration : 'days';
	} else {
		$drip_interval = 0;
		$drip_duration = 'days';
	}

	$return .= '<input type="number" min="0" value="' . $drip_interval . '" name="it_exchange_content_access_rules[' . $count . '][drip-interval]" />';
	$return .= '<select class="it-exchange-membership-content-drip-duration" name="it_exchange_content_access_rules[' . $count . '][drip-duration]">';
	$durations = array(
		'days'   => __( 'Days', 'LION' ),
		'weeks'  => __( 'Weeks', 'LION' ),
		'months' => __( 'Months', 'LION' ),
		'years'  => __( 'Years', 'LION' ),
	);
	$durations = apply_filters( 'it-exchange-membership-drip-durations', $durations );
	foreach ( $durations as $key => $string ) {
		$return .= '<option value="' . $key . '"' . selected( $key, $drip_duration, false ) . '>' . $string . '</option>';
	}
	$return .= '</select>';

	return $return;
}

/**
 * Builds the actual restriction rule HTML, used for non-iThemes Exchange post types
 *
 * @since 1.0.0
 *
 * @param int $post_id WordPress $post ID
 *
 * @return string HTML output of drip rule div
 */
function it_exchange_membership_addon_build_post_restriction_rules( $post_id ) {

	$return = '';
	$post   = get_post( $post_id );

	$exemptions = get_post_meta( $post_id, '_item-content-rule-exemptions', true );

	$return .= '<div class="it-exchange-membership-restrictions">';

	$factory     = new IT_Exchange_Membership_Rule_Factory();
	$memberships = $factory->make_all_for_post( $post );

	if ( empty( $memberships ) ) {
		$return .= '<div class="it-exchange-membership-no-restrictions">' . __( 'No membership restrictions for this content.', 'LION' ) . '</div>';
	} else {

		ob_start();

		foreach ( $memberships as $membership => $membership_rules ) {
			?>
			<div class="it-exchange-membership-restriction-group">
				<input type="hidden" name="it_exchange_membership_id" value="<?php echo $membership; ?>">

				<?php foreach ( $membership_rules as $rule ): /** @var $rule IT_Exchange_Membership_Content_RuleInterface */ ?>

					<?php $parents = it_exchange_membership_addon_get_all_the_parents( $membership ); ?>
					<?php $exemption = ! empty( $exemptions[ $membership ] ) ? $exemptions[ $membership ] : array(); ?>
					<?php $type = $rule->get_type(); ?>

					<div class="it-exchange-membership-rule it-exchange-membership-rule-<?php echo $type; ?>">

						<label class="screen-reader-text" for="it-exchange-restriction-exemption-<?php echo $membership . $type; ?>">
							<?php _e( 'Is this content exempt from the normal restriction rule.', 'LION' ); ?>
						</label>
						<input id="it-exchange-restriction-exemption-<?php echo $membership . $type; ?>" class="it-exchange-restriction-exemptions"
						       type="checkbox" name="restriction-exemptions[]" value="<?php echo $type; ?>" <?php checked( in_array( $type, $exemption ), false ); ?>>

						<?php echo get_the_title( $membership ); ?>

						<?php if ( ! empty( $parents ) ) : ?>
							<p class="description"><?php printf( __( 'Included in: %s', 'LION' ), join( ', ', array_map( 'get_the_title', $parents ) ) ); ?></p>
						<?php endif; ?>

						<span class="it-exchange-membership-remove-rule">&times;</span>

						<div class="it-exchange-membership-rule-description"><?php echo $rule->get_short_description(); ?></div>

						<?php foreach ( $rule->get_delay_rules() as $delay_rule ): ?>
							<div class="it-exchange-membership-rule-delay"><?php _e( 'Delay', 'LION' ); ?></div>
							<div class="it-exchange-membership-<?php echo $delay_rule->get_type(); ?>-rule">
								<?php echo $delay_rule->get_field_html( $membership ); ?>
							</div>
						<?php endforeach; ?>

					</div>

				<?php endforeach; ?>
			</div>
			<?php
		}

		$return .= ob_get_clean();
	}

	$return .= '</div>';

	return $return;

}

/**
 * Get all content rules.
 *
 * @since 1.18
 *
 * @param bool $flat Whether to return a flat array, or an array segmented by type.
 *
 * @return IT_Exchange_Membership_Content_RuleInterface[]|array[]
 */
function it_exchange_membership_addon_get_content_rules( $flat = true ) {

	$rules = array();

	$hidden     = apply_filters( 'it_exchange_membership_addon_hidden_post_types', array( 'attachment' ) );
	$post_types = array_diff( get_post_types( array( 'public' => true ) ), $hidden );

	foreach ( $post_types as $post_type ) {
		$rule = new IT_Exchange_Membership_Content_Rule_Post( $post_type );

		if ( $flat ) {
			$rules[] = $rule;
		} else {
			$rules[ $rule->get_type() ][] = $rule;
		}
	}

	$cpt = new IT_Exchange_Membership_Content_Rule_Post_Type();

	if ( $flat ) {
		$rules[] = $cpt;
	} else {
		$rules[ $cpt->get_type() ][] = $cpt;
	}
	$hidden     = apply_filters( 'it_exchange_membership_addon_hidden_taxonomies', array( 'post_format' ) );
	$taxonomies = array_diff( get_taxonomies( array( 'public' => true ) ), $hidden );

	foreach ( $taxonomies as $taxonomy ) {
		$rule = new IT_Exchange_Membership_Content_Rule_Term( $taxonomy );

		if ( $flat ) {
			$rules[] = $rule;
		} else {
			$rules[ $rule->get_type() ][] = $rule;
		}
	}

	return $rules;
}

/**
 * Gets the membership currently being viewed from the membership dashboard on the
 * WordPress frontend
 *
 * @since 1.0.0
 *
 * @return mixed object|bool
 */
function it_exchange_membership_addon_get_current_membership() {
	$page_slug = it_exchange_get_page_slug( 'memberships', true );
	if ( $membership_slug = get_query_var( $page_slug ) ) {
		if ( 'itememberships' === $membership_slug ) {
			return 'itememberships';
		} else {
			$args  = array(
				'name'        => $membership_slug,
				'post_type'   => 'it_exchange_prod',
				'post_status' => 'publish',
				'numberposts' => 1
			);
			$posts = get_posts( $args );
			foreach ( $posts as $post ) { //should only be one
				return it_exchange_get_product( $post );
			}
		}
	}

	return false;
}

/**
 * Returns membership access rules sorted by selected type
 *
 * @since 1.0.0
 *
 * @param int  $membership_product_id
 * @param bool $exclude_exempted (optional) argument to exclude exemptions from access rules (true by default)
 *
 * @return array
 */
function it_exchange_membership_access_rules_sorted_by_selected_type( $membership_product_id, $exclude_exempted = true ) {
	$access_rules        = it_exchange_get_product_feature( $membership_product_id, 'membership-content-access-rules' );
	$sorted_access_rules = array();

	foreach ( $access_rules as $rule ) {
		if ( $exclude_exempted && 'posts' === $rule['selected'] ) {
			$restriction_exemptions = get_post_meta( $rule['term'], '_item-content-rule-exemptions', true );
			if ( ! empty( $restriction_exemptions ) ) {
				if ( array_key_exists( $membership_product_id, $restriction_exemptions ) ) {
					continue;
				}
			}
		}
		$sorted_access_rules[ $rule['selected'] ][] = array(
			'type' => $rule['selection'],
			'term' => $rule['term'],
		);
	}

	return $sorted_access_rules;
}

/**
 * Returns true if product in cart is a membership product
 *
 * @since 1.0.0
 *
 * @param object|bool $cart_products
 *
 * @return bool
 */
function it_exchange_membership_cart_contains_membership_product( $cart_products = false ) {
	if ( ! $cart_products ) {
		$cart_products = it_exchange_get_cart_products();
	}

	foreach ( $cart_products as $product ) {
		if ( ! empty( $product['product_id'] ) ) {
			if ( 'membership-product-type' === it_exchange_get_product_type( $product['product_id'] ) ) {
				return true;
			}
		}
	}

	return false;
}

/**
 * For hierarchical membership types
 * Finds all the most-parental membership types in the member_access session
 * Used generally to prevent duplicate content from being printed
 * in the member's dashboard
 *
 * @since 1.2.0
 *
 * @param array $membership_products current list of accessible membership products
 *
 * @return array
 */
function it_exchange_membership_addon_setup_most_parent_member_access_array( $membership_products ) {
	$found_ids  = array();
	$parent_ids = array();
	foreach ( $membership_products as $product_id => $txn_id ) {
		if ( false !== get_post_status( $product_id ) ) {
			if ( false !== $found_id = it_exchange_membership_addon_get_most_parent_from_member_access( $product_id, $membership_products ) ) {
				if ( ! in_array( $found_id, $found_ids ) ) {
					$found_ids[] = $found_id;
				}
			}
		}
	}
	foreach ( $found_ids as $found_id ) {
		$txn_keys = array_keys( $membership_products, $found_id );
		if ( ! empty( $txn_keys ) ) {
			$txn_id = array_shift( $txn_keys );
		}
		if ( ! empty( $txn_id ) ) {
			$parent_ids[ $found_id ] = $txn_id;
		}
	}

	return $parent_ids;
}

/**
 * For hierarchical membership types
 * Get all child membership products and adds it to an array to be used
 * for generating the member_access session
 *
 * @since 1.2.0
 *
 * @param array    $membership_products current list of accessible membership products
 * @param array    $product_ids
 * @param int|bool $parent_txn_id
 *
 * @return array
 */
function it_exchange_membership_addon_setup_recursive_member_access_array( $membership_products, $product_ids = array(), $parent_txn_id = false ) {
	if ( ! empty( $membership_products ) ) {
		foreach ( $membership_products as $product_id => $txn_id ) {
			if ( false !== get_post_status( $product_id ) ) {
				if ( array_key_exists( $product_id, $product_ids ) ) {
					continue;
				}

				if ( ! $parent_txn_id ) {
					$proper_txn_id = $txn_id;
				} else {
					$proper_txn_id = $parent_txn_id;
				}

				$product_ids[ $product_id ] = $proper_txn_id;
				if ( $child_ids = get_post_meta( $product_id, '_it-exchange-membership-child-id' ) ) {
					$child_ids   = array_flip( $child_ids ); //we need the child IDs to be the keys
					$product_ids = it_exchange_membership_addon_setup_recursive_member_access_array( $child_ids, $product_ids, $proper_txn_id );
				}
			}
		}
	}

	return $product_ids;
}

/**
 * Gets the highest level parent from the parent access session for a given product ID
 *
 * @since 1.2.0
 *
 * @param int   $product_id    Membership product to check
 * @param array $parent_access Parent access session (or other array)
 *
 * @return array
 */
function it_exchange_membership_addon_get_most_parent_from_member_access( $product_id, $parent_access ) {
	$most_parent = false;
	if ( $childs_parent_ids = get_post_meta( $product_id, '_it-exchange-membership-parent-id' ) ) {
		foreach ( $childs_parent_ids as $parent_id ) {
			if ( false !== get_post_status( $parent_id ) ) {
				if ( array_key_exists( $parent_id, $parent_access ) ) {
					$most_parent = $parent_id;
				} //potentially the most parent, but we need to keep checking!

				if ( false !== $found_id = it_exchange_membership_addon_get_most_parent_from_member_access( $parent_id, $parent_access ) ) {
					$most_parent = $found_id;
				}
			}
		}
	}
	if ( ! $most_parent && array_key_exists( $product_id, $parent_access ) ) {
		$most_parent = $product_id;
	}

	return $most_parent;
}

/**
 * For hierarchical membership types
 * Prints or returns an HTML formatted list of memberships and their children
 *
 * @since 1.2.0
 *
 * @param array $product_ids Parent IDs of membership products
 * @param array $args        array of arguments for the function
 *
 * @return string|null
 */
function it_exchange_membership_addon_display_membership_hierarchy( $product_ids, $args = array() ) {
	$defaults = array(
		'echo'         => true,
		'delete'       => true,
		'hidden_input' => true,
	);
	$args     = wp_parse_args( $args, $defaults );
	extract( $args );

	$output = '';
	foreach ( $product_ids as $product_id ) {
		if ( false !== get_post_status( $product_id ) ) {
			$output .= '<ul>';
			$output .= '<li data-child-id="' . $product_id . '"><div class="inner-wrapper">' . get_the_title( $product_id );

			if ( $delete ) {
				$output .= ' <a href data-membership-id="' . $product_id . '" class="it-exchange-membership-addon-delete-membership-child it-exchange-remove-item">&times;</a>';
			}

			if ( $hidden_input ) {
				$output .= ' <input type="hidden" name="it-exchange-membership-child-ids[]" value="' . $product_id . '" />';
			}

			$output .= '</div>';

			if ( $child_ids = get_post_meta( $product_id, '_it-exchange-membership-child-id' ) ) {
				$output .= it_exchange_membership_addon_display_membership_hierarchy( $child_ids, array(
					'echo'         => false,
					'delete'       => false,
					'hidden_input' => false
				) );
			}

			$output .= '</li>';
			$output .= '</ul>';
		}
	}

	if ( $echo ) {
		echo $output;
	} else {
		return $output;
	}
}

/**
 * For hierarchical membership types
 * Returns an array of all the product's parents
 *
 * @since 1.2.0
 *
 * @param int   $membership_id product ID of membership
 * @param array $parent_ids    array of of current parent_ids
 *
 * @return array|bool
 */
function it_exchange_membership_addon_get_all_the_parents( $membership_id, $parent_ids = array() ) {
	$parents = it_exchange_get_product_feature( $membership_id, 'membership-hierarchy', array( 'setting' => 'parents' ) );
	if ( ! empty( $parents ) ) {
		foreach ( $parents as $parent_id ) {
			if ( false !== get_post_status( $parent_id ) ) {
				$parent_ids[] = $parent_id;
				if ( false !== $results = it_exchange_membership_addon_get_all_the_parents( $parent_id ) ) {
					$parent_ids = array_merge( $parent_ids, $results );
				}
			}
		}
	} else {
		return false;
	}

	return $parent_ids;
}

/**
 * For hierarchical membership types
 * Returns an array of all the product's children
 *
 * @since 1.2.16
 *
 * @param int   $membership_id product ID of membership
 * @param array $child_ids     array of of current child_ids
 *
 * @return array|bool
 */
function it_exchange_membership_addon_get_all_the_children( $membership_id, $child_ids = array() ) {
	$children = it_exchange_get_product_feature( $membership_id, 'membership-hierarchy', array( 'setting' => 'children' ) );
	if ( ! empty( $children ) ) {
		foreach ( $children as $child_id ) {
			if ( false !== get_post_status( $child_id ) ) {
				$child_ids[] = $child_id;
				if ( false !== $results = it_exchange_membership_addon_get_all_the_children( $child_id ) ) {
					$child_ids = array_merge( $child_ids, $results );
				}
			}
		}
	} else {
		return false;
	}

	return $child_ids;
}