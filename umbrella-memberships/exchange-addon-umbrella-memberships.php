<?php

namespace ITEGMS;

/**
 * Class Plugin
 *
 * @package ITEGMS
 */
class Plugin {

	/**
	 * Translation Slug.
	 */
	const SLUG = 'ibd-exchange-addon-itegms';

	/**
	 * Exchange add-on slug.
	 */
	const ADD_ON = 'umbrella-memberships';

	/**
	 * @var string
	 */
	static $dir;

	/**
	 * @var string
	 */
	static $url;

	/**
	 * @var string
	 */
	static $version;

	/**
	 * Constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		self::$dir     = plugin_dir_path( __FILE__ );
		self::$url     = plugin_dir_url( __FILE__ );
		self::$version = ITE_MEMBERSHIP_PLUGIN_VERSION;

		register_activation_hook( __FILE__, array( $this, 'activate' ) );

		add_action( 'it_exchange_register_addons', array( $this, 'register' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
	}

	/**
	 * Run the upgrade routine if necessary.
	 *
	 * @since 1.0
	 */
	public static function upgrade() {
		$current_version = get_option( 'itegms_version', 0.1 );

		if ( $current_version != self::$version ) {

			/**
			 * Runs when the version upgrades.
			 *
			 * @since 1.0
			 *
			 * @param string $current_version
			 * @param string $new_version
			 */
			do_action( 'itegms_upgrade', $current_version, self::$version );

			update_option( 'itegms_version', self::$version );
		}
	}

	/**
	 * Register this add-on with iThemes Exchange.
	 *
	 * @since 1.0
	 */
	public function register() {

		$desc = __( 'Sell umbrella memberships with iThemes Exchange.', 'LION' );
		$desc .= ' ' . __( "Allows for one customer to pay and manage memberships for multiple users.", 'LION' );

		$options = array(
			'name'              => __( 'Umbrella Memberships', 'LION' ),
			'description'       => $desc,
			'author'            => 'iThemes',
			'author_url'        => 'https://ithemes.com/exchange/membership',
			'file'              => dirname( __FILE__ ) . '/init.php',
			'icon'              => self::$url . 'assets/img/icon-50.png',
			'category'          => 'other',
			'settings-callback' => array( 'ITEGMS\Settings', 'display' ),
			'basename'          => plugin_basename( __FILE__ ),
			'labels'            => array(
				'singular_name' => __( 'Umbrella Membership', 'LION' ),
			),
			'options'           => array(
				'auto-enable' => false
			)
		);

		it_exchange_register_addon( self::ADD_ON, $options );

		add_filter( 'it_exchange_redirect_on_disable_3rd_party_addon', function ( $url, $addon ) {

			if ( $addon === self::ADD_ON ) {
				$url = false;
			}

			return $url;
		}, 10, 2 );
	}

	/**
	 * The activation hook.
	 *
	 * @since 1.0
	 */
	public static function activate() {

		update_option( 'itegms_initial_version', self::$version );

		wp_schedule_event( strtotime( 'Tomorrow 4AM' ), 'daily', 'itegms_daily_cron' );
	}

	/**
	 * Register scripts and styles.
	 *
	 * @since 1.0
	 */
	public function scripts() {
		wp_register_style( 'itegms-account-page', self::$url . 'assets/css/itegms-account-page.css', array(), self::$version );
		wp_register_script( 'itegms-account-page', self::$url . 'assets/js/itegms-account-page.js', array( 'jquery' ), self::$version );
	}
}

new Plugin();