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
	global $post;
	
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
	} else if ( isset( $post_type ) && 'it_exchange_prod' !== $post_type ) {
		wp_enqueue_script( 'it-exchange-membership-addon-add-edit-post', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/js/add-edit-post.js' );
	}
}
add_action( 'admin_enqueue_scripts', 'it_exchange_membership_addon_admin_wp_enqueue_scripts' );

/**
 * Inits the scripts used by IT Exchange dashboard
 *
 * @since 1.0.0
 * @return void
*/
function it_exchange_membership_addon_admin_wp_enqueue_styles() {
	global $post, $hook_suffix;

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
	if ( isset( $post_type ) && 'it_exchange_prod' === $post_type ) {
		wp_enqueue_style( 'it-exchange-membership-addon-add-edit-product', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/styles/add-edit-product.css' );
	} else if ( isset( $post_type ) && 'it_exchange_prod' !== $post_type ) {
		wp_enqueue_style( 'it-exchange-membership-addon-add-edit-post', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/styles/add-edit-post.css' );
	}
}
add_action( 'admin_print_styles', 'it_exchange_membership_addon_admin_wp_enqueue_styles' );

/**
 * Loads the frontend CSS on all exchange pages
 *
 * @since 1.0.0
 *
 * @return void
*/
function it_exchange_membership_addon_load_public_scripts( $current_view ) {
	// Frontend Membership Dashboard CSS & JS
	if ( it_exchange_is_page( 'memberships' ) ) {
		wp_enqueue_script( 'it-exchange-membership-addon-public-js', ITUtility::get_url_from_file( dirname( __FILE__ ) . '/assets/js/membership-dashboard.js' ), array( 'jquery-zoom' ), false, true );
		wp_enqueue_style( 'it-exchange-membership-addon-public-css', ITUtility::get_url_from_file( dirname( __FILE__ ) . '/assets/styles/membership-dashboard.css' ) );
	}
}
add_action( 'wp_enqueue_scripts', 'it_exchange_membership_addon_load_public_scripts' );

/**
 * Adds necessary details to Exchange upon successfully completed transaction
 *
 * @since 1.0.0
 * @param int $transaction_id iThemes Exchange Transaction ID 
 * @return void
*/
function it_exchange_membership_addon_add_transaction( $transaction_id ) {
	$cart_object = get_post_meta( $transaction_id, '_it_exchange_cart_object', true );
	$customer_id = get_post_meta( $transaction_id, '_it_exchange_customer_id', true );
	$customer = new IT_Exchange_Customer( $customer_id );
	$member_access = $customer->get_customer_meta( 'member_access' );
	
	foreach ( $cart_object->products as $product ) {
		if ( it_exchange_product_supports_feature( $product['product_id'], 'membership-content-access-rules' ) ) {
			//This is a membership product!
			if ( !in_array( $product['product_id'], (array)$member_access ) ) {
				//If this user isn't already a member of this product, add it to their access list
				$member_access[$transaction_id] = $product['product_id'];
				$customer->update_customer_meta( 'member_access', $member_access );
			}
		}
	}
}
add_action( 'it_exchange_add_transaction_success', 'it_exchange_membership_addon_add_transaction' );

/**
 * Adds necessary details to Exchange upon successfully completed child transaction
 *
 * @since 1.0.0
 * @param int $transaction_id iThemes Exchange Child Transaction ID 
 * @return void
*/
function it_exchange_membership_addon_add_child_transaction( $transaction_id ) {
	$parent_txn_id =  get_post_meta( $transaction_id, '_it_exchange_parent_tx_id', true );
	it_exchange_membership_addon_add_transaction( $parent_txn_id );
}
add_action( 'it_exchange_add_child_transaction_success', 'it_exchange_membership_addon_add_child_transaction' );

function it_exchange_membership_addon_setup_customer_session() {
	if ( is_user_logged_in() ) {
		$user_id = get_current_user_id();
		$customer = new IT_Exchange_Customer( $user_id );
		if ( ! $member_access = it_exchange_get_session_data( 'member_access' ) ) {		
			$member_access = $customer->get_customer_meta( 'member_access' );	
			if ( !empty( $member_access ) ) {
				foreach( $member_access as $txn_id => $product_id ) {
					$transaction = it_exchange_get_transaction( $txn_id );
					$subscription_status = $transaction->get_transaction_meta( 'subscriber_status' );
					//empty means it was never set... which should mean that recurring payments isn't setup
					if ( !empty( $subscription_status ) && 'active' !== $subscription_status )
						unset( $member_access[$txn_id] );
				}
				$customer->update_customer_meta( 'member_access', $member_access );
				it_exchange_add_session_data( 'member_access', $member_access );
			}
		}
	} else {
		it_exchange_clear_session_data( 'member_access' );
	}
}
add_action( 'wp', 'it_exchange_membership_addon_setup_customer_session' );


function it_exchange_membership_addon_content_filter( $content ) {
	if ( it_exchange_membership_addon_is_content_restricted() )
		return it_exchange_membership_addon_content_restricted_template();
	if ( it_exchange_membership_addon_is_content_dripped() )
		return it_exchange_membership_addon_content_dripped_template();
	return $content;	
}
add_filter( 'the_content', 'it_exchange_membership_addon_content_filter' );

function it_exchange_membership_addon_excerpt_filter( $excerpt ) {
	if ( it_exchange_membership_addon_is_content_restricted() )
		return it_exchange_membership_addon_excerpt_restricted_template();
	if ( it_exchange_membership_addon_is_content_dripped() )
		return it_exchange_membership_addon_excerpt_dripped_template();
	return $excerpt;
}
add_filter( 'the_excerpt', 'it_exchange_membership_addon_excerpt_filter' );

function it_exchange_membership_addon_content_restricted_template() {
	$GLOBALS['wp_query']->is_single = false; //false -- so comments_template() doesn't add comments
	$GLOBALS['wp_query']->is_page = false;   //false -- so comments_template() doesn't add comments
	ob_start();
	it_exchange_get_template_part( 'content', 'restricted' );
	return ob_get_clean();	
}

function it_exchange_membership_addon_excerpt_restricted_template() {
	$GLOBALS['wp_query']->is_single = false; //false -- so comments_template() doesn't add comments
	$GLOBALS['wp_query']->is_page = false;   //false -- so comments_template() doesn't add comments
	ob_start();
	it_exchange_get_template_part( 'excerpt', 'restricted' );
	return ob_get_clean();	
}

function it_exchange_membership_addon_content_dripped_template() {
	$GLOBALS['wp_query']->is_single = false; //false -- so comments_template() doesn't add comments
	$GLOBALS['wp_query']->is_page = false;   //false -- so comments_template() doesn't add comments
	ob_start();
	it_exchange_get_template_part( 'content', 'dripped' );
	return ob_get_clean();	
}

function it_exchange_membership_addon_excerpt_dripped_template() {
	$GLOBALS['wp_query']->is_single = false; //false -- so comments_template() doesn't add comments
	$GLOBALS['wp_query']->is_page = false;   //false -- so comments_template() doesn't add comments
	ob_start();
	it_exchange_get_template_part( 'excerpt', 'dripped' );
	return ob_get_clean();	
}

function it_exchange_membership_addon_template_path( $possible_template_paths, $template_names ) {
	$possible_template_paths[] = dirname( __FILE__ ) . '/templates/';
	return $possible_template_paths;
}
add_filter( 'it_exchange_possible_template_paths', 'it_exchange_membership_addon_template_path', 10, 2 );

/*
 * Registers the membership page
 *
 * @since 1.0.0
 *
 * @return void
*/
function it_exchange_membership_addon_account_page() {
    $options = array(
		'slug'          => 'memberships',
		'name'          => __( 'Memberships', 'LION' ),
		'rewrite-rules' => array( 115, 'it_exchange_get_memberships_page_rewrites' ),
		'url'           => 'it_exchange_get_membership_page_urls',
		'settings-name' => __( 'Membership Page', 'LION' ),
		'tip'           => __( 'Membership pages appear in the customers account profile.', 'LION' ),
		'type'          => 'exchange',
		'menu'          => true,
		'optional'      => true,
    );  
    it_exchange_register_page( 'memberships', $options );
}
add_action( 'init', 'it_exchange_membership_addon_account_page', 9 );

/**
 * Returns rewrites for membership pages
 *
 * @since 1.0.0
 *
 * @param string page
 * @return array
*/
function it_exchange_get_memberships_page_rewrites( $page ) {
	$slug = it_exchange_get_page_slug( $page );
	switch( $page ) {
		case 'memberships' :
			$account_slug = it_exchange_get_page_slug( 'account' );

			// If we're using WP as acount page type, add the WP slug to rewrites and return.
			if ( 'wordpress' == it_exchange_get_page_type( 'account' ) ) {
				$account = get_page( it_exchange_get_page_wpid( 'account' ) );
				$account_slug = $account->post_name;
			}

			$rewrites = array(
				$account_slug  . '/([^/]+)/' . $slug  . '/([^/]+)'  => 'index.php?' . $account_slug . '=$matches[1]&' . $slug . '=$matches[2]',
				$account_slug . '/' . $slug  . '/([^/]+)' => 'index.php?' . $account_slug . '=1&' . $slug . '=$matches[2]',
			);
			return $rewrites;
			break;
	}
	return false;
}

/**
 * Returns URL for membership pages
 *
 * @since 1.0.0
 *
 * @param string page
 * @return array
*/
function it_exchange_get_membership_page_urls( $page ) {
	// Get slugs
    $slug       = it_exchange_get_page_slug( $page );
    $permalinks = (boolean) get_option( 'permalink_structure' );
    $base       = trailingslashit( get_home_url() );

	// Account Slug
	if ( 'wordpress' == it_exchange_get_page_type( 'account' ) ) {
		$account_page = get_page( it_exchange_get_page_wpid( 'account' ) );
		$account_slug = $account_page->post_name;
	} else {
		$account_slug = it_exchange_get_page_slug( 'account' );
	}
	
	// Replace account value with name if user is logged in
	if ( $permalinks )
		$base = trailingslashit( $base . $account_slug );
	else
		$base = add_query_arg( array( $account_slug => 1 ), $base );

	$account_name = get_query_var( 'account' );
	if ( $account_name && '1' != $account_name && ( 'login' != $page && 'logout' != $page ) ) {
		if ( $permalinks ) {
			$base = trailingslashit( $base . $account_name );
		} else {
			$base = remove_query_arg( $account_slug, $base );
			$base = add_query_arg( array( $account_slug => $account_name ), $base );
		}
	}

	if ( $permalinks )
		return trailingslashit( $base . $slug );
	else
		return add_query_arg( $slug, '', $base );
}

function it_exchange_membership_addon_pages( $pages ) {
	$pages[] = 'memberships';
	return $pages;	
}
add_filter( 'it_exchange_pages_to_protect', 'it_exchange_membership_addon_pages' );
add_filter( 'it_exchange_profile_pages', 'it_exchange_membership_addon_pages' );
add_filter( 'it_exchange_account_based_pages', 'it_exchange_membership_addon_pages' );

function it_exchange_membership_addon_append_to_customer_menu_loop( $nav, $customer ) {
	$memberships = $customer->get_customer_meta( 'member_access' );
	$page_slug = 'memberships';
	$permalinks = (bool)get_option( 'permalink_structure' );
	
	foreach ( $memberships as $membership_id ) {
		
		$membership_post = get_post( $membership_id );
		$membership_slug = $membership_post->post_name;
		
		$query_var = get_query_var( 'memberships' );
		
		$class = !empty( $query_var ) && $query_var == $membership_slug ? ' class="current"' : '';
		
		if ( $permalinks )
			$url = it_exchange_get_page_url( $page_slug ) . $membership_slug;
		else
			$url = it_exchange_get_page_url( $page_slug ) . '=' . $membership_slug;
			
		$nav .= '<li' . $class . '><a href="' . $url . '">' . get_the_title( $membership_id ) . '</a></li>';	
	}
	
	return $nav;
}
add_filter( 'it_exchange_after_customer_menu_loop', 'it_exchange_membership_addon_append_to_customer_menu_loop', 10, 2 );