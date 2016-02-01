<?php
/**
 * Contains the drip rule.
 *
 * @since   1.18
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Membership_Delay_Rule_Drip
 */
class IT_Exchange_Membership_Delay_Rule_Drip implements IT_Exchange_Membership_Delay_RuleInterface {

	const D_DAYS = 'days';
	const D_WEEKS = 'weeks';
	const D_MONTHS = 'months';
	const D_YEARS = 'years';

	/**
	 * @var WP_Post
	 */
	private $post;

	/**
	 * @var IT_Exchange_Membership
	 */
	private $membership;

	/**
	 * @var int
	 */
	private $interval = 0;

	/**
	 * @var string
	 */
	private $duration = self::D_DAYS;

	/**
	 * IT_Exchange_Membership_Content_Rule_Drip constructor.
	 *
	 * @param WP_Post                $post
	 * @param IT_Exchange_Membership $membership
	 */
	public function __construct( WP_Post $post, IT_Exchange_Membership $membership = null ) {
		$this->post       = $post;
		$this->membership = $membership;

		if ( ! $membership ) {
			return;
		}

		$this->interval = (int) get_post_meta( $post->ID, '_item-content-rule-drip-interval-' . $membership->ID, true );

		$duration = get_post_meta( $post->ID, '_item-content-rule-drip-duration-' . $membership->ID, true );

		if ( array_key_exists( $duration, self::get_durations() ) ) {
			$this->duration = $duration;
		}
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
	public function evaluate( IT_Exchange_Subscription $subscription, WP_Post $post = null ) {
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

		$interval = $this->interval;

		ob_start();
		?>

		<label for="<?php echo $context; ?>-interval" class="screen-reader-text">
			<?php _e( 'Drip Interval', 'LION' ); ?>
		</label>
		<input id="<?php echo $context; ?>-interval" class="it-exchange-membership-drip-rule-interval" type="number" min="0" value="<?php echo $interval; ?>" name="it_exchange_membership_drip_interval">

		<label for="<?php echo $context; ?>-drip" class="screen-reader-text">
			<?php _e( 'Drip Duration', 'LION' ); ?>
		</label>
		<select id="<?php echo $context; ?>-drip" class="it-exchange-membership-drip-rule-duration" name="it_exchange_membership_drip_duration">
			<?php foreach ( self::get_durations() as $type => $label ): ?>
				<option value="<?php echo $type; ?>" <?php selected( $type, $this->duration ); ?>>
					<?php echo $label; ?>
				</option>
			<?php endforeach; ?>
		</select>

		<?php

		return ob_get_clean();
	}

	/**
	 * Save the data to the post.
	 *
	 * @since 1.18
	 *
	 * @param array $data
	 *
	 * @return bool
	 *
	 * @throws UnexpectedValueException If rule was not constructed with a IT_Exchange_Membership object.
	 * @throws InvalidArgumentException If invalid data.
	 */
	public function save( array $data ) {

		if ( ! $this->membership ) {
			throw new UnexpectedValueException( 'Constructed with null IT_Exchange_Membership.' );
		}

		$r1 = true;
		$r2 = true;

		if ( array_key_exists( 'interval', $data ) ) {

			if ( is_null( $data['interval'] ) ) {
				$r1 = delete_post_meta( $this->post->ID, '_item-content-rule-drip-interval-' . $this->membership->ID );
			} else {
				$r1 = update_post_meta( $this->post->ID, '_item-content-rule-drip-interval-' . $this->membership->ID, $data['interval'] );
			}
		}

		if ( array_key_exists( 'duration', $data ) ) {

			if ( is_null( $data['duration'] ) ) {
				$r2 = delete_post_meta( $this->post->ID, '_item-content-rule-drip-duration-' . $this->membership->ID );
			} else {

				$duration = $data['duration'];

				if ( ! array_key_exists( $duration, self::get_durations() ) ) {
					throw new InvalidArgumentException( "Invalid duration '$duration'" );
				}

				$r2 = update_post_meta( $this->post->ID, '_item-content-rule-drip-duration-' . $this->membership->ID, $duration );
			}
		}

		return $r1 && $r2;
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
		return 'This content will be accessible in 5 days.';
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
		return $label ? __( 'Drip', 'LION' ) : 'drip';
	}

	/**
	 * Get the possible drip durations.
	 *
	 * @since 1.18
	 *
	 * @return array
	 */
	public static function get_durations() {

		$durations = array(
			self::D_DAYS   => __( 'Days', 'LION' ),
			self::D_WEEKS  => __( 'Weeks', 'LION' ),
			self::D_MONTHS => __( 'Months', 'LION' ),
			self::D_YEARS  => __( 'Years', 'LION' )
		);

		$durations = apply_filters( 'it-exchange-membership-drip-durations', $durations );

		return $durations;
	}
}