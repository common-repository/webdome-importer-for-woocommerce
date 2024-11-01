<?php
/**
 * WDWI Cronjob Page
 *
 * Shows all Cronjob details
 *
 * @link 
 * @package Webdome Importer for Woocommerce
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }


class WDWI_Cronjob_Page {

    static function setup() {

		add_action( 'admin_menu', array( __CLASS__, 'wdwi_add_cronjob_page' ) );

	}

    static function wdwi_add_cronjob_page() {

        add_submenu_page('wdwi-settings', 'Cronjob', 'Cronjob', 'manage_options', 'wdwi-cronjob', array( __CLASS__, 'display_cronjob_page'));
        
	}

    static function display_cronjob_page() {

		ob_start();
		?>
		<div id="wdwi-cronjobs" class="wdwi-cronjobs-wrap wrap">

		<h1>Cronjobs</h1>

		<?php
		if( isset( $_GET['do_cron'] ) ):

			?>
			<h2 class="cron-title">Folgende Cronjobs wurden ausgeführt</h2>
			<?php
			switch( sanitize_text_field( $_GET['do_cron'] ) ) {
				case 'wdwi_cj_yadore_merchants':
					$args = WDWI_CJ_Yadore_Merchants::setup( 'user' );
					break;
				case 'wdwi_cj_yadore_products':
					$args = WDWI_CJ_Yadore_Products::setup( 'user' );
					break;
				case 'wdwi_cj_products_import':
					$args = WDWI_CJ_Products_Import::setup( 'user' );
					break;
				case 'wdwi_cj_merchant_mapping':
					$args = WDWI_CJ_Merchant_Mapping::setup( 'user' );
					break;
				case 'wdwi_cj_delete_products':
					$args = WDWI_CJ_Delete_Products::setup( 'user' );
					break;
				case 'wdwi_cj_merge_files':
					$args = WDWI_CJ_Merge_Files::setup( 'user' );
					break;
			}
			WDWI_Helper::wdwi_helper_cronjob_result( $args );

		endif;

		chmod($mydir = ABSPATH . 'wp-cron.php', 0774);
		?>

		<h2 class="cron-title">Infos</h2>

		<p>TODO: In der wp-config.php muss folgender Eintrag eingefügt werden: >>> define('DISABLE_WP_CRON', true); <<<</p>
		<br />
		<p>TODO: Einen Server Cronjob einrichten, der die Datei wp-cron.php aufruft: Beispielsweise: >>> /usr/bin/php80 /usr/www/users/dachps/wp-cron.php; <<<</p>
		<br />
		<h2 class="cron-title">Cronjobs ausführen</h2>

		<?php 
		?>

		<a href="/wp-admin/admin.php?page=wdwi-cronjob&do_cron=wdwi_cj_yadore_merchants" class="button button-primary">Cronjob Yadore Merchants ausführen</a><br /><br />
		<a href="/wp-admin/admin.php?page=wdwi-cronjob&do_cron=wdwi_cj_yadore_products" class="button button-primary">Cronjob Yadore Products Crawler ausführen (längere Laufzeit)</a><br /><br />
		<a href="/wp-admin/admin.php?page=wdwi-cronjob&do_cron=wdwi_cj_merge_files" class="button button-primary">Cronjob Merge Files</a><br /><br />
		<a href="/wp-admin/admin.php?page=wdwi-cronjob&do_cron=wdwi_cj_delete_products" class="button button-primary">Cronjob Delete Products</a><br /><br />
		<a href="/wp-admin/admin.php?page=wdwi-cronjob&do_cron=wdwi_cj_products_import" class="button button-primary">Cronjob Produkte importieren</a><br /><br />
		<a href="/wp-admin/admin.php?page=wdwi-cronjob&do_cron=wdwi_cj_merchant_mapping" class="button button-primary">Cronjob Merchant Mapping</a><br /><br />		
		
		<?php
		
		if( file_exists( ABSPATH  . '/config.json' ) ) {
			$config = json_decode( file_get_contents( ABSPATH . '/config.json' ), true );
			if( isset( $config["last_changed_date"] ) ) {
				echo '<h2>Aktuelle Seite: ' . $config['sites'][$config['current_site']]['page'] . '</h2>';
				print("<pre>");
				print_r($config);
			}
		}
		?>

		<?php
		echo ob_get_clean();
	}
}

// Run Setting Class.
WDWI_Cronjob_Page::setup();
