<?php
/**
 * iThemes Exchange Membership Add-on
 * @package IT_Exchange_Addon_Membership
 * @since 1.0.0
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
 * @param int $selection current selection (if it exists)
 * @param string $selection_type current selection type (if it exists)
 * @param int $count current row count, used for JavaScript/AJAX
 * @return string HTML output of selections row div
*/
function it_exchange_membership_addon_get_selections( $selection = 0, $selection_type = NULL, $count ) {
	
	$return  = '<div class="it-exchange-content-access-type column"><select class="it-exchange-membership-content-type-selections" name="it_exchange_content_access_rules[' . $count . '][selection]">';
	$return .= '<option value="">' . __( 'Select Content', 'LION' ) . '</option>';
	
	//Posts
	$hidden_post_types = apply_filters( 'it_exchange_membership_addon_hidden_post_types', array( 'attachment', 'revision', 'nav_menu_item', 'it_exchange_tran', 'it_exchange_coupon', 'it_exchange_prod', 'it_exchange_download' ) );
	$post_types = get_post_types( array(), 'objects' );
	
	foreach ( $post_types as $post_type ) {
		if ( in_array( $post_type->name, $hidden_post_types ) ) 
			continue;
			
		if ( 'posts' === $selection_type && $post_type->name === $selection )
			$selected = 'selected="selected"';
		else
			$selected = '';
			
		$return .= '<option data-type="posts" value="' . $post_type->name . '" ' . $selected . '>' . $post_type->label . '</option>';	
	}
	
	//Post Types
	if ( 'post_types' === $selection_type && 'post_type' === $selection )
		$selected = 'selected="selected"';
	else
		$selected = '';
		
	$return .= '<option data-type="post_types" value="post_type" ' . $selected . '>' . __( 'Post Types', 'LION' ) . '</option>';
	
	//Taxonomies
	$taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );
	foreach ( $taxonomies as $tax ) {
		// we want to skip post format taxonomies, not really needed here
		if ( 'post_format' === $tax->name )
			continue;
			
		if ( 'taxonomy' === $selection_type && $tax->name === $selection )
			$selected = 'selected="selected"';
		else
			$selected = '';
			
		$return .= '<option data-type="taxonomy" value="' . $tax->name . '" ' . $selected . '>' . $tax->label . '</option>';	
	}	
	$return .= '</select></div>';
	
	return $return;
}

/**
 * Builds the actual content rule HTML
 *
 * @since 1.0.0
 *
 * @param array $rule A Memberships rule
 * @param int $count current row count, used for JavaScript/AJAX
 * @param int $product_id Memberhip's product ID
 * @return string HTML output of selections row div
*/
function it_exchange_membership_addon_build_content_rule( $rule, $count, $product_id ) {

	$options = '';
	
	$selection = !empty( $rule['selection'] ) ? $rule['selection'] : false;
	$selected  = !empty( $rule['selected'] ) ? $rule['selected'] : false;
	$value     = !empty( $rule['term'] ) ? $rule['term'] : false;

	$return  = '<div class="it-exchange-membership-content-access-rule columns-wrapper" data-count="' . $count . '">';
	
	$return .= '<div class="it-exchange-membership-addon-sort-content-access-rule column col-1_4-12"></div>';
	
	$return .= it_exchange_membership_addon_get_selections( $selection, $selected, $count );
	$return .= '<div class="it-exchange-content-access-content column col-6-12"><div class="it-exchange-membership-content-type-terms">';
	switch( $selected ) {
		
		case 'posts':
			$posts = get_posts( array( 'post_type' => $selection, 'posts_per_page' => -1 ) );
			foreach ( $posts as $post ) {
				$options .= '<option value="' . $post->ID . '" ' . selected( $post->ID, $value, false ) . '>' . get_the_title( $post->ID ) . '</option>';	
			}
			break;
		
		case 'post_types':
			$hidden_post_types = apply_filters( 'it_exchange_membership_addon_hidden_post_types', array( 'attachment', 'revision', 'nav_menu_item', 'it_exchange_tran', 'it_exchange_coupon', 'it_exchange_prod', 'it_exchange_download', 'page' ) );
			$post_types = get_post_types( array(), 'objects' );
			foreach ( $post_types as $post_type ) {
				if ( in_array( $post_type->name, $hidden_post_types ) ) 
					continue;
					
				$options .= '<option value="' . $post_type->name . '" ' . selected( $post_type->name, $value, false ) . '>' . $post_type->label . '</option>';	
			}
			break;
		
		case 'taxonomy':
			$terms = get_terms( $selection, array( 'hide_empty' => false ) );
			foreach ( $terms as $term ) {
				$options .= '<option value="' . $term->term_id . '"' . selected( $term->term_id, $value, false ) . '>' . $term->name . '</option>';	
			}
			break;
		
	}

	$return .= '<input type="hidden" value="' . $selected . '" name="it_exchange_content_access_rules[' . $count . '][selected]" />';
	$return .= '<select class="it-exchange-membership-content-type-term" name="it_exchange_content_access_rules[' . $count . '][term]">';
	$return .= $options;
	$return .= '</select>';
	$return .= '</div></div>';
	
	$return .= '<div class="it-exchange-content-access-delay column col-3-12 column-reduce-padding"><div class="it-exchange-membership-content-type-drip">';
	if ( 'posts' === $selected ) {
		$return .= it_exchange_membership_addon_build_drip_rules( $rule, $count, $product_id );
	}
	$return .= '</div></div>';
	
	$return .= '<div class="it-exchange-membership-addon-remove-content-access-rule column col-3_4-12">';
	$return .= '<a href="#">×</a>';
	$return .= '</div>';
	
	$return .= '</div>';
	
	return $return;
	
}

/**
 * Builds the actual drip rule HTML
 *
 * @since 1.0.0
 *
 * @param array $rule A Memberships rule
 * @param int $count current row count, used for JavaScript/AJAX
 * @param int $product_id Memberhip's product ID
 * @return string HTML output of drip rule div
*/
function it_exchange_membership_addon_build_drip_rules( $rule = false, $count, $product_id = false ) {
	
	$return = '';

	if ( !empty( $product_id ) && !empty( $rule['selected'] ) && 'posts' === $rule['selected'] && !empty( $rule['term'] ) )
		$drip_interval = get_post_meta( $rule['term'], '_item-content-rule-drip-interval-' . $product_id, true );
	else
		$drip_interval = 0;
		
	if ( 0 < $drip_interval ) {
		$drip_duration = get_post_meta( $rule['term'], '_item-content-rule-drip-duration-' . $product_id, true );
		$drip_duration = !empty( $drip_duration ) ? $drip_duration : 'days';
	} else {
		$drip_duration = 'days';
	}
	
	$return  .= '<input type="number" min="0" value="' . $drip_interval . '" name="it_exchange_content_access_rules[' . $count . '][drip-interval]" />';
	$return .= '<select class="it-exchange-membership-content-drip-duration" name="it_exchange_content_access_rules[' . $count . '][drip-duration]">';
	$durations = array(
		'days'   => __( 'Days', 'LION' ),
		'weeks'  => __( 'Weeks', 'LION' ),
		'months' => __( 'Months', 'LION' ),
		'years'  => __( 'Years', 'LION' ),
	);
	$durations = apply_filters( 'it-exchange-membership-drip-durations', $durations );
	foreach( $durations as $key => $string ) {
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
 * @return string HTML output of drip rule div
*/
function it_exchange_membership_addon_build_post_restriction_rules( $post_id ) {
	
	$return = '';
	$rules = array();
	
	$post_type = get_post_type( $post_id );
	
	/*
	* Use get_post_meta() to retrieve an existing value
	* from the database and use the value for the form.
	*/
	$post_rules = get_post_meta( $post_id, '_item-content-rule', true );
	$post_type_rules = get_option( '_item-content-rule-post-type-' . $post_type, array() );
	$taxonomy_rules = array();
	$restriction_exemptions = get_post_meta( $post_id, '_item-content-rule-exemptions', true );
	
	$taxonomies = get_object_taxonomies( $post_type );
	$terms = wp_get_object_terms( $post_id, $taxonomies );
	foreach( $terms as $term ) {
		$term_rules = get_option( '_item-content-rule-tax-' . $term->taxonomy . '-' . $term->term_id, array() );
		if ( !empty( $term_rules ) )
			$taxonomy_rules[$term->taxonomy][$term->term_id]  = array_merge( $taxonomy_rules, $term_rules );
	}
		
	//Re-order for output!
	if ( !empty( $post_rules ) ) {
		foreach( $post_rules as $product_id ) {
			$rules[$product_id]['post'] = true;
		}
	}
	if ( !empty( $post_type_rules ) ) {
		foreach( $post_type_rules as $product_id ) {
			$post_type = get_post_type_object( $post_type );
			if ( !empty( $post_type->labels->singular_name ) )
				$name = $post_type->labels->singular_name;
			else if ( !empty( $post_type->labels->name ) )
				$name = $post_type->labels->name;
			else
				$name = $post_type->label;
			$rules[$product_id]['post_type'] = $post_type->labels->singular_name;
		}
	}
	if ( !empty( $taxonomy_rules ) ) {
		foreach( $taxonomy_rules as $taxonomy => $term_rules ) {
			foreach( $term_rules as $term_id => $product_ids ) {
				foreach( $product_ids as $product_id ) {
					$rules[$product_id]['taxonomy'][] = $taxonomy;
					$rules[$product_id][$taxonomy]['term_ids'][] = $term_id;
				}
			}
		}
	}	
	
	$return .= '<div class="it-exchange-membership-restrictions">';
	
	if ( !empty( $rules ) ) {
			
		foreach ( $rules as $membership_id => $rule ) {
			$return .= '<div class="it-exchange-membership-restriction-group">';
			$title = get_the_title( $membership_id );
			$restriction_exception = !empty( $restriction_exemptions[$membership_id] ) ? $restriction_exemptions[$membership_id] : array();
			
			$return .= '<input type="hidden" name="it_exchange_membership_id" value="' . $membership_id . '">';
			
			if ( !empty( $rule['post'] ) && true === $rule['post'] ) {
				$return .= '<div class="it-exchange-membership-rule-post it-exchange-membership-rule">';
				$return .= '<input class="it-exchange-restriction-exceptions" type="checkbox" name="restriction-exceptions[]" value="post" ' . checked( in_array( 'post', $restriction_exception ), false, false ) . '>';
				$return .= $title;
				$return .= '<span class="it-exchange-membership-remove-rule">&times;</span>';
				
				$drip_interval = get_post_meta( $post_id, '_item-content-rule-drip-interval-' . $membership_id, true );				
				
				if ( 0 < $drip_interval ) {
					$drip_duration = get_post_meta( $post_id, '_item-content-rule-drip-duration-' . $membership_id, true );
					$drip_duration = !empty( $drip_duration ) ? $drip_duration : 'days';
					
					if ( !empty( $drip_interval ) && !empty( $drip_duration ) ) {
					
						$return .= '<div class="it-exchange-membership-rule-delay">' . __( 'Delay', 'LION' ) . '</div>';
						$return .= '<div class="it-exchange-membership-drip-rule">';
						$return .= '<input class="it-exchange-membership-drip-rule-interval" type="number" min="0" value="' . $drip_interval . '" name="it_exchange_membership_drip_interval" />';
						$return .= '<select class="it-exchange-membership-drip-rule-duration" name="it_exchange_membership_drip_duration">';
						$durations = array(
							'days'   => __( 'Days', 'LION' ),
							'weeks'  => __( 'Weeks', 'LION' ),
							'months' => __( 'Months', 'LION' ),
							'years'  => __( 'Years', 'LION' ),
						);
						$durations = apply_filters( 'it-exchange-membership-drip-durations', $durations );
						foreach( $durations as $key => $string ) {
							$return .= '<option value="' . $key . '"' . selected( $key, $drip_duration, false ) . '>' . $string . '</option>';
						}
						$return .= '</select>';
						$return .= '</div>';
					
					}

				}
				
				$return .= '</div>';
			}
			
			if ( !empty( $rule['post_type'] ) ) {
				$return .= '<div class="it-exchange-membership-rule-post-type it-exchange-membership-rule">';
				$return .= '<input class="it-exchange-restriction-exceptions" type="checkbox" name="restriction-exceptions[]" value="posttype" ' . checked( in_array( 'posttype', $restriction_exception ), false, false ) . '>';
				$return .= $title;
				$return .= '<div class="it-exchange-membership-rule-description">' . $rule['post_type'] . '</div>';
				$return .= '</div>';
			}
			
			if ( !empty( $rule['taxonomy'] ) ) {
				foreach ( $rule['taxonomy'] as $taxonomy ) {
					foreach( $rules[$product_id][$taxonomy]['term_ids'] as $term_id ) {
						$term = get_term_by( 'id', $term_id, $taxonomy );
						$return .= '<div class="it-exchange-membership-rule-post-type it-exchange-membership-rule">';
						$return .= '<input class="it-exchange-restriction-exceptions" type="checkbox" name="restriction-exceptions[]" value="taxonomy|' . $taxonomy . '|' . $term_id . '" ' . checked( in_array( 'taxonomy|' . $taxonomy . '|' . $term_id, $restriction_exception ), false, false ) . '>';
						$return .= $title;
						$return .= '<div class="it-exchange-membership-rule-description">' . ucwords( $taxonomy ) . ' "' .  $term->name . '"</div>';
						$return .= '</div>';
					}
				}
			}
			$return .= '</div>';
		}
	
	} else {
	
		$return .= '<div class="it-exchange-membership-no-restrictions">' . __( 'No membership restrictions for this content.', 'LION' ) . '</div>';
		
	}
	
	$return .= '</div>';
	
	return $return;
	
}

/**
 * Checks if current content should be restricted
 * if admin - false
 * if member has access - false
 * if post|posttype|taxonomy has rule - true (unless above rule overrides)
 * if exemption exists - true
 *
 * An exemption basically tells the Membership addon that a member who has access to
 * specific content should not have access to it. For instance, say you have a post in 
 * a restricted category and you have two membership levels who have access to that category
 * but you only want that post to be visible to one of the memberships. By adding the
 * exemption for the other membership, they will no longer have access to that content.
 *
 * @since 1.0.0
 *
 * @return bool
*/
function it_exchange_membership_addon_is_content_restricted() {
	global $post;
	$restriction = false;
	
	if ( current_user_can( 'administrator' ) )
		return false;
	
	$member_access = it_exchange_get_session_data( 'member_access' );
		
	$restriction_exemptions = get_post_meta( $post->ID, '_item-content-rule-exemptions', true );
	if ( !empty( $restriction_exemptions ) ) {
		foreach( $member_access as $txn_id => $product_id ) {
			if ( array_key_exists( $product_id, $restriction_exemptions ) )
				$restriction = true; //we don't want restrict yet, not until we know there aren't other memberships that still have access to this content
			else
				continue; //get out of this, we're in a membership that hasn't been exempted
		}
		if ( $restriction ) //if it has been restricted, we can return true now
			return true;
	}
	
	$post_rules = get_post_meta( $post->ID, '_item-content-rule', true );
	if ( !empty( $post_rules ) ) {
		if ( empty( $member_access ) ) return true;
		foreach( $member_access as $txn_id => $product_id ) {
			if ( in_array( $product_id, $post_rules ) )
				return false;	
		}
		$restriction = true;
	}
	
	$post_type_rules = get_option( '_item-content-rule-post-type-' . $post->post_type, array() );	
	if ( !empty( $post_type_rules ) ) {
		if ( empty( $member_access ) ) return true;
		foreach( $member_access as $txn_id => $product_id ) {
			if ( !empty( $restriction_exemptions[$product_id] )  )
				return true;
			if ( in_array( $product_id, $post_type_rules ) )
				return false;	
		}
		$restriction = true;
	}
	
	$taxonomy_rules = array();
	$taxonomies = get_object_taxonomies( $post->post_type );
	$terms = wp_get_object_terms( $post->ID, $taxonomies );
	foreach( $terms as $term ) {
		$term_rules = get_option( '_item-content-rule-tax-' . $term->taxonomy . '-' . $term->term_id, array() );
		if ( !empty( $term_rules ) ) {
			if ( empty( $member_access ) ) return true;
			foreach( $member_access as $txn_id => $product_id ) {
				if ( in_array( $product_id, $term_rules ) )
					return false;	
			}
			$restriction = true;
		}
	}
	
	return $restriction;
}

/**
 * Checks if current content should be dripped
 * if admin - false
 * if member has access - check if content is dripped, otherwise false
 * Dripped content is basically published content that you want to arbitrarily delay for
 * your members. Say you have a class and you want to release 1 class a week to your membership
 * this will allow you to do that. Simply set your content to the appropriate timeline and new members 
 * will have access to the classes based on the set schedule.
 *
 * @since 1.0.0
 *
 * @return bool
*/
function it_exchange_membership_addon_is_content_dripped() {
	global $post;
	$dripped = false;
	
	if ( current_user_can( 'administrator' ) )
		return false;
	
	$member_access = it_exchange_get_session_data( 'member_access' );
	foreach( $member_access as $txn_id => $product_id ) {
		$interval = get_post_meta( $post->ID, '_item-content-rule-drip-interval-' . $product_id, true );
		$interval = !empty( $interval ) ? $interval : 0;
		$duration = get_post_meta( $post->ID, '_item-content-rule-drip-duration-' . $product_id, true );
		$duration = !empty( $duration ) ? $duration : 'days';
		if ( 0 < $interval ) {
			$purchase_time = get_post_time( 'U', true, $product_id );
			$dripping = strtotime( $interval . ' ' . $duration, $purchase_time );
			$now = time();
			if ( $dripping < $now )						
				return false; // we can return here because they should have access to this content with this membership
			else
				$dripped = true; // we don't want to return here, because other memberships might have access to content sooner
		}
	}
	return $dripped;
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
	if ( $membership_slug = get_query_var( 'memberships' ) ) {
		$args = array(
		  'name' => $membership_slug,
		  'post_type' => 'it_exchange_prod',
		  'post_status' => 'publish',
		  'numberposts' => 1
		);
		$posts = get_posts( $args );
		foreach( $posts as $post ) { //should only be one
			return it_exchange_get_product( $post );
		}
	}
	return false;
}

/*
 * Returns membership access rules sorted by selected type
 *
 * @since 1.0.0
 *
 * @param int $membership_product_id
 * @param bool $exclude_exempted (optional) argument to exclude exemptions from access rules (true by default)
 * @return array
*/
function it_exchange_membership_access_rules_sorted_by_selected_type( $membership_product_id, $exclude_exempted=true ) {
	$access_rules = it_exchange_get_product_feature( $membership_product_id, 'membership-content-access-rules' );
	$sorted_access_rules = array();
	
	foreach( $access_rules as $rule ) {
		if ( $exclude_exempted && 'posts' === $rule['selected'] ) {		
			$restriction_exemptions = get_post_meta( $rule['term'], '_item-content-rule-exemptions', true );
			if ( !empty( $restriction_exemptions ) ) {
				if ( array_key_exists( $membership_product_id, $restriction_exemptions ) )
					continue;
			}
		}
		$sorted_access_rules[$rule['selected']][] = array(
			'type' => $rule['selection'],
			'term' => $rule['term'],
		);
	}
	
	return $sorted_access_rules;
	
}