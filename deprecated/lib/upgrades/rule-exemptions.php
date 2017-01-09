<?php
/**
 * Contains the upgrade routines for fixing membership exemptions.
 *
 * @since   1.18
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Memberships_Fix_Rule_Exemptions_Upgrade
 */
class IT_Exchange_Memberships_Fix_Rule_Exemptions_Upgrade implements IT_Exchange_UpgradeInterface {

	/**
	 * @var IT_Exchange_Membership_Rule_Factory
	 */
	private $factory;

	/**
	 * Get the iThemes Exchange version this upgrade applies to.
	 *
	 * @since 1.33
	 *
	 * @return string
	 */
	public function get_version() {
		return '1.18';
	}

	/**
	 * Get the name of this upgrade.
	 *
	 * @since 1.33
	 *
	 * @return string
	 */
	public function get_name() {
		return __( 'Membership Rule Exemptions', 'LION' );
	}

	/**
	 * Get the slug for this upgrade. This should be globally unique.
	 *
	 * @since 1.33
	 *
	 * @return string
	 */
	public function get_slug() {
		return 'membership-rule-exemptions';
	}

	/**
	 * Get the description for this upgrade. 1-3 sentences.
	 *
	 * @since 1.33
	 *
	 * @return string
	 */
	public function get_description() {
		return __( 'Change how membership content rule exemptions are stored. Ensures content desired to be public is public.', 'LION' );
	}

	/**
	 * Get the group this upgrade belongs to.
	 *
	 * Example 'Core' or 'Membership'.
	 *
	 * @since 1.33
	 *
	 * @return string
	 */
	public function get_group() {
		return __( 'Membership', 'LION' );
	}

	/**
	 * Get the total records needed to be processed for this upgrade.
	 *
	 * This is used to build the upgrade UI.
	 *
	 * @since 1.33
	 *
	 * @return int
	 */
	public function get_total_records_to_process() {
		return count( $this->get_posts( - 1, 1, true ) );
	}

	/**
	 * Get all posts with rule exemptions.
	 *
	 * @since 1.18
	 *
	 * @param int  $number
	 * @param int  $page
	 * @param bool $ids
	 *
	 * @return array
	 */
	protected function get_posts( $number = - 1, $page = 1, $ids = false ) {

		$args = array(
			'posts_per_page' => $number,
			'page'           => $page,
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => '_item-content-rule-exemptions',
					'compare' => 'EXISTS'
				),
				array(
					'relation' => 'OR',
					array(
						'key'     => '_upgrade_completed',
						'compare' => 'NOT EXISTS'
					),
					array(
						'key'     => '_upgrade_completed',
						'value'   => $this->get_slug(),
						'compare' => '!='
					)
				)
			)
		);

		if ( $ids ) {
			$args['fields'] = 'ids';
		}

		$query = new WP_Query( $args );

		return $query->get_posts();
	}

	/**
	 * Upgrade an individual post.
	 *
	 * @since 1.18
	 *
	 * @param WP_Post                           $post
	 * @param IT_Exchange_Upgrade_SkinInterface $skin
	 * @param bool                              $verbose
	 *
	 * @throws IT_Exchange_Upgrade_Exception
	 */
	protected function upgrade_post( WP_Post $post, IT_Exchange_Upgrade_SkinInterface $skin, $verbose ) {

		if ( $verbose ) {
			$skin->debug( 'Upgrade Post ' . $post->ID );
		}

		$restrictions = get_post_meta( $post->ID, '_item-content-rule-exemptions', true );

		if ( ! is_array( $restrictions ) ) {

			if ( $verbose ) {
				$skin->debug( 'Skipped Post ' . $post->ID );
			}

			return;
		}

		foreach ( $restrictions as $product_id => $exemptions ) {

			foreach ( $exemptions as $exemption ) {

				if ( $exemption == 'posttype' ) {
					$type = 'post_types';
					$term = get_post_type( $post );
				} elseif ( $exemption == $post->post_type ) {
					$type = 'posts';
					$term = $post->ID;
				} elseif ( strpos( $exemption, 'taxonomy' ) !== false ) {
					$type = 'taxonomy';
					$term = preg_replace( '/\D/', '', $exemption );

					if ( $this->is_term_shared( $term ) ) {
						throw new IT_Exchange_Upgrade_Exception(
							"Found shared term '{$term}'. All shared terms must be split before upgrading. " .
							'Please upgrade to WordPress 4.3 or later.'
						);
					}
				} else {
					continue;
				}

				$key = "_rule-exemption-{$type}-{$term}-{$product_id}";

				update_post_meta( $post->ID, $key, true );

				if ( $verbose ) {
					$skin->debug( 'Added Exemption: ' . $key );
				}
			}
		}

		$rules = $this->factory->make_all_for_post( $post );

		$all_exempt = true;

		foreach ( $rules as $rule ) {

			if ( ! $rule->is_post_exempt( $post ) ) {
				$all_exempt = false;
			}
		}

		if ( $all_exempt ) {

			if ( $verbose ) {
				$skin->debug( 'All rules exempted. Disabling restrictions.' );
			}

			update_post_meta( $post->ID, '_it-exchange-content-restriction-disabled', true );
		}

		update_post_meta( $post->ID, '_upgrade_completed', $this->get_slug() );

		if ( $verbose ) {
			$skin->debug( 'Upgraded Post ' . $post->ID );
			$skin->debug( '' );
		}
	}

	/**
	 * Is a term shared.
	 *
	 * @since 1.18
	 *
	 * @param int $term_id
	 *
	 * @return bool
	 */
	protected function is_term_shared( $term_id ) {

		global $wpdb;

		if ( get_option( 'finished_splitting_shared_terms' ) ) {
			return false;
		}

		$tt_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_taxonomy WHERE term_id = %d", $term_id ) );

		return $tt_count > 1;
	}

	/**
	 * Get the suggested rate at which the upgrade routine should be processed.
	 *
	 * The rate refers to how many items are upgraded in one step.
	 *
	 * @since 1.33
	 *
	 * @return int
	 */
	public function get_suggested_rate() {
		return 30;
	}

	/**
	 * Perform the upgrade according to the given configuration.
	 *
	 * Throwing an upgrade exception will halt the upgrade process and notify the user.
	 *
	 * @param IT_Exchange_Upgrade_Config        $config
	 * @param IT_Exchange_Upgrade_SkinInterface $skin
	 *
	 * @return void
	 *
	 * @throws IT_Exchange_Upgrade_Exception
	 */
	public function upgrade( IT_Exchange_Upgrade_Config $config, IT_Exchange_Upgrade_SkinInterface $skin ) {

		$this->factory = new IT_Exchange_Membership_Rule_Factory();

		foreach ( $this->get_posts( $config->get_number(), $config->get_step() ) as $post ) {
			$this->upgrade_post( $post, $skin, $config->is_verbose() );
		}
	}
}