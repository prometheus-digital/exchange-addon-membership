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
	 * @var IT_Exchange_Membership_Delay_RuleInterface[]
	 */
	private $delay_rules = array();

	/**
	 * @var array
	 */
	protected $data = array();

	/**
	 * IT_Exchange_Membership_AbstractContent_Rule constructor.
	 *
	 * @param array $data
	 */
	public function __construct( array $data = array() ) {
		$this->data = $data;
	}

	/**
	 * Add a delay rule to this content rule.
	 *
	 * @since 1.18
	 *
	 * @param IT_Exchange_Membership_Delay_RuleInterface $delay_rule
	 *
	 * @return self
	 */
	public function add_delay_rule( IT_Exchange_Membership_Delay_RuleInterface $delay_rule ) {
		$this->delay_rules[] = $delay_rule;

		return $this;
	}

	/**
	 * Get all the delay rules.
	 *
	 * @since 1.18
	 *
	 * @return IT_Exchange_Membership_Delay_RuleInterface[]
	 */
	public function get_delay_rules() {
		return $this->delay_rules;
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

		foreach ( $this->get_delay_rules() as $delay_rule ) {
			if ( ! $delay_rule->evaluate( $subscription, $post ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check if a post is exempt from this rule.
	 *
	 * This is mainly used for global rules like taxonomies.
	 *
	 * @since 1.18
	 *
	 * @param WP_Post                $post
	 * @param IT_Exchange_Membership $membership
	 *
	 * @return bool
	 */
	public function is_post_exempt( WP_Post $post, IT_Exchange_Membership $membership ) {
		return (bool) get_post_meta( $post->ID, $this->get_exemption_meta_key( $membership ), true );
	}

	/**
	 * Set a given post to be exempt from this content rule.
	 *
	 * @since 1.18
	 *
	 * @param WP_Post                $post
	 * @param IT_Exchange_Membership $membership
	 * @param bool                   $exempt
	 */
	public function set_post_exempt( WP_Post $post, IT_Exchange_Membership $membership, $exempt = true ) {
		update_post_meta( $post->ID, $this->get_exemption_meta_key( $membership ), (bool) $exempt );
	}

	/**
	 * Get the exemption meta key.
	 *
	 * @since 1.18
	 *
	 * @param IT_Exchange_Membership $membership
	 *
	 * @return string
	 */
	protected function get_exemption_meta_key( IT_Exchange_Membership $membership ) {
		return "_rule-exemption-{$this->get_type()}-{$this->get_term()}-{$membership->ID}";
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
}