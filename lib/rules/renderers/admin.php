<?php
/**
 * Contains rule renderer class.
 *
 * @since   1.18
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Membership_Admin_Rule_Renderer
 */
class IT_Exchange_Membership_Admin_Rule_Renderer {

	/**
	 * @var array
	 */
	private $rules;
	/**
	 * @var IT_Exchange_Membership
	 */
	private $membership;

	/**
	 * @var IT_Exchange_Membership_Rule_Factory
	 */
	private $factory;

	/**
	 * @var string
	 */
	private $name = 'it_exchange_content_access_rules';

	/**
	 * IT_Exchange_Membership_Admin_Rule_Renderer constructor.
	 *
	 * @param array                               $rules
	 * @param IT_Exchange_Membership              $membership
	 * @param IT_Exchange_Membership_Rule_Factory $factory
	 */
	public function __construct( array $rules, IT_Exchange_Membership $membership, IT_Exchange_Membership_Rule_Factory $factory ) {
		$this->rules      = $rules;
		$this->membership = $membership;
		$this->factory    = $factory;
	}

	/**
	 * Render the content rules form.
	 *
	 * @since 1.18
	 *
	 * @return string
	 */
	public function render() {

		$grouped = $this->factory->make_all_for_membership_grouped( $this->membership );
		$name    = $this->name;
		$count   = 0;
		$groups  = 0;

		ob_start();

		foreach ( $grouped as $maybe_group ) : ?>

			<?php if ( $maybe_group instanceof IT_Exchange_Membership_Content_Rule_Group ) :

				$group = $maybe_group;
				$groups ++;
				?>

				<div class="it-exchange-membership-addon-content-access-rule it-exchange-membership-addon-content-access-group columns-wrapper" data-count="<?php echo $count; ?>">
					<div class="it-exchange-membership-addon-sort-content-access-rule column col-1_4-12"></div>

					<label for="it_exchange_membership_group_name_<?php echo $group->get_ID(); ?>" class="screen-reader-text">
						<?php _e( 'Group Name', 'LION' ); ?>
					</label>
					<input type="text" id="it_exchange_membership_group_name_<?php echo $group->get_ID(); ?>"
					       name="<?php echo $name . "[$count]"; ?>[group]" value="<?php echo $group->get_name(); ?>">

					<input type="hidden" name="<?php echo $name . "[$count]"; ?>[group_id]" value="<?php echo $group->get_ID(); ?>">

					<?php echo $this->render_layout_controls( $group, $count ); ?>
					<?php echo $this->render_group_actions(); ?>

					<input type="hidden" class="it-exchange-content-access-group" name="<?php echo $name . "[$count]"; ?>[grouped_id]" value="<?php echo $group->get_ID(); ?>">

					<div class="columns-wrapper it-exchange-membership-content-access-group-content content-access-sortable" data-group-id="<?php echo $group->get_ID(); ?>">

						<?php foreach ( $group->get_rules() as $rule ): ?>
							<?php echo $this->render_rule( $rule, ++ $count, $group ); ?>
						<?php endforeach; ?>

					</div>
				</div>

			<?php else: ?>
				<?php echo $this->render_rule( $maybe_group, $count ); ?>
			<?php endif;

			++ $count;

		endforeach; ?>

		<script type="text/javascript" charset="utf-8">
			var it_exchange_membership_addon_content_access_iteration = <?php echo $count; ?>;
			var it_exchange_membership_addon_content_access_group_iteration = <?php echo $groups; ?>;
		</script>

		<?php

		return ob_get_clean();
	}

	/**
	 * Render the group actions component.
	 *
	 * @since 1.18
	 *
	 * @return string
	 */
	protected function render_group_actions() {

		ob_start();
		?>
		<div class="it-exchange-membership-addon-group-action-wrapper">
			<div class="it-exchange-membership-addon-group-action">ACTION</div>
			<div class="it-exchange-membership-addon-group-actions">
				<div class="it-exchange-membership-addon-ungroup-content-access-group column">
					<a href="#"><?php _e( 'Ungroup', 'LION' ); ?></a>
				</div>
				<div class="it-exchange-membership-addon-remove-content-access-group column">
					<a href="#"><?php _e( 'Delete Group', 'LION' ); ?></a>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render the group layout component.
	 *
	 * @since 1.18
	 *
	 * @param IT_Exchange_Membership_Rule_Layoutable $layoutable
	 * @param int                                    $count
	 *
	 * @return string
	 */
	protected function render_layout_controls( IT_Exchange_Membership_Rule_Layoutable $layoutable, $count ) {

		ob_start();

		$name = $this->name;

		$active_grid = 'grid' === $layoutable->get_layout() ? 'active-group-layout' : '';
		$active_list = 'list' === $layoutable->get_layout() ? 'active-group-layout' : '';
		?>
		<div class="group-layout-options">
			<span class="group-layout <?php echo $active_grid; ?>" data-type="grid">grid</span>
			<span class="group-layout <?php echo $active_list; ?>" data-type="list">list</span>
			<input type="hidden" class="group-layout-input" name="<?php echo $name . "[$count]"; ?>[group_layout]" value="<?php echo $layoutable->get_layout(); ?>">
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Render a single rule component.
	 *
	 * @since 1.18
	 *
	 * @param IT_Exchange_Membership_Content_RuleInterface $rule
	 * @param int                                          $count
	 * @param IT_Exchange_Membership_Content_Rule_Group    $group
	 *
	 * @return string
	 */
	protected function render_rule( IT_Exchange_Membership_Content_RuleInterface $rule, $count, IT_Exchange_Membership_Content_Rule_Group $group = null ) {

		ob_start();
		$name = $this->name;

		?>
		<div class="it-exchange-membership-addon-content-access-rule columns-wrapper" data-count="<?php echo $count; ?>">
			<div class="it-exchange-membership-addon-sort-content-access-rule column col-1_4-12"></div>

			<input type="hidden" name="<?php echo $name . "[$count]"; ?>[id]" value="<?php echo $rule->get_rule_id(); ?>">

			<?php echo it_exchange_membership_addon_get_selections( $rule->get_selection(), $rule->get_type(), $count ); ?>

			<div class="it-exchange-content-access-content column col-6-12">
				<div class="it-exchange-membership-content-type-terms">
					<input type="hidden" value="<?php echo $rule->get_type(); ?>" name="<?php echo $name . "[$count]"; ?>[selected]">
					<?php echo $rule->get_field_html( $name . "[$count]" ); ?>

					<?php if ( $rule instanceof IT_Exchange_Membership_Rule_Layoutable ): ?>
						<?php echo $this->render_layout_controls( $rule, $count ); ?>
					<?php endif; ?>
				</div>
			</div>

			<div class="it-exchange-content-access-delay column col-3-12">

				<?php if ( $rule instanceof IT_Exchange_Membership_Content_Rule_Delayable ): ?>

					<?php
					$current_delay = $rule->get_delay_rule();
					$current_delay = $current_delay ? $current_delay->get_type() : '';
					$all_delay     = it_exchange_membership_addon_get_delay_rules( $rule->get_post_for_delay(), $rule->get_membership() );
					?>

					<div class="it-exchange-membership-delay-rule-selection">
						<label for="<?php echo $name . "[$count]"; ?>-delay-type" class="screen-reader-text">
							<?php _e( 'Select a delay rule type.', 'LION' ); ?>
						</label>
						<select name="<?php echo $name . "[$count]"; ?>[delay-type]" id="<?php echo $name . "[$count]"; ?>-delay-type">
							<option value=""><?php _e( 'None', 'LION' ); ?></option>
							<?php foreach ( $all_delay as $delay ): ?>
								<option value="<?php echo $delay->get_type(); ?>" <?php selected( $delay->get_type(), $current_delay ); ?>>
									<?php echo $delay->get_type( true ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<?php foreach ( $all_delay as $delay ): ?>
						<?php $hidden = $delay->get_type() === $current_delay ? '' : ' hidden'; ?>
						<div class="it-exchange-membership-content-delay-rule-<?php echo $delay->get_type() . $hidden; ?> it-exchange-membership-content-delay-rule">
							<?php echo $delay->get_field_html( $name . "[$count][delay]" ); ?>
						</div>
					<?php endforeach; ?>

				<?php else: ?>
					<div class="it-exchange-content-access-delay-unavailable">
						<?php _e( 'Available for single posts or pages.', 'LION' ); ?>
					</div>
				<?php endif; ?>

			</div>

			<div class="it-exchange-membership-addon-remove-content-access-rule column col-3_4-12">
				<a href="#">×</a>
			</div>

			<input type="hidden" class="it-exchange-content-access-group" name="<?php echo $name . "[$count]"; ?>[grouped_id]" value="<?php echo $group ? $group->get_ID() : ''; ?>">

		</div>
		<?php

		return ob_get_clean();
	}


}