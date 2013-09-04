<?php
/**
 * The following file contains utility functions specific to our membership add-on
 * If you're building your own product-type addon, it's likely that you will
 * need to do similar things. This includes enqueueing scripts, formatting data for stripe, etc.
*/

/**
 * Adds actions to the plugins page for the iThemes Exchange Membership plugin
 *
 * @since 1.0.0
 *
 * @param array $meta Existing meta
 * @param string $plugin_file the wp plugin slug (path)
 * @param array $plugin_data the data WP harvested from the plugin header
 * @param string $context 
 * @return array
*/
function it_exchange_membership_plugin_row_actions( $actions, $plugin_file, $plugin_data, $context ) {
    
    $actions['setup_addon'] = '<a href="' . get_admin_url( NULL, 'admin.php?page=it-exchange-addons&add-on-settings=membership' ) . '">' . __( 'Setup Add-on', 'LION' ) . '</a>';
    
    return $actions;
    
}
add_filter( 'plugin_action_links_exchange-addon-membership/exchange-addon-membership.php', 'it_exchange_membership_plugin_row_actions', 10, 4 );

function it_exchange_membership_addon_get_selections( $selection = 0, $selection_type = NULL, $count ) {
	
	$return  = '<div class="column col-3-12"><select class="it-exchange-membership-content-type-selections" name="it_exchange_content_access_rules[' . $count . '][selection]">';
	$return .= '<option value="">' . __( 'Select Content', 'LION' ) . '</option>';
	
	//Posts
	$hidden_post_types = apply_filters( 'it_exchange_membership_addon_hidden_post_types', array( 'attachment', 'revision', 'nav_menu_item' ) );
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

function it_exchange_membership_addon_content_rule( $selected, $selection, $value, $count ) {

	$options = '';

	$return  = '<div class="it-exchange-membership-content-access-rule columns-wrapper" data-count="' . $count . '">';
	
	$return .= '<div class="it-exchange-membership-addon-sort-content-access-rule column col-1_4-12"></div>';
	
	$return .= it_exchange_membership_addon_get_selections( $selection, $selected, $count );
	$return .= '<div class="column col-3-12"><div class="it-exchange-membership-content-type-terms">';
	switch( $selected ) {
		
		case 'posts':
			$posts = get_posts( array( 'post_type' => $selection, 'posts_per_page' => -1 ) );
			foreach ( $posts as $post ) {
				$options .= '<option value="' . $post->ID . '" ' . selected( $post->ID, $value, false ) . '>' . get_the_title( $value ) . '</option>';	
			}
			break;
		
		case 'post_types':
			$hidden_post_types = apply_filters( 'it_exchange_membership_addon_hidden_post_types', array( 'attachment', 'revision', 'nav_menu_item' ) );
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
	
	$return .= '<div class="it-exchange-membership-addon-remove-content-access-rule column col-1-12">';
	$return .= '<a href="#">×</a>';
	$return .= '</div>';
	
	$return .= '</div>';
	
	return $return;
	
}