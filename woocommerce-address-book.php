<?php
/**
 * Plugin Name: WooCommerce Universal Address Book
 * Plugin URI: https://counterculturecoffee.com
 * Description: Gives customers the option to store multiple addresses for either shipping or billing and retrieve them on checkout. Inspired by the WooCommerce Address Book by Hall Internet Marketing.
 * Version: 1.0
 * Author: Khayyam Abdullah
 * Author URI: https://counterculturecoffee.com
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path: /languages
 * Text Domain: wc-uab-address-book
 *
 * @package WooCommerce Address Book
 */

// Prevent direct access data leaks.
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once ABSPATH . 'wp-admin/includes/plugin.php';
$woo_path = 'woocommerce/woocommerce.php';

if ( ! is_plugin_active( $woo_path ) && ! is_plugin_active_for_network( $woo_path ) ) {

	deactivate_plugins( plugin_basename( __FILE__ ) );

	/**
	 * Deactivate the plugin if WooCommerce is not active.
	 *
	 * @since    1.0.0
	 */
	function woocommerce_notice__error() {

    $class   = 'notice notice-error';
		$message = __( 'WooCommerce Address Book requires WooCommerce and has been deactivated.', 'wc-uab-address-book' );

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_attr( $message ) );
	}
	add_action( 'admin_notices', 'woocommerce_notice__error' );
	add_action( 'network_admin_notices', 'woocommerce_notice__error' );

} else {

	/**
	 * WooCommerce Address Book.
	 *
	 * @class    WC_Address_Book
	 * @version  1.0
	 * @package  WooCommerce Address Book
	 * @category Class
	 * @author   Khayyam Abdullah
	 */
	class WC_Address_Book {

		/**
		 * Initializes the plugin by setting localization, filters, and administration functions.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			// Version Number.
			if (!defined('PLUGIN_VERSION')){
				define('PLUGIN_VERSION', '1.0');
			}

			// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
			register_activation_hook( __FILE__, array( $this, 'activate' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

			add_action( 'init', array( $this, 'plugin_textdomain' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'scripts_styles' ), 20 );

			if (!defined('PLUGIN_PATH')){
				define ('PLUGIN_PATH', plugin_dir_path( __FILE__ ));
			}

			require_once PLUGIN_PATH . 'includes/address-book-properties.php';
			require_once PLUGIN_PATH . 'includes/address-book-init.php';
			require_once PLUGIN_PATH . 'includes/address-book-menu.php';
			require_once PLUGIN_PATH . 'includes/address-book-checkout.php';
			require_once PLUGIN_PATH . 'includes/address-book-update.php';
			require_once PLUGIN_PATH . 'includes/address-book-options.php';

			//@TODO newer versions will have an admin settings page, which will have files from and admin folder

			add_action( 'wp_ajax_ajax_handler', array( $this, 'ajax_handler' ) );

		} // end constructor

		/**
		 * Fired when the plugin is activated.
		 *
		 * @param boolean $network_wide - True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
		 * @since 1.0.0
		 */
		public function activate( $network_wide ) {

			// Make sure only admins can wipe the date.
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}

		}

		/**
		 * Fired when the plugin is deactivated.
		 *
		 * @param boolean $network_wide - True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
		 * @since 1.0.0
		 */
		public function deactivate( $network_wide ) {

			flush_rewrite_rules();

		}

		/**
		 * Loads the plugin text domain for translation
		 *
		 * @since 1.0.0
		 */
		public function plugin_textdomain() {

			load_plugin_textdomain( 'wc-uab-address-book', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );//@TODO REDO ALL of these in language file

		}

		/**
		 * Enqueue scripts and styles
		 *
		 * @since 1.0.0
		 */
		public function scripts_styles() {
			if ( ! is_admin() ) {
				wp_enqueue_script( 'jquery' );

			 	wp_enqueue_script( 'wc-country-select' );

				wp_enqueue_style( 'wc-uab-address-book', plugins_url( '/assets/css/style.css', __FILE__ ), array(), 'PLUGIN_VERSION' );
				wp_enqueue_script( 'wc-uab-address-book', plugins_url( '/assets/js/scripts.js', __FILE__ ), array( 'jquery', 'wp-api' ), 'PLUGIN_VERSION', true );

				wp_localize_script( 'wc-uab-address-book', 'wc_address_book', array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
				));
			}
		}

		public function ajax_handler() {

			$wc_address_book_init = new WC_Address_Book_Init();

			$user    = wp_get_current_user();
			$user_id = $user->ID;

			$new_address = 'address_fields';

			if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['address_fields'] == true)) {

				$wc_address_book_init->save_my_new_address($user_id, $new_address);

			} else {

				die();
			}
		}

	}

	// Init Class.
	$wc_address_book = new WC_Address_Book();

}
