<?php
/**
 * Plugin Settings
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2015 (c) Iron Bound Designs.
 * @license     GPLv2
 */

namespace ITEGMS;

use IronBound\WP_Notifications\Template\Editor;
use IronBound\WP_Notifications\Template\Factory;

/**
 * Class Settings
 *
 * @package ITEGMS
 */
class Settings {

	/**
	 * @var string $status_message will be displayed if not empty
	 */
	private $status_message;

	/**
	 * @var string $error_message will be displayed if not empty
	 */
	private $error_message;

	/**
	 * @var array
	 */
	private $form_values;

	/**
	 * Settings page.
	 */
	const PAGE = 'it-exchange-addons';

	/**
	 * Short slug.
	 */
	const SHORT = 'itegms';

	/**
	 * Display the settings page.
	 *
	 * @since 1.0
	 */
	public static function display() {
		$settings = new Settings();
		$settings->print_settings_page();
	}

	/**
	 * Initialize the addon settings.
	 *
	 * @since 1.0
	 */
	public static function init() {

		$invitation = self::get_default_invitation();
		$new_user   = self::get_default_new_user();
		$removed    = self::get_default_removed();
		$expired    = self::get_default_expired();

		add_filter( 'it_storage_get_defaults_exchange_addon_' . self::SHORT, function ( $defaults )
		use ( $invitation, $new_user, $removed, $expired ) {

			$defaults['license']             = '';
			$defaults['activation']          = '';
			$defaults['invitation']          = $invitation;
			$defaults['invitation-new-user'] = $new_user;
			$defaults['removed']             = $removed;
			$defaults['expired']             = $expired;

			return $defaults;
		} );
	}

	/**
	 * Get the default invitation email.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	protected static function get_default_invitation() {
		return <<<TAG
		Hi {first_name},

Welcome to {shop_name}'s {membership_name} program. You've been invited to this program by {payer_first_name} {payer_last_name}. If you have any questions about this, you can contact {payer_first_name} by email at {payer_email}.

You can access your exclusive membership content at the following url: {membership_dashboard_url}

- The {shop_name} Team
TAG;
	}

	/**
	 * Get the default new user invitation email.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	protected static function get_default_new_user() {
		return <<<TAG
		Hi {first_name},

Welcome to {shop_name}!

You've been invited to {shop_name}'s {membership_name} program by {payer_first_name} {payer_last_name}. If you have any questions about this, you can contact {payer_first_name} by email at {payer_email}.

We've automatically created an account for you.

You can login here, {login_url}, with the following information:

Username: {username}

Password: {password}

We recommend that you change your password when you login. You can do that from your profile page: {profile_url}

You can access your exclusive membership content at the following url: {membership_dashboard_url}

Welcome to {shop_name}!

- The {shop_name} Team
TAG;
	}

	/**
	 * Get the default removed email.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	protected static function get_default_removed() {
		return <<<TAG
Hi {first_name},

Your access to {shop_name}'s {membership_name} program has been revoked by {payer_first_name} {payer_last_name}. If you have any questions about this, you can contact {payer_first_name} by email at {payer_email}.

- The {shop_name} Team
TAG;
	}

	/**
	 * Get the default expired email.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	protected static function get_default_expired() {
		return <<<TAG
Hi {first_name},

Your access to {shop_name}'s {membership_name} program has expired. This is typically due to a lapse of payment. If you have any questions about this, you can contact {payer_first_name} {payer_last_name} by email at {payer_email}.

- The {shop_name} Team
TAG;
	}


	/**
	 * Get an option.
	 *
	 * @since 1.0
	 *
	 * @param string $field
	 *
	 * @return mixed|null
	 */
	public static function get( $field = '' ) {

		$options = it_exchange_get_option( 'addon_' . self::SHORT );

		if ( empty( $field ) ) {
			return $options;
		}

		if ( isset( $options[ $field ] ) ) { // if the field exists with that name just return it
			return $options[ $field ];
		} else if ( strpos( $field, "." ) !== false ) { // if the field name was passed using array dot notation
			$pieces  = explode( '.', $field );
			$context = $options;
			foreach ( $pieces as $piece ) {
				if ( ! is_array( $context ) || ! array_key_exists( $piece, $context ) ) {
					// error occurred
					return null;
				}
				$context = &$context[ $piece ];
			}

			return $context;
		} else {
			return null; // we didn't find the data specified
		}
	}

	/**
	 * Class constructor
	 *
	 * Sets up the class.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		$page  = empty( $_GET['page'] ) ? false : $_GET['page'];
		$addon = empty( $_GET['add-on-settings'] ) ? false : $_GET['add-on-settings'];

		if ( empty( $_POST ) || ! is_admin() ) {
			return;
		}

		if ( self::PAGE != $page || Plugin::ADD_ON != $addon ) {
			return;
		}

		add_action( 'it_exchange_save_add_on_settings_' . self::SHORT, array(
			$this,
			'save'
		) );

		if ( isset( $_POST['activate'] ) ) {
			$this->activate();
		} else if ( isset( $_POST['deactivate'] ) ) {
			$this->deactivate();
		} else {
			do_action( 'it_exchange_save_add_on_settings_' . self::SHORT );
		}
	}

	/**
	 * Prints settings page
	 *
	 * @since 1.0
	 */
	function print_settings_page() {
		$settings          = it_exchange_get_option( 'addon_' . self::SHORT, true );
		$this->form_values = empty( $this->error_message ) ? $settings : \ITForm::get_post_data();

		$form_options = array(
			'id'     => 'it-exchange-add-on-' . self::SHORT . '-settings',
			'action' => 'admin.php?page=' . self::PAGE . '&add-on-settings=' . Plugin::ADD_ON,
		);

		$form = new \ITForm( $this->form_values, array(
			'prefix' => 'it-exchange-add-on-' . self::SHORT
		) );

		if ( ! empty ( $this->status_message ) ) {
			\ITUtility::show_status_message( $this->status_message );
		}
		if ( ! empty( $this->error_message ) ) {
			\ITUtility::show_error_message( $this->error_message );
		}
		?>
		<div class="wrap">
			<h2><?php _e( 'Umbrella Membership Settings', Plugin::SLUG ); ?></h2>

			<?php do_action( 'it_exchange_' . self::SHORT . '_settings_page_top' ); ?>
			<?php do_action( 'it_exchange_addon_settings_page_top' ); ?>
			<?php $form->start_form( $form_options, 'it-exchange-itegms-settings' ); ?>
			<?php do_action( 'it_exchange_' . self::SHORT . '_settings_form_top', $form ); ?>
			<?php $this->get_form_table( $form, $this->form_values ); ?>
			<?php do_action( 'it_exchange_' . self::SHORT . '_settings_form_bottom', $form ); ?>

			<p class="submit">
				<?php $form->add_submit( 'submit', array(
					'value' => __( 'Save Changes', Plugin::SLUG ),
					'class' => 'button button-primary button-large'
				) ); ?>
			</p>

			<?php $form->end_form(); ?>
			<?php $this->inline_scripts(); ?>
			<?php do_action( 'it_exchange_' . self::SHORT . '_settings_page_bottom' ); ?>
			<?php do_action( 'it_exchange_addon_settings_page_bottom' ); ?>
		</div>

		<?php
	}

	/**
	 * Render the settings table
	 *
	 * @since 1.0
	 *
	 * @param \ITForm $form
	 * @param array   $settings
	 */
	function get_form_table( $form, $settings = array() ) {
		if ( ! empty( $settings ) ) {
			foreach ( $settings as $key => $var ) {
				$form->set_option( $key, $var );
			}
		}

		$info       = $this->get_key_info();
		$activation = $form->get_option( 'activation' );

		if ( $info ) {

			if ( ! isset( $info->activations->list->{$activation} ) || $info->activations->list->{$activation}->status == 'deactivated' ) {
				$still_active = false;
			} else {
				$still_active = true;
			}

		} else {
			$still_active = true;
		}
		?>

		<style type="text/css">
			.description.active {
				color: #8cc53e;
			}

			.description.expired {
				color: #ffba00;
			}

			.description.disabled {
				color: #dd3d36;
			}
		</style>

		<div class="it-exchange-addon-settings it-exchange-<?php echo esc_attr( self::SHORT ); ?>-addon-settings">


			<label for="license"><?php _e( "License Key", Plugin::SLUG ); ?></label>
			<?php $form->add_text_box( 'license' ); ?>

			<?php if ( empty( $activation ) || ! $still_active ): ?>
				<?php submit_button( __( "Activate", Plugin::SLUG ), 'secondary large', 'activate', false, 'style="height:46px;padding:0 20px;"' ); ?>
			<?php else: ?>
				<?php submit_button( __( "Deactivate", Plugin::SLUG ), 'secondary large', 'deactivate', false, 'style="height:46px;padding:0 20px;"' ); ?>
			<?php endif; ?>

			<?php if ( $info && $still_active ): ?>
				<p class="description <?php echo $info->status; ?>">
					<?php if ( $info->status == 'active' ): ?>
						<?php if ( $info->expires ): ?>
							<?php printf( __( "License is active and expires %s", Plugin::SLUG ), date( get_option( 'date_format' ), strtotime( $info->expires ) ) ); ?>
						<?php else: ?>
							<?php printf( __( "License is active.", Plugin::SLUG ) ); ?>
						<?php endif; ?>
					<?php elseif ( $info->status == 'expired' ): ?>
						<?php _e( "License has expired.", Plugin::SLUG ); ?>
					<?php elseif ( $info->status == 'disabled' ): ?>
						<?php _e( "License is disabled.", Plugin::SLUG ); ?>
					<?php endif; ?>
				</p>
			<?php elseif ( ! $still_active && $form->get_option( 'activation' ) ): ?>
				<p class="description expired"><?php _e( "License deactivated remotely.", Plugin::SLUG ); ?></p>
			<?php endif; ?>

			<h3><?php _e( "General", Plugin::SLUG ); ?></h3>

			<div class="invitation-container">
				<label for="invitation"><?php _e( "Invitation Email", Plugin::SLUG ); ?></label>

				<p class="description"><?php _e( "Email sent to members when they're invited to an umbrella membership.", Plugin::SLUG ); ?></p>

				<?php
				$editor = new Editor( Factory::make( 'itegms-invitation' ), array(
					'mustSelectItem'    => __( "You must select an item", Plugin::SLUG ),
					'selectTemplateTag' => __( "Select Template Tag", Plugin::SLUG ),
					'templateTag'       => __( "Template Tag", Plugin::SLUG ),
					'selectATag'        => __( "Select a tag", Plugin::SLUG ),
					'insertTag'         => __( "Insert", Plugin::SLUG ),
					'cancel'            => __( "Cancel", Plugin::SLUG ),
					'insertTemplateTag' => __( "Insert Template Tag", Plugin::SLUG )
				) );

				$editor->thickbox();

				wp_editor( $settings['invitation'], 'invitation', array(
					'textarea_name' => 'it-exchange-add-on-' . self::SHORT . '-invitation',
					'textarea_rows' => 10,
					'textarea_cols' => 30,
					'editor_class'  => 'large-text'
				) );

				$editor->__destruct();
				unset( $editor );

				$form->get_text_area( 'invitation', array(
					'rows'  => 10,
					'cols'  => 30,
					'class' => 'large-text'
				) ); ?>
			</div>

			<div class="invitation-new-user-container">
				<label for="invitation-new-user">
					<?php _e( "Invitation & New User Email", Plugin::SLUG ); ?>
				</label>

				<p class="description">
					<?php _e( "Email sent to members when they're invited to an umbrella membership and have had an account created for them.", Plugin::SLUG ); ?>
				</p>

				<?php
				$editor = new Editor( Factory::make( 'itegms-invitation-new-user' ), array(
					'mustSelectItem'    => __( "You must select an item", Plugin::SLUG ),
					'selectTemplateTag' => __( "Select Template Tag", Plugin::SLUG ),
					'templateTag'       => __( "Template Tag", Plugin::SLUG ),
					'selectATag'        => __( "Select a tag", Plugin::SLUG ),
					'insertTag'         => __( "Insert", Plugin::SLUG ),
					'cancel'            => __( "Cancel", Plugin::SLUG ),
					'insertTemplateTag' => __( "Insert Template Tag", Plugin::SLUG )
				) );

				$editor->thickbox();

				wp_editor( $settings['invitation-new-user'], 'invitation-new-user', array(
					'textarea_name' => 'it-exchange-add-on-' . self::SHORT . '-invitation-new-user',
					'textarea_rows' => 10,
					'textarea_cols' => 30,
					'editor_class'  => 'large-text'
				) );

				$editor->__destruct();
				unset( $editor );

				$form->get_text_area( 'invitation-new-user', array(
					'rows'  => 10,
					'cols'  => 30,
					'class' => 'large-text'
				) ); ?>
			</div>

			<div class="removed-container">
				<label for="removed"><?php _e( "Removed Email", Plugin::SLUG ); ?></label>

				<p class="description"><?php _e( "Email sent to members when they're removed from an umbrella membership.", Plugin::SLUG ); ?></p>

				<?php
				$editor = new Editor( Factory::make( 'itegms-removed' ), array(
					'mustSelectItem'    => __( "You must select an item", Plugin::SLUG ),
					'selectTemplateTag' => __( "Select Template Tag", Plugin::SLUG ),
					'templateTag'       => __( "Template Tag", Plugin::SLUG ),
					'selectATag'        => __( "Select a tag", Plugin::SLUG ),
					'insertTag'         => __( "Insert", Plugin::SLUG ),
					'cancel'            => __( "Cancel", Plugin::SLUG ),
					'insertTemplateTag' => __( "Insert Template Tag", Plugin::SLUG )
				) );

				$editor->thickbox();

				wp_editor( $settings['removed'], 'removed', array(
					'textarea_name' => 'it-exchange-add-on-' . self::SHORT . '-removed',
					'textarea_rows' => 10,
					'textarea_cols' => 30,
					'editor_class'  => 'large-text'
				) );

				$editor->__destruct();
				unset( $editor );

				$form->get_text_area( 'removed', array(
					'rows'  => 10,
					'cols'  => 30,
					'class' => 'large-text'
				) ); ?>
			</div>

			<div class="expired-container">
				<label for="expired"><?php _e( "Expired Email", Plugin::SLUG ); ?></label>

				<p class="description"><?php _e( "Email sent to members when their membership has expired.", Plugin::SLUG ); ?></p>

				<?php
				$editor = new Editor( Factory::make( 'itegms-expired' ), array(
					'mustSelectItem'    => __( "You must select an item", Plugin::SLUG ),
					'selectTemplateTag' => __( "Select Template Tag", Plugin::SLUG ),
					'templateTag'       => __( "Template Tag", Plugin::SLUG ),
					'selectATag'        => __( "Select a tag", Plugin::SLUG ),
					'insertTag'         => __( "Insert", Plugin::SLUG ),
					'cancel'            => __( "Cancel", Plugin::SLUG ),
					'insertTemplateTag' => __( "Insert Template Tag", Plugin::SLUG )
				) );

				$editor->thickbox();

				wp_editor( $settings['expired'], 'expired', array(
					'textarea_name' => 'it-exchange-add-on-' . self::SHORT . '-expired',
					'textarea_rows' => 10,
					'textarea_cols' => 30,
					'editor_class'  => 'large-text'
				) );

				$editor->__destruct();
				unset( $editor );

				$form->get_text_area( 'expired', array(
					'rows'  => 10,
					'cols'  => 30,
					'class' => 'large-text'
				) ); ?>
			</div>
		</div>

		<?php
	}

	/**
	 * Render inline scripts.
	 *
	 * @since 1.0
	 */
	function inline_scripts() {
		wp_enqueue_script( 'jquery' );
		?>

		<script type="text/javascript">
			jQuery(document).ready(function ($) {


			});
		</script>

		<?php
	}

	/**
	 * Save settings.
	 *
	 * @since 1.0
	 */
	function save() {
		$defaults = it_exchange_get_option( 'addon_' . self::SHORT );

		$new_values = wp_parse_args( \ITForm::get_post_data(), $defaults );
		// Check nonce
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'it-exchange-' . self::SHORT . '-settings' ) ) {
			$this->error_message = __( 'Error. Please try again', Plugin::SLUG );

			return;
		}

		/**
		 * Filter the settings errors before saving.
		 *
		 * @since 1.0
		 *
		 * @param string[] $errors     Errors
		 * @param array    $new_values Mixed
		 */
		$errors = apply_filters( 'it_exchange_add_on_' . self::SHORT . '_validate_settings',
			$this->get_form_errors( $new_values, $defaults ), $new_values );

		if ( ! $errors && it_exchange_save_option( 'addon_' . self::SHORT, $new_values ) ) {
			$this->status_message = __( 'Settings saved.', Plugin::SLUG );
		} else if ( $errors ) {
			$errors              = implode( '<br />', $errors );
			$this->error_message = $errors;
		} else {
			$this->error_message = __( 'Settings not saved.', Plugin::SLUG );
		}
	}

	/**
	 * Validates for values.
	 *
	 * @since 1.0
	 *
	 * @param array $values
	 * @param array $old_values
	 *
	 * @return array
	 */
	public function get_form_errors( $values, $old_values ) {
		$errors = array();

		return $errors;
	}

	/**
	 * Handle activation POST request.
	 *
	 * @since 1.0
	 */
	protected function activate() {

		if ( empty( $_POST['it-exchange-add-on-itegms-license'] ) ) {
			$this->error_message = __( "A license key is required for activation", Plugin::SLUG );

			return;
		}

		$key = $_POST['it-exchange-add-on-itegms-license'];

		$response = Plugin::$updater->activate( $key );

		if ( is_wp_error( $response ) ) {

			if ( ! $response->get_error_message() ) {
				$msg = __( "An unexpected error occurred.", Plugin::SLUG );
			} else {
				$msg = $response->get_error_message();
			}

			$this->error_message = $msg;

			return;
		}

		$options               = it_exchange_get_option( 'addon_itegms' );
		$options['license']    = $key;
		$options['activation'] = $response;
		it_exchange_save_option( 'addon_itegms', $options );

		$info = Plugin::$updater->get_info( $key );

		if ( ! is_wp_error( $info ) ) {
			set_transient( 'itegms_key_info', $info, DAY_IN_SECONDS );

			$active = $info->activations->count_active;
			$max    = $info->max;

			if ( empty( $max ) ) {
				$left = '-';
			} else {
				$left = $max - $active;
			}

			if ( $left == '-' ) {
				$this->status_message = __( "License activated. You have unlimited activations left.", Plugin::SLUG );
			} else {
				$this->status_message = sprintf(
					_n( "License activated. You have %d activation left.",
						"License activated. You have %d activations left.",
						$left, Plugin::SLUG ), $left
				);
			}
		} else {
			$this->status_message = __( "License activated.", Plugin::SLUG );
		}
	}

	/**
	 * Handle deactivation POST request.
	 *
	 * @since 1.0
	 */
	protected function deactivate() {

		$options = it_exchange_get_option( 'addon_itegms' );

		$response = Plugin::$updater->deactivate( $options['license'], $options['activation'] );

		if ( is_wp_error( $response ) ) {
			$this->error_message = $response->get_error_message();

			return;
		}

		$options               = it_exchange_get_option( 'addon_itegms' );
		$options['activation'] = '';
		it_exchange_save_option( 'addon_itegms', $options );

		$this->status_message = __( "License deactivated.", Plugin::SLUG );
	}

	/**
	 * Get info about the key.
	 *
	 * @since 1.0.2
	 *
	 * @param bool $break_cache
	 *
	 * @return object|bool
	 */
	protected function get_key_info( $break_cache = false ) {

		$options = it_exchange_get_option( 'addon_itegms' );
		$key     = $options['license'];

		if ( $break_cache || false === ( $data = get_transient( 'itegms_key_info' ) ) ) {

			$data = Plugin::$updater->get_info( $key );

			if ( ! is_wp_error( $data ) ) {
				set_transient( 'itegms_key_info', $data, DAY_IN_SECONDS );
			}
		}

		return $data;
	}
}