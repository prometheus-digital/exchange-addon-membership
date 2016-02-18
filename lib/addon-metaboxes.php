<?php
/**
 * iThemes Exchange Membership Add-on
 * @package IT_Exchange_Addon_Membership
 * @since   1.0.0
 */

/**
 * The following file contains metabox functions specific to our membership add-on
 */

/**
 * Adds a box to the main column on the Post and Page edit screens.
 *
 * @since 1.0.0
 * @return void
 */
function it_exchange_membership_add_post_metabox() {
	$args = array(
		'public' => true
	);

	$hidden_post_types = apply_filters( 'it_exchange_membership_addon_hidden_post_types', array(
		'attachment',
		'it_exchange_prod',
	) );

	$post_types = get_post_types( $args );

	foreach ( $post_types as $post_type ) {
		if ( in_array( $post_type, $hidden_post_types ) ) {
			continue;
		}

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

/**
 * Outputs metabox.
 *
 * @since 1.0.0
 *
 * @param object $post WordPress Post object
 *
 * @return void
 */
function it_exchange_membership_addon_membership_access_metabox( $post ) {

	$disabled = get_post_meta( $post->ID, '_it-exchange-content-restriction-disabled', true );
	$hidden = $disabled ? ' hidden' : '';

	echo '<label for="it-exchange-switch-member-access">' . __( 'Enable Content Restriction', 'LION' ) . '</label>';
	echo '<input type="checkbox" id="it-exchange-switch-member-access"' . checked( $disabled, false, false ) . '>';

	echo '<div class="it-exchange-membership-content-access-rules-settings' . $hidden .'">';
	echo '<h4>' . __( 'Who can access this post?', 'LION' ) . '</h4>';

	echo it_exchange_membership_addon_build_post_restriction_rules( $post->ID );

	echo '<div class="it-exchange-membership-new-restrictions">';
	echo '</div>';

	echo '<div class="it-exchange-add-new-restriction">';
	echo '<a href="#" class="button">' . __( 'Add Restriction', 'LION' ) . '</a>';
	echo '</div>';
	echo '</div>';
}