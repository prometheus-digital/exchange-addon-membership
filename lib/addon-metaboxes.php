<?php
/**
 * The following file contains metabox functions specific to our membership add-on
*/

/**
 * Adds a box to the main column on the Post and Page edit screens.
 */
function it_exchange_membership_add_post_metabox() {
    $args = array(
	   'public'   => true
	);
	
	$output = 'names'; // names or objects, note names is the default
	$operator = 'and'; // 'and' or 'or'
	
	$post_types = get_post_types( $args, $output, $operator ); 

    foreach ( $post_types as $post_type ) {
        add_meta_box(
            'it_exchange_membership_addon_membership_access_metabox',
            __( 'Membership Access', 'LION' ),
            'it_exchange_membership_addon_membership_access_metabox',
            $post_type,
			'side'
        );
    }
}
add_action( 'add_meta_boxes', 'it_exchange_membership_add_post_metabox' );

function it_exchange_membership_addon_membership_access_metabox( $post ) {
	
	// Add a nonce field so we can check for it later.
	wp_nonce_field( 'it_exchange_membership_addon_membership_access_metabox', 'it_exchange_membership_addon_membership_access_metabox_nonce' );
	
	echo '<h4>' . __( 'Who can access this post?', 'LION' ) . '</h4>';
	
	echo it_exchange_membership_addon_build_post_restriction_rules( $post->ID );
	
	echo '<div class="it-exchange-membership-new-restrictions">';
	echo '</div>';
	
	echo '<div class="it-exchange-add-new-restriction">';
	echo '<a href class="button">' . __( 'Add Restriction', 'LION' ) . '</a>';
	echo '</div>';
}