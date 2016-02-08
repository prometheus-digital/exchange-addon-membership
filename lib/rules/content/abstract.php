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
abstract class IT_Exchange_Membership_AbstractContent_Rule implements IT_Exchange_Membership_Content_RuleInterface {

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
	}

	/**
	 * Evaluate the rule.
	 *
	 * @since 1.18
	 *
	 * @param IT_Exchange_Subscription $subscription
	 * @param WP_Post                  $post
	 *
	 * @return bool True if readable
	 */
	public function evaluate( IT_Exchange_Subscription $subscription, WP_Post $post ) {
		return ! $this->is_post_exempt( $post );
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