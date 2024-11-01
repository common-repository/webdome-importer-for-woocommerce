<?php
/**
 * WDWI Cronjob Merchant Mapping
 *
 * 
 *
 * @link 
 * @package Webdome Firm Management
 */

class WDWI_CJ_Merchant_Mapping {

    public static function setup( $bot ) {
        $args = self::wdwi_cj_main( $bot );
        return $args;
    }

    private static function wdwi_cj_head( $bot_cat ) {
        $name = "WDWI Merchant Mapping";
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
        $count = 0;
        $bodynew = "";
        $comment = "";
        $settings = get_option('wdwi_settings');
        $mappings = isset( $settings["wdwi_merchant_mappings"] ) ? explode( ';', $settings["wdwi_merchant_mappings"] ) : [];
        $maps = [];

        foreach( $mappings as $map ) {
            $arr = explode( '###', $map );
            $maps[ $arr[ 0 ] ] = $arr[ 1 ];
        }
        unset($mappings);

        if( count( $maps ) < 1 ) {
            $comment = "FEHLER: KEINE MAPPINGS ANGELEGT!!!";

            return array(
                "count" => $count,
                "body" => $bodynew,
                "comment" => $comment,
            );
        }

        $args_terms = [
            'taxonomy' => 'merchants',
            'hide_empty' => false,
            'fields' => 'ids',
        ];
        $terms = get_terms( $args_terms );

        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'merchants',
                    'field' => 'id',
                    'terms' => $terms,
                    'operator' => 'NOT IN'
                )
                ),
            'return' => 'ids',
        );
        $products = wc_get_products( $args );

        foreach( $products as $product ):
            $p = wc_get_product( $product );
            $url = apply_filters( 'woocommerce_product_add_to_cart_url', $p->get_product_url(), $p );
            foreach( $maps as $key => $value ) {
                if( str_contains( $url, $key ) ) {
                    $count = $count = $count + 1;
                    $term = get_term( $value, 'merchants' )->slug;
                    wp_set_object_terms( $p->id, array( $term ), 'merchants', true);
                    break;
                }
            }
        endforeach;

        $comment = "Es wurden " . strval($count) . " von " . strval(count($products)) . " Produkten geupdatet;";

        return array(
            "count" => $count,
            "body" => $bodynew,
            "comment" => $comment,
            "state" => '',
        );

    }

}