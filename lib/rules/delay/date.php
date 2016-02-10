<?php
/**
 * Contains the 'date' delay rule.
 *
 * @since   1.18
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Membership_Delay_Rule_Date
 */
class IT_Exchange_Membership_Delay_Rule_Date implements IT_Exchange_Membership_Delay_RuleInterface {

	/**
	 * @var WP_Post
	 */
	private $post;

	/**
	 * @var IT_Exchange_Membership
	 */
	private $membership;

	/**
	 * @var DateTime
	 */
	private $date;

	/**
	 * IT_Exchange_Membership_Delay_Rule_Date constructor.
	 *
	 * @param WP_Post                $post
	 * @param IT_Exchange_Membership $membership
	 */
	public function __construct( WP_Post $post = null, IT_Exchange_Membership $membership = null ) {
		$this->post       = $post;
		$this->membership = $membership;

		if ( ! $post || ! $membership ) {
			return;
		}

		$date = get_post_meta( $post->ID, '_item-content-rule-date-' . $membership->ID, true );

		if ( ! empty( $date ) ) {
			$this->date = new DateTime( $date, new DateTimeZone( 'UTC' ) );
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

		if ( ! $this->date ) {
			return false;
		}

		$now = new DateTime( 'now', new DateTimeZone( 'UTC' ) );

		return $this->date < $now;
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
	 * @throws UnexpectedValueException
	 * @throws InvalidArgumentException
	 */
	public function save( array $data ) {

		if ( ! $this->membership ) {
			throw new UnexpectedValueException( 'Constructed with null IT_Exchange_Membership' );
		}

		if ( ! $this->post ) {
			throw new UnexpectedValueException( 'Constructed with null WP_Post' );
		}

		if ( array_key_exists( 'date', $data ) ) {
			if ( is_null( $data['date'] ) ) {
				return delete_post_meta( $this->post->ID, '_item-content-rule-date-' . $this->membership->ID );
			} else {

				$date = new DateTime( $data['date'], new DateTimeZone( 'UTC' ) );
				$date = $date->format( 'Y-m-d H:i:s' );

				return update_post_meta( $this->post->ID, '_item-content-rule-date-' . $this->membership->ID, $date );
			}
		}

		return false;
	}

	/**
	 * Get the availability date for this rule.
	 *
	 * Null can be returned to indicate that the subscription will never
	 * have access to this content.
	 *
	 * @since 1.18
	 *
	 * @param IT_Exchange_Subscription $subscription
	 *
	 * @return DateTime|null
	 */
	public function get_availability_date( IT_Exchange_Subscription $subscription ) {
		return $this->date;
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

		$df = get_option( 'date_format' );

		ob_start();
		?>

		<label for="<?php echo $context; ?>-date" class="screen-reader-text">
			<?php _e( 'Available on', 'LION' ); ?>
		</label>

		<input type="datetime" name="<?php echo $context ?>[date]" id="<?php echo $context; ?>-date" class="datepicker"
		       value="<?php echo $this->date ? $this->date->format( $df ) : ''; ?>" data-format="<?php echo $df; ?>">

		<?php
		return ob_get_clean();
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
		return $label ? __( 'Date', 'LION' ) : 'date';
	}
}