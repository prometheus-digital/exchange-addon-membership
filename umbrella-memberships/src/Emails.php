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
	 * @var bool
	 */
	private static $doing_save = false;

	/**
	 * @var \IT_Exchange_Sendable[]
	 */
	private static $queue = array();

	/**
	 * Constructor.
	 */
	public function __construct() {

		add_action( 'it_exchange_register_email_notifications', array( __CLASS__, 'register_emails' ) );
		add_action( 'it_exchange_email_notifications_register_tags', array( __CLASS__, 'register_tags' ), 20 );

		add_action( 'itegms_create_relationship', array( __CLASS__, 'send_invitation' ) );
		add_action( 'itegms_create_relationship_new_user', array( __CLASS__, 'send_invitation_new_user' ), 10, 2 );
		add_action( 'itegms_delete_relationship', array( __CLASS__, 'send_removal' ) );
		add_action( 'itegms_expire_relationship', array( __CLASS__, 'send_expired' ) );
	}

	/**
	 * Register email notifications with Exchange.
	 *
	 * @since 1.19.11
	 *
	 * @param \IT_Exchange_Email_Notifications $notifications
	 */
	public static function register_emails( \IT_Exchange_Email_Notifications $notifications ) {

		$r = $notifications->get_replacer();

		$notifications
			->register_notification( new \IT_Exchange_Customer_Email_Notification(
				__( 'Umbrella Membership Invitation', 'LION' ), 'itegms-invitation', null, array(
					'defaults'    => array(
						'subject' => sprintf( __( "You've been given access to %s by %s", 'LION' ),
							$r->format_tag( 'membership_name' ), $r->format_tag( 'customer_first_name' ) ),
						'body'    => self::get_default_invitation()
					),
					'group'       => __( 'Umbrella Memberships', 'LION' ),
					'description' => __( "Email sent to members when they're invited to an umbrella membership.", 'LION' )
				)
			) )
			->register_notification( new \IT_Exchange_Customer_Email_Notification(
				__( 'Umbrella Membership Invitation New User', 'LION' ), 'itegms-invitation-new-user', null, array(
					'defaults'    => array(
						'subject' => sprintf( __( "You've been given access to %s by %s", 'LION' ),
							$r->format_tag( 'membership_name' ), $r->format_tag( 'customer_first_name' ) ),
						'body'    => self::get_default_new_user()
					),
					'group'       => __( 'Umbrella Memberships', 'LION' ),
					'description' => __( "Email sent to members when they're invited to an umbrella membership and have had an account created for them.", 'LION' )
				)
			) )
			->register_notification( new \IT_Exchange_Customer_Email_Notification(
				__( 'Umbrella Membership Removal', 'LION' ), 'itegms-removed', null, array(
					'defaults'    => array(
						'subject' => sprintf( __( "You're access to %s has been revoked by %s", 'LION' ),
							$r->format_tag( 'membership_name' ), $r->format_tag( 'customer_first_name' ) ),
						'body'    => self::get_default_removed()
					),
					'group'       => __( 'Umbrella Memberships', 'LION' ),
					'description' => __( "Email sent to members when they're removed from an umbrella membership.", 'LION' )
				)
			) )
			->register_notification( new \IT_Exchange_Customer_Email_Notification(
				__( 'Umbrella Membership Expired', 'LION' ), 'itegms-expired', null, array(
					'defaults'    => array(
						'subject' => sprintf( __( "You're access to %s has expired", 'LION' ),
							$r->format_tag( 'membership_name' ) ),
						'body'    => self::get_default_expired()
					),
					'group'       => __( 'Umbrella Memberships', 'LION' ),
					'description' => __( "Email sent to members when their membership has expired.", 'LION' )
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
			'umbrella_membership_password', __( 'Umbrella Membership Password', 'LION' ),
			__( 'The newly created user\'s auto-generated password.', 'LION' ), function ( $context ) {
			return $context['umbrella-membership-password'];
		} );
		$tag->add_required_context( 'umbrella-membership-password' );
		$tag->add_available_for( 'itegms-invitation-new-user' );

		$replacer->add_tag( $tag );

		$tags = array(
			'customer_first_name',
			'customer_last_name',
			'customer_fullname',
			'customer_username',
			'customer_email'
		);

		foreach ( $tags as $tag ) {
			$tag = $replacer->get_tag( $tag );

			if ( $tag ) {
				$tag->add_available_for( 'itegms-invitation' )->add_available_for( 'itegms-invitation-new-user' )
				    ->add_available_for( 'itegms-removed' )->add_available_for( 'itegms-expired' );
			}
		}
	}

	/**
	 * Call when perform a save to bulk send all emails
	 *
	 * @param bool $doing
	 */
	public static function doing_save( $doing = true ) {
		self::$doing_save = $doing;

		if ( ! $doing && self::$queue ) {

			try {
				it_exchange_send_email( self::$queue );

			}
			catch ( \IT_Exchange_Email_Delivery_Exception $e ) {
				it_exchange_add_message( 'error', $e->getMessage() );
			}

			self::$queue = array();
		}
	}

	/**
	 * Send the invitation notification.
	 *
	 * @since 1.0
	 *
	 * @param Relationship $rel
	 */
	public static function send_invitation( Relationship $rel ) {

		$notification = self::$notifications->get_notification( 'itegms-invitation' );

		if ( ! $notification->is_active() ) {
			return;
		}

		$email = new \IT_Exchange_Email(
			new \IT_Exchange_Email_Recipient_Customer( $rel->get_member() ),
			$notification,
			array(
				'umbrella-membership' => $rel,
				'customer'            => $rel->get_purchase()->get_customer(),
				'membership'          => $rel->get_purchase()->get_membership()
			)
		);

		if ( self::$doing_save ) {
			self::$queue[] = $email;
		} else {
			it_exchange_send_email( $email );
		}
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

		$notification = self::$notifications->get_notification( 'itegms-invitation-new-user' );

		if ( ! $notification->is_active() ) {
			return;
		}

		$email = new \IT_Exchange_Email(
			new \IT_Exchange_Email_Recipient_Customer( $rel->get_member() ),
			$notification,
			array(
				'umbrella-membership'          => $rel,
				'customer'                     => $rel->get_purchase()->get_customer(),
				'umbrella-membership-password' => $password,
				'membership'                   => $rel->get_purchase()->get_membership()
			)
		);

		if ( self::$doing_save ) {
			self::$queue[] = $email;
		} else {
			it_exchange_send_email( $email );
		}
	}

	/**
	 * Send the removal notification.
	 *
	 * @since 1.0
	 *
	 * @param Relationship $rel
	 */
	public static function send_removal( Relationship $rel ) {

		$notification = self::$notifications->get_notification( 'itegms-removed' );

		if ( ! $notification->is_active() ) {
			return;
		}

		$email = new \IT_Exchange_Email(
			new \IT_Exchange_Email_Recipient_Customer( $rel->get_member() ),
			$notification,
			array(
				'umbrella-membership' => $rel,
				'customer'            => $rel->get_purchase()->get_customer(),
				'membership'          => $rel->get_purchase()->get_membership()
			)
		);

		if ( self::$doing_save ) {
			self::$queue[] = $email;
		} else {
			it_exchange_send_email( $email );
		}
	}

	/**
	 * Send the expired notification.
	 *
	 * @since 1.0
	 *
	 * @param Relationship $rel
	 */
	public static function send_expired( Relationship $rel ) {

		$notification = self::$notifications->get_notification( 'itegms-expired' );

		if ( ! $notification->is_active() ) {
			return;
		}

		$email = new \IT_Exchange_Email(
			new \IT_Exchange_Email_Recipient_Customer( $rel->get_member() ),
			$notification,
			array(
				'umbrella-membership' => $rel,
				'customer'            => $rel->get_purchase()->get_customer(),
				'membership'          => $rel->get_purchase()->get_membership()
			)
		);

		if ( self::$doing_save ) {
			self::$queue[] = $email;
		} else {
			it_exchange_send_email( $email );
		}
	}

	/**
	 * Get the default invitation email.
	 *
	 * @since 1.19.11
	 *
	 * @return string
	 */
	protected static function get_default_invitation() {

		/** @var \IT_Exchange_Email_Tag_Replacer $r */
		$r = it_exchange_email_notifications()->get_replacer();

		return <<<TAG
		Hi {$r->format_tag( 'first_name' )}

Welcome to {$r->format_tag( 'company_name' )}'s {$r->format_tag( 'membership_name' )} program. You've been invited to this program by {$r->format_tag( 'customer_fullname' )}. If you have any questions about this, you can contact {$r->format_tag( 'customer_first_name' )} by email at {$r->format_tag( 'customer_email' )}.

You can access your exclusive membership content at the following url: {$r->format_tag( 'membership_dashboard' )}

- The {$r->format_tag( 'company_name' )} Team
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

		/** @var \IT_Exchange_Email_Tag_Replacer $r */
		$r = it_exchange_email_notifications()->get_replacer();

		return <<<TAG
		Hi {$r->format_tag( 'first_name' )}

Welcome to {$r->format_tag( 'company_name' )}!

You've been invited to {$r->format_tag( 'company_name' )}'s {$r->format_tag( 'membership_name' )} program by {$r->format_tag( 'customer_fullname' )}. If you have any questions about this, you can contact {$r->format_tag( 'customer_first_name' )} by email at {$r->format_tag( 'customer_email' )}.

We've automatically created an account for you.

You can login here, {$r->format_tag( 'login_link' )}, with the following information:

Username: {$r->format_tag( 'username' )}

Password: {$r->format_tag( 'umbrella_membership_password' )}

We recommend that you change your password when you login. You can do that from your profile page: {$r->format_tag( 'profile_link' )}

You can access your exclusive membership content at the following url: {$r->format_tag( 'membership_dashboard' )}

Welcome to {$r->format_tag( 'company_name' )}!

- The {$r->format_tag( 'company_name' )} Team
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

		/** @var \IT_Exchange_Email_Tag_Replacer $r */
		$r = it_exchange_email_notifications()->get_replacer();

		return <<<TAG
Hi {$r->format_tag( 'first_name' )}

Your access to {$r->format_tag( 'company_name' )}'s {$r->format_tag( 'membership_name' )} program has been revoked by {$r->format_tag( 'customer_fullname' )}. If you have any questions about this, you can contact {$r->format_tag( 'customer_first_name' )} by email at {$r->format_tag( 'customer_email' )}.

- The {$r->format_tag( 'company_name' )} Team
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

		/** @var \IT_Exchange_Email_Tag_Replacer $r */
		$r = it_exchange_email_notifications()->get_replacer();

		return <<<TAG
Hi {$r->format_tag( 'first_name' )}

Your access to {$r->format_tag( 'company_name' )}'s {$r->format_tag( 'membership_name' )} program has expired. This is typically due to a lapse of payment. If you have any questions about this, you can contact {$r->format_tag( 'customer_fullname' )} by email at {$r->format_tag( 'customer_email' )}.

- The {$r->format_tag( 'company_name' )} Team
TAG;
	}
}