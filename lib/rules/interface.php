<?php
/**
 * Membership rule interface.
 *
 * @since   1.18
 * @license GPLv2
 */

/**
 * Interface IT_Exchange_Membership_RuleInterface
 */
interface IT_Exchange_Membership_RuleInterface {
	/**
	 * Evaluate the rule.
	 *
	 * @since    1.18
	 *
	 * @param IT_Exchange_User_MembershipInterface $user_membership
	 * @param WP_Post                              $post
	 *
	 * @return bool True if readable
	 */
	public function evaluate( IT_Exchange_User_MembershipInterface $user_membership, WP_Post $post );

	/**
	 * Get HTML to render the necessary form fields.
	 *
	 * @since    1.18
	 *
	 * @param string $context Context to preface field name attributes.
	 *
	 * @return string
	 */
	public function get_field_html( $context );

	/**
	 * Delete the rule from the database.
	 *
	 * @since 1.18
	 *
	 * @return bool
	 * @throws UnexpectedValueException
	 */
	public function delete();

	/**
	 * Get the type of this restriction.
	 *
	 * @since 1.18
	 *
	 * @param bool $label
	 *
	 * @return string
	 */
	public function get_type( $label = false );

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
	public function save( array $data = array() );
}