<?php
/**
 * Hooks for email sending.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2015 (c) Iron Bound Designs.
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
	 * Constructor.
	 */
	public function __construct() {
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
	 * Invitation listeners.
	 *
	 * @since 1.0
	 *
	 * @param Manager $manager
	 */
	public static function invitation_listeners( Manager $manager ) {

		foreach ( self::get_common_listeners() as $listener ) {
			$manager->listen( $listener );
		}

		$manager->listen( new Listener( 'membership_dashboard_url', function ( Relationship $rel ) {

			$membership = $rel->get_purchase()->get_membership();

			$page_slug       = 'memberships';
			$permalinks      = (bool) get_option( 'permalink_structure' );
			$membership_slug = $membership->post_name;

			if ( $permalinks ) {
				$url = trailingslashit( it_exchange_get_page_url( $page_slug ) ) . $membership_slug;
			} else {
				$url = it_exchange_get_page_url( $page_slug ) . '=' . $membership_slug;
			}

			return $url;
		} ) );
	}

	/**
	 * Invitation listeners when a new user is created as a result of the
	 * invitation.
	 *
	 * @since 1.0
	 *
	 * @param Manager $manager
	 */
	public static function invitation_new_user_listeners( Manager $manager ) {

		foreach ( self::get_common_listeners() as $listener ) {
			$manager->listen( $listener );
		}

		$manager->listen( new Listener( 'membership_dashboard_url', function ( Relationship $rel ) {

			$membership = $rel->get_purchase()->get_membership();

			$page_slug       = 'memberships';
			$permalinks      = (bool) get_option( 'permalink_structure' );
			$membership_slug = $membership->post_name;

			if ( $permalinks ) {
				$url = trailingslashit( it_exchange_get_page_url( $page_slug ) ) . $membership_slug;
			} else {
				$url = it_exchange_get_page_url( $page_slug ) . '=' . $membership_slug;
			}

			return $url;
		} ) );

		$manager->listen( new Listener( 'password', function ( $password ) {
			return $password;
		} ) );
	}

	/**
	 * Removed listeners.
	 *
	 * @since 1.0
	 *
	 * @param Manager $manager
	 */
	public static function removed_listeners( Manager $manager ) {

		foreach ( self::get_common_listeners() as $listener ) {
			$manager->listen( $listener );
		}
	}

	/**
	 * Expired listeners.
	 *
	 * @since 1.0
	 *
	 * @param Manager $manager
	 */
	public static function expired_listeners( Manager $manager ) {

		foreach ( self::get_common_listeners() as $listener ) {
			$manager->listen( $listener );
		}
	}

	/**
	 * Get common listeners.
	 *
	 * @since 1.0
	 *
	 * @return Listener[]
	 */
	private static function get_common_listeners() {
		$listeners = array();

		$listeners[] = new Listener( 'shop_name', function () {

			$settings = it_exchange_get_option( 'settings_general' );

			return $settings['company-name'];
		} );

		$listeners[] = new Listener( 'username', function ( \WP_User $to ) {
			return $to->user_login;
		} );

		$listeners[] = new Listener( 'user_email', function ( \WP_User $to ) {
			return $to->user_email;
		} );

		$listeners[] = new Listener( 'first_name', function ( \WP_User $to ) {
			return $to->first_name;
		} );

		$listeners[] = new Listener( 'last_name', function ( \WP_User $to ) {
			return $to->last_name;
		} );

		$listeners[] = new Listener( 'login_url', function () {
			return it_exchange_get_page_url( 'login' );
		} );

		$listeners[] = new Listener( 'profile_url', function () {
			return it_exchange_get_page_url( 'profile' );
		} );

		$listeners[] = new Listener( 'membership_name', function ( Relationship $rel ) {
			return $rel->get_purchase()->get_membership()->post_title;
		} );

		$listeners[] = new Listener( 'membership_url', function ( Relationship $rel ) {
			return get_permalink( $rel->get_purchase()->get_membership()->ID );
		} );

		$listeners[] = new Listener( 'payer_first_name', function ( Relationship $rel ) {
			return $rel->get_purchase()->get_customer()->wp_user->first_name;
		} );

		$listeners[] = new Listener( 'payer_last_name', function ( Relationship $rel ) {
			return $rel->get_purchase()->get_customer()->wp_user->last_name;
		} );

		$listeners[] = new Listener( 'payer_username', function ( Relationship $rel ) {
			return $rel->get_purchase()->get_customer()->wp_user->user_login;
		} );

		$listeners[] = new Listener( 'payer_email', function ( Relationship $rel ) {
			return $rel->get_purchase()->get_customer()->wp_user->user_email;
		} );

		return $listeners;
	}

	/**
	 * Send the invitation notification.
	 *
	 * @since 1.0
	 *
	 * @param Relationship $rel
	 */
	public static function send_invitation( Relationship $rel ) {

		$to      = $rel->get_member()->wp_user;
		$manager = Factory::make( 'itegms-invitation' );
		$message = Settings::get( 'invitation' );
		$subject = sprintf( __( 'You\'ve been given access to %1$s by %2$s', Plugin::SLUG ),
			$rel->get_purchase()->get_membership()->post_title,
			$rel->get_purchase()->get_customer()->wp_user->first_name
		);

		/**
		 * Filter the subject line of the invitation notification.
		 *
		 * @since 1.0
		 *
		 * @param string       $subject
		 * @param Relationship $rel
		 */
		$subject = apply_filters( 'itegms_invitation_notification_subject', $subject, $rel );

		/**
		 * Filter the message of the invitation notification.
		 *
		 * @since 1.0
		 *
		 * @param string       $message
		 * @param Relationship $rel
		 */
		$message = apply_filters( 'itegms_invitation_notification_message', $message, $rel );

		$notification = new Notification( $to, $manager, $message, $subject );

		$notification->add_data_source( $rel );

		$notification->set_strategy( new iThemes_Exchange() );
		$notification->send();
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

		$to      = $rel->get_member()->wp_user;
		$manager = Factory::make( 'itegms-invitation-new-user' );
		$message = Settings::get( 'invitation-new-user' );
		$subject = sprintf( __( 'You\'ve been given access to %1$s by %2$s', Plugin::SLUG ),
			$rel->get_purchase()->get_membership()->post_title,
			$rel->get_purchase()->get_customer()->wp_user->first_name );

		/**
		 * Filter the subject line of the invitation new user notification.
		 *
		 * @since 1.0
		 *
		 * @param string       $subject
		 * @param Relationship $rel
		 * @param string       $password
		 */
		$subject = apply_filters( 'itegms_invitation_new_user_notification_subject', $subject, $rel, $password );

		/**
		 * Filter the message of the invitation new user notification.
		 *
		 * @since 1.0
		 *
		 * @param string       $message
		 * @param Relationship $rel
		 * @param string       $password
		 */
		$message = apply_filters( 'itegms_invitation_new_user_notification_message', $message, $rel, $password );

		$notification = new Notification( $to, $manager, $message, $subject );

		$notification->add_data_source( $rel );
		$notification->add_data_source( new Container( $password ), 'password' );

		$notification->set_strategy( new iThemes_Exchange() );
		$notification->send();
	}

	/**
	 * Send the removal notification.
	 *
	 * @since 1.0
	 *
	 * @param Relationship $rel
	 */
	public static function send_removal( Relationship $rel ) {

		$to      = $rel->get_member()->wp_user;
		$manager = Factory::make( 'itegms-removed' );
		$message = Settings::get( 'removed' );
		$subject = sprintf( __( 'Your access to %1$s has been revoked by %2$s', Plugin::SLUG ),
			$rel->get_purchase()->get_membership()->post_title,
			$rel->get_purchase()->get_customer()->wp_user->first_name );

		/**
		 * Filter the subject line of the removal notification.
		 *
		 * @since 1.0
		 *
		 * @param string       $subject
		 * @param Relationship $rel
		 */
		$subject = apply_filters( 'itegms_removal_notification_subject', $subject, $rel );

		/**
		 * Filter the message of the removal notification.
		 *
		 * @since 1.0
		 *
		 * @param string       $message
		 * @param Relationship $rel
		 */
		$message = apply_filters( 'itegms_removal_notification_message', $message, $rel );

		$notification = new Notification( $to, $manager, $message, $subject );

		$notification->add_data_source( $rel );;

		$notification->set_strategy( new iThemes_Exchange() );
		$notification->send();
	}

	/**
	 * Send the expired notification.
	 *
	 * @since 1.0
	 *
	 * @param Relationship $rel
	 */
	public static function send_expired( Relationship $rel ) {

		$to      = $rel->get_member()->wp_user;
		$manager = Factory::make( 'itegms-expired' );
		$message = Settings::get( 'expired' );
		$subject = sprintf( __( 'Your access to %1$s has expired.', Plugin::SLUG ),
			$rel->get_purchase()->get_membership()->post_title );

		/**
		 * Filter the subject line of the expired notification.
		 *
		 * @since 1.0
		 *
		 * @param string       $subject
		 * @param Relationship $rel
		 */
		$subject = apply_filters( 'itegms_expired_notification_subject', $subject, $rel );

		/**
		 * Filter the message of the expired notification.
		 *
		 * @since 1.0
		 *
		 * @param string       $message
		 * @param Relationship $rel
		 */
		$message = apply_filters( 'itegms_expired_notification_message', $message, $rel );

		$notification = new Notification( $to, $manager, $message, $subject );

		$notification->add_data_source( $rel );;

		$notification->set_strategy( new iThemes_Exchange() );
		$notification->send();
	}
}