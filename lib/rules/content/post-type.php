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
class IT_Exchange_Membership_Content_Rule_Post_Type implements IT_Exchange_Membership_Content_RuleInterface {

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
	 * @param WP_Post                  $post
	 * @param IT_Exchange_Subscription $subscription
	 *
	 * @return bool True if readable
	 */
	public function evaluate( WP_Post $post, IT_Exchange_Subscription $subscription ) {
		// TODO: Implement evaluate() method.
	}

	/**
	 * Get HTML to render the necessary form fields.
	 *
	 * @since    1.18
	 *
	 * @param string $context Context to preface field name attributes.
	 *
	 * @param array  $data
	 *
	 * @return string
	 * @internal param IT_Exchange_Membership|null $membership
	 */
	public function get_field_html( $context, array $data = array() ) {

		$hidden = apply_filters( 'it_exchange_membership_addon_hidden_post_types', array(
			'attachment',
			'nav_menu_item',
			'it_exchange_prod',
			'page'
		) );

		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		$selected = empty( $data['term'] ) ? false : $data['term'];

		ob_start();
		?>

		<label for="<?php echo $context; ?>-post-types" class="screen-reader-text">
			<?php _e( 'Select a post type to hide.', 'LION' ); ?>
		</label>

		<select class="it-exchange-membership-content-type-term" id="<?php echo $context; ?>-post-types" name="<?php echo $context; ?>[term]">

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
		return 'post_type';
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
		return __( 'Post Type', 'LION' );
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
}