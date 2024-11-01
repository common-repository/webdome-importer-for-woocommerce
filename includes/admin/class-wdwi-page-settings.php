<?php
/**
 * WDWI Settings Page
 *
 * Registers all plugin settings with the WordPress Settings API.
 *
 * @link 
 * @package Webdome Importer for Woocommerce
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }


class WDWI_Settings_Page {

    static function setup() {

		// Add settings page to admin menu.
		add_action( 'admin_menu', array( __CLASS__, 'add_settings_page' ) );

	}

    static function add_settings_page() {
        
		add_menu_page('WD Woo Importer', 'WD Woo Importer', 'manage_options', 'wdwi-settings', array( __CLASS__, 'display_settings_page' ), 'dashicons-database-import', 3);
        add_submenu_page('wdwi-settings', 'Settings', 'Settings', 'manage_options', 'wdwi-settings', array( __CLASS__, 'display_settings_page'));

	}

    static function display_settings_page() {

		ob_start();
		?>

		<div id="wdwi-settings" class="wdwi-settings-wrap wrap">

			<?php

			global $wpdb;

        	$mytables=$wpdb->get_results("SHOW TABLES", ARRAY_N);
			$tables = [];
			foreach( $mytables as $table ) {
				foreach( $table as $t ) {
					array_push( $tables, $t );	
				}
			}

			foreach( WDWI_Database_Tables::WDWI_TABLES as $key => $table ) {
				if ( !in_array( $wpdb->prefix . $table, $tables ) ) {
					echo '<h2 style="color: red;">Database Error: Folgende Tabelle fehlt: ' . $table . '</h2>';
				}
			}

			?>

			<h1>Einstellungen</h1>

			<?php 
				$set = get_option('wdwi_settings');
				$license = isset( $set["wdwi_license_key"] ) ? sanitize_text_field( $set["wdwi_license_key"] ) : '';
				if( md5($license) !== '0ac04131d58556f86aa7db03324e1127' ) {
					echo '<h1 style="margin: 1 3em; text-align: center; background-color: red; font-weight: bold; font-size: 36px; color: white;">Bitte gültigen Lizenz-Key aktivieren!!!</h1>';
				}

			?>

			<form class="wdwi-settings-form" method="post" action="options.php">
				<?php
					settings_fields( 'wdwi_settings' );
					do_settings_sections( 'wdwi_settings' );
					submit_button();
				?>
			</form>

		</div>
		<style>.regular-text {width: 40em !important;}</style>

		<?php
		echo ob_get_clean();
	}
}

// Run Setting Class.
WDWI_Settings_Page::setup();
