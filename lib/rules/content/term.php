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
class IT_Exchange_Membership_Content_Rule_Term extends IT_Exchange_Membership_AbstractContent_Rule implements IT_Exchange_Membership_Rule_Layoutable {

	/**
	 * @var string
	 */
	private $taxonomy;

	/**
	 * IT_Exchange_Membership_Content_Rule_Post constructor.
	 *
	 * @param string                 $taxonomy
	 * @param IT_Exchange_Membership $membership
	 * @param array                  $data
	 */
	public function __construct( $taxonomy, IT_Exchange_Membership $membership = null, array $data = array() ) {
		parent::__construct( $membership, $data );

		$this->taxonomy = $taxonomy;
	}

	/**
	 * Get the layout for this rule.
	 *
	 * @since 1.18
	 *
	 * @return string
	 */
	public function get_layout() {
		return isset( $this->data['group_layout'] ) ? $this->data['group_layout'] : self::L_GRID;
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

		$terms = wp_get_object_terms( $post->ID, get_object_taxonomies( get_post_type( $post ) ), array( 'fields' => 'ids' ) );

		return in_array( $this->get_term(), $terms );
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

		$query = new WP_Query( array(
			'posts_per_page' => $number,
			'tax_query'      => array(
				array(
					'taxonomy' => $this->get_selection(),
					'field'    => 'id',
					'terms'    => $this->get_term()
				)
			)
		) );

		return $query->get_posts();
	}

	/**
	 * Get the more content URL.
	 *
	 * @since 1.1.8
	 *
	 * @return string
	 */
	public function get_more_content_url() {

		$url = get_term_link( (int) $this->get_term() );

		if ( is_wp_error( $url ) ) {
			return '';
		}

		return $url;
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
	 * @param bool $label
	 *
	 * @return string
	 */
	public function get_selection( $label = false ) {
		return $label ? get_taxonomy( $this->taxonomy )->label : $this->taxonomy;
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
		return sprintf( "%s '%s'", $this->get_selection( true ), get_term( $this->data['term'] )->name );
	}
}