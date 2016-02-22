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
class IT_Exchange_Membership_Delay_Rule_Date implements IT_Exchange_Membership_Delay_Rule {

	/**
	 * @var IT_Exchange_Membership_Rule_Delayable
	 */
	private $rule;

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
	 * @param IT_Exchange_Membership_Rule_Delayable $rule
	 * @param IT_Exchange_Membership                $membership
	 */
	public function __construct( IT_Exchange_Membership_Rule_Delayable $rule = null, IT_Exchange_Membership $membership = null ) {
		$this->rule       = $rule;
		$this->membership = $membership;

		if ( ! $rule || ! $membership ) {
			return;
		}

		$date = $rule->get_delay_meta( '_item-content-rule-date-' . $membership->ID );

		if ( ! empty( $date ) ) {
			$this->date = new DateTime( $date, new DateTimeZone( 'UTC' ) );
		}
	}

	/**
	 * Evaluate the rule.
	 *
	 * @since 1.18
	 *
	 * @param IT_Exchange_User_Membership $user_membership
	 * @param WP_Post                     $post
	 *
	 * @return bool True if readable
	 */
	public function evaluate( IT_Exchange_User_Membership $user_membership, WP_Post $post = null ) {

		if ( ! $this->date ) {
			return false;
		}

		$now = new DateTime( 'now', new DateTimeZone( 'UTC' ) );

		return $this->date < $now;
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

		$df = str_replace( 'F', 'M', get_option( 'date_format' ) );
		$jdf = it_exchange_php_date_format_to_jquery_datepicker_format( $df );

		ob_start();
		?>

		<label for="<?php echo $context; ?>-date" class="screen-reader-text">
			<?php _e( 'Available on', 'LION' ); ?>
		</label>

		<input type="datetime" name="<?php echo $context ?>[date]" id="<?php echo $context; ?>-date" class="datepicker"
		       value="<?php echo $this->date ? $this->date->format( $df ) : ''; ?>" data-format="<?php echo $jdf; ?>">

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
	 * @throws UnexpectedValueException
	 * @throws InvalidArgumentException
	 */
	public function save( array $data = array() ) {

		if ( ! $this->membership ) {
			throw new UnexpectedValueException( 'Constructed with null IT_Exchange_Membership' );
		}

		if ( ! $this->rule ) {
			throw new UnexpectedValueException( 'Constructed with null Delayable rule.' );
		}

		if ( array_key_exists( 'date', $data ) ) {
			if ( is_null( $data['date'] ) ) {

				$this->date = null;

				return $this->rule->delete_delay_meta( '_item-content-rule-date-' . $this->membership->ID );
			} else {

				$date = new DateTime( $data['date'], new DateTimeZone( 'UTC' ) );

				$this->date = $date;

				$date = $date->format( 'Y-m-d H:i:s' );

				return $this->rule->update_delay_meta( '_item-content-rule-date-' . $this->membership->ID, $date );
			}
		}

		return false;
	}

	/**
	 * Delete the rule from the database.
	 *
	 * @since 1.18
	 *
	 * @return bool
	 * @throws UnexpectedValueException
	 */
	public function delete() {

		if ( ! $this->membership ) {
			throw new UnexpectedValueException( 'Constructed with null IT_Exchange_Membership' );
		}

		if ( ! $this->rule ) {
			throw new UnexpectedValueException( 'Constructed with null Delayable rule.' );
		}

		return $this->rule->delete_delay_meta( '_item-content-rule-date-' . $this->membership->ID );
	}

	/**
	 * Get the availability date for this rule.
	 *
	 * Null can be returned to indicate that the subscription will never
	 * have access to this content.
	 *
	 * @since 1.18
	 *
	 * @param IT_Exchange_Subscription|IT_Exchange_User_Membership $user_membership
	 *
	 * @return DateTime|null
	 */
	public function get_availability_date( IT_Exchange_User_Membership $user_membership ) {
		return $this->date;
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