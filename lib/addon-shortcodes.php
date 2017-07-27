<?php
/**
 * ExchangeWP Membership Add-on
 * @package IT_Exchange_Addon_Membership
 * @since   1.0.0
 */

/**
 * Creates a shortcode that returns content template parts for pages
 *
 * @since 1.0.0
 *
 * @param array $atts attributes passed in via shortcode arguments
 *
 * @return string the shortcode content
 */
function it_exchange_membership_addon_add_included_content_shortcode( $atts ) {
	global $post;

	if ( empty( $post->ID ) ) {
		return '';
	}

	$membership_settings = it_exchange_get_option( 'addon_membership' );

	$defaults = array(
		'product_id'         => $post->ID,
		'before'             => '<div class="it-exchange-restricted-content">',
		'after'              => '</div>',
		'title'              => '',
		'toggle'             => $membership_settings['memberships-group-toggle'],
		'posts_per_grouping' => 5,
		'show_drip'          => 'on',
		'show_drip_time'     => 'on',
		'show_icon'          => 'on',
		'link_to_content'    => is_user_logged_in(),
		'layout'             => $membership_settings['memberships-dashboard-view'],
		'child_description'  => '<p class="description">' . sprintf( __( '(Included with %s)', 'LION' ), get_the_title( $post->ID ) ) . '</p>',
	);

	$atts = shortcode_atts( $defaults, $atts );

	$product_type = it_exchange_get_product_type( $atts['product_id'] );

	if ( 'membership-product-type' !== $product_type ) {
		return '';
	}

	$all_access = it_exchange_membership_addon_setup_recursive_member_access_array( array( $atts['product_id'] => '' ) );

	if ( empty( $all_access ) ) {
		$all_access = array( $atts['product_id'] => '' );
	}

	$product_id  = $atts['product_id'];
	$result      = '';
	$count       = 0;
	$memberships = it_exchange_membership_addon_get_customer_memberships();

	if ( isset( $memberships[ $product_id ] ) ) {

		$customer = it_exchange_get_current_customer();
		$product  = it_exchange_get_product( $product_id );

		$user_membership = it_exchange_get_user_membership_for_product( $customer, $product );
	} else {
		$user_membership = null;
	}

	$factory = new IT_Exchange_Membership_Rule_Factory();

	$result .= '<div class="it-exchange-membership-membership-content">';

	foreach ( $all_access as $product_id => $ignore ) {

		$count ++;

		$product = it_exchange_get_product( $product_id );

		if ( ! $product->supports_feature( 'membership-content-access-rules' ) ) {
			continue;
		}

		if ( ! $product->has_feature( 'membership-content-access-rules' ) ) {
			continue;
		}

		$access_rules = $product->get_feature( 'membership-content-access-rules' );

		if ( empty( $access_rules ) ) {
			continue;
		}

		if ( 1 === $count && ! empty( $atts['title'] ) ) {
			$result .= '<h2>' . $atts['title'] . '</h2>';
		}

		$renderer       = new IT_Exchange_Membership_Front_Rule_Renderer( $access_rules, $factory, $user_membership );
		$render_options = $atts;

		$render_options['include_product_title'] = count( $all_access ) > 1;
		$render_options['as_child']              = $count >= 2;

		$result .= $renderer->render( $render_options, $product );
	}

	$result .= '</div>';

	return $result;
}

add_shortcode( 'it-exchange-membership-included-content', 'it_exchange_membership_addon_add_included_content_shortcode' );

/**
 * Creates a shortcode that hides/displays member content
 *
 * @since 1.0.18
 *
 * @param array  $atts    attributes passed in via shortcode arguments
 * @param string $content current content
 *
 * @return string the shortcode content
 */
function it_exchange_membership_addon_member_content_shortcode( $atts, $content = null ) {
	$membership_settings = it_exchange_get_option( 'addon_membership' );

	$defaults = array(
		'membership_ids' => 0,
	);
	$atts     = shortcode_atts( $defaults, $atts );
	extract( $atts );
	$membership_ids = explode( ',', $membership_ids );

	if ( is_user_logged_in() ) {
		$member_access = it_exchange_membership_addon_get_customer_memberships();
		if ( ! empty( $member_access ) ) {
			foreach ( $member_access as $product_id => $txn_id ) {
				if ( in_array( $product_id, $membership_ids ) ) {
					return do_shortcode( $content );
				}
			}
		}
	}

	return '';
}

add_shortcode( 'it-exchange-member-content', 'it_exchange_membership_addon_member_content_shortcode' );
