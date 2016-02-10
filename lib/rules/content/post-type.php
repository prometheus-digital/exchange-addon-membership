<?php
/**
 * Contains the post type content rule.
 *
 * @since   1.18
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Membership_Content_Rule_Post_Type
 */
class IT_Exchange_Membership_Content_Rule_Post_Type extends IT_Exchange_Membership_AbstractContent_Rule implements IT_Exchange_Membership_Rule_Layoutable {

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
		return $this->get_term() === get_post_type( $post );
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
			'post_type'      => $this->get_term(),
			'posts_per_page' => $number
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

		if ( $this->get_term() === 'posts' ) {
			return get_home_url();
		} else {
			return get_post_type_archive_link( $this->get_term() );
		}
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

		$hidden = apply_filters( 'it_exchange_membership_addon_hidden_post_types', array(
			'attachment',
			'it_exchange_prod',
			'page'
		) );

		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		$selected   = empty( $data['term'] ) ? false : $data['term'];

		ob_start();
		?>

		<label for="<?php echo $context; ?>-post-types" class="screen-reader-text">
			<?php _e( 'Select a post type to hide.', 'LION' ); ?>
		</label>

		<select class="it-exchange-membership-content-type-post-type" id="<?php echo $context; ?>-post-types" name="<?php echo $context; ?>[term]">

			<?php foreach ( $post_types as $post_type ): ?>
				<?php if ( ! in_array( $post_type->name, $hidden ) ): ?>
					<option value="<?php echo $post_type->name ?>" <?php selected( $post_type->name, $selected ); ?>>
						<?php echo $post_type->label; ?>
					</option>
				<?php endif; ?>
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

			if ( ! ( $rules = get_option( '_item-content-rule-post-type-' . $rule['term'] ) ) ) {
				$rules = array();
			}

			if ( ! in_array( $this->get_membership()->ID, $rules ) ) {

				/**
				 * Fires when a post type is added to the protection rules.
				 *
				 * @since 1.9
				 *
				 * @param int    $product_id
				 * @param string $post_type
				 * @param array  $rule
				 */
				do_action( 'it_exchange_membership_add_post_type_rule', $this->get_membership()->ID, $this->get_term(), $rule );

				$rules[] = $this->get_membership()->ID;

				$r2 = update_option( '_item-content-rule-post-type-' . $rule['term'], $rules );
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

		if ( ! ( $rules = get_option( '_item-content-rule-post-type-' . $rule['term'] ) ) ) {
			$rules = array();
		}

		if ( false !== $key = array_search( $this->get_membership()->ID, $rules ) ) {

			/**
			 * Fires when a post type is removed from the protection rules.
			 *
			 * @since 1.0
			 *
			 * @param int    $product_id
			 * @param string $post_type
			 * @param array  $rule
			 */
			do_action( 'it_exchange_membership_remove_post_type_rule', $this->get_membership()->ID, $this->get_term(), $rule );

			unset( $rules[ $key ] );
			if ( empty( $rules ) ) {
				delete_option( '_item-content-rule-post-type-' . $rule['term'] );
			} else {
				update_option( '_item-content-rule-post-type-' . $rule['term'], $rules );
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
		return $label ? __( 'Post Type', 'LION' ) : 'post_type';
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
		return $label ? __( 'Post Type', 'LION' ) : 'post_types';
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
		return get_post_type_object( $this->data['term'] )->label;
	}
}