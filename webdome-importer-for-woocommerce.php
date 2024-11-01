<?php
/*
Plugin Name: Webdome Importer for WooCommerce
Description: Importer for Products in Woocommerce
Author: Webdome Webentwicklung
Author URI: https://www.web-dome.de
Version: 2.2.3
Text Domain: wd-woo-imp
Domain Path: /languages/
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Webdome Importer for WooCommerce
Copyright(C) since 2022, Webdome - Fabian.Heidger@web-dome.de

*/

namespace Webdome_Importer_For_Woocommerce;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package Webdome Importer for Woocommerce
 */
class Webdome_Importer_For_Woocommerce {

	/**
	 * Call all Functions to setup the Plugin
	 *
	 * @return void
	 */

	static function setup() {

		// Setup Constants.
		self::constants();

		// Setup Translation.
		add_action( 'plugins_loaded', array( __CLASS__, 'translation' ) );

		// Include Files.
		self::includes();

		// CSS / JS
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'wdwi_enqueue_style' ) );

	}

	/**
	 * Setup plugin constants
	 *
	 * @return void
	 */
	static function constants() {

		// Define Plugin Name.
		define( 'WDWI_NAME', 'Webdome_Importer_for_WooCommerce' );

		// Define Version Number.
		define( 'WDWI_VERSION', '2.2.3' );

		// Plugin Folder Path.
		define( 'WDWI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

		// Plugin Folder URL.
		define( 'WDWI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

		// Plugin Root File.
		define( 'WDWI_PLUGIN_FILE', __FILE__ );

	}

	/**
	 * Load Translation File
	 *
	 * @return void
	 */
	static function translation() {

		//load_plugin_textdomain( 'wp-theme-changelogs', false, dirname( plugin_basename( WDWI_PLUGIN_FILE ) ) . '/languages/' );

	}

	/**
	 * Include required files
	 *
	 * @return void
	 */
	static function includes() {

		// Check License-Key
		$settings = get_option('wdwi_settings');
        $license = isset( $settings["wdwi_license_key"] ) ? sanitize_text_field( $settings["wdwi_license_key"] ) : '';

		// Include Settings.
		require_once WDWI_PLUGIN_DIR . 'includes/admin/class-wdwi-database.php';
		require_once WDWI_PLUGIN_DIR . 'includes/admin/class-wdwi-settings.php';
		require_once WDWI_PLUGIN_DIR . 'includes/admin/class-wdwi-page-settings.php';
		
		// Include all other Files
		if( md5($license) === '0ac04131d58556f86aa7db03324e1127' ) {

			self::includes_cronjobs();

		}		
		
	}

	static function includes_cronjobs() {

		// Load Cronjobs
		require_once WDWI_PLUGIN_DIR . 'includes/cronjob/PHP/class-wdwi-cj-yadore-merchants.php';
		require_once WDWI_PLUGIN_DIR . 'includes/cronjob/PHP/class-wdwi-cj-yadore-products.php';
		require_once WDWI_PLUGIN_DIR . 'includes/cronjob/PHP/class-wdwi-cj-products-import.php';
		require_once WDWI_PLUGIN_DIR . 'includes/cronjob/PHP/class-wdwi-cj-merchant-mapping.php';
		require_once WDWI_PLUGIN_DIR . 'includes/cronjob/PHP/class-wdwi-cj-delete-products.php';
		require_once WDWI_PLUGIN_DIR . 'includes/cronjob/PHP/class-wdwi-cj-merge-files.php';

		// Load Admin Includes
		require_once WDWI_PLUGIN_DIR . 'includes/admin/class-wdwi-helper.php';
		require_once WDWI_PLUGIN_DIR . 'includes/admin/class-wdwi-rest.php';
		require_once WDWI_PLUGIN_DIR . 'includes/admin/class-wdwi-frontend.php';
		require_once WDWI_PLUGIN_DIR . 'includes/admin/class-wdwi-checkup.php';
		require_once WDWI_PLUGIN_DIR . 'includes/admin/class-wdwi-products.php';
		require_once WDWI_PLUGIN_DIR . 'includes/admin/class-wdwi-merchants.php';
		require_once WDWI_PLUGIN_DIR . 'includes/admin/class-wdwi-page-cronjob.php';
		require_once WDWI_PLUGIN_DIR . 'includes/admin/class-wdwi-page-cronjob-logs.php';

		// Add a filter to 'template_include' hook
		add_filter( 'template_include', array( __CLASS__, 'includes_template_files' ) );

	}

	static function includes_template_files( $template ) {
		if( is_archive() && ( get_query_var('post_type') == 'wdwi_products' || get_query_var('taxonomy') == 'wdwi_categories' ) ) {
			$template = WDWI_PLUGIN_DIR . 'includes/template-files/taxonomy-wdwi_categories.php';
		}
		return $template;
	}

	static function wdwi_enqueue_style() {

		wp_enqueue_style( 'wdwi', WDWI_PLUGIN_URL . 'assets/css/main.css', array(), WDWI_VERSION, 'all' );
		wp_enqueue_script( 'wdwi', WDWI_PLUGIN_URL . 'assets/js/script.js', array(), WDWI_VERSION, 'all' );

	}

}

// Run Plugin.
Webdome_Importer_For_Woocommerce::setup();
