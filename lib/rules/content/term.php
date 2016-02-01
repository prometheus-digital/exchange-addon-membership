<?php
/**
 * Contains the post content rule.
 *
 * @since   1.18
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Membership_Content_Rule_Term
 */
class IT_Exchange_Membership_Content_Rule_Term implements IT_Exchange_Membership_Content_RuleInterface {

	/**
	 * @var string
	 */
	private $taxonomy;

	/**
	 * @var array
	 */
	private $data;

	/**
	 * IT_Exchange_Membership_Content_Rule_Post constructor.
	 *
	 * @param string $taxonomy
	 * @param array  $data
	 */
	public function __construct( $taxonomy, array $data = array() ) {
		$this->taxonomy = $taxonomy;
		$this->data     = $data;
	}

	/**
	 * Check if tis content type is groupable.
	 *
	 * @since 1.18
	 *
	 * @return bool
	 */
	public function is_groupable() {
		return true;
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

		/** @var WP_Term[] $terms */
		$terms    = get_terms( $this->taxonomy, array( 'hide_empty' => false ) );
		$selected = empty( $data['term'] ) ? false : $data['term'];

		ob_start();
		?>

		<label for="<?php echo $context; ?>-term" class="screen-reader-text">
			<?php _e( 'Select a term to restrict.', 'LION' ); ?>
		</label>

		<select class="it-exchange-membership-content-type-term" id="<?php echo $context; ?>-term" name="<?php echo $context; ?>[term]">

			<?php foreach ( $terms as $term ): ?>
				<option value="<?php echo $term->term_id; ?>" <?php selected( $term->term_id, $selected ); ?>>
					<?php echo $term->name; ?>
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
		return $this->taxonomy;
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
		return get_taxonomy( $this->taxonomy )->label;
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
		return $label ? __( 'Taxonomy', 'LION' ) : 'taxonomy';
	}
}