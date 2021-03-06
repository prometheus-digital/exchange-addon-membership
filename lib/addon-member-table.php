<?php
/**
 * ExchangeWP Membership Add-on
 * @package IT_Exchange_Addon_Membership
 * @since   CHANGEME
 */

/*************************** LOAD THE BASE CLASS *******************************
 *******************************************************************************
 * The WP_List_Table class isn't automatically available to plugins, so we need
 * to check if it's available and load it if necessary.
 */
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class IT_Exchange_Membership_List_Table extends WP_List_Table {

	/**
	 * Check the current user's permissions.
	 *
	 * @since  CHANGEME
	 * @access public
	 */
	public function ajax_user_can() {
		return current_user_can( 'list_users' );
	}

	/**
	 * Prepare the users list for display.
	 *
	 * @since  CHANGEME
	 * @access public
	 */
	public function prepare_items() {
		global $membership, $usersearch;

		/**
		 * REQUIRED. Now we need to define our column headers. This includes a complete
		 * array of columns to be displayed (slugs & titles), a list of columns
		 * to keep hidden, and a list of columns that are sortable. Each of these
		 * can be defined in another method (as we've done here) before being
		 * used to build the value for our _column_headers property.
		 */
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		/**
		 * REQUIRED. Finally, we build an array to be used by the class for column
		 * headers. The $this->_column_headers property takes an array which contains
		 * 3 other arrays. One for all columns, one for hidden columns, and one
		 * for sortable columns.
		 */
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$usersearch = isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';

		$membership = isset( $_REQUEST['membership'] ) ? $_REQUEST['membership'] : '';

		$users_per_page = $this->get_items_per_page( 'users_page_it_exchange_members_table_per_page' );

		$paged = $this->get_pagenum();

		$args = array(
			'number'      => $users_per_page,
			'offset'      => ( $paged - 1 ) * $users_per_page,
			'search'      => $usersearch,
			'fields'      => 'all_with_meta',
			'post_status' => 'any'
		);

		if ( ! empty( $membership ) ) {
			$args['meta_query'] = array(
				array(
					'key'     => '_it_exchange_customer_member_access',
					'value'   => "i:$membership;",
					'compare' => 'LIKE',
				)
			);
		} else {
			$args['meta_query'] = array(
				array(
					'key'     => '_it_exchange_customer_member_access',
					'compare' => 'EXISTS',
				),
				array(
					'key'     => '_it_exchange_customer_member_access',
					'compare' => '!=',
					'value'   => 'a:0:{}' // empty serialized array
				)
			);
		}

		if ( '' !== $args['search'] ) {
			$args['search'] = '*' . $args['search'] . '*';
		}

		if ( isset( $_REQUEST['orderby'] ) ) {
			$args['orderby'] = $_REQUEST['orderby'];
		}

		if ( isset( $_REQUEST['order'] ) ) {
			$args['order'] = $_REQUEST['order'];
		}

		// Query the user IDs for this page
		$wp_user_search = new WP_User_Query( $args );

		$this->items = $wp_user_search->get_results();

		$this->set_pagination_args( array(
			'total_items' => $wp_user_search->get_total(),
			'per_page'    => $users_per_page,
		) );
	}

	/**
	 * Output 'no users' message.
	 *
	 * @since  CHANGEME
	 * @access public
	 */
	public function no_items() {
		_e( 'No matching users were found.' );
	}

	/**
	 * Get a list of columns for the list table.
	 *
	 * @since  CHANGEME
	 * @access public
	 *
	 * @return array Array in which the key is the ID of the column,
	 *               and the value is the description.
	 */
	public function get_columns() {
		$c = array(
			'username'   => __( 'Username', 'LION' ),
			'name'       => __( 'Name', 'LION' ),
			'email'      => __( 'E-mail', 'LION' ),
			'membership' => __( 'Membership', 'LION' ),
		);

		return $c;
	}

	/**
	 * Get a list of sortable columns for the list table.
	 *
	 * @since  CHANGEME
	 * @access public
	 *
	 * @return array Array of sortable columns.
	 */
	public function get_sortable_columns() {
		$c = array(
			'username'   => 'login',
			'name'       => 'name',
			'email'      => 'email',
			'membership' => 'membership',
		);

		return $c;
	}

	/**
	 * Generate the list table rows.
	 *
	 * @since  CHANGEME
	 * @access public
	 */
	public function display_rows() {

		$style = '';
		foreach ( $this->items as $user_object ) {
			$style = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
			echo "\n\t" . $this->single_row( $user_object, $style );
		}
	}

	/**
	 * Generate HTML for a single row on the users.php admin panel.
	 *
	 * @since  CHANGEME
	 * @access public
	 *
	 * @param object $user_object The current user object.
	 * @param string $style       Optional. Style attributes added to the <tr> element.
	 *                            Must be sanitized. Default empty.
	 *
	 * @return string Output for a single row.
	 */
	public function single_row( $user_object, $style = '' ) {
		if ( ! ( is_object( $user_object ) && is_a( $user_object, 'WP_User' ) ) ) {
			$user_object = get_userdata( (int) $user_object );
		}

		$user_object->filter = 'display';
		$email               = $user_object->user_email;
		$avatar              = get_avatar( $user_object->ID, 32 );

		// Check if the user for this row is editable
		if ( current_user_can( 'list_users' ) ) {
			// Set up the user editing link
			$edit_link = esc_url( add_query_arg( 'wp_http_referer', urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ), get_edit_user_link( $user_object->ID ) ) );

			// Set up the hover actions for this user
			$actions = array();

			if ( current_user_can( 'edit_user', $user_object->ID ) ) {
				$edit            = "<strong><a href=\"$edit_link\">$user_object->user_login</a></strong><br />";
				$actions['edit'] = '<a href="' . $edit_link . '">' . __( 'Edit', 'LION' ) . '</a>';
			} else {
				$edit = "<strong>$user_object->user_login</strong><br />";
			}

			/**
			 * Filter the action links displayed under each user in the Users list table.
			 *
			 * @since 2.8.0
			 *
			 * @param array   $actions     An array of action links to be displayed.
			 *                             Default 'Edit', 'Delete' for single site, and
			 *                             'Edit', 'Remove' for Multisite.
			 * @param WP_User $user_object WP_User object for the currently-listed user.
			 */
			$actions = apply_filters( 'user_row_actions', $actions, $user_object );
			$edit .= $this->row_actions( $actions );

		} else {
			$edit = '<strong>' . $user_object->user_login . '</strong>';
		}

		$r = "<tr id='user-$user_object->ID'$style>";

		list( $columns, $hidden ) = $this->get_column_info();

		foreach ( $columns as $column_name => $column_display_name ) {
			$class = "class=\"$column_name column-$column_name\"";

			$style = '';
			if ( in_array( $column_name, $hidden ) ) {
				$style = ' style="display:none;"';
			}

			$attributes = "$class$style";

			switch ( $column_name ) {
				case 'username':
					$r .= "<td $attributes>$avatar $edit</td>";
					break;
				case 'name':
					$r .= "<td $attributes>$user_object->first_name $user_object->last_name</td>";
					break;
				case 'email':
					$r .= "<td $attributes><a href='mailto:$email' title='" . esc_attr( sprintf( __( 'E-mail: %s', 'LION' ), $email ) ) . "'>$email</a></td>";
					break;
				case 'membership':

					$user_memberships = it_exchange_get_user_memberships( it_exchange_get_customer( $user_object->ID ) );

					$membership_labels = array();

					foreach ( $user_memberships as $user_membership ) {

						$product = $user_membership->get_membership();

						if ( $product ) {
							$title = get_the_title( $product->ID );
						} else {
							$title = __( 'Missing Membership', 'LION' );
						}

						if ( $expires = $user_membership->get_end_date() ) {

							$now = new DateTime();

							if ( $expires < $now ) {
								$expires = sprintf( __( 'Expired %s', 'LION' ), $expires->format( get_option( 'date_format' ) ) );
							} else {
								$expires = sprintf( __( 'Expires %s', 'LION' ), $expires->format( get_option( 'date_format' ) ) );
							}
						} else {
							$expires = __( 'Forever', 'LION' );
						}

						if ( $user_membership->is_auto_renewing() ) {
							$expires .= ' (auto-renewing)';
						}

						$membership_labels[] = "$title <span class='tip' title='$expires'>i</span>";
					}

					$r .= "<td $attributes>" . join( ', ', $membership_labels ) . "</td>";
					break;
				default:
					$r .= "<td $attributes>";

					/**
					 * Filter the display output of custom columns in the Users list table.
					 *
					 * @since 2.8.0
					 *
					 * @param string $output      Custom column output. Default empty.
					 * @param string $column_name Column name.
					 * @param int    $user_id     ID of the currently-listed user.
					 */
					$r .= apply_filters( 'manage_users_page_it-exchange-members-table_custom_column', '', $column_name, $user_object->ID );
					$r .= "</td>";
			}
		}
		$r .= '</tr>';

		return $r;
	}

	/**
	 * Extra controls to be displayed between bulk actions and pagination
	 *
	 * @since  3.1.0
	 * @access protected
	 *
	 * @param string $which
	 */
	protected function extra_tablenav( $which ) {

		if ( $which !== 'top' ) {
			return;
		}

		$member_products = it_exchange_get_products( array(
			'product_type' => 'membership-product-type',
			'numberposts'  => - 1,
			'meta_query'   => array(
				array(
					'key'     => '_it_exchange_transaction_id',
					'compare' => 'EXISTS'
				)
			),
			'show_hidden' => true
		) );

		$current = empty( $_GET['membership'] ) ? false : $_GET['membership'];
		?>

		<label class="screen-reader-text" for="filter-by-membership">
			<?php _e( 'Filter members table by membership.', 'LION' ); ?>
		</label>
		<select name="membership" id="filter-by-membership">

			<option value=""><?php _e( 'All Memberships', 'LION' ); ?></option>

			<?php foreach ( $member_products as $product ): ?>
				<option value="<?php echo esc_attr( $product->ID ); ?>" <?php selected( $product->ID, $current ); ?>>
					<?php echo $product->post_title; ?>
				</option>
			<?php endforeach; ?>
		</select>

		<?php

		submit_button( __( 'Filter' ), 'button', 'filter_action', false, array( 'id' => 'members-query-submit' ) );
	}

	/**
	 * Display the table
	 *
	 * @since  3.1.0
	 * @access public
	 */
	public function display() {
		$singular = $this->_args['singular'];

		$this->display_tablenav( 'top' );

		?>
		<table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
			<thead>
			<tr>
				<?php $this->print_column_headers(); ?>
			</tr>
			</thead>

			<tfoot>
			<tr>
				<?php $this->print_column_headers( false ); ?>
			</tr>
			</tfoot>

			<tbody id="the-list"<?php
			if ( $singular ) {
				echo " data-wp-lists='list:$singular'";
			} ?>>
			<?php $this->display_rows_or_placeholder(); ?>
			</tbody>
		</table>
		<?php
		$this->display_tablenav( 'bottom' );
	}

}
