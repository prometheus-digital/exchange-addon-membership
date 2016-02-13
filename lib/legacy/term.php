<?php
/**
 * Contains the post content rule.
 *
 * @since   1.18
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Membership_Content_Rule_Term
 *
 * Legacy rule for WP 4.4 and lower.
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

		if ( empty( $this->data['selection'] ) ) {
			$this->data['selection'] = $taxonomy;
		}
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

		$r2 = true;

		if ( $r1 = parent::save( $data ) ) {

			$rule = $this->data;

			if ( ! ( $rules = get_option( '_item-content-rule-tax-' . $rule['selection'] . '-' . $rule['term'] ) ) ) {
				$rules = array();
			}

			if ( ! in_array( $this->get_membership()->ID, $rules ) ) {

				/**
				 * Fires when a term is added to the protection rule.
				 *
				 * @since 1.9
				 *
				 * @param int   $product_id
				 * @param int   $term_id
				 * @param array $rule
				 */
				do_action( 'it_exchange_membership_add_taxonomy_rule', $this->get_membership()->ID, $this->get_term(), $rule );

				$rules[] = $this->get_membership()->ID;

				$r2 = update_option( '_item-content-rule-tax-' . $rule['selection'] . '-' . $rule['term'], $rules );
			}
		}

		return $r1 && $r2;
	}

	/**
	 * Delete the rule from the database.
	 *
	 * @since 1.18
	 *
	 * @return bool
	 */
	public function delete() {

		$r1 = true;
		$r2 = parent::delete();

		$rule = $this->data;

		if ( ! ( $rules = get_option( '_item-content-rule-tax-' . $rule['selection'] . '-' . $rule['term'] ) ) ) {
			$rules = array();
		}

		if ( false !== $key = array_search( $this->get_membership()->ID, $rules ) ) {

			/**
			 * Fires when a term is removed from the protection rules.
			 *
			 * @since 1.0
			 *
			 * @param int   $product_id
			 * @param int   $term_id
			 * @param array $rule
			 */
			do_action( 'it_exchange_membership_remove_taxonomy_rule', $this->get_membership()->ID, $this->get_term(), $rule );

			unset( $rules[ $key ] );
			if ( empty( $rules ) ) {
				$r1 = delete_option( '_item-content-rule-tax-' . $rule['selection'] . '-' . $rule['term'] );
			} else {
				$r1 = update_option( '_item-content-rule-tax-' . $rule['selection'] . '-' . $rule['term'], $rules );
			}
		}

		return $r1 && $r2;
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