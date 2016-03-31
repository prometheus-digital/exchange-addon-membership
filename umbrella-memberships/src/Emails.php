<?php
/**
 * Hooks for email sending.
 *
 * @author      iThemes
 * @since       1.0
 * @copyright   2015 (c) Iron Bound Designs, 2016 iThemes.
 * @license     GPLv2
 */

namespace ITEGMS;

use IronBound\WP_Notifications\Notification;
use IronBound\WP_Notifications\Strategy\iThemes_Exchange;
use IronBound\WP_Notifications\Template\Factory;
use IronBound\WP_Notifications\Template\Listener;
use IronBound\WP_Notifications\Template\Manager;
use ITEGMS\Relationship\Relationship;

/**
 * Class Emails
 *
 * @package ITEGMS
 */
class Emails {

	/**
	 * @var \IT_Exchange_Email_Notifications
	 */
	private static $notifications;

	/**
	 * Constructor.
	 */
	public function __construct() {

		add_action( 'it_exchange_register_email_notifications', array( __CLASS__, 'register_emails' ) );
		add_action( 'it_exchange_email_notifications_register_tags', array( __CLASS__, 'register_tags' ) );

		add_action( 'ibd_wp_notifications_template_manager_itegms-invitation', array(
			__CLASS__,
			'invitation_listeners'
		) );

		add_action( 'ibd_wp_notifications_template_manager_itegms-invitation-new-user', array(
			__CLASS__,
			'invitation_new_user_listeners'
		) );

		add_action( 'ibd_wp_notifications_template_manager_itegms-removed', array(
			__CLASS__,
			'removed_listeners'
		) );

		add_action( 'ibd_wp_notifications_template_manager_itegms-expired', array(
			__CLASS__,
			'expired_listeners'
		) );

		add_action( 'itegms_create_relationship', array(
			__CLASS__,
			'send_invitation'
		) );

		add_action( 'itegms_create_relationship_new_user', array(
			__CLASS__,
			'send_invitation_new_user'
		), 10, 2 );

		add_action( 'itegms_delete_relationship', array(
			__CLASS__,
			'send_removal'
		) );

		add_action( 'itegms_expire_relationship', array(
			__CLASS__,
			'send_expired'
		) );
	}

	/**
	 * Register email notifications with Exchange.
	 *
	 * @since 1.19.11
	 *
	 * @param \IT_Exchange_Email_Notifications $notifications
	 */
	public static function register_emails( \IT_Exchange_Email_Notifications $notifications ) {

		$notifications
			->register_notification( new \IT_Exchange_Customer_Email_Notification(
				__( 'Umbrella Membership Invitation', 'LION' ), 'itegms-invitation', null, array(
					'defaults' => array(
						'subject' => sprintf( __( "You've been given access to %s by %s", 'LION' ),
							'[it_exchange_email show=umbrella_membership_name]', '[it_exchange_email show=customer_first_name]' ),
						'body'    => self::get_default_invitation()
					),
					'group'    => __( 'Umbrella Memberships', 'LION' )
				)
			) )
			->register_notification( new \IT_Exchange_Customer_Email_Notification(
				__( 'Umbrella Membership Invitation New User', 'LION' ), 'itegms-invitation-new-user', null, array(
					'defaults' => array(
						'subject' => sprintf( __( "You've been given access to %s by %s", 'LION' ),
							'[it_exchange_email show=umbrella_membership_name]', '[it_exchange_email show=customer_first_name]' ),
						'body'    => self::get_default_new_user()
					),
					'group'    => __( 'Umbrella Memberships', 'LION' )
				)
			) )
			->register_notification( new \IT_Exchange_Customer_Email_Notification(
				__( 'Umbrella Membership Removal', 'LION' ), 'itegms-removed', null, array(
					'defaults' => array(
						'subject' => sprintf( __( "You're access to %s has been revoked by %s", 'LION' ),
							'[it_exchange_email show=umbrella_membership_name]', '[it_exchange_email show=customer_first_name]' ),
						'body'    => self::get_default_expired()
					),
					'group'    => __( 'Umbrella Memberships', 'LION' )
				)
			) )
			->register_notification( new \IT_Exchange_Customer_Email_Notification(
				__( 'Umbrella Membership Expired', 'LION' ), 'itegms-expired', null, array(
					'defaults' => array(
						'subject' => sprintf( __( "You're access to %s has expired", 'LION' ),
							'[it_exchange_email show=umbrella_membership_name]' ),
						'body'    => self::get_default_removed()
					),
					'group'    => __( 'Umbrella Memberships', 'LION' )
				)
			) );

		self::$notifications = $notifications;
	}

	/**
	 * Register custom email tags.
	 *
	 * @since 1.19.11
	 *
	 * @param \IT_Exchange_Email_Tag_Replacer $replacer
	 */
	public static function register_tags( \IT_Exchange_Email_Tag_Replacer $replacer ) {

		$tag = new \IT_Exchange_Email_Tag_Base(
			'umbrella_membership_name', __( 'Umbrella Membership Name', 'LION' ),
			__( 'The name of the membership being joined.', 'LION' ), function ( $context ) {

			/** @var Relationship $relationship */
			$relationship = $context['umbrella-membership'];

			return $relationship->get_purchase()->get_membership()->post_title;
		} );
		$tag->add_required_context( 'umbrella-membership' );
		$tag->add_available_for( 'itegms-invitation' )->add_available_for( 'itegms-invitation-new-user' )
		    ->add_available_for( 'itegms-removed' )->add_available_for( 'itegms-expired' );

		$replacer->add_tag( $tag );

		$tag = new \IT_Exchange_Email_Tag_Base(
			'umbrella_membership_dashboard', __( 'Umbrella Membership Dashboard', 'LION' ),
			__( 'A link to the user dashboard for the membership.', 'LION' ), function ( $context ) {

			/** @var Relationship $relationship */
			$relationship = $context['umbrella-membership'];

			return $relationship->get_purchase()->get_membership()->get_dashboard();
		} );
		$tag->add_required_context( 'umbrella-membership' );
		$tag->add_available_for( 'itegms-invitation' )->add_available_for( 'itegms-invitation-new-user' )
		    ->add_available_for( 'itegms-removed' )->add_available_for( 'itegms-expired' );

		$tag = new \IT_Exchange_Email_Tag_Base(
			'umbrella_membership_password', __( 'Umbrella Membership Password', 'LION' ),
			__( 'The newly created user\'s auto-generated password.', 'LION' ), function ( $context ) {
			return $context['umbrella-membership-password'];
		} );
		$tag->add_required_context( 'umbrella-membership-password' );
		$tag->add_available_for( 'itegms-invitation-new-user' );

		$replacer->add_tag( $tag );
	}

	/**
	 * Send the invitation notification.
	 *
	 * @since 1.0
	 *
	 * @param Relationship $rel
	 */
	public static function send_invitation( Relationship $rel ) {
		it_exchange_send_email( new \IT_Exchange_Email(
				new \IT_Exchange_Email_Recipient_Customer( $rel->get_member() ),
				self::$notifications->get_notification( 'itegms-invitation' ),
				array(
					'umbrella-membership' => $rel,
					'customer'            => $rel->get_purchase()->get_customer()
				)
			)
		);
	}

	/**
	 * Send the invitation notification.
	 *
	 * @since 1.0
	 *
	 * @param Relationship $rel
	 * @param string       $password
	 */
	public static function send_invitation_new_user( Relationship $rel, $password ) {

		it_exchange_send_email( new \IT_Exchange_Email(
				new \IT_Exchange_Email_Recipient_Customer( $rel->get_member() ),
				self::$notifications->get_notification( 'itegms-invitation-new-user' ),
				array(
					'umbrella-membership'          => $rel,
					'customer'                     => $rel->get_purchase()->get_customer(),
					'umbrella-membership-password' => $password
				)
			)
		);
	}

	/**
	 * Send the removal notification.
	 *
	 * @since 1.0
	 *
	 * @param Relationship $rel
	 */
	public static function send_removal( Relationship $rel ) {

		it_exchange_send_email( new \IT_Exchange_Email(
				new \IT_Exchange_Email_Recipient_Customer( $rel->get_member() ),
				self::$notifications->get_notification( 'itegms-removed' ),
				array(
					'umbrella-membership' => $rel,
					'customer'            => $rel->get_purchase()->get_customer()
				)
			)
		);
	}

	/**
	 * Send the expired notification.
	 *
	 * @since 1.0
	 *
	 * @param Relationship $rel
	 */
	public static function send_expired( Relationship $rel ) {

		it_exchange_send_email( new \IT_Exchange_Email(
				new \IT_Exchange_Email_Recipient_Customer( $rel->get_member() ),
				self::$notifications->get_notification( 'itegms-expired' ),
				array(
					'umbrella-membership' => $rel,
					'customer'            => $rel->get_purchase()->get_customer()
				)
			)
		);
	}

	/**
	 * Get the default invitation email.
	 *
	 * @since 1.19.11
	 *
	 * @return string
	 */
	protected static function get_default_invitation() {
		return <<<TAG
		Hi [it_exchange_email show="first_name"],

Welcome to [it_exchange_email show="company_name"]'s [it_exchange_email show="umbrella_membership_name"] program. You've been invited to this program by [it_exchange_email show="customer_fullname"]. If you have any questions about this, you can contact [it_exchange_email show="customer_first_name"] by email at [it_exchange_email show="customer_email"].

You can access your exclusive membership content at the following url: [it_exchange_email show="umbrella_membership_dashboard"]

- The [it_exchange_email show="company_name"] Team
TAG;
	}

	/**
	 * Get the default new user invitation email.
	 *
	 * @since 1.19.11
	 *
	 * @return string
	 */
	protected static function get_default_new_user() {
		return <<<TAG
		Hi [it_exchange_email show="first_name"],

Welcome to [it_exchange_email show="company_name"]!

You've been invited to [it_exchange_email show="company_name"]'s [it_exchange_email show="umbrella_membership_name"] program by [it_exchange_email show="customer_fullname"]. If you have any questions about this, you can contact [it_exchange_email show="customer_first_name"] by email at [it_exchange_email show="customer_email"].

We've automatically created an account for you.

You can login here, [it_exchange_email show="login_link"], with the following information:

Username: [it_exchange_email show="username"]

Password: [it_exchange_email show="umbrella_membership_password"]

We recommend that you change your password when you login. You can do that from your profile page: [it_exchange_email show="profile_link"]

You can access your exclusive membership content at the following url: [it_exchange_email show="umbrella_membership_dashboard"]

Welcome to [it_exchange_email show="company_name"]!

- The [it_exchange_email show="company_name"] Team
TAG;
	}

	/**
	 * Get the default removed email.
	 *
	 * @since 1.19.11
	 *
	 * @return string
	 */
	protected static function get_default_removed() {
		return <<<TAG
Hi [it_exchange_email show="first_name"],

Your access to [it_exchange_email show="company_name"]'s [it_exchange_email show="umbrella_membership_name"] program has been revoked by [it_exchange_email show="customer_fullname"]. If you have any questions about this, you can contact [it_exchange_email show="customer_first_name"] by email at [it_exchange_email show="customer_email"].

- The [it_exchange_email show="company_name"] Team
TAG;
	}

	/**
	 * Get the default expired email.
	 *
	 * @since 1.19.11
	 *
	 * @return string
	 */
	protected static function get_default_expired() {
		return <<<TAG
Hi [it_exchange_email show="first_name"],

Your access to [it_exchange_email show="company_name"]'s [it_exchange_email show="umbrella_membership_name"] program has expired. This is typically due to a lapse of payment. If you have any questions about this, you can contact [it_exchange_email show="customer_fullname"] by email at [it_exchange_email show="customer_email"].

- The [it_exchange_email show="company_name"] Team
TAG;
	}
}