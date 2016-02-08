<?php
/**
 * Contains the term content rule.
 *
 * @since   1.18
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Membership_Content_Rule_Post
 */
class IT_Exchange_Membership_Content_Rule_Post extends IT_Exchange_Membership_AbstractContent_Rule implements IT_Exchange_Membership_Content_Rule_Delayable {

	/**
	 * @var string
	 */
	private $post_type;

	/**
	 * @var IT_Exchange_Membership_Delay_RuleInterface
	 */
	private $delay_rule;

	/**
	 * IT_Exchange_Membership_Content_Rule_Post constructor.
	 *
	 * @param string                 $post_type
	 * @param IT_Exchange_Membership $membership
	 * @param array                  $data
	 */
	public function __construct( $post_type, IT_Exchange_Membership $membership = null, array $data = array() ) {
		parent::__construct( $membership, $data );

		$this->post_type = $post_type;
	}

	/**
	 * Set this content rule's delay rule.
	 *
	 * @since 1.18
	 *
	 * @param IT_Exchange_Membership_Delay_RuleInterface $delay_rule
	 *
	 * @return self
	 */
	public function set_delay_rule( IT_Exchange_Membership_Delay_RuleInterface $delay_rule ) {
		$this->delay_rule = $delay_rule;

		return $this;
	}

	/**
	 * Retrieve this content rule's delay rule.
	 *
	 * @since 1.18
	 *
	 * @return IT_Exchange_Membership_Delay_RuleInterface
	 */
	public function get_delay_rule() {
		return $this->delay_rule;
	}

	/**
	 * Check if this content rule matches a post.
	 *
	 * @since 1.18
	 *
	 * @param WP_Post $post
	 *
	 * @return bool
	 */
	public function matches_post( WP_Post $post ) {
		return $this->get_term() == $post->ID;
	}

	/**
	 * Get matching posts for this rule.
	 *
	 * @since 1.18
	 *
	 * @param int $number
	 *
	 * @return WP_Post[]
	 */
	public function get_matching_posts( $number = 5 ) {

		if ( ! $this->get_term() ) {
			return array();
		}

		return array( get_post( $this->get_term() ) );
	}

	/**
	 * Get the more content URL.
	 *
	 * @since 1.1.8
	 *
	 * @return string
	 */
	public function get_more_content_url() {
		return '';
	}

	/**
	 * Get HTML to render the necessary form fields.
	 *
	 * @since    1.18
	 *
	 * @param string $context Context to preface field name attributes.
	 *
	 * @return string
	 */
	public function get_field_html( $context ) {

		$data = $this->data;

		$posts = get_posts( array( 'post_type' => $this->post_type, 'posts_per_page' => - 1, 'post_status' => 'any' ) );

		$selected = empty( $data['term'] ) ? false : $data['term'];

		ob_start();
		?>

		<label for="<?php echo $context; ?>-post" class="screen-reader-text">
			<?php _e( 'Select a post to restrict.', 'LION' ); ?>
		</label>

		<select class="it-exchange-membership-content-type-post" id="<?php echo $context; ?>-post" name="<?php echo $context; ?>[term]">

			<?php foreach ( $posts as $post ): ?>
				<option value="<?php echo $post->ID; ?>" <?php selected( $post->ID, $selected ); ?>>
					<?php echo $post->post_title; ?>
				</option>
			<?php endforeach; ?>

		</select>

		<?php

		return ob_get_clean();
	}

	/**
	 * String representation of this rule.
	 *
	 * Ex. This content will be accessible in 5 days.
	 *
	 * @since 1.18
	 *
	 * @return string
	 */
	public function __toString() {
		return 'This content is for members only.';
	}

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
	public function get_selection( $label = false ) {
		return $label ? get_post_type_object( $this->post_type )->label : $this->post_type;
	}

	/**
	 * Get the type of this restriction.
	 *
	 * @since 1.18
	 *
	 * @param bool $label
	 *
	 * @return string
	 */
	public function get_type( $label = false ) {
		return $label ? __( 'Post Types', 'LION' ) : 'posts';
	}

	/**
	 * Get the short description for this rule.
	 *
	 * Ex. Category "Protected"
	 *
	 * @since 1.18
	 *
	 * @return string
	 */
	public function get_short_description() {
		return '';
	}
}