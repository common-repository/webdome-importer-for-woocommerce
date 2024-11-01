<?php
/**
 * WDWI Checkup Functions
 *
 * Registers the database
 *
 * @link 
 * @package Webdome Importer for Woocommerce
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

// wdwi_cj_yadore_merchants_hook
// wdwi_cj_yadore_products_hook
// wdwi_cj_products_import_hook
// wdwi_cj_merchant_mapping_hook

class WDWI_Checkups {

    static function setup() {

        add_action( 'admin_init', array( __CLASS__, 'wdwi_checkup_functions' ) );

    }

    static function wdwi_checkup_functions() {

        self::wdwi_checkup_database();
    }

    static function wdwi_checkup_database() {

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
                add_action ('admin_notices', function( ) {echo "<div class=\"notice notice-warning is-dismissible\"><p>ACHTUNG: Webdome Importer for Woocommerce - Tabellen fehlen!!! Bitte in den Einstellungen des Plugins prüfen.</p></div>";} );
            }
        }

    }

}

// Run Setting Class.
WDWI_Checkups::setup();