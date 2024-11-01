<?php
/**
 * WDWI Cronjob Handler
 *
 * Shows all Cronjob details
 *
 * @link 
 * @package Webdome Importer for Woocommerce
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }


class WDWI_Cronjob_Handler {

    static function setup() {

        $settings = get_option('wdwi_settings');

        foreach( WDWI_Settings::WDWI_Cornjobs as $job ) {
            add_action( 'wdwi_cj_' . $job . '_hook', array( __CLASS__, 'wdwi_cj_' . $job . '_run' ) );
        }

        add_filter( 'cron_schedules', array( __CLASS__, 'wdwi_cj_scheduler' ) );
         
        // Uninstall Hook
        register_uninstall_hook(WDWI_PLUGIN_FILE, array(__CLASS__, 'wdwi_delete_scheduled_cronjobs'));

	}

    static function wdwi_cj_scheduler( $schedules ) {

        foreach( WDWI_Settings::WDWI_Cornjobs as $job ) {

            $settings = get_option('wdwi_settings');
            $interval = isset( $settings["wdwi_cj_interval_" . $job] ) ? sanitize_text_field ( $settings["wdwi_cj_interval_" . $job] ) : '';

            $schedules['wdwi_cj_' . $job . '_interval'] = array(
                'interval' => $interval,
                'display'  => esc_html__( 'Intervall für CJ: ' . $job ), );

        }

        return $schedules;

    }

    static function wdwi_cj_yadore_merchants_run() {

        WDWI_CJ_Yadore_Merchants::setup( 'cron' );

    }

    static function wdwi_cj_yadore_products_run() {

        WDWI_CJ_Yadore_Products::setup( 'cron' );

    }

    static function wdwi_cj_products_import_run() {

        WDWI_CJ_Products_Import::setup( 'cron' );

    }

    static function wdwi_cj_merchant_mapping_run() {

        WDWI_CJ_Merchant_Mapping::setup( 'cron' );

    }

    static function wdwi_delete_scheduled_cronjobs() {

        $timestamp = wp_next_scheduled( 'wdwi_cj_yadore_merchants_hook' );
		wp_unschedule_event( $timestamp, 'wdwi_cj_yadore_merchants_hook' );

        $timestamp = wp_next_scheduled( 'wdwi_cj_yadore_products_hook' );
		wp_unschedule_event( $timestamp, 'wdwi_cj_yadore_products_hook' );

        $timestamp = wp_next_scheduled( 'wdwi_cj_products_import_hook' );
		wp_unschedule_event( $timestamp, 'wdwi_cj_products_import_hook' );

        $timestamp = wp_next_scheduled( 'wdwi_cj_merchant_mapping_hook' );
		wp_unschedule_event( $timestamp, 'wdwi_cj_merchant_mapping_hook' );

    }

}

// Run Setting Class.
WDWI_Cronjob_Handler::setup();
