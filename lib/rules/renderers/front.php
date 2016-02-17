<?php
/**
 * Contains rule renderer class.
 *
 * @since   1.18
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Membership_Front_Rule_Renderer
 */
class IT_Exchange_Membership_Front_Rule_Renderer {

	/**
	 * @var array
	 */
	private $rules;

	/**
	 * @var IT_Exchange_User_MembershipInterface
	 */
	private $user_membership;

	/**
	 * @var IT_Exchange_Membership_Rule_Factory
	 */
	private $factory;

	/**
	 * IT_Exchange_Membership_Front_Rule_Renderer constructor.
	 *
	 * @param array                               $rules
	 * @param IT_Exchange_Subscription            $subscription
	 * @param IT_Exchange_Membership_Rule_Factory $factory
	 */
	public function __construct( array $rules, IT_Exchange_User_MembershipInterface $user_membership, IT_Exchange_Membership_Rule_Factory $factory ) {
		$this->rules           = $rules;
		$this->user_membership = $user_membership;
		$this->factory         = $factory;
	}

	/**
	 * Render the membership content.
	 *
	 * @since 1.18
	 *
	 * @param array                  $options
	 * @param IT_Exchange_Membership $membership
	 *
	 * @return string
	 */
	public function render( array $options, IT_Exchange_Membership $membership ) {

		$defaults = array(
			'as_child'              => false,
			'include_product_title' => true,
			'show_drip'             => 'on',
			'show_drip_time'        => 'on'
		);

		$options = wp_parse_args( $options, $defaults );

		$options['toggle']         = $options['toggle'] === 'true';
		$options['show_drip']      = $options['show_drip'] === 'on';
		$options['show_drip_time'] = $options['show_drip_time'] === 'on';

		$grouped = $this->factory->make_all_for_membership_grouped( $membership );
		ob_start();
		?>

		<div class="it-exchange-content-wrapper it-exchange-content-<?php echo $options['layout']; ?> it-exchange-clearfix">

			<?php if ( $options['include_product_title'] ): ?>
				<h3><?php echo get_the_title( $membership ); ?></h3>
			<?php endif; ?>

			<?php if ( $options['as_child'] ): ?>
				<?php echo $options['child_description']; ?>
			<?php endif; ?>

			<?php foreach ( $grouped as $maybe_group ): ?>

				<?php if ( $maybe_group instanceof IT_Exchange_Membership_Content_Rule_Group ): ?>

					<?php
					$group       = $maybe_group;
					$group_class = $options['toggle'] ? 'it-exchange-content-group-toggle' : '';
					$hidden      = $options['toggle'] ? ' class="it-exchange-hidden"' : '';
					?>

					<div class="it-exchange-content-group it-exchange-content-group-layout-<?php echo $group->get_layout() ?> <?php echo $group_class; ?>">

						<p class="it-exchange-group-content-label">
							<span class="it-exchange-group-title"><?php echo $group->get_name(); ?></span>

							<?php if ( $options['toggle'] ): ?>
								<span class="it-exchange-open-group"></span>
							<?php endif; ?>
						</p>

						<ul<?php echo $hidden; ?>>

							<?php foreach ( $group->get_rules() as $rule ): ?>

								<?php echo $this->render_rule( $rule, $options ); ?>

							<?php endforeach; ?>
						</ul>
					</div>

				<?php else: ?>

					<?php echo $this->render_rule( $maybe_group, $options ); ?>

				<?php endif; ?>

			<?php endforeach; ?>
		</div>

		<?php

		return ob_get_clean();
	}

	/**
	 * Render a rule and all of its posts.
	 *
	 * @since 1.18
	 *
	 * @param IT_Exchange_Membership_Content_RuleInterface $rule
	 * @param array                                        $options
	 *
	 * @return string
	 */
	protected function render_rule( IT_Exchange_Membership_Content_RuleInterface $rule, array $options ) {

		$group_class  = $options['toggle'] ? 'it-exchange-content-group-toggle' : '';
		$hidden       = $options['toggle'] ? ' class="it-exchange-hidden"' : '';
		$posts        = $rule->get_matching_posts( $options['posts_per_grouping'] );
		$more_content = $rule->get_more_content_url();

		$posts = apply_filters( 'it_exchange_membership_addon_membership_content_restricted_posts', $posts, $rule->get_selection(), $rule->get_type(), $rule->get_term() );

		ob_start();

		echo $options['before'];
		?>

		<?php if ( $rule instanceof IT_Exchange_Membership_Rule_Layoutable ): ?>
			<div class="it-exchange-content-group it-exchange-content-group-layout-<?php echo $rule->get_layout() ?> <?php echo $group_class; ?>">

				<p class="it-exchange-group-content-label">
					<span class="it-exchange-group-title"><?php echo $rule->get_short_description(); ?></span>

					<?php if ( $options['toggle'] ): ?>
						<span class="it-exchange-open-group"></span>
					<?php endif; ?>
				</p>

				<ul<?php echo $hidden; ?>>
					<?php foreach ( $posts as $post ):
						echo $this->render_post( $post, $rule, $options );
					endforeach; ?>

					<?php if ( ! empty( $more_content ) && $options['posts_per_grouping'] == count( $posts ) ): ?>
						<li class="it-exchange-content-more">
							<a href="<?php echo $more_content; ?>">
								<?php _e( 'Read more content in this group', 'LION' ); ?>
							</a>
						</li>
					<?php endif; ?>
				</ul>
			</div>

		<?php else:
			foreach ( $posts as $post ):
				echo $this->render_post( $post, $rule, $options );
			endforeach;
		endif;

		echo $options['after'];

		return ob_get_clean();
	}

	/**
	 * Render a single post item.
	 *
	 * @since 1.18
	 *
	 * @param WP_Post                                      $post
	 * @param IT_Exchange_Membership_Content_RuleInterface $rule
	 * @param array                                        $options
	 *
	 * @return string
	 */
	protected function render_post( WP_Post $post, IT_Exchange_Membership_Content_RuleInterface $rule, array $options ) {

		if ( $rule instanceof IT_Exchange_Membership_Rule_Delayable && $rule->get_delay_rule() ) {
			$delay = $rule->get_delay_rule()->get_availability_date( $this->user_membership );

			if ( ! $delay ) {
				return '';
			}
		} else {
			$delay = null;
		}

		$now = new DateTime();

		$unavailable = $delay && $now < $delay ? 'it-exchange-content-unavailable' : '';

		ob_start();
		?>

		<li>
			<div class="it-exchange-content-group it-exchange-content-single <?php echo $unavailable; ?>">
				<div class="it-exchange-content-item-icon">
					<a class="it-exchange-item-icon" href="<?php echo get_permalink( $post ); ?>"></a>
				</div>

				<div class="it-exchange-content-item-info">
					<p class="it-exchange-group-content-label">
						<?php if ( $options['show_drip'] && $options['show_drip_time'] && $delay && $now < $delay ): ?>
							<span class="it-exchange-item-unavailable-message">
								<?php printf( __( 'available in %s', 'LION' ), human_time_diff( $delay->format( 'U' ) ) ); ?>
							</span>

							<span class="it-exchange-item-title"><?php echo get_the_title( $post ); ?></span>
						<?php else: ?>
							<a href="<?php echo get_permalink( $post ); ?>">
								<span class="it-exchange-item-title"><?php echo get_the_title( $post ); ?></span>
							</a>
						<?php endif; ?>
					</p>
				</div>
			</div>
		</li>

		<?php

		return ob_get_clean();
	}

}