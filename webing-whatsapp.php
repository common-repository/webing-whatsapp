<?php
/**
 * Plugin Name: WP WhatsApp
 * Plugin URI: https://wordpress.org/plugins/webing-whatsapp
 * Description: Start WhatsApp Chat Directly From Your Website.
 * Version: 1.0.0
 * Author: Webing
 * Author URI: https://www.webing.co.il
 * Text Domain: webing-whatsapp
 *
 * @package     Webing\WhatsApp
 * @subpackage  Plugin
 * @author      Webing <info@webing.co.il>
 * @link        https://www.webing.co.il
 * @version     1.0.0
 */

namespace Webing\WhatsApp;

use Webing\WhatsApp\Core\Admin;

// Block direct access to the file via url.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// Plugin's Constants
if ( ! defined( 'WGWA_PLUGIN_PATH' ) ) {
	define( 'WGWA_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'WGWA_PLUGIN_URL' ) ) {
	define( 'WGWA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}


/**
 * Class Plugin
 *
 * @package Webing\WhatsApp
 */
class Plugin {

	/**
	 * @var null|Plugin
	 */
	private static $instance = null;

	/**
	 * @var null|Admin
	 */
	private $admin = null;


	/**
	 * Plugin constructor.
	 */
	function __construct() {

		$this->load_classes();
		$this->init_classes();

		add_action( 'wp_footer', [ $this, 'print_button' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'register_button_style' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'register_admin_assets' ] );
		add_action( 'plugins_loaded', [ $this, 'register_textdomain' ] );

	}


	/**
	 * Load plugin classes.
	 */
	private function load_classes() {

		require_once( 'core/admin.php' );

	}


	/**
	 * Initiates the plugin classes.
	 */
	private function init_classes() {

		$this->admin = new Admin();

	}


	/**
	 * Retrieves admin class.
	 *
	 * @return null|Admin
	 */
	public function get_admin() {

		return $this->admin;

	}


	/**
	 * Print the button.
	 *
	 * @return bool
	 */
	public function print_button() {

		$settings = $this->admin->get_settings();

		if ( empty( $settings[ 'phone_number' ] ) || empty( $settings[ 'message' ] ) ) {
			return false;
		}

		if ( 0 === strpos( $settings[ 'phone_number' ], '0' ) ) {
			$settings[ 'phone_number' ] = substr( $settings[ 'phone_number' ], 1 );
		}

		$settings[ 'phone_number' ] = str_replace( '-', '', $settings[ 'phone_number' ] );

		$button_alignment = ( ! empty( $settings[ 'button_position' ] ) ) ? $settings[ 'button_position' ] : 'align_left';
		$icon_url         = WGWA_PLUGIN_URL . 'assets/images/whatsapp-logo.svg';
		$has_text         = ( empty( $settings[ 'button_text' ] ) ) ? 'icon-only' : 'has-text';
		$phone_number     = sprintf( '%s%s', $settings[ 'phone_code' ], $settings[ 'phone_number' ] );

		echo "<div class='webing-whatsapp-button {$button_alignment}'>";
		printf( '	<a href="https://api.whatsapp.com/send?phone=%s&text=%s" target="_blank" class="%s">', $phone_number, $settings[ 'message' ], $has_text );
		echo "      <img src='{$icon_url}' alt='" . esc_attr__( 'WhatsApp Logo', 'webing-whatsapp' ) . "'>";

		if ( ! empty( $settings[ 'button_text' ] ) ) {
			echo "		<span>{$settings[ 'button_text' ]}</span>";
		}

		echo "	</a>";
		echo "</div>";

	}


	/**
	 * Register plugin stylesheets.
	 */
	public function register_button_style() {

		wp_enqueue_style( 'webing-whatsapp-button-css', WGWA_PLUGIN_URL . 'assets/css/button-style.min.css' );

	}


	/**
	 * Register plugin admin assets.
	 */
	public function register_admin_assets() {

		wp_enqueue_style( 'webing-whatsapp-css', WGWA_PLUGIN_URL . 'assets/css/admin-styles.min.css' );

		wp_enqueue_script( 'webing-whatsapp-js', WGWA_PLUGIN_URL . 'assets/js/admin-scripts.min.js', [ 'jquery' ] );

	}


	/**
	 * Enable i18n and l10n support.
	 */
	public function register_textdomain() {

		load_plugin_textdomain( 'webing-whatsapp' );

	}


	/**
	 * Retrieve plugin instance for better performance.
	 *
	 * @return null|Plugin
	 */
	public static function get_instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;

	}

}

// Initiate the plugin.
Plugin::get_instance();