<?php
/**
 * iThemes Exchange Membership Add-on
 * @package IT_Exchange_Addon_Membership
 * @since   1.0.0
 */

/**
 * Shows the nag when needed.
 *
 * @since 1.0.0
 *
 * @return void
 */
function it_exchange_membership_addon_show_permlink_update_nag() {
	$version = get_option( 'it-exchange-membership-addon-version', true );
	if ( version_compare( $version, '1.6.0', '<' ) ) { //Updated membership redirect rules in 1.6.0
		if ( ! empty( $_GET['flush-rewrite-rules'] ) ) {
			flush_rewrite_rules();
			update_option( 'it-exchange-membership-addon-version', ITE_MEMBERSHIP_PLUGIN_VERSION );
		} else {
			?>
			<div id="it-exchange-add-on-permalink-nag" class="it-exchange-nag">
				<?php printf( __( 'The latest version of iThemes Exchange Membership requires you to reset your WordPress permalinks. %sClick here to reset your permalinks%s', 'LION' ), '<a href="' . add_query_arg( 'flush-rewrite-rules', 1 ) . '">', '</a>' ); ?>
			</div>
			<script type="text/javascript">
				jQuery( document ).ready( function () {
					if ( jQuery( '.wrap > h2' ).length == '1' ) {
						jQuery( "#it-exchange-add-on-permalink-nag" ).insertAfter( '.wrap > h2' ).addClass( 'after-h2' );
					}
				} );
			</script>
			<?php
		}
	}
}

add_action( 'admin_notices', 'it_exchange_membership_addon_show_permlink_update_nag' );

/**
 * Show a nag about PHP version 5.3 requirement.
 *
 * @since 1.16.5
 */
function it_exchange_membership_show_php_version_nag() {

	if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {

		if ( isset( $_GET['dismiss-php-version-nag'] ) ) {
			update_option( 'it-exchange-membership-php-version-nag', false );
		}

		$show = get_option( 'it-exchange-membership-php-version-nag', true );

		if ( ! $show ) {
			return;
		}

		$dismiss_url = add_query_arg( 'dismiss-php-version-nag', true );

		?>
		<div id="it-exchange-add-on-php-version-nag" class="it-exchange-nag">
			<?php printf( __(
				'The next versions of Membership will require PHP version 5.3 for certain features. You are currently running version %s. Please contact your host to upgrade your PHP version.',
				'LION' ), PHP_VERSION
			); ?>
			<a class="dismiss btn" href="<?php echo esc_url( $dismiss_url ); ?>">&times;</a>
		</div>
		<script type="text/javascript">
			jQuery( document ).ready( function () {
				if ( jQuery( '.wrap > h2' ).length == '1' ) {
					jQuery( "#it-exchange-add-on-php-version-nag" ).insertAfter( '.wrap > h2' ).addClass( 'after-h2' );
				}
			} );
		</script>
		<?php
	}
}

add_action( 'admin_notices', 'it_exchange_membership_show_php_version_nag' );

/**
 * Shows a nag requiring version 1.35.0 of Exchange.
 *
 * @since 1.0.0
 * @since 1.18 Increase minimum requirement to 1.35.2
 *
 * @return void
 */
function it_exchange_membership_addon_show_version_nag() {
	if ( version_compare( $GLOBALS['it_exchange']['version'], '1.35.2', '<' ) ) {
		?>
		<div id="it-exchange-add-on-min-version-nag" class="it-exchange-nag">
			<?php printf( __( 'The Membership add-on requires iThemes Exchange version 1.35.2 or greater. %sPlease upgrade Exchange%s.', 'LION' ), '<a href="' . admin_url( 'update-core.php' ) . '">', '</a>' ); ?>
		</div>
		<script type="text/javascript">
			jQuery( document ).ready( function () {
				if ( jQuery( '.wrap > h2' ).length == '1' ) {
					jQuery( "#it-exchange-add-on-min-version-nag" ).insertAfter( '.wrap > h2' ).addClass( 'after-h2' );
				}
			} );
		</script>
		<?php
	}
}

add_action( 'admin_notices', 'it_exchange_membership_addon_show_version_nag' );

/**
 * Shows a nag requiring version 1.8+ of recurring payments.
 *
 * @since 1.18
 */
function it_exchange_membership_addon_show_recurring_payments_version_nag() {
	if ( function_exists( 'it_exchange_register_recurring_payments_addon' ) && ! class_exists( 'IT_Exchange_Subscription' ) ) {
		?>
		<div id="it-exchange-add-on-min-version-nag" class="it-exchange-nag">
			<?php printf( __( 'The Membership add-on requires Recurring Payments version 1.8.0 or greater. %sPlease upgrade the Recurring Payments add-on%s.', 'LION' ), '<a href="' . admin_url( 'update-core.php' ) . '">', '</a>' ); ?>
		</div>
		<script type="text/javascript">
			jQuery( document ).ready( function () {
				if ( jQuery( '.wrap > h2' ).length == '1' ) {
					jQuery( "#it-exchange-add-on-min-version-nag" ).insertAfter( '.wrap > h2' ).addClass( 'after-h2' );
				}
			} );
		</script>
		<?php
	}
}

add_action( 'admin_notices', 'it_exchange_membership_addon_show_recurring_payments_version_nag' );

/**
 * Shows a nag requiring version 4.2 of WordPress
 *
 * @since 1.18
 *
 * @return void
 */
function it_exchange_membership_addon_show_wp_version_nag() {
	if ( version_compare( $GLOBALS['wp_version'], '4.2.0', '<' ) ) {
		?>
		<div id="it-exchange-add-on-min-version-nag" class="it-exchange-nag">
			<?php printf( __( 'The Membership add-on requires WordPress version 4.2 or greater. %sPlease upgrade WordPress%s.', 'LION' ), '<a href="' . admin_url( 'update-core.php' ) . '">', '</a>' ); ?>
		</div>
		<script type="text/javascript">
			jQuery( document ).ready( function () {
				if ( jQuery( '.wrap > h2' ).length == '1' ) {
					jQuery( "#it-exchange-add-on-min-version-nag" ).insertAfter( '.wrap > h2' ).addClass( 'after-h2' );
				}
			} );
		</script>
		<?php
	}
}

add_action( 'admin_notices', 'it_exchange_membership_addon_show_wp_version_nag' );

/**
 * Adds a members table to the Users WP Menu.
 *
 * @since 1.2.0
 *
 * @return void
 */
function it_exchange_membership_addon_admin_menu() {

	$cap  = it_exchange_get_admin_menu_capability( 'members-table', 'activate_plugins' );
	$hook = add_submenu_page( 'users.php', 'iThemes Exchange ' . __( 'Members', 'LION' ), __( 'Members', 'LION' ), $cap, 'it-exchange-members-table', 'it_exchange_membership_addon_members_table' );
	add_action( "load-$hook", 'it_exchange_membership_addon_members_table_add_screen_option' );
}

add_action( 'admin_menu', 'it_exchange_membership_addon_admin_menu' );

/**
 * Add screen options for members_table.
 *
 * @since 1.2.0
 *
 * @return void
 */
function it_exchange_membership_addon_members_table_add_screen_option() {
	add_screen_option( 'per_page', array( 'label' => _x( 'Members', 'members per page (screen options)' ) ) );
}

/**
 * Set screen options for members_table.
 *
 * @since 1.2.0
 *
 * @param mixed  $status
 * @param string $option
 * @param mixed  $value
 *
 * @return mixed
 */
function it_exchange_membership_addon_members_table_set_screen_option( $status, $option, $value ) {

	if ( 'users_page_it_exchange_members_table_per_page' == $option ) {
		return $value;
	}

	return $status;
}

add_filter( 'set-screen-option', 'it_exchange_membership_addon_members_table_set_screen_option', 10, 3 );

/**
 * Output the members table.
 *
 * @since 1.2.0
 *
 * @return void
 */
function it_exchange_membership_addon_members_table() {

	if ( ! current_user_can( 'list_users' ) ) {
		wp_die( __( 'Cheatin&#8217; uh?' ) );
	}

	$wp_list_table = new IT_Exchange_Membership_List_Table();
	$pagenum       = $wp_list_table->get_pagenum();
	$title         = __( 'iThemes Exchange Members' );

	$wp_list_table->prepare_items();
	$total_pages = $wp_list_table->get_pagination_arg( 'total_pages' );

	if ( $pagenum > $total_pages && $total_pages > 0 ) {
		?>
		<script type="text/javascript">
			document.location = '<?php echo add_query_arg( 'paged', $total_pages ); ?>';
		</script>
		<?php
	}

	?>
	<div class="wrap">
		<h2>
			<?php echo esc_html( $title );

			if ( ! empty( $usersearch ) ) {
				printf( '<span class="subtitle">' . __( 'Search results for &#8220;%s&#8221;', 'LION' ) . '</span>', esc_html( $usersearch ) );
			}
			?>
		</h2>

		<?php $wp_list_table->views(); ?>

		<form id="it-exchange-members-table-form" action="<?php echo esc_url( add_query_arg( 'page', 'it-exchange-members-table', admin_url( 'users.php' ) ) ); ?>" method="get">
			<input type="hidden" name="page" value="it-exchange-members-table" />
			<?php $wp_list_table->search_box( __( 'Search Members', 'LION' ), 'users' ); ?>

			<?php $wp_list_table->display(); ?>
		</form>

		<br class="clear" />
	</div>
	<?php
}

/**
 * Adds actions to the plugins page for the iThemes Exchange Membership plugin
 *
 * @since 1.0.0
 *
 * @param array $actions Existing meta
 *
 * @return array
 */
function it_exchange_membership_plugin_row_actions( $actions ) {

	$actions['setup_addon'] = '<a href="' . get_admin_url( null, 'admin.php?page=it-exchange-addons&add-on-settings=membership-product-type' ) . '">' . __( 'Setup Add-on', 'LION' ) . '</a>';

	return $actions;
}

add_filter( 'plugin_action_links_exchange-addon-membership/exchange-addon-membership.php', 'it_exchange_membership_plugin_row_actions', 10 );

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

/**
 * Enqueues Membership scripts to WordPress Dashboard
 *
 * @since 1.0.0
 *
 * @param string $hook_suffix WordPress passed variable
 *
 * @return void
 */
function it_exchange_membership_addon_admin_wp_enqueue_scripts( $hook_suffix ) {

	if ( 'users_page_it-exchange-members-table' === $hook_suffix ) {
		wp_enqueue_script( 'jquery-ui-tooltip' );
		wp_enqueue_script( 'it-exchange-dialog' );

		return;
	}

	$screen = get_current_screen();

	if ( ! $screen ) {
		return;
	}

	if ( 'it_exchange_prod' === $screen->post_type ) {
		$deps = array(
			'post',
			'jquery-ui-sortable',
			'jquery-ui-droppable',
			'jquery-ui-tabs',
			'jquery-ui-tooltip',
			'jquery-ui-datepicker',
			'autosave'
		);
		wp_enqueue_script( 'it-exchange-membership-addon-add-edit-product', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/js/add-edit-product.js', $deps );
	} else if ( 'it_exchange_prod' !== $screen->post_type && $screen->base == 'post' ) {

		wp_register_script( 'switchery', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/assets/switchery/switchery.min.js' );

		$deps = array( 'jquery-ui-datepicker', 'switchery' );
		wp_enqueue_script( 'it-exchange-membership-addon-add-edit-post', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/js/add-edit-post.js', $deps );
		wp_localize_script( 'it-exchange-membership-addon-add-edit-post', 'ITE_MEMBERSHIP', array(
			'nonce' => wp_create_nonce( 'it-exchange-membership-post-edit' )
		) );
	}
}

add_action( 'admin_enqueue_scripts', 'it_exchange_membership_addon_admin_wp_enqueue_scripts' );

/**
 * Enqueues Membership styles to WordPress Dashboard
 *
 * @since 1.0.0
 *
 * @return void
 */
function it_exchange_membership_addon_admin_wp_enqueue_styles() {

	global $post;

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

		if ( $post_id ) {
			$post = get_post( $post_id );
		}

		if ( isset( $post ) && ! empty( $post ) ) {
			$post_type = $post->post_type;
		}
	}

	// Exchange Product pages
	if ( isset( $post_type ) && 'it_exchange_prod' === $post_type ) {
		wp_enqueue_style( 'it-exchange-membership-addon-add-edit-product', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/styles/add-edit-product.css' );
		wp_localize_script( 'it-exchange-membership-addon-add-edit-product', 'ITE_MEMBERSHIP', array(
			'nonce' => wp_create_nonce( 'it-exchange-membership-product-edit' )
		) );
	} else if ( isset( $post_type ) && 'it_exchange_prod' !== $post_type ) {

		wp_register_style( 'switchery', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/assets/switchery/switchery.min.css' );
		$deps = array( 'switchery' );

		wp_enqueue_style( 'it-exchange-membership-addon-add-edit-post', ITUtility::get_url_from_file( dirname( __FILE__ ) ) . '/styles/add-edit-post.css', $deps );
	}
}

add_action( 'admin_print_styles', 'it_exchange_membership_addon_admin_wp_enqueue_styles' );

add_action( 'init', function() {
	if ( it_exchange_is_page( 'memberships' ) ) {
		add_filter( 'it_exchange_preload_cart_item_types', '__return_true' );
	}
} );

/**
 * Enqueues Membership scripts to WordPress frontend
 *
 * @since 1.0.0
 *
 * @return void
 */
function it_exchange_membership_addon_load_public_scripts() {

	if ( ! it_exchange_is_page( 'memberships' ) ) {
		return;
	}

	wp_enqueue_script(
		'it-exchange-membership-addon-public-js',
		ITUtility::get_url_from_file( dirname( __FILE__ ) . '/assets/js/membership-dashboard.js' ),
		array( 'it-exchange-rest', 'jquery.payment' )
	);

	$membership = it_exchange_membership_addon_get_current_membership();

	if ( ! $membership instanceof IT_Exchange_Membership ) {
		return;
	}

	$user_membership = it_exchange_get_user_membership_for_product( it_exchange_get_current_customer(), $membership );

	if ( ! $user_membership instanceof ITE_Proratable_User_Membership ) {
		return;
	}

	$membership_serializer = new \iThemes\Exchange\Membership\REST\Memberships\Serializer();
	$prorate_serializer    = new \iThemes\Exchange\RecurringPayments\REST\Subscriptions\ProrateSerializer();
	$requestor             = new ITE_Prorate_Credit_Requestor( new ITE_Daily_Price_Calculator() );
	$requestor->register_provider( 'IT_Exchange_Subscription' );
	$requestor->register_provider( 'IT_Exchange_Transaction' );

	$upgrades = $downgrades = array();

	foreach ( $user_membership->get_available_upgrades() as $upgrade ) {
		if ( $requestor->request_upgrade( $upgrade, false ) ) {
			$upgrades[] = $prorate_serializer->serialize( $upgrade );
		}
	}

	foreach ( $user_membership->get_available_downgrades() as $downgrade ) {
		if ( $requestor->request_downgrade( $downgrade ) ) {
			$downgrades[] = $prorate_serializer->serialize( $downgrade );
		}
	}

	wp_localize_script( 'it-exchange-membership-addon-public-js', 'ITExchangeMembershipPublic', array(
		'userMembership' => $membership_serializer->serialize( $user_membership ),
		'upgrades'       => $upgrades,
		'downgrades'     => $downgrades,
		'i18n'           => array(
			'changeMyMembership' => __( 'Change my Membership', 'LION' ),
			'upgrade'            => __( 'Upgrade', 'LION' ),
			'downgrade'          => __( 'Downgrade', 'LION' ),
			'prorate'            => __( 'Prorate', 'LION' ),
			'cancel'             => __( 'Cancel', 'LION' ),
		)
	) );

	add_filter( 'it_exchange_preload_schemas', function( $schemas ) {
		$schemas = is_array( $schemas ) ? $schemas : array();

		return array_merge( $schemas, array(
			'cart',
			'cart-item-product',
			'cart-item-fee',
			'cart-item-tax',
			'cart-purchase',
			'payment-token',
			'prorate-request',
		) );
	} );

	it_exchange_add_inline_script(
		'it-exchange-membership-addon-public-js',
		include( dirname( __FILE__ ) . '/assets/templates/change-my-membership.html' )
	);

	it_exchange_add_inline_script(
		'it-exchange-rest',
		include( IT_Exchange::$dir . 'lib/assets/templates/visual-cc.html' )
	);

	it_exchange_add_inline_script(
		'it-exchange-rest',
		include( IT_Exchange::$dir . 'lib/assets/templates/token-selector.html' )
	);

	it_exchange_add_inline_script(
		'it-exchange-rest',
		include( IT_Exchange::$dir . 'lib/assets/templates/checkout.html' )
	);

	wp_enqueue_style( 'it-exchange-membership-addon-public-css', ITUtility::get_url_from_file( dirname( __FILE__ ) . '/assets/styles/membership-dashboard.css' ) );
}

add_action( 'wp_enqueue_scripts', 'it_exchange_membership_addon_load_public_scripts' );

/**
 * Outputs the Membership media form button
 *
 * @since 1.0.18
 * @return void
 */
function it_exchange_membership_addon_media_form_button() {
	global $post;

	if ( isset( $_REQUEST['post_type'] ) ) {
		$post_type = $_REQUEST['post_type'];
	} else {
		if ( isset( $_REQUEST['post'] ) ) {
			$post_id = (int) $_REQUEST['post'];
		} elseif ( isset( $_REQUEST['post_ID'] ) ) {
			$post_id = (int) $_REQUEST['post_ID'];
		} else {
			$post_id = 0;
		}

		if ( $post_id ) {
			$post = get_post( $post_id );
		}

		if ( isset( $post ) && ! empty( $post ) ) {
			$post_type = $post->post_type;
		}
	}

	add_thickbox();

	if ( isset( $post_type ) && 'it_exchange_prod' !== $post_type ) {

		$title = __( 'Restrict content to a specific Membership product in this post with the Member Content shortcode', 'LION' );

		// display button matching new UI
		echo "<style>.it_exchange_membership_media_icon{
					background:url(" . ITUtility::get_url_from_file( dirname( __FILE__ ) . '/images/membership16px.png' ) . ") no-repeat top left;
					display: inline-block;
					height: 16px;
					margin: 0 2px 0 0;
					vertical-align: text-top;
					width: 16px;
				}
				.wp-core-ui a.it_exchange_membership_media_link{
					 padding-left: 0.4em;
				}
			</style>
			<a href=\"#TB_inline?width=380&inlineId=select-membership-product\" class=\"thickbox button it_exchange_membership_media_link\" id=\"add_membership_content\" title=\"{$title}\">
			<span class=\"it_exchange_membership_media_icon\"></span> " . __( 'Member Content', 'LION' ) . '</a>';
	}
}

add_action( 'media_buttons', 'it_exchange_membership_addon_media_form_button', 15 );

/**
 * Outputs the Membershpi MCE Popup
 *
 * @since 1.0.18
 * @return void
 */
function it_exchange_membership_addon_mce_popup_footer() {
	global $post;

	if ( isset( $_REQUEST['post_type'] ) ) {
		$post_type = $_REQUEST['post_type'];
	} else {
		if ( isset( $_REQUEST['post'] ) ) {
			$post_id = (int) $_REQUEST['post'];
		} elseif ( isset( $_REQUEST['post_ID'] ) ) {
			$post_id = (int) $_REQUEST['post_ID'];
		} else {
			$post_id = 0;
		}

		if ( $post_id ) {
			$post = get_post( $post_id );
		}

		if ( isset( $post ) && ! empty( $post ) ) {
			$post_type = $post->post_type;
		}
	}

	if ( isset( $post_type ) && 'it_exchange_prod' !== $post_type ) {
		?>
		<script>
			function InsertMemberContentShortcode() {
				var membership_ids = [];

				jQuery( '#add-membership-id option:selected' ).each( function () {
					membership_ids.push( jQuery( this ).val() );
				} );

				if ( membership_ids.length == 0 ) {
					alert( "<?php _e( 'You must select at least one membership product', 'LION' ); ?>" );
					return;
				}

				var content = tinyMCE.activeEditor.selection.getContent();

				window.send_to_editor( '[it-exchange-member-content membership_ids="' + membership_ids.join() + '"]' + content + '[/it-exchange-member-content]' );
			}
		</script>

		<div id="select-membership-product" style="display:none;">
			<div class="wrap">
				<div>
					<p class="description" style="padding:0 15px;">
						<?php _e( 'To restrict content within this post or page to users with a specific membership level, highlight the section you’d like to restrict in your content, choose the membership levels you’d like to give access to the content and click the Insert Shortcode button below.', 'LION' ); ?>
					</p>
					<div style="padding:15px 15px 0 15px;">
						<h3><?php _e( 'Select Membership Product(s)', 'LION' ); ?></h3>
                        <span>
                            <?php _e( 'Choose the Memberships allowed to see selected content.', 'LION' ); ?>
                        </span>
					</div>
					<div style="padding:15px 15px 0 15px;">
						<select id="add-membership-id" multiple="multiple" size="5">
							<?php
							$membership_products = it_exchange_get_products( array(
								'product_type'   => 'membership-product-type',
								'show_hidden'    => true,
								'posts_per_page' => - 1
							) );
							foreach ( $membership_products as $membership ) {
								echo '<option value="' . $membership->ID . '">' . get_the_title( $membership->ID ) . '</option>';
							}
							?>
						</select>
					</div>
					<div style="padding:15px;">
						<input type="button" class="button-primary" value="<?php _e( 'Insert Shortcode', 'LION' ); ?>" onclick="InsertMemberContentShortcode();" />&nbsp;&nbsp;&nbsp;
						<a class="button" style="color:#bbb;" href="#" onclick="tb_remove(); return false;"><?php _e( 'Cancel', 'LION' ); ?></a>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

}

add_action( 'admin_footer', 'it_exchange_membership_addon_mce_popup_footer' );

/**
 * Adds shortcode information below extended description box
 *
 * @since 1.0.0
 *
 * @param object $post WordPress post object
 *
 * @return void
 */
function it_exchange_membership_addon_after_print_extended_description_metabox( $post ) {

	$product_type = it_exchange_get_product_type( $post->ID );

	if ( 'membership-product-type' === $product_type ) {

		$codex = '<a href="http://ithemes.com/codex/page/Exchange_Product_Types:_Memberships#Shortcodes">';

		echo '<p class="description">[it-exchange-membership-included-content] - ';
		echo sprintf( __( 'Displays content available with this membership. %sClick here to read about the available shortcode options%s.', 'LION' ), $codex, '</a>' );
		echo '</p>';
	}
}

add_action( 'it_exchange_after_print_extended_description_metabox', 'it_exchange_membership_addon_after_print_extended_description_metabox' );

/**
 * Adds necessary details to Exchange upon successfully completed transaction
 *
 * @since 1.0.0
 *
 * @param int $transaction_id iThemes Exchange Transaction ID
 *
 * @return void
 */
function it_exchange_membership_addon_add_transaction( $transaction_id ) {

	$cart_object         = get_post_meta( $transaction_id, '_it_exchange_cart_object', true );
	$customer_id         = get_post_meta( $transaction_id, '_it_exchange_customer_id', true );
	$customer            = new IT_Exchange_Customer( $customer_id );
	$transaction         = it_exchange_get_transaction( $transaction_id );
	$member_access       = $customer->get_customer_meta( 'member_access' );
	$cancel_subscription = it_exchange_get_session_data( 'cancel_subscription' );

	foreach ( $cart_object->products as $product ) {
		$product_id   = $product['product_id'];
		$product_type = it_exchange_get_product_type( $product_id );

		if ( 'membership-product-type' === $product_type || it_exchange_product_supports_feature( $product_id, 'membership-content-access-rules' ) ) {

			//This is a membership product!
			if ( ! in_array( $product_id, (array) $member_access ) && it_exchange_transaction_is_cleared_for_delivery( $transaction_id ) ) {
				//If this user isn't already a member of this product, add it to their access list
				$member_access[ $transaction_id ][] = $product_id;
			}
		}

		if ( ! empty ( $cancel_subscription[ $product_id ] ) ) {
			$transaction->update_transaction_meta( 'free_days', $cancel_subscription[ $product_id ]['free_days'] );
			$transaction->update_transaction_meta( 'credit', $cancel_subscription[ $product_id ]['credit'] );
		}
	}

	$customer->update_customer_meta( 'member_access', $member_access );
}

add_action( 'it_exchange_add_transaction_success', 'it_exchange_membership_addon_add_transaction', 0 );

/**
 * Adds necessary details to Exchange upon successfully completed child transaction
 *
 * @since 1.0.0
 *
 * @param int $transaction_id iThemes Exchange Child Transaction ID
 *
 * @return void
 */
function it_exchange_membership_addon_add_child_transaction( $transaction_id ) {
	$parent_txn_id = get_post_meta( $transaction_id, '_it_exchange_parent_tx_id', true );
	it_exchange_membership_addon_add_transaction( $parent_txn_id );
}

add_action( 'it_exchange_add_child_transaction_success', 'it_exchange_membership_addon_add_child_transaction' );

/**
 * Creates sessions data with logged in customer's membership access rules
 *
 * @since 1.0.0
 * @return void
 */
function it_exchange_membership_addon_setup_customer_session() {

	if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
		return;
	}

	if ( ! is_user_logged_in() ) {

		it_exchange_clear_session_data( 'member_access' );
		it_exchange_clear_session_data( 'parent_access' );

		return;
	}

	$user_id       = get_current_user_id();
	$customer      = new IT_Exchange_Customer( $user_id );
	$member_access = $customer->get_customer_meta( 'member_access' );

	if ( empty( $member_access ) ) {

		it_exchange_clear_session_data( 'member_access' );
		it_exchange_clear_session_data( 'parent_access' );

		return;
	}

	$flip_member_access = array();
	foreach ( $member_access as $txn_id => $product_id_array ) {
		// we want the transaction ID to be the value to help us determine child access relations to transaction IDs
		// Can't use array_flip because product_id_array is an array -- now :)
		foreach ( (array) $product_id_array as $product_id ) {
			$flip_member_access[ $product_id ] = $txn_id;
		}
	}

	$member_access = it_exchange_membership_addon_setup_recursive_member_access_array( $flip_member_access );

	if ( ! empty( $member_access ) ) {
		it_exchange_update_session_data( 'member_access', $member_access );
	}

	$parent_access = it_exchange_membership_addon_setup_most_parent_member_access_array( $flip_member_access );

	if ( ! empty( $parent_access ) ) {
		it_exchange_update_session_data( 'parent_access', $parent_access );
	}
}

add_action( 'wp', 'it_exchange_membership_addon_setup_customer_session' );

/**
 * Update a customer's member access whenever the transaction's status changes.
 *
 * This is only used for memberships which are not connected to a subscription. Subscription
 * memberships will have their access changes whenever the subscription status changes.
 *
 * @since 1.18
 *
 * @param IT_Exchange_Transaction $transaction
 * @param string                  $old_status
 * @param bool                    $old_status_cleared
 * @param string                  $new_status
 */
function it_exchange_update_member_access_on_transaction_status_change( $transaction, $old_status, $old_status_cleared, $new_status ) {

	$memberships = array();

	foreach ( $transaction->get_products() as $product ) {

		$product = it_exchange_get_product( $product['product_id'] );

		if ( $product instanceof IT_Exchange_Membership ) {
			$memberships[] = $product->ID;
		}
	}

	if ( empty( $memberships ) ) {
		return;
	}

	if ( function_exists( 'it_exchange_get_transaction_subscriptions' ) ) {

		// account for cases where a subscription membership ( or any other product ) and a non-subscription
		// membership are purchased in the same transaction
		$subs = it_exchange_get_transaction_subscriptions( $transaction );

		foreach ( $subs as $sub ) {
			if ( $sub->get_product() instanceof IT_Exchange_Membership ) {
				$i = array_search( $sub->get_product()->ID, $memberships );

				if ( $i !== false ) {
					unset( $memberships[ $i ] );
				}
			}
		}

		if ( empty( $memberships ) ) {
			return;
		}
	}

	$customer = it_exchange_get_transaction_customer( $transaction );

	$member_access = $customer->get_customer_meta( 'member_access' );

	if ( ! is_array( $member_access ) ) {
		$member_access = array();
	}

	$new_cleared = it_exchange_transaction_is_cleared_for_delivery( $transaction );

	if ( $new_cleared && ! $old_status_cleared ) {

		if ( ! isset( $member_access[ $transaction->ID ] ) ) {
			$member_access[ $transaction->ID ] = array();
		}

		foreach ( $memberships as $membership ) {
			if ( ! in_array( $membership, $member_access[ $transaction->ID ] ) ) {
				$member_access[ $transaction->ID ][] = $membership;
			}
		}
	} elseif ( ! $new_cleared && $old_status_cleared ) {
		unset( $member_access[ $transaction->ID ] );
	} else {
		return;
	}

	$customer->update_customer_meta( 'member_access', $member_access );
}

add_action( 'it_exchange_update_transaction_status', 'it_exchange_update_member_access_on_transaction_status_change', 10, 4 );

/**
 * Update the member access array when the subscription status changes.
 *
 * @since 1.18
 *
 * @param string                   $new_status
 * @param string                   $old_status
 * @param IT_Exchange_Subscription $subscription
 */
function it_exchange_update_member_access_on_subscription_status_change( $new_status, $old_status, $subscription ) {

	$customer      = $subscription->get_beneficiary();
	$member_access = $customer->get_customer_meta( 'member_access' );

	if ( ! is_array( $member_access ) ) {
		$member_access = array();
	}

	$tid = $subscription->get_transaction()->ID;

	if ( ! in_array( $new_status, array( IT_Exchange_Subscription::STATUS_ACTIVE, IT_Exchange_Subscription::STATUS_COMPLIMENTARY ) ) ) {
		unset( $member_access[ $tid ] );
	} else {

		if ( ! isset( $member_access[ $tid ] ) ) {
			$member_access[ $tid ] = array();
		}

		if ( ! in_array( $subscription->get_product()->ID, $member_access[ $tid ] ) ) {
			$member_access[ $subscription->get_transaction()->ID ][] = $subscription->get_product()->ID;
		}
	}

	$customer->update_customer_meta( 'member_access', $member_access );
}

add_action( 'it_exchange_transition_subscription_status', 'it_exchange_update_member_access_on_subscription_status_change', 10, 3 );

/**
 * Revoke a member's access when a transaction is deleted.
 *
 * @since 1.18
 *
 * @param int $transaction_id
 */
function it_exchange_revoke_member_access_when_transaction_deleted( $transaction_id ) {

	if ( get_post_type( $transaction_id ) !== 'it_exchange_tran' ) {
		return;
	}

	$customer = it_exchange_get_transaction_customer( $transaction_id );

	$member_access = $customer->get_customer_meta( 'member_access' );

	if ( empty( $member_access ) ) {
		return;
	}

	unset( $member_access[ $transaction_id ] );

	$customer->update_customer_meta( 'member_access', $member_access );
}

add_action( 'before_delete_post', 'it_exchange_revoke_member_access_when_transaction_deleted', 0 );

/**
 * Creates sessions data with logged in customer's membership access rules
 *
 * @since 1.0.0
 *
 * @param int $post_id WordPress Post ID
 *
 * @return void
 */
function it_exchange_before_delete_membership_product( $post_id ) {

	if ( get_post_type( $post_id ) !== 'it_exchange_prod' ) {
		return;
	}

	$membership = it_exchange_get_product( $post_id );

	if ( ! $membership instanceof IT_Exchange_Membership ) {
		return;
	}

	$factory = new IT_Exchange_Membership_Rule_Factory();
	$rules   = $factory->make_all_for_membership( $membership );

	foreach ( $rules as $rule ) {
		$rule->delete();
	}
}

add_action( 'before_delete_post', 'it_exchange_before_delete_membership_product' );

/**
 * Whenever protected content is deleted, delete all its associated protection rules.
 * 
 * @since 1.19.14
 * 
 * @param int $post_id
 */
function it_exchange_delete_rules_when_protected_content_deleted( $post_id ) {

	$post = get_post( $post_id );

	if ( ! $post || $post->post_type === 'it_exchange_prod' ) {
		return;
	}

	$factory = new IT_Exchange_Membership_Rule_Factory();
	$rules   = $factory->make_all_for_post( $post );

	foreach ( $rules as $rule ) {
		$rule->delete();
	}
}

add_action( 'before_delete_post', 'it_exchange_delete_rules_when_protected_content_deleted' );

/**
 * Checks if $post is restriction rules apply, if so, return Membership restricted templates
 * If not, check if $post drip rules apply, if so, return Membership dripped templates
 * Otherwise, return $post's $content
 *
 * @since 1.0.0
 *
 * @param string $content
 *
 * @return string
 */
function it_exchange_membership_addon_content_filter( $content ) {

	if ( it_exchange_membership_addon_is_content_restricted( null, $failed_rules ) ) {
		return it_exchange_membership_addon_content_restricted_template( $failed_rules );
	}

	if ( it_exchange_membership_addon_is_content_dripped( null, $failed_rules ) ) {
		return it_exchange_membership_addon_content_dripped_template( $failed_rules );
	}

	return $content;
}

add_filter( 'the_content', 'it_exchange_membership_addon_content_filter', 8 );

/**
 * Checks if $post is restriction rules apply, if so, return Membership restricted templates
 * If not, check if $post drip rules apply, if so, return Membership dripped templates
 * Otherwise, return $post's $content
 *
 * @since 1.0.0
 *
 * @param string $excerpt
 *
 * @return string
 */
function it_exchange_membership_addon_excerpt_filter( $excerpt ) {

	if ( it_exchange_membership_addon_is_content_restricted( null, $failed_rules ) ) {
		return it_exchange_membership_addon_excerpt_restricted_template( $failed_rules );
	}

	if ( it_exchange_membership_addon_is_content_dripped( null, $failed_rules ) ) {
		return it_exchange_membership_addon_excerpt_dripped_template( $failed_rules );
	}

	return $excerpt;
}

add_filter( 'the_excerpt', 'it_exchange_membership_addon_excerpt_filter', 8 );

/**
 * Checks if $product is restriction rules apply, if so, return Membership restricted templates
 * If not, check if $product drip rules apply, if so, return Membership dripped templates
 * Otherwise, return $product's $result
 *
 * @since 1.2.0
 *
 * @param string $result
 * @param array  $options
 *
 * @return string
 */
function it_exchange_membership_addon_super_widget_filter( $result, $options ) {

	global $post;

	if ( 'it_exchange_prod' === $post->post_type ) {

		if ( it_exchange_membership_addon_is_product_restricted( null, $failed_rules ) ) {

			it_exchange_set_global( 'membership_failed_rules', $failed_rules );

			ob_start();
			it_exchange_get_template_part( 'product', 'restricted' );

			it_exchange_set_global( 'membership_failed_rules', null );

			return ob_get_clean();
		} else if ( it_exchange_membership_addon_is_product_dripped( null, $failed_rules ) ) {

			it_exchange_set_global( 'membership_failed_delay', $failed_rules );

			ob_start();
			it_exchange_get_template_part( 'product', 'dripped' );

			it_exchange_set_global( 'membership_failed_delay', null );

			return ob_get_clean();
		}
	}

	return $result;
}

add_filter( 'it_exchange_theme_api_product_purchase_options', 'it_exchange_membership_addon_super_widget_filter', 10, 2 );

/**
 * Function to modify the default transaction confirmation elements
 *
 * @since 1.0.0
 *
 * @param array $elements Elements being loaded by Theme API
 *
 * @return array $elements Modified elements array
 */
function it_exchange_membership_addon_content_confirmation_after_product_attrubutes( $elements ) {
	it_exchange_get_template_part( 'content-confirmation/elements/membership-confirmation' );
}

add_filter( 'it_exchange_content_confirmation_after_product_attibutes', 'it_exchange_membership_addon_content_confirmation_after_product_attrubutes' );

/**
 * Adds Membership Template Path to iThemes Exchange Template paths
 *
 * @since 1.0.0
 *
 * @param array $possible_template_paths iThemes Exchange existing Template paths array
 * @param array $template_names
 *
 * @return array
 */
function it_exchange_membership_addon_template_path( $possible_template_paths, $template_names ) {
	$possible_template_paths[] = dirname( __FILE__ ) . '/templates/';

	return $possible_template_paths;
}

add_filter( 'it_exchange_possible_template_paths', 'it_exchange_membership_addon_template_path', 10, 2 );

/**
 * Replaces base-price content product element with customer-pricing element, if found
 *
 * @since 1.0.7
 *
 * @param array $parts Element array for temmplate parts
 *
 * @return array Modified array with new customer-pricing element (if base-price was found).
 */
function it_exchange_memnbership_addon_get_content_product_product_advanced_loop_elements( $parts ) {

	$product = it_exchange_get_the_product_id();

	if ( it_exchange_product_has_feature( $product, 'membership-information', array( 'setting' => 'intended-audience' ) ) ) {
		$parts[] = 'intended-audience';
	}

	if ( it_exchange_product_has_feature( $product, 'membership-information', array( 'setting' => 'objectives' ) ) ) {
		$parts[] = 'objectives';
	}

	if ( it_exchange_product_has_feature( $product, 'membership-information', array( 'setting' => 'prerequisites' ) ) ) {
		$parts[] = 'prerequisites';
	}

	return $parts;
}

add_filter( 'it_exchange_get_content_product_product_advanced_loop_elements', 'it_exchange_memnbership_addon_get_content_product_product_advanced_loop_elements' );

/**
 * Adds upgrade price after base-price content product element, if found
 *
 * @since 1.2.0
 *
 * @param array $parts Element array for temmplate parts
 *
 * @return array Modified array with new customer-pricing element (if base-price was found).
 */
function it_exchange_memnbership_addon_get_content_product_product_info_loop_elements( $parts ) {
	array_unshift( $parts, 'upgrade-details' );
	array_unshift( $parts, 'downgrade-details' );

	return $parts;
}

add_filter( 'it_exchange_get_content_product_product_info_loop_elements', 'it_exchange_memnbership_addon_get_content_product_product_info_loop_elements' );

/**
 * Registers the membership frontend dashboard page in iThemes Exchange
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

add_action( 'it_libraries_loaded', 'it_exchange_membership_addon_account_page', 11 );

/**
 * Returns rewrites for membership frontend dashboard page
 * callback from rewrite-rules $options in it_exchange_membership_addon_account_page()
 *
 * @since 1.0.0
 *
 * @param string $page
 *
 * @return mixed array|false
 */
function it_exchange_get_memberships_page_rewrites( $page ) {
	switch ( $page ) {
		case 'memberships' :
			$slug         = it_exchange_get_page_slug( $page );
			$account_slug = it_exchange_get_page_slug( 'account' );

			// If we're using WP as acount page type, add the WP slug to rewrites and return.
			if ( 'wordpress' == it_exchange_get_page_type( 'account' ) ) {
				$account      = get_page( it_exchange_get_page_wpid( 'account' ) );
				$account_slug = $account->post_name;
			}

			$rewrites = array(
				$account_slug . '/([^/]+)/' . $slug . '/([^/]+)/?$' => 'index.php?' . $account_slug . '=$matches[1]&' . $slug . '=$matches[2]',
				$account_slug . '/' . $slug . '/([^/]+)/?$'         => 'index.php?' . $account_slug . '=1&' . $slug . '=$matches[1]',
				$account_slug . '/([^/]+)/' . $slug . '/?$'         => 'index.php?' . $account_slug . '=$matches[1]&' . $slug . '=itememberships',
				$account_slug . '/' . $slug . '/?$'                 => 'index.php?' . $account_slug . '=1&' . $slug . '=itememberships',
			);

			return $rewrites;
			break;
	}

	return false;
}

/**
 * Modifies rewrite rules when setting the Memberships page to a WordPress page
 *
 * @since 1.0.19
 *
 * @param array $existing rewrite rules
 *
 * @return array modified rewrite rules
 */
function it_exchange_membership_addon_register_rewrite_rules( $existing ) {
	if ( 'wordpress' == it_exchange_get_page_type( 'memberships', true ) ) {
		$wpid = it_exchange_get_page_wpid( 'memberships' );
		if ( $wp_page = get_page( $wpid ) ) {
			$page_slug = get_page_uri( $wpid );
		} else {
			$page_slug = 'memberships';
		}

		$rewrite  = array(
			$page_slug . '/([^/]+)/?$' => 'index.php?pagename=' . $page_slug . '&' . $page_slug . '=$matches[1]',
			$page_slug . '/?$'         => 'index.php?pagename=' . $page_slug . '&' . $page_slug . '=itememberships'
		);
		$existing = array_merge( $rewrite, $existing );
	}

	return $existing;
}

add_filter( 'rewrite_rules_array', 'it_exchange_membership_addon_register_rewrite_rules' );

/**
 * Returns URL for membership frontend dashboard page
 *
 * @since 1.0.0
 *
 * @param string $page
 *
 * @return string URL
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
	if ( $permalinks ) {
		$base = trailingslashit( $base . $account_slug );
	} else {
		$base = add_query_arg( array( $account_slug => 1 ), $base );
	}

	$account_name = get_query_var( 'account' );
	if ( $account_name && '1' != $account_name && ( 'login' != $page && 'logout' != $page ) ) {
		if ( $permalinks ) {
			$base = trailingslashit( $base . $account_name );
		} else {
			$base = remove_query_arg( $account_slug, $base );
			$base = add_query_arg( array( $account_slug => $account_name ), $base );
		}
	}

	if ( $permalinks ) {
		return trailingslashit( esc_url( $base . $slug ) );
	} else {
		return esc_url( add_query_arg( $slug, '', $base ) );
	}
}

/**
 * Adds memberships to iThemes Exchange's pages API for:
 *  protected pages
 *  profile pages
 *  account based pages
 *
 * @since 1.0.0
 *
 * @param array $pages
 *
 * @return array
 */
function it_exchange_membership_addon_pages( $pages ) {
	$pages[] = 'memberships';

	return $pages;
}

add_filter( 'it_exchange_pages_to_protect', 'it_exchange_membership_addon_pages' );
add_filter( 'it_exchange_profile_pages', 'it_exchange_membership_addon_pages' );
add_filter( 'it_exchange_account_based_pages', 'it_exchange_membership_addon_pages' );

/**
 * If we're visiting a membership URL and we're not logged in, redirect to the login page, rather than the registration page
 * because we're assuming that someone visiting a membership page probably already has an account. So this
 * will reduce confusion to people who think they need to register again.
 *
 * @since CHANGEME
 *
 * @param string $url    current URL to redirect to
 * @param array  $options
 * @param string $status HTTP Status Code
 *
 * @return string filtered URL to redirect to
 */
function it_exchange_membership_addon_redirect_for_protected_pages_to_login_when_not_logged_in( $url, $options, $status ) {
	if ( ! empty( $options['current-page'] ) && 'memberships' == $options['current-page'] ) {
		$url = it_exchange_get_page_url( 'login' );
	}

	return $url;
}

add_filter( 'it_exchange_redirect_for-protected-pages-to-registration-when-not-logged-in', 'it_exchange_membership_addon_redirect_for_protected_pages_to_login_when_not_logged_in', 10, 3 );

/**
 * Adds memberships URLs to customer's menus in the iThemes Exchange account pages
 *
 * @since 1.0.0
 *
 * @param string $nav Current nav HTML
 *
 * @return array
 */
function it_exchange_membership_addon_append_to_customer_menu_loop( $nav = '' ) {

	$memberships = it_exchange_membership_addon_get_customer_memberships();
	$memberships = it_exchange_membership_addon_setup_most_parent_member_access_array( $memberships );

	$page_slug  = 'memberships';
	$permalinks = (bool) get_option( 'permalink_structure' );

	if ( ! empty( $memberships ) ) {
		foreach ( $memberships as $membership_id => $txn_id ) {
			if ( ! empty( $membership_id ) ) {
				$membership_post = get_post( $membership_id );
				if ( ! empty( $membership_post ) ) {
					$membership_slug = $membership_post->post_name;

					$query_var = get_query_var( 'memberships' );

					$class = 'it-exchange-membership-page-link';

					if ( ! empty( $query_var ) && $query_var == $membership_slug ) {
						$class .= ' current';
					}

					$class = " class=\"$class\"";

					if ( $permalinks ) {
						$url = trailingslashit( it_exchange_get_page_url( $page_slug ) ) . $membership_slug;
					} else {
						$url = it_exchange_get_page_url( $page_slug ) . '=' . $membership_slug;
					}

					$nav .= '<li' . $class . '><a href="' . $url . '">' . get_the_title( $membership_id ) . '</a></li>';
				}
			}
		}
	}

	return $nav;
}

add_filter( 'it_exchange_after_customer_menu_loop', 'it_exchange_membership_addon_append_to_customer_menu_loop', 10, 2 );

/**
 * Adds memberships URLs to customer's menus in the iThemes Exchange account pages
 *
 * @since 1.0.0
 *
 * @param string $product_name Product Name
 * @param object $product_obj  iThemes Exchange Product Object
 *
 * @return string $product_name Product Name
 */
function it_exchange_membership_addon_email_notification_order_table_product_name( $product_name, $product_obj ) {
	if ( it_exchange_product_has_feature( $product_obj['product_id'], 'membership-content-access-rules' ) ) {

		$page_slug  = 'memberships';
		$permalinks = (bool) get_option( 'permalink_structure' );

		$membership_post = get_post( $product_obj['product_id'] );
		if ( ! empty( $membership_post ) ) {
			$membership_slug = $membership_post->post_name;

			if ( $permalinks ) {
				$url = trailingslashit( it_exchange_get_page_url( $page_slug ) ) . $membership_slug;
			} else {
				$url = it_exchange_get_page_url( $page_slug ) . '=' . $membership_slug;
			}

			$product_name .= '<p><small>&nbsp;&nbsp;<a href="' . $url . '">' . __( 'View available content', 'LION' ) . '</a><p></small>';
		}
	}

	return $product_name;
}

add_filter( 'it_exchange_email_notification_order_table_product_name', 'it_exchange_membership_addon_email_notification_order_table_product_name', 10, 2 );

/**
 * Replaces base-price with upgrade price
 *
 * @since 1.0.0
 *
 * @param string $db_base_price default Base Price
 * @param array  $product       iThemes Exchange Product
 * @param bool   $format        Whether or not the price should be formatted
 *
 * @return string $db_base_price modified, if upgrade price has been set for product
 */
function it_exchange_membership_addon_get_credit_pricing_cart_product_base_price( $db_base_price, $product, $format ) {
	$updown_details = it_exchange_get_session_data( 'updowngrade_details' );

	if ( ! empty( $updown_details[ $product['product_id'] ] ) && 'credit' == $updown_details[ $product['product_id'] ]['upgrade_type'] ) {
		$db_base_price = it_exchange_convert_from_database_number( it_exchange_convert_to_database_number( $db_base_price ) );
		$db_base_price = $db_base_price - $updown_details[ $product['product_id'] ]['credit'];

		if ( $format ) {
			$db_base_price = it_exchange_format_price( $db_base_price );
		}
	}

	return $db_base_price;
}

//add_filter( 'it_exchange_get_cart_product_base_price', 'it_exchange_membership_addon_get_credit_pricing_cart_product_base_price', 10, 3 );

/**
 * Replaces base-price with upgrade price
 *
 * @since CHANGEME
 *
 * @param int    $old_term_id      Old Term ID
 * @param int    $new_term_id      New Term ID
 * @param int    $term_taxonomy_id Taxonomy ID for Term
 * @param string $taxonomy         Taxonomy Slug for Term
 *
 * @return string $db_base_price modified, if upgrade price has been set for product
 */
function it_exchange_membership_addon_split_shared_term( $old_term_id, $new_term_id, $term_taxonomy_id, $taxonomy ) {

	$term_rules = get_option( '_item-content-rule-tax-' . $taxonomy . '-' . $old_term_id, array() );

	if ( ! empty( $term_rules ) ) {
		delete_option( '_item-content-rule-tax-' . $taxonomy . '-' . $old_term_id );
		update_option( '_item-content-rule-tax-' . $taxonomy . '-' . $new_term_id, $term_rules );
	}
}

add_action( 'split_shared_term', 'it_exchange_membership_addon_split_shared_term', 10, 4 );

/**
 * Add Membership Keys that should not be duplicated for a product
 *
 * @since CHANGEME
 *
 * @param array $keys Keys that should not be duplicated
 *
 * @return array $keys Keys that should not be duplicated
 */
function it_exchange_membership_addon_duplicate_product_addon_default_product_meta_invalid_keys( $keys ) {
	$keys[] = '_item-content-rule';
	$keys[] = '_item-content-rule-exemptions';

	return $keys;
}

add_filter( 'it_exchange_duplicate_product_addon_default_product_meta_invalid_keys', 'it_exchange_membership_addon_duplicate_product_addon_default_product_meta_invalid_keys' );

/**
 * Add Membership pages to account based pages list
 *
 * @since CHANGEME
 *
 * @param array $account_based_pages Array of account based pages
 *
 * @return array $account_based_pages Array of account based pages
 */
function it_exchange_membership_addon_account_based_pages( $account_based_pages ) {
	$account_based_pages[] = 'memberships';

	return $account_based_pages;
}

add_filter( 'it_exchange_account_based_pages', 'it_exchange_membership_addon_account_based_pages' );
