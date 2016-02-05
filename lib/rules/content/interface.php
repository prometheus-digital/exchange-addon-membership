<?php
/**
 * Contains rule interface.
 *
 * @since   1.18
 * @license GPLv2
 */

/**
 * Interface IT_Exchange_Membership_Content_RuleInterface
 */
interface IT_Exchange_Membership_Content_RuleInterface extends IT_Exchange_Membership_RuleInterface {

	/**
	 * Add a delay rule to this content rule.
	 *
	 * @since 1.18
	 *
	 * @param IT_Exchange_Membership_Delay_RuleInterface $delay_rule
	 *
	 * @return self
	 */
	public function add_delay_rule( IT_Exchange_Membership_Delay_RuleInterface $delay_rule );

	/**
	 * Get all the delay rules.
	 *
	 * @since 1.18
	 *
	 * @return IT_Exchange_Membership_Delay_RuleInterface[]
	 */
	public function get_delay_rules();

	/**
	 * Does this content rule support delay rules.
	 *
	 * @since 1.18
	 *
	 * @return bool
	 */
	public function supports_delay_rules();

	/**
	 * Check if this content rule matches a post.
	 *
	 * @since 1.18
	 *
	 * @param WP_Post $post
	 *
	 * @return bool
	 */
	public function matches_post( WP_Post $post );

	/**
	 * Get matching posts for this rule.
	 *
	 * @since 1.18
	 *
	 * @param int $number
	 *
	 * @return WP_Post[]
	 */
	public function get_matching_posts( $number = 5 );

	/**
	 * Get the more content URL.
	 *
	 * @since 1.1.8
	 *
	 * @return string
	 */
	public function get_more_content_url();

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
	public function is_post_exempt( WP_Post $post );

	/**
	 * Set a given post to be exempt from this content rule.
	 *
	 * @since 1.18
	 *
	 * @param WP_Post $post
	 * @param bool    $exempt
	 *
	 * @return
	 */
	public function set_post_exempt( WP_Post $post, $exempt = true );

	/**
	 * Get the value this content rule instance represents.
	 *
	 * This is used to build the content access type dropdown.
	 *
	 * @since 1.18
	 *
	 * @param bool $label
	 *
	 * @return string
	 */
	public function get_selection( $label = false );

	/**
	 * Get the short description for this rule.
	 *
	 * Ex. Category "Protected"
	 *
	 * @since 1.18
	 *
	 * @return string
	 */
	public function get_short_description();

	/**
	 * Get the term for this rule.
	 *
	 * The term is what differentiates each rule. For example the post ID, post type, or term ID.
	 *
	 * @since 1.18
	 *
	 * @return string|int|null
	 */
	public function get_term();

	/**
	 * Get this rule's membership.
	 *
	 * @since 1.18
	 *
	 * @return IT_Exchange_Membership|null
	 */
	public function get_membership();
}