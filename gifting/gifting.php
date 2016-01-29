<?php
/**
 * Gifting add-on.
 *
 * @since   1.18
 * @license GPLv2
 */

namespace iThemes\Exchange\Membership\Gifting;

/**
 * Class Gifting
 * @package iThemes\Exchange\Membership\Gifting
 */
class Gifting {

	/**
	 * Add-on Slug.
	 */
	const ADD_ON = 'memberships-gifting';

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
	 * Gifting constructor.
	 *
	 * @since 1.18
	 */
	public function __construct() {

		self::$dir     = plugin_dir_path( __FILE__ );
		self::$url     = plugin_dir_url( __FILE__ );
		self::$version = ITE_MEMBERSHIP_PLUGIN_VERSION;

		add_action( 'it_exchange_register_addons', array( $this, 'register' ) );
	}

	/**
	 * Register the gifting add-on with Exchange.
	 *
	 * @since 1.18
	 */
	public function register() {

		$desc = __( 'Let your customers gift memberships.', 'LION' );

		$options = array(
			'name'        => __( 'Membership Gifting', 'LION' ),
			'description' => $desc,
			'author'      => 'iThemes',
			'author_url'  => 'https://ithemes.com/exchange/membership',
			'file'        => dirname( __FILE__ ) . '/init.php',
			'category'    => 'other',
			'basename'    => plugin_basename( __FILE__ ),
			'labels'      => array(
				'singular_name' => __( 'Membership Gifting', 'LION' ),
			),
			'options'     => array(
				'auto-enable' => false
			)
		);

		it_exchange_register_addon( self::ADD_ON, $options );

		$our_slug = self::ADD_ON;

		add_filter( 'it_exchange_redirect_on_disable_3rd_party_addon', function ( $url, $addon ) use ( $our_slug ) {

			if ( $addon === $our_slug ) {
				$url = false;
			}

			return $url;
		}, 10, 2 );
	}
}

new Gifting();