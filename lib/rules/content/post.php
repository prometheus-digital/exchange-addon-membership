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
class IT_Exchange_Membership_Content_Rule_Post implements IT_Exchange_Membership_Content_RuleInterface {

	/**
	 * @var string
	 */
	private $post_type;

	/**
	 * @var array
	 */
	private $data;

	/**
	 * IT_Exchange_Membership_Content_Rule_Post constructor.
	 *
	 * @param string $post_type
	 * @param array  $data
	 */
	public function __construct( $post_type, array $data = array() ) {
		$this->post_type = $post_type;
		$this->data = $data;
	}

	/**
	 * Check if tis content type is groupable.
	 *
	 * @since 1.18
	 *
	 * @return bool
	 */
	public function is_groupable() {
		return false;
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
		// TODO: Implement evaluate() method.
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

		<select class="it-exchange-membership-content-type-term" id="<?php echo $context; ?>-post" name="<?php echo $context; ?>[term]">

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
	 * @return string
	 */
	public function get_value() {
		return $this->post_type;
	}

	/**
	 * Get the label this content rule instance represents.
	 *
	 * This is used to build the content access type dropdown.
	 *
	 * @since 1.18
	 *
	 * @return string
	 */
	public function get_label() {
		return get_post_type_object( $this->post_type )->label;
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
}