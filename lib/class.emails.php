<?php
/**
 * Contains the class for managing emails.
 *
 * @since   1.9.11
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Membership_Emails
 */
class IT_Exchange_Membership_Emails {

	/**
	 * IT_Exchange_Membership_Emails constructor.
	 */
	public function __construct() {
		add_action( 'it_exchange_register_email_notifications', array( $this, 'register_emails' ) );
		add_action( 'it_exchange_email_notifications_register_tags', array( $this, 'register_tags' ) );
	}

	/**
	 * Send a welcome email.
	 *
	 * @since 1.9.11
	 *
	 * @param IT_Exchange_User_Membership $membership
	 */
	public static function send_welcome( IT_Exchange_User_Membership $membership ) {

		$notification = it_exchange_email_notifications()->get_notification( 'membership-welcome' );

		if ( ! $notification->is_active() ) {
			return;
		}

		$customer  = it_exchange_get_customer( $membership->get_user() );
		$recipient = new IT_Exchange_Email_Recipient_Customer( $customer );

		$email = new IT_Exchange_Email( $recipient, $notification, array(
			'membership' => $membership->get_membership(),
			'customer'   => $customer
		) );

		try {
			it_exchange_send_email( $email );
		}
		catch ( IT_Exchange_Email_Delivery_Exception $e ) {
			error_log( $e->getMessage() );
		}
	}

	/**
	 * Register emails.
	 *
	 * @since 1.9.11
	 *
	 * @param IT_Exchange_Email_Notifications $notifications
	 */
	public function register_emails( IT_Exchange_Email_Notifications $notifications ) {

		$notifications->register_notification( new IT_Exchange_Customer_Email_Notification(
			__( 'Membership Welcome', 'LION' ), 'membership-welcome', null, array(
				'defaults'    => array(
					'subject' => sprintf( __( 'Welcome to %s!', 'LION' ),
						'[it_exchange_email show=membership_name]' ),
					'body'    => self::get_default_welcome()
				),
				'group'       => __( 'Membership', 'LION' ),
				'description' => __( "Email sent to new members.", 'LION' )
			)
		) );
	}

	/**
	 * Register email tags.
	 *
	 * @since 1.9.11
	 *
	 * @param IT_Exchange_Email_Tag_Replacer $replacer
	 */
	public function register_tags( IT_Exchange_Email_Tag_Replacer $replacer ) {

		$tags = array(
			'membership_name'      => array(
				'name'      => __( 'Membership Name', 'it-l10n-ithemes-exchange' ),
				'desc'      => __( 'The name of the membership program.', 'it-l10n-ithemes-exchange' ),
				'context'   => array( 'membership' ),
				'available' => array(
					'membership-welcome',
					'itegms-invitation',
					'itegms-invitation-new-user',
					'itegms-removed',
					'itegms-expired'
				)
			),
			'membership_dashboard' => array(
				'name'      => __( 'Membership Dashboard', 'it-l10n-ithemes-exchange' ),
				'desc'      => __( 'A link to the membership dashboard.', 'it-l10n-ithemes-exchange' ),
				'context'   => array( 'membership' ),
				'available' => array(
					'membership-welcome',
					'itegms-invitation',
					'itegms-invitation-new-user',
					'itegms-removed',
					'itegms-expired'
				)
			),
		);

		foreach ( $tags as $tag => $config ) {

			$obj = new IT_Exchange_Email_Tag_Base( $tag, $config['name'], $config['desc'], array( $this, $tag ) );

			foreach ( $config['context'] as $context ) {
				$obj->add_required_context( $context );
			}

			foreach ( $config['available'] as $notification ) {
				$obj->add_available_for( $notification );
			}

			$replacer->add_tag( $obj );
		}
	}

	/**
	 * Replace the membership name tag.
	 *
	 * @since 1.9.11
	 *
	 * @param array $context
	 *
	 * @return string
	 */
	public function membership_name( $context ) {
		return $context['membership']->post_title;
	}

	/**
	 * Replace the membership dashboard tag.
	 *
	 * @since 1.9.11
	 *
	 * @param array $context
	 *
	 * @return string
	 */
	public function membership_dashboard( $context ) {
		return $context['membership']->get_dashboard();
	}

	/**
	 * Get the default welcome email contents.
	 *
	 * @since 1.9.11
	 *
	 * @return string
	 */
	protected static function get_default_welcome() {
		return <<<TAG
		Hi [it_exchange_email show="first_name"],

Welcome to [it_exchange_email show="company_name"]'s [it_exchange_email show="membership_name"] program.

You can access your exclusive membership content at the following url: [it_exchange_email show="membership_dashboard"]

- The [it_exchange_email show="company_name"] Team
TAG;
	}

}

new IT_Exchange_Membership_Emails();