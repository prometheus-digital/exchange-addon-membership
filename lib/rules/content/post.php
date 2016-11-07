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
class IT_Exchange_Membership_Content_Rule_Post extends IT_Exchange_Membership_Base_Content_Rule implements IT_Exchange_Membership_Rule_Delayable {

	/**
	 * @var string
	 */
	private $post_type;

	/**
	 * @var IT_Exchange_Membership_Delay_Rule
	 */
	private $delay_rule;

	/**
	 * @var array
	 */
	private static $cache = array();

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

		if ( empty( $this->data['selection'] ) ) {
			$this->data['selection'] = $post_type;
		}
	}

	/**
	 * Set this content rule's delay rule.
	 *
	 * @since 1.18
	 *
	 * @param IT_Exchange_Membership_Delay_Rule $delay_rule
	 *
	 * @return self
	 */
	public function set_delay_rule( IT_Exchange_Membership_Delay_Rule $delay_rule ) {
		$this->delay_rule = $delay_rule;

		return $this;
	}

	/**
	 * Retrieve this content rule's delay rule.
	 *
	 * @since 1.18
	 *
	 * @return IT_Exchange_Membership_Delay_Rule
	 */
	public function get_delay_rule() {
		return $this->delay_rule;
	}

	/**
	 * Get delay meta for a given key.
	 *
	 * @since 1.18
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function get_delay_meta( $key ) {
		return get_post_meta( $this->get_term(), $key, true );
	}

	/**
	 * Update delay meta for a given key.
	 *
	 * @since 1.18
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return bool
	 */
	public function update_delay_meta( $key, $value ) {
		return update_post_meta( $this->get_term(), $key, $value );
	}

	/**
	 * Delete delay meta for a given key.
	 *
	 * @since 1.18
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function delete_delay_meta( $key ) {
		return delete_post_meta( $this->get_term(), $key );
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

		$post = get_post( $this->get_term() );

		if ( ! current_user_can( 'read_post', $post ) ) {
			return array();
		}

		return array( $post );
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

		if ( empty( self::$cache[ $this->post_type ] ) ) {

			$query = new WP_Query( array(
				'post_type'              => $this->post_type,
				'posts_per_page'         => - 1,
				'post_status'            => 'any',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false
			) );

			self::$cache[ $this->post_type ] = $query->posts;
		}

		$posts = self::$cache[ $this->post_type ];

		$selected = empty( $data['term'] ) ? false : $data['term'];

		ob_start();
		?>

		<label for="<?php echo $context; ?>-post" class="screen-reader-text">
			<?php _e( 'Select a post to restrict.', 'LION' ); ?>
		</label>

		<select class="it-exchange-membership-content-type-post" id="<?php echo $context; ?>-post"
		        name="<?php echo $context; ?>[term]">

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

			if ( ! ( $rules = get_post_meta( $this->get_term(), '_item-content-rule', true ) ) ) {
				$rules = array();
			}

			if ( ! in_array( $this->get_membership()->ID, $rules ) ) {

				/**
				 * Fires when a post of any type is added to the protection rules.
				 *
				 * @since 1.9
				 *
				 * @param int   $product_id
				 * @param int   $post_id
				 * @param array $rule
				 */
				do_action( 'it_exchange_membership_add_post_rule', $this->get_membership()->ID, $this->get_term(), $rule );

				$rules[] = $this->get_membership()->ID;

				$r2 = update_post_meta( $rule['term'], '_item-content-rule', $rules );
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
		$r2 = true;
		$r3 = parent::delete();

		if ( $this->get_delay_rule() ) {
			$r1 = $this->get_delay_rule()->delete();
		}

		$rule = $this->data;

		if ( ! ( $rules = get_post_meta( $this->get_term(), '_item-content-rule', true ) ) ) {
			$rules = array();
		}

		if ( false !== $key = array_search( $this->get_membership()->ID, $rules ) ) {

			/**
			 * Fires when a post of any type is removed from the protection rules.
			 *
			 * @since 1.9
			 *
			 * @param int   $product_id
			 * @param int   $post_id
			 * @param array $rule
			 */
			do_action( 'it_exchange_membership_remove_post_rule', $this->get_membership()->ID, $this->get_term(), $rule );

			unset( $rules[ $key ] );
			if ( empty( $rules ) ) {
				$r2 = delete_post_meta( $this->get_term(), '_item-content-rule' );
			} else {
				$r2 = update_post_meta( $this->get_term(), '_item-content-rule', $rules );
			}
		}

		return $r1 && $r2 && $r3;
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