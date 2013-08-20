<?php

/**
 * Adds digital downloads to the iThemes Exchange Membership plugin
 *
 * @since 1.0.0
 *
 * @return void
*/
function ithemes_exchange_membership_addon_add_feature_digital_downloads() {
	it_exchange_add_feature_support_to_product_type( 'downloads', 'membership-product-type' );
}
add_action( 'it_exchange_enabled_addons_loaded', 'ithemes_exchange_membership_addon_add_feature_digital_downloads' );

function it_exchange_membership_addon_admin_wp_enqueue_scripts( $hook_suffix ) {
	if ( isset( $_REQUEST['post_type'] ) ) {
		$post_type = $_REQUEST['post_type'];
	} else {
		if ( isset( $_REQUEST['post'] ) )
			$post_id = (int) $_REQUEST['post'];
		elseif ( isset( $_REQUEST['post_ID'] ) )
			$post_id = (int) $_REQUEST['post_ID'];
		else
			$post_id = 0;

		if ( $post_id )
			$post = get_post( $post_id );

		if ( isset( $post ) && !empty( $post ) )
			$post_type = $post->post_type;
	}

	if ( isset( $post_type ) && 'it_exchange_prod' === $post_type ) {
		$deps = array( 'post', 'jquery-ui-sortable', 'jquery-ui-droppable', 'jquery-ui-tabs', 'jquery-ui-tooltip', 'jquery-ui-datepicker', 'autosave' );
		wp_enqueue_script( 'it-exchange-membership-addon-add-edit-product', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/js/add-edit-product.js', $deps );
	}
}
add_action( 'admin_enqueue_scripts', 'it_exchange_membership_addon_admin_wp_enqueue_scripts' );

/**
 * Inits the scripts used by IT Exchange dashboard
 *
 * @since 0.4.0
 * @return void
*/
function it_exchange_membership_addon_admin_wp_enqueue_styles() {
	global $hook_suffix;

	if ( isset( $_REQUEST['post_type'] ) ) {
		$post_type = $_REQUEST['post_type'];
	} else {
		if ( isset( $_REQUEST['post'] ) ) {
			$post_id = (int) $_REQUEST['post'];
		} else if ( isset( $_REQUEST['post_ID'] ) ) {
			$post_id = (int) $_REQUEST['post_ID'];
		} else {
			$post_id = 0;
		}

		if ( $post_id )
			$post = get_post( $post_id );

		if ( isset( $post ) && !empty( $post ) )
			$post_type = $post->post_type;
	}

	// Exchange Product pages
	if ( isset( $post_type ) && 'it_exchange_prod' === $post_type )
		wp_enqueue_style( 'it-exchange-membership-addon-add-edit-product', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/styles/add-edit-product.css' );
}
add_action( 'admin_print_styles', 'it_exchange_membership_addon_admin_wp_enqueue_styles' );

function it_exchange_membership_addon_ajax_show_empty_content_access_rule() {	
	$taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );
	
	$return  = '<div class="it-exchange-empty-content-access-rule">';
	
	$return .= '<select class="it-exchange-membership-content-type-selections" name="content_type_selection">';
	$return .= '<option value="">' . __( 'Select Content', 'LION' ) . '</option>';
	
	$hidden_post_types = apply_filters( 'it_exchange_membership_addon_hidden_post_types', array( 'attachment', 'revision', 'nav_menu_item' ) );
	$post_types = get_post_types( array(), 'objects' );
	
	foreach ( $post_types as $post_type ) {
		if ( in_array( $post_type->name, $hidden_post_types ) ) 
			continue;
			
		$return .= '<option data-type="posts" value="' . $post_type->name . '">' . $post_type->label . '</option>';	
	}
	
	$return .= '<option data-type="post_types" value="post_type">' . __( 'Post Types', 'LION' ) . '</option>';
	foreach ( $taxonomies as $tax ) {
		// we want to skip post format taxonomies, not really needed here
		if ( 'post_format' === $tax->name )
			continue;
			
		$return .= '<option data-type="taxonomy" value="' . $tax->name . '">' . $tax->label . '</option>';	
	}	
	$return .= '</select>';
	
	$return .= '<div class="it-exchange-membership-content-type-terms hidden">';
	$return .= '</div>';
	
	/* No Drip Yet!
	$return .= '<div class="it_exchange-membership-content-type-drip hidden">';
	$return .= '<input type="text" class="it-exchange-membership-content-type-drip-time small" name="content_type_drip_times" disabled="disabled">';
	
	$return .= '<select class="it-exchange_membership-content-type-drip-type" name="content_type_drip_types" disabled="disabled">';
	$return .= '<option value="days">' . __( 'Day(s)', 'LION' ) . '</option>';
	$return .= '<option value="weeks">' . __( 'Week(s)', 'LION' ) . '</option>';
	$return .= '<option value="months">' . __( 'Month(s)', 'LION' ) . '</option>';
	$return .= '<option value="years">' . __( 'Year(s)', 'LION' ) . '</option>';
	$return .= '</select>';
	$return .= '</div>';
	/**/
	
	$return .= '<div class="it-exchange-membership-content-rule-submit hidden">';
	$return .= '<a href class="button">' . __( 'OK', 'LION' ) . '</a>';
	$return .= '</div>';
	
	$return .= '</div>';
	
	die( $return );
}
add_action( 'wp_ajax_it-exchange-membership-addon-show-empty-content-access-rule', 'it_exchange_membership_addon_ajax_show_empty_content_access_rule' );

function it_exchange_membership_addon_ajax_get_content_type_term() {
	
	$return = '';
	
	if ( !empty( $_REQUEST['type'] ) && !empty( $_REQUEST['value'] ) ) {
			
		$type = $_REQUEST['type'];
		$value = $_REQUEST['value'];
	
		switch( $type ) {
			
			case 'posts':
				$posts = get_posts( array( 'post_type' => $value, 'posts_per_page' => -1 ) );
				$return .= '<input type="hidden" value="posts" name="content_type_selected" />';
				$return .= '<select class="it-exchange-membership-content-type-term" name="content_type_term">';
				foreach ( $posts as $post ) {
					$return .= '<option value="' . $post->ID . '">' . $post->post_title . '</option>';	
				}
				$return .= '</select>';
				break;
			
			case 'post_types':
				$hidden_post_types = apply_filters( 'it_exchange_membership_addon_hidden_post_types', array( 'attachment', 'revision', 'nav_menu_item' ) );
				$post_types = get_post_types( array(), 'objects' );
				$return .= '<input type="hidden" value="post_types" name="content_type_selected" />';
				$return .= '<select class="it-exchange-membership-content-type-term" name="content_type_term">';
				foreach ( $post_types as $post_type ) {
					if ( in_array( $post_type->name, $hidden_post_types ) ) 
						continue;
						
					$return .= '<option value="' . $post_type->name . '">' . $post_type->label . '</option>';	
				}
				$return .= '</select>';
				break;
			
			case 'taxonomy':
				$terms = get_terms( $value, array( 'hide_empty' => false ) );
				$return .= '<input type="hidden" value="taxonomy" name="content_type_selected" />';
				$return .= '<select class="it-exchange-membership-content-type-term" name="content_type_term">';
				foreach ( $terms as $term ) {
					$return .= '<option value="' . $term->term_id . '">' . $term->name . '</option>';	
				}
				$return .= '</select>';
				break;
			
		}
			
	}

	die( $return );
	
}
add_action( 'wp_ajax_it-exchange-membership-addon-content-type-terms', 'it_exchange_membership_addon_ajax_get_content_type_term' );

function it_exchange_membership_addon_content_rule( $selected, $selection, $term, $count ) {

	$return  = '<div class="it-exchange-membership-content-access-rule">';
	$return .= '<input type="hidden" name="it_exchange_content_access_rules[' . $count . '][selected]" value="' . $selected . '" />';
	$return .= '<input type="hidden" name="it_exchange_content_access_rules[' . $count . '][selection]" value="' . $selection . '" />';
	$return .= '<input type="hidden" name="it_exchange_content_access_rules[' . $count . '][term]" value="' . $term . '" />';

	switch( $selected ) {
		
		case 'posts':
			$return .= '<span class="it_exchange_content_type_post">' . __( 'Post', 'LION' ) . ':</span> ';
			$return .= '<span class="it_exchange_content_type_post_title">' . get_the_title( $term ) . '</span> ';
			break;
		
		case 'post_types':
			$return .= '<span class="it_exchange_content_type_post_type">' . __( 'Post Type', 'LION' ) . ':</span> ';
			$post_type_obj = get_post_type_object( $term );
			$return .= '<span class="it_exchange_content_type_post_type_title">' . $post_type_obj->labels->name . '</span> ';
			break;
		
		case 'taxonomy':
			$tax_obj = get_taxonomy( $selection );
			$return .= '<span class="it_exchange_content_type_taxonomy">' . $tax_obj->labels->name . ':</span> ';
			$term_obj = get_term_by( 'id', $term, $selection );
			$return .= '<span class="it_exchange_content_type_post_type_title">' . $term_obj->name . '</span> ';
			break;
		
	}
	
	$return .= '<div class="it-exchange-membership-addon-remove-content-access-rule">';
	$return .= '<a href="#">×</a>';
	$return .= '</div>';
	
	$return .= '</div>';
	
	return $return;
	
}

function it_exchange_membership_addon_ajax_add_content_rule() {
	
	$return = '';
	
	if ( !empty( $_REQUEST['content_type_selected'] ) 
		&& !empty( $_REQUEST['content_type_selection'] )
		&& !empty( $_REQUEST['content_type_term'] )
		&& isset( $_REQUEST['count'] ) ) {
			
		$selected  = $_REQUEST['content_type_selected'];
		$selection = $_REQUEST['content_type_selection'];
		$term      = $_REQUEST['content_type_term'];
		$count     = $_REQUEST['count'];
		
		$return = it_exchange_membership_addon_content_rule( $selected, $selection, $term, $count );
			
	}

	die( $return );
}
add_action( 'wp_ajax_it-exchange-membership-addon-add-content-rule', 'it_exchange_membership_addon_ajax_add_content_rule' );

function it_exchange_membership_addon_is_content_restricted() {
		
	global $post;
	
	if ( $restricted = get_post_meta( $post->ID, '_item-content-rule', true ) )
		return true;

	if ( $restricted = get_option( '_item-content-rule-post-type-' . $post->post_type ) )
		return true;
	
	$taxonomies = get_object_taxonomies( $post->post_type ); 
	
	foreach ( $taxonomies as $tax ) {
	
		$terms = wp_get_post_terms( $post->ID, $tax );
		
		if ( !empty( $terms ) ) {
		
			foreach ( $terms as $term ) {
			
				if ( $restricted = get_option( '_item-content-rule-tax-' . $tax . '-' . $term->term_id ) ) 
					return true;
				
			}
			
		}
				
	}
	
	return false;
	
}

function it_exchange_membership_addon_content_filter( $content ) {
	if ( it_exchange_membership_addon_is_content_restricted() )
		$content = it_exchange_membership_addon_content_restricted_template();
		
	return $content;	
}
add_filter( 'the_content', 'it_exchange_membership_addon_content_filter' );

function it_exchange_membership_addon_excerpt_filter( $excerpt ) {
	if ( it_exchange_membership_addon_is_content_restricted() )
		$excerpt = it_exchange_membership_addon_excerpt_restricted_template();
		
	return $excerpt;
}
add_filter( 'the_excerpt', 'it_exchange_membership_addon_excerpt_filter' );

function it_exchange_membership_addon_content_restricted_template() {
	$GLOBALS['wp_query']->is_single = false; //false -- so comments_template() doesn't add comments
	$GLOBALS['wp_query']->is_page = false;   //false -- so comments_template() doesn't add comments
	//Get the Membership Addon's Content Restricted template
	//add_filter( 'template_include', 'it_exchange_membership_addon_fetch_template' );
	ob_start();
	it_exchange_get_template_part( 'content', 'restricted' );
	return ob_get_clean();	
}

function it_exchange_membership_addon_excerpt_restricted_template() {
	$GLOBALS['wp_query']->is_single = false; //false -- so comments_template() doesn't add comments
	$GLOBALS['wp_query']->is_page = false;   //false -- so comments_template() doesn't add comments
	//Get the Membership Addon's Content Restricted template
	//add_filter( 'template_include', 'it_exchange_membership_addon_fetch_template' );
	ob_start();
	it_exchange_get_template_part( 'excerpt', 'restricted' );
	return ob_get_clean();	
}

function it_exchange_membership_addon_template_path( $possible_template_paths, $template_names ) {
	$possible_template_paths[] = dirname( __FILE__ ) . '/templates/';
	return $possible_template_paths;
}
add_filter( 'it_exchange_possible_template_paths', 'it_exchange_membership_addon_template_path', 10, 2 );