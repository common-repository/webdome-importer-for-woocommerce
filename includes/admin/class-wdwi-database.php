<?php
/**
 * WDWI Database
 *
 * Registers the database
 *
 * @link 
 * @package Webdome Importer for Woocommerce
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }


class WDWI_Database_Tables {

    const WDWI_TABLES = array('wdwi_log_cronjobs');

    static function setup() {

        register_activation_hook(WDWI_PLUGIN_FILE, array(__CLASS__, 'wdwi_create_database_tables'));
        register_uninstall_hook(WDWI_PLUGIN_FILE, array(__CLASS__, 'wdwi_delete_database_tables'));

        if (WDWI_VERSION !== get_option('wdwi_version')) {
            self::wdwi_create_database_tables();
        }

        update_option('wdwi_version', WDWI_VERSION);

    }

    static function wdwi_create_database_tables() {
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $table_name_1 = $wpdb->prefix . 'wdwi_log_cronjobs';

        $wpdb->query(
            "DELETE FROM " . $wpdb->prefix . "wdwi_log_cronjobs
            WHERE cron_hash = ''"
        );

        $sql = "CREATE TABLE $table_name_1 (
            cj_id INT NOT NULL AUTO_INCREMENT ,
            name VARCHAR(100) NOT NULL ,
            run_date DATE NOT NULL ,
            time_start DATETIME NOT NULL ,
            time_end DATETIME NOT NULL ,
            duration FLOAT NOT NULL ,
            useragent VARCHAR(255) NOT NULL ,
            ip VARCHAR(50) NOT NULL ,
            cat VARCHAR(10) NOT NULL ,
            comment TEXT NOT NULL ,
            cron_hash VARCHAR(200) DEFAULT NULL ,
            PRIMARY KEY (cj_id) ,
            UNIQUE KEY wdwi_log_cronjobs_cron_hash (cron_hash)
        ) $charset_collate;";
        dbDelta( $sql );

        // $table_name_2 = $wpdb->prefix . 'wdwi_temp_products';
        // $sql = "CREATE TABLE $table_name_2 (
        //     tp_id INT NOT NULL AUTO_INCREMENT ,
        //     ext_id VARCHAR(200) NOT NULL ,
        //     ean VARCHAR(25) DEFAULT NULL ,
        //     woo_key INT DEFAULT NULL ,
        //     cat VARCHAR(25) NOT NULL ,
        //     name VARCHAR(255) NOT NULL ,
        //     short_desc TEXT DEFAULT NULL ,
        //     price FLOAT NOT NULL ,
        //     merchant_key INT NOT NULL ,
        //     insert_date DATETIME NOT NULL , 
        //     img TEXT NOT NULL ,
        //     url TEXT NOT NULL ,
        //     task VARCHAR(20) NOT NULL ,
        //     locked BOOLEAN NOT NULL DEFAULT 0 ,
        //     cron_hash_key VARCHAR(200) DEFAULT NULL ,
        //     PRIMARY KEY (tp_id) ,
        //     UNIQUE KEY wdwi_temp_products_ext_id (ext_id)
        // ) $charset_collate;";
        // dbDelta( $sql );

    }

    static function wdwi_delete_database_tables() {

        global $wpdb;
        foreach( self::WDWI_TABLES as $table ) {
            $wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . $table );
        }

    }
}

// Run Setting Class.
WDWI_Database_Tables::setup();
