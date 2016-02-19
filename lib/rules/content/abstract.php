<?php
/**
 * Abstract rule class.
 *
 * @since   1.18
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Membership_AbstractContent_Rule
 */
abstract class IT_Exchange_Membership_AbstractContent_Rule implements IT_Exchange_Membership_Content_Rule {

	/**
	 * @var IT_Exchange_Membership
	 */
	private $membership;

	/**
	 * @var array
	 */
	protected $data = array();

	/**
	 * IT_Exchange_Membership_AbstractContent_Rule constructor.
	 *
	 * @param IT_Exchange_Membership $membership
	 * @param array                  $data
	 */
	public function __construct( IT_Exchange_Membership $membership = null, array $data = array() ) {
		$this->membership = $membership;
		$this->data       = $data;

		if ( empty( $this->data['selected'] ) ) {
			$this->data['selected'] = $this->get_type();
		}
	}

	/**
	 * Evaluate the rule.
	 *
	 * @since    1.18
	 *
	 * @param IT_Exchange_User_Membership $user_membership
	 * @param WP_Post                     $post
	 *
	 * @return bool True if readable
	 */
	public function evaluate( IT_Exchange_User_Membership $user_membership, WP_Post $post ) {
		return $this->matches_post( $post );
	}

	/**
	 * Check if a post is exempt from this rule.
	 *
	 * This is mainly used for global rules like taxonomies.
	 *
	 * @since 1.18
	 *
	 * @param WP_Post $post
	 *
	 * @return bool
	 */
	public function is_post_exempt( WP_Post $post ) {
		return (bool) get_post_meta( $post->ID, $this->get_exemption_meta_key(), true );
	}

	/**
	 * Set a given post to be exempt from this content rule.
	 *
	 * @since 1.18
	 *
	 * @param WP_Post $post
	 * @param bool    $exempt
	 */
	public function set_post_exempt( WP_Post $post, $exempt = true ) {
		update_post_meta( $post->ID, $this->get_exemption_meta_key(), (bool) $exempt );
	}

	/**
	 * Save the data.
	 *
	 * @since 1.18
	 *
	 * @param array $data
	 *
	 * @return bool
	 *
	 * @throws UnexpectedValueException
	 * @throws InvalidArgumentException
	 */
	public function save( array $data = array() ) {

		if ( ! $this->get_membership() ) {
			throw new UnexpectedValueException( 'Constructed with null IT_Exchange_Membership' );
		}

		$id = $this->get_rule_id();

		$this->data = array_filter( array_merge( $this->data, $data ) );

		$access = $this->get_membership()->get_feature( 'membership-content-access-rules' );

		if ( ! $id ) {
			$this->data['id'] = md5( serialize( $this->data ) . uniqid() );

			$access[] = $this->data;
		} else {
			foreach ( $access as $key => $rule ) {
				if ( isset( $rule['id'] ) && $rule['id'] === $id ) {

					$access[ $key ] = array_filter( array_merge( $rule, $data ) );

					break;
				}
			}
		}

		$this->get_membership()->update_feature( 'membership-content-access-rules', $access );

		return true;
	}

	/**
	 * Delete the rule from the database.
	 *
	 * @since 1.18
	 *
	 * @return bool
	 */
	public function delete() {

		if ( ! $this->get_membership() ) {
			throw new UnexpectedValueException( 'Constructed with null IT_Exchange_Membership' );
		}

		$id = $this->get_rule_id();

		if ( ! $id ) {
			return false;
		}

		$access = $this->get_membership()->get_feature( 'membership-content-access-rules' );

		foreach ( $access as $key => $rule ) {
			if ( isset( $rule['id'] ) && $rule['id'] === $id ) {
				unset( $access[ $key ] );

				break;
			}
		}

		$this->get_membership()->update_feature( 'membership-content-access-rules', $access );

		$this->delete_exemptions();

		return true;
	}

	/**
	 * Delete all exemptions from this rule.
	 *
	 * @since 1.18
	 */
	private function delete_exemptions() {

		$key = $this->get_exemption_meta_key();

		/** @var $wpdb wpdb */
		global $wpdb;

		$mids = $wpdb->get_results( "SELECT meta_id FROM $wpdb->postmeta WHERE meta_key = '$key'" );

		foreach ( $mids as $mid ) {
			delete_metadata_by_mid( 'post', $mid->meta_id );
		}
	}

	/**
	 * Get the unique ID for this rule.
	 *
	 * @since 1.18
	 *
	 * @return string
	 */
	public function get_rule_id() {
		return isset( $this->data['id'] ) ? $this->data['id'] : null;
	}

	/**
	 * Get the exemption meta key.
	 *
	 * @since 1.18
	 *
	 * @return string
	 */
	protected function get_exemption_meta_key() {
		return "_rule-exemption-{$this->get_type()}-{$this->get_term()}-{$this->get_membership()->ID}";
	}

	/**
	 * Get the term for this rule.
	 *
	 * The term is what differentiates each rule. For example the post ID, post type, or term ID.
	 *
	 * @since 1.18
	 *
	 * @return string|int|null
	 */
	public function get_term() {
		return isset( $this->data['term'] ) ? $this->data['term'] : null;
	}

	/**
	 * Get this rule's membership.
	 *
	 * @since 1.18
	 *
	 * @return IT_Exchange_Membership|null
	 */
	public function get_membership() {
		return $this->membership;
	}
}