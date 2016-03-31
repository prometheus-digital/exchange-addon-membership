<?php
/**
 * Membership class.
 *
 * @since   1.18
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Membership
 *
 * @since 1.18
 */
class IT_Exchange_Membership extends IT_Exchange_Product {

	/**
	 * IT_Exchange_Membership constructor.
	 *
	 * @param WP_Post|int $post
	 */
	public function __construct( $post ) {
		parent::__construct( $post );

		if ( $this->product_type !== 'membership-product-type' ) {
			throw new InvalidArgumentException( 'Invalid product type.' );
		}
	}

	/**
	 * Get all the children of this membership.
	 *
	 * @since 1.18
	 *
	 * @return IT_Exchange_Membership[]
	 */
	public function get_children() {

		$ids = it_exchange_membership_addon_get_all_the_children( $this->ID );

		return array_map( 'it_exchange_get_product', $ids );
	}

	/**
	 * Get all the parents of this membership.
	 *
	 * @since 1.18
	 *
	 * @return IT_Exchange_Membership[]
	 */
	public function get_parents() {

		$ids = it_exchange_membership_addon_get_all_the_parents( $this->ID );

		return array_map( 'it_exchange_get_product', $ids );
	}

	/**
	 * Get the URL to the dashboard.
	 * 
	 * @since 1.19.11
	 * 
	 * @return string
	 */
	public function get_dashboard() {

		$page_slug       = 'memberships';
		$permalinks      = (bool) get_option( 'permalink_structure' );
		$membership_slug = $this->post_name;

		if ( $permalinks ) {
			$url = trailingslashit( it_exchange_get_page_url( $page_slug ) ) . $membership_slug;
		} else {
			$url = it_exchange_get_page_url( $page_slug ) . '=' . $membership_slug;
		}
		
		return $url;
	}

}