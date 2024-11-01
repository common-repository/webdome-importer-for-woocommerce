<?php
/**
 * WDWI Cronjob Import Products of Temp-Table
 *
 * 
 *
 * @link 
 * @package Webdome Firm Management
 */

class WDWI_CJ_Products_Import {

    public static function setup( $bot ) {
        $args = self::wdwi_cj_main( $bot );
        update_option( 'wdwi_import_log', date('Y-m-d H:i:s') );
        return $args;
    }

    private static function wdwi_cj_head( $bot_cat ) {
        $name = "WDWI Load Products into Woocommerce";
        date_default_timezone_set('Europe/Berlin');
        $timestamp_start = time();
        $time_cronjob_start = date("Y-m-d H:i:s",$timestamp_start);

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $cip = sanitize_text_field ( $_SERVER['HTTP_CLIENT_IP'] );
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $cip = sanitize_text_field ( $_SERVER['HTTP_X_FORWARDED_FOR'] );
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $cip = sanitize_text_field ( $_SERVER['REMOTE_ADDR'] );
        } else {
            $cip = "";
        }

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $useragent = sanitize_text_field ( $_SERVER['HTTP_USER_AGENT'] );
        } else {
            $useragent = "none";
        }

        return array(
            "name" => $name,
            "time_start" => $time_cronjob_start,
            "timestamp_start" => $timestamp_start,
            "useragent" => $useragent,
            "ip" => $cip,
            "bot_cat" => $bot_cat,
            "hash" => md5(time()),
        );
    }

    private static function wdwi_cj_main( $bot ) {		
        $v_head = self::wdwi_cj_head( $bot );
        $v_main = self::wdwi_cj_main_task( $v_head["hash"] );

        $v_foot = self::wdwi_cj_foot($v_head["timestamp_start"]);

        global $wpdb;
        $table_logs = $wpdb->prefix . "wdwi_log_cronjobs"; 
        WDWI_Helper::wdwi_set_cronjob_log( $v_head["name"], $v_head["time_start"], $v_foot["time_end"], $v_foot["duration"], $v_head["useragent"], $v_head["ip"], $v_head["bot_cat"], $v_main["comment"], $v_head["hash"] );
        
        return array(
            'name'       => $v_head["name"],
            'time_start' => $v_head["time_start"],
            'time_end'   => $v_foot["time_end"],
            'duration'   => $v_foot["duration"],
            'useragent'  => $v_head["useragent"],
            'ip'         => $v_head["ip"],
            'bot_cat'    => $v_head["bot_cat"],
            'comment'    => $v_main["comment"],
            'state'      => $v_main["state"]
        );
    }

    private static function wdwi_cj_foot($timestamp_start) {
        $timestamp_end = time();
        $time_cronjob_end = date("Y-m-d H:i:s",$timestamp_end);
        $duration = $timestamp_end - $timestamp_start;
        return array(
            "duration" => $duration,
            "timestamp_end" => $timestamp_end,
            "time_end" => $time_cronjob_end,
        );
    }

    private static function wdwi_cj_main_task( $hash ) {
        global $wpdb;
        $table = $wpdb->prefix . 'wdwi_temp_products';
        $table_posts = $wpdb->prefix . 'posts';
        $table_postmeta = $wpdb->prefix . 'postmeta';
        $count = 0;
        $count_insert = 0;
        $count_no_image = 0;
        $deleted_terms = [];
        $bodynew = "";
        $comment = "";
        $logs = [];

        $settings = get_option('wdwi_settings');
        $batch = isset( $settings["wdwi_cronjobs_batch_size"] ) ? sanitize_text_field( $settings["wdwi_cronjobs_batch_size"] ) : 250;
        if( $batch == '' ) {
            $batch = 250;
        }
        $keywords_old = isset( $settings["wdwi_keywords"] ) ? explode( ';', sanitize_text_field( $settings["wdwi_keywords"] ) ) : [];
        $button = isset( $settings["wdwi_shop_button"] ) ? $settings["wdwi_shop_button"] : 'Zum Shop';

        $keywords = [];
        foreach( $keywords_old as $key ) {
            $keywords = array_merge( $keywords, explode( ' ', $key ) );
        }

        if( isset( $settings['wdwi_import_debugging'] ) ) {
            if( $settings['wdwi_import_debugging'] == 'on' ) {
                $debug = true;
            } else {
                $debug = false;
            }
        } else {
            $debug = false;
        }

        $merchants = [];
        $args = [
            'taxonomy' => 'merchants',
            'hide_empty' => false,
            'meta_key' => 'wdwi_merchant_locked',
            'meta_value' => 'no'
        ];
        $terms = get_terms( $args );

        foreach( $terms as $t ):
            $merchants[$t->term_id]["slug"] = $t->slug;
        endforeach;

        $args_locked = [
            'taxonomy' => 'merchants',
            'hide_empty' => false,
            'meta_key' => 'wdwi_merchant_locked',
            'meta_value' => 'yes',
            'fields' => 'ids'
        ];
        $terms_locked = get_terms( $args_locked );


        $products = json_decode(file_get_contents( WDWI_PLUGIN_DIR . "temp/merged.json" ), true);

        remove_all_actions('wp_insert_post');

        foreach( $products['ids'] as $key => $p ) {

            $count += 1;


            switch( sanitize_text_field( $p["cat"] ) ) {
				case 'yadore':
					if( isset( $merchants[$p["merchant"]] ) ) {
                        $woo_key = self::wdwi_new_product_yadore( $p, $merchants[$p["merchant"]]["slug"], $button );
                    } else {
                        $woo_key = 'no_merchant';
                    }
                    break;
			}
            if( is_numeric( $woo_key ) ) {
                $count_insert += 1;
            } else if( $woo_key == 'Keine URL' ) {
                $count_no_image += 1;
            } else {
                // wp_mail( 'jobs.customer@web-dome.de', 'WP-Error', json_encode( $woo_key, true ) );
            }

            unset( $products['ids'][$key] );

            if ( $count >= $batch ) { break; }

        }
        file_put_contents(WDWI_PLUGIN_DIR . "temp/merged.json", json_encode($products));

        $comment = $comment . "Produkte von $count Produkten neu manuell angelegt: $count_insert (Kein Bild: $count_no_image)";

        return array(
            "count" => $count_insert,
            "body" => $bodynew,
            "comment" => $comment,
            "state" => count($products['ids']),
        );

    }

    private static function wdwi_new_product_yadore( $args, $merchants, $button ) {

        if( $args["image"] != '' && !is_null( $args["image"] ) ):

            $post_id = wp_insert_post( array(
                'post_title'    => $args["title"],
                'post_excerpt'  => is_null( $args["description"] ) ? '' : $args["description"],
                'post_status'   => 'publish',
                'post_type'     => "wdwi_products",
            ) );

            $metaValues = array(
                'wdwi_sku'          => $args["ean"],
                'wdwi_price'          => $args["price"],
                'wdwi_url'          => $args["url"],
                'wdwi_prodcut_ext_id'          => $args["id"],
                'wdwi_product_img_url'          => $args["image"],
            );

            wp_update_post( array(
                'ID'            => $post_id,
                'meta_input'    => $metaValues,
            ) );

            wp_set_object_terms( $post_id, array( $merchants ), 'merchants', true);

            return $post_id;
        else:
            return 'Keine URL';
        endif;
        
    }

}