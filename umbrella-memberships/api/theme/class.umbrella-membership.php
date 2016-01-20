<?php
/**
 * it_exchange() API for umbrella memberships.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2015 (c) Iron Bound Designs.
 * @license     GPLv2
 */

/**
 * Class IT_Theme_API_Umbrella_Membership
 */
class IT_Theme_API_Umbrella_Membership implements IT_Theme_API {

	/**
	 * @var IT_Exchange_Product
	 */
	private $product;

	/**
	 * Total seats available.
	 *
	 * @var int
	 */
	private $seats = 0;
	/**
	 * @var \ITEGMS\Relationship\Relationship[]
	 */
	private $relationships = array();

	/**
	 * @var array
	 */
	public $_tag_map = array(
		'seats'   => 'seats',
		'members' => 'members'
	);

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->product = it_exchange_membership_addon_get_current_membership();

		if ( ! $this->product || ! $this->product instanceof IT_Exchange_Product ) {
			return;
		}

		$cid = it_exchange_get_current_customer_id();

		$purchase_query = new \ITEGMS\Purchase\Purchase_Query( array(
			'customer'   => $cid,
			'membership' => $this->product->ID,
			'active'     => true
		) );

		/** @var \ITEGMS\Purchase\Purchase[] $purchases */
		$purchases = $purchase_query->get_results();

		$purchase_ids = array();

		foreach ( $purchases as $purchase ) {
			$this->seats += $purchase->get_seats();
			$purchase_ids[] = $purchase->get_pk();
		}

		if ( empty( $purchase_ids ) ) {
			return;
		}

		$rel_query = new \ITEGMS\Relationship\Relationship_Query( array(
			'purchase__in' => $purchase_ids
		) );

		$this->relationships = array_values( $rel_query->get_results() );
	}

	/**
	 * Get the API context.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public function get_api_context() {
		return 'umbrella-membership';
	}

	/**
	 * Get info about the number of seats purchased.
	 *
	 * @since 1.0
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function seats( $options = array() ) {

		$defaults = array(
			'format' => 'html',
			'label'  => __( "Seats", \ITEGMS\Plugin::SLUG )
		);
		$options  = ITUTility::merge_defaults( $options, $defaults );

		$label = $options['label'];
		$seats = $this->seats;
		$used  = count( $this->relationships );

		$value = "{$used} &frasl; {$seats}";

		switch ( $options['format'] ) {

			case 'label':
				return $label;
			case 'value':
				return $value;
			case 'html':
			default:
				$html = "<span class='it-exchange-umbrella-membership-seats'>";
				$html .= "{$label}: {$value}";
				$html .= "</span>";

				return $html;
		}
	}

	/**
	 * Display the list of child members.
	 *
	 * @since 1.0
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function members( $options = array() ) {

		$defaults = array(
			'per_page' => 20
		);

		$options = ITUtility::merge_defaults( $options, $defaults );

		$per_page = absint( $options['per_page'] );

		/**
		 * Filters the number of members shown per page on the membership dashboard.
		 *
		 * @since 1.0
		 *
		 * @param int                  $per_page
		 * @param \IT_Exchange_Product $product
		 */
		$per_page = apply_filters( 'itegms_membership_dashboard_members_list_per_page', $per_page, $this->product );

		$page_slug       = 'memberships';
		$permalinks      = (bool) get_option( 'permalink_structure' );
		$membership_slug = $this->product->post_name;

		if ( $permalinks ) {
			$url = trailingslashit( it_exchange_get_page_url( $page_slug ) ) . $membership_slug;
		} else {
			$url = it_exchange_get_page_url( $page_slug ) . '=' . $membership_slug;
		}

		$html = '';

		if ( count( $this->relationships ) > $this->seats ) {

			$members = count( $this->relationships );
			$seats   = $this->seats;

			$warning = _n( "Warning: You have more one member than membership seats available. Please purchase a larger membership plan.",
				"Warning: You have %d more members than membership seats available. Please purchase a larger membership plan.",
				$members - $seats, \ITEGMS\Plugin::SLUG );

			$warning = sprintf( $warning, $members - $seats );

			/**
			 * Filters the error displayed if a team leader has more members registered, than seats available.
			 *
			 * @since 1.0
			 *
			 * @param string               $warning
			 * @param \IT_Exchange_Product $product
			 * @param int                  $members Total number of members registered.
			 * @param int                  $seats   Total number of seats purchased.
			 */
			$warning = apply_filters( 'itegms_membership_dashboard too_many_members', $warning, $this->product, $members, $seats );

			if ( trim( $warning ) !== '' ) {
				$html .= '<ul class="it-exchange-messages it-exchange-errors" style="padding-top: 5px;"><li>';
				$html .= $warning;
				$html .= '</li></ul>';
			}
		}

		$html .= "<form method='POST' action='{$url}' class='itegms-member-form'>";

		$html .= '<h4>' . __( "Members", \ITEGMS\Plugin::SLUG ) . '</h4>';

		$description = __( "To add a member, simply input their name and email address in the list below.", \ITEGMS\Plugin::SLUG );
		$description .= ' ' . __( "To remove a member, simply press the &times; next to their name in the list below.", \ITEGMS\Plugin::SLUG );
		$description .= ' ' . __( "Be sure to save your changes using the 'Save Members' button at the bottom of the list.", ITEGMS\Plugin::SLUG );

		/**
		 * Filters the description displayed above the members table
		 * on the membership dashboard.
		 *
		 * @since 1.0
		 *
		 * @param string               $description
		 * @param \IT_Exchange_Product $product
		 */
		$description = apply_filters( 'itegms_members_table_description', $description, $this->product );

		$html .= '<div class="it-exchange-hidden" id="itegms-members-list">';

		$html .= "<p>$description</p>";

		$html .= '<div class="itegms-list">';

		$html .= '<ul class="pages">';

		$html .= $this->generate_members_list( $per_page );

		$html .= '</ul>';

		$html .= '</div>';

		$html .= $this->generate_pagination( $per_page );

		$label = __( "Save Members", \ITEGMS\Plugin::SLUG );
		$html .= "<input type=\"submit\" name='itegms-save-members' value=\"$label\">";

		$html .= '</div>';

		$cid = it_exchange_get_current_customer_id();
		$html .= wp_nonce_field( "itegms-save-{$cid}-members", '_wpnonce', false, false );

		$html .= "<input type='hidden' name='itegms_prod' value='{$this->product->ID}'>";

		$html .= '</form>';

		return $html;
	}

	/**
	 * Generate the members list.
	 *
	 * @since 1.0
	 *
	 * @param int $per_page
	 *
	 * @return string
	 */
	protected function generate_members_list( $per_page ) {

		$number_on_current_page = 0;

		$page = 1;

		$html = '';

		for ( $i = 0; $i < $this->seats; $i ++ ) {

			if ( $number_on_current_page == 0 ) {

				$hidden  = $page == 1 ? '' : 'itegms-page-hidden';
				$current = $page == 1 ? 'itegms-page-current' : '';

				$html .= "<li class=\"itegms-page itegms-page-$page $hidden $current\">";
				$html .= '<ul class="members">';
			}

			$html .= $this->generate_member( $i );

			$number_on_current_page += 1;

			if ( $number_on_current_page == $per_page || $i == ( $this->seats - 1 ) ) {
				$html .= '</ul>';
				$html .= '</li>';

				$number_on_current_page = 0;
				$page += 1;
			}
		}

		return $html;
	}

	/**
	 * Generate the HTML for a single member row.
	 *
	 * @since 1.0
	 *
	 * @param int $i
	 *
	 * @return string
	 */
	protected function generate_member( $i ) {

		$html = '';

		$html .= '<li>';

		$html .= '<div class="itegms-member-name">';

		$html .= "<label for='itegms-member-{$i}-name'>";
		$html .= sprintf( __( "Member %d Name", \ITEGMS\Plugin::SLUG ), $i + 1 );
		$html .= "</label>";

		if ( isset( $this->relationships[ $i ] ) ) {
			$rel      = $this->relationships[ $i ];
			$val      = $rel->get_member()->wp_user->display_name;
			$readonly = 'readonly';
		} else {
			$val      = '';
			$readonly = '';
		}

		$html .= "<input type='text' name='itegms_member[{$i}][name]' $readonly id='itegms-member-{$i}-name' value='{$val}'>";

		$html .= '</div>';

		$html .= '<div class="itegms-member-email">';

		$html .= "<label for='itegms-member-{$i}-email'>";
		$html .= sprintf( __( "Member %d Email", \ITEGMS\Plugin::SLUG ), $i + 1 );
		$html .= "</label>";

		if ( isset( $this->relationships[ $i ] ) ) {
			$rel      = $this->relationships[ $i ];
			$val      = $rel->get_member()->wp_user->user_email;
			$readonly = 'readonly';
		} else {
			$val      = '';
			$readonly = '';
		}

		$html .= "<input type='email' name='itegms_member[{$i}][email]' $readonly id='itegms-member-{$i}-email' value='{$val}'>";

		$html .= '</div>';

		$title = __( "Remove", \ITEGMS\Plugin::SLUG );
		$html .= '<div class="itegms-remove-member-container">';
		$html .= '<label>&nbsp;</label>';

		$delete = sprintf( __( "Delete Member %d", \ITEGMS\Plugin::SLUG ), $i );

		$html .= "<a href=\"javascript:\" aria-label='$delete' class='itegms-remove-member' data-id=\"$i\" title=\"$title\">&times;</a>";
		$html .= '</div>';

		$html .= '</li>';

		return $html;
	}


	/**
	 * Generate the pagination.
	 *
	 * @since 1.0
	 *
	 * @param int $per_page
	 *
	 * @return string
	 */
	protected function generate_pagination( $per_page ) {

		$pages = ceil( $this->seats / $per_page );

		if ( $pages == 1 ) {
			return '';
		}

		$html = '<ul class="itegms-pagination">';

		for ( $i = 1; $i <= $pages; $i ++ ) {

			$disabled = $i == 1 ? 'disabled' : '';

			$html .= '<li>';
			$html .= "<button data-page=\"$i\" $disabled>$i</>";
			$html .= '</li>';
		}

		$html .= '</ul>';

		return $html;
	}
}