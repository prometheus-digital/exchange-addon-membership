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

			$delayable = $rule instanceof IT_Exchange_Membership_Content_Rule_Delayable ? 'yes' : 'no';
			$selected  = selected( $rule->get_selection(), $selection, false );

			$return .= "<option value='{$rule->get_selection()}' data-type='{$rule->get_type()}' data-delayable='$delayable' $selected>";
			$return .= $rule->get_selection( true );
			$return .= "</option>";
		}

		if ( count( $rules ) > 1 ) {
			$return .= '</optgroup>';
		}
	}

	if ( $other ) {

		$return .= '<optgroup label="' . __( 'Other', 'LION' ) . '">';

		foreach ( $other as $rule ) {

			$delayable = $rule instanceof IT_Exchange_Membership_Content_Rule_Delayable ? 'yes' : 'no';
			$selected  = selected( $rule->get_selection( false ), $selection, false );

			$return .= "<option value='{$rule->get_selection(false)}' data-type='{$rule->get_type()}' data-delayable='$delayable' $selected>";
			$return .= $rule->get_selection( true );
			$return .= "</option>";
		}

		$return .= '</optgroup>';
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
	$rules      = ! is_array( $rules ) ? array() : $rules;
	$membership = it_exchange_get_product( $product_id );

	$factory  = new IT_Exchange_Membership_Rule_Factory();
	$renderer = new IT_Exchange_Membership_Admin_Rule_Renderer( $rules, $membership, $factory );

	$return = '<div class="it-exchange-membership-addon-content-access-rules content-access-sortable">';
	$return .= $renderer->render();
	$return .= '</div>';

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

	$return .= '<div class="it-exchange-membership-restrictions">';

	$factory = new IT_Exchange_Membership_Rule_Factory();
	$rules   = $factory->make_all_for_post( $post );

	if ( empty( $rules ) ) {
		$return .= '<div class="it-exchange-membership-no-restrictions">' . __( 'No membership restrictions for this content.', 'LION' ) . '</div>';
		$return .= '</div>';

		return $return;
	}

	$df = get_option( 'date_format' );
	$df = it_exchange_php_date_format_to_jquery_datepicker_format( $df );

	$return .= '<input type="hidden" id="it_exchange_membership_df" value="' . $df . '">';

	ob_start();

	foreach ( $rules as $rule ) {
		?>
		<div class="it-exchange-membership-restriction-group">
			<input type="hidden" name="it_exchange_membership_id" value="<?php echo $rule->get_membership()->ID; ?>">
			<input type="hidden" name="it_exchange_rule_id" value="<?php echo $rule->get_rule_id() ?>">

			<?php
			$membership_id = $rule->get_membership()->ID;
			$parents       = it_exchange_membership_addon_get_all_the_parents( $membership_id );
			$type          = $rule->get_type();
			$exempt        = $rule->is_post_exempt( $post );
			?>

			<div class="it-exchange-membership-rule it-exchange-membership-rule-<?php echo $type; ?>">

				<label class="screen-reader-text" for="it-exchange-restriction-exemption-<?php echo $membership_id . $type; ?>">
					<?php _e( 'Is this content exempt from the normal restriction rule.', 'LION' ); ?>
				</label>
				<input id="it-exchange-restriction-exemption-<?php echo $membership_id . $type; ?>" class="it-exchange-restriction-exemptions"
				       type="checkbox" name="restriction-exemptions[]" value="<?php echo $type; ?>" data-term="<?php echo $rule->get_term(); ?>"
				       data-selection="<?php echo $rule->get_selection(); ?>" <?php checked( ! $exempt ); ?>>

				<?php echo get_the_title( $membership_id ); ?>

				<?php if ( ! empty( $parents ) ) : ?>
					<p class="description"><?php printf( __( 'Included in: %s', 'LION' ), join( ', ', array_map( 'get_the_title', $parents ) ) ); ?></p>
				<?php endif; ?>

				<span class="it-exchange-membership-remove-rule">&times;</span>

				<div class="it-exchange-membership-rule-description"><?php echo $rule->get_short_description(); ?></div>

				<?php if ( $rule instanceof IT_Exchange_Membership_Content_Rule_Delayable && $rule->get_delay_rule() ): ?>
					<?php $delay_rule = $rule->get_delay_rule(); ?>

					<div class="it-exchange-membership-rule-delay"><?php echo $delay_rule->get_type( true ); ?></div>
					<div class="it-exchange-membership-<?php echo $delay_rule->get_type(); ?>-rule it-exchange-membership-delay-rule" data-type="<?php echo $delay_rule->get_type(); ?>">
						<?php echo $delay_rule->get_field_html( "[$membership_id][delay]" ); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	$return .= ob_get_clean();

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

	/**
	 * Filters the available content rules.
	 *
	 * @since 1.18
	 *
	 * @param IT_Exchange_Membership_Content_RuleInterface[]|array[] $rules
	 * @param bool                                                   $flat
	 */
	$rules = apply_filters( 'it_exchange_membership_addon_get_content_rules', $rules, $flat );

	return $rules;
}

/**
 * Get all possible delay rules.
 *
 * @since 1.18
 *
 * @param IT_Exchange_Membership_Content_Rule_Delayable|null $rule
 * @param IT_Exchange_Membership|null                        $membership
 *
 * @return IT_Exchange_Membership_Delay_RuleInterface[]
 */
function it_exchange_membership_addon_get_delay_rules( IT_Exchange_Membership_Content_Rule_Delayable $rule = null, IT_Exchange_Membership $membership = null ) {

	$rules = array();

	$rules[] = new IT_Exchange_Membership_Delay_Rule_Drip( $rule, $membership );
	$rules[] = new IT_Exchange_Membership_Delay_Rule_Date( $rule, $membership );

	/**
	 * Filter the available delay rules.
	 *
	 * @since 1.18
	 *
	 * @param IT_Exchange_Membership_Delay_RuleInterface[]  $rules
	 * @param IT_Exchange_Membership_Content_Rule_Delayable $post
	 * @param IT_Exchange_Membership                        $membership
	 */
	$rules = apply_filters( 'it_exchange_membership_addon_get_delay_rules', $rules, $rule, $membership );

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

/**
 * Returns Membership content restricted template part
 *
 * @since 1.0.0
 * @since 1.18 Add $failed parameter.
 *
 * @param IT_Exchange_Membership_RuleInterface[] $failed
 *
 * @return string
 */
function it_exchange_membership_addon_content_restricted_template( array $failed = array() ) {

	it_exchange_set_global( 'membership_failed_rules', $failed );

	$GLOBALS['wp_query']->is_single = false; //false -- so comments_template() doesn't add comments
	$GLOBALS['wp_query']->is_page   = false;   //false -- so comments_template() doesn't add comments
	ob_start();
	it_exchange_get_template_part( 'content', 'restricted' );

	it_exchange_set_global( 'membership_failed_rules', null );

	return ob_get_clean();
}

/**
 * Returns Membership excerpt restricted template part
 *
 * @since 1.0.0
 * @since 1.18 Add $failed parameter.
 *
 * @param IT_Exchange_Membership_RuleInterface[] $failed
 *
 * @return string
 */
function it_exchange_membership_addon_excerpt_restricted_template( array $failed = array() ) {

	it_exchange_set_global( 'membership_failed_rules', $failed );

	$GLOBALS['wp_query']->is_single = false; //false -- so comments_template() doesn't add comments
	$GLOBALS['wp_query']->is_page   = false;   //false -- so comments_template() doesn't add comments
	ob_start();
	it_exchange_get_template_part( 'excerpt', 'restricted' );

	it_exchange_set_global( 'membership_failed_rules', null );

	return ob_get_clean();
}

/**
 * Returns Membership content dripped template part
 *
 * @since 1.0.0
 * @since 1.18 Add $failed parameter.
 *
 * @param IT_Exchange_Membership_RuleInterface[] $failed
 *
 * @return string
 */
function it_exchange_membership_addon_content_dripped_template( array $failed = array() ) {

	it_exchange_set_global( 'membership_failed_delay', $failed );

	$GLOBALS['wp_query']->is_single = false; //false -- so comments_template() doesn't add comments
	$GLOBALS['wp_query']->is_page   = false;   //false -- so comments_template() doesn't add comments
	ob_start();
	it_exchange_get_template_part( 'content', 'dripped' );

	it_exchange_set_global( 'membership_failed_delay', null );

	return ob_get_clean();
}

/**
 * Returns Membership excerpt dripped template part
 *
 * @since 1.0.0
 * @since 1.18 Add $failed parameter.
 *
 * @param IT_Exchange_Membership_RuleInterface[] $failed
 *
 * @return string
 */
function it_exchange_membership_addon_excerpt_dripped_template( array $failed = array() ) {

	it_exchange_set_global( 'membership_failed_delay', $failed );

	$GLOBALS['wp_query']->is_single = false; //false -- so comments_template() doesn't add comments
	$GLOBALS['wp_query']->is_page   = false;   //false -- so comments_template() doesn't add comments
	ob_start();
	it_exchange_get_template_part( 'excerpt', 'dripped' );

	it_exchange_set_global( 'membership_failed_delay', null );

	return ob_get_clean();
}