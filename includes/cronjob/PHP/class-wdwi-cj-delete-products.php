<?php
/**
 * WDWI Cronjob Delete All Products
 *
 * 
 *
 * @link 
 * @package Webdome Firm Management
 */

class WDWI_CJ_Delete_Products {

    public static function setup( $bot ) {
        $args = self::wdwi_cj_main( $bot );
        return $args;
    }

    private static function wdwi_cj_head( $bot_cat ) {
        $name = "WDWI Delete All Products";
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
        $batch_deleting = isset( $settings["wdwi_cronjob_deleting_batch_size"] ) ? strval($settings["wdwi_cronjob_deleting_batch_size"]) : '1000';
        $table_posts = $wpdb->prefix . 'posts';
        $table_postmeta = $wpdb->prefix . 'postmeta';
        $table_term_relationships = $wpdb->prefix . 'term_relationships';
        $table_term_taxonomy = $wpdb->prefix . 'term_taxonomy';
        $table_terms = $wpdb->prefix . 'terms';

        $products = $wpdb->get_results("SELECT ID FROM $table_posts WHERE post_type IN ('wdwi_products') LIMIT $batch_deleting", ARRAY_A);

        while( count( $products ) > 0 ) {
            $ids = implode(',', array_map( function ($entry) {return $entry['ID'];}, $products));
            $wpdb->query("DELETE relations.*
            FROM $table_term_relationships AS relations
            INNER JOIN $table_term_taxonomy AS taxes
            ON relations.term_taxonomy_id=taxes.term_taxonomy_id
            INNER JOIN $table_terms AS terms
            ON taxes.term_id=terms.term_id
            WHERE object_id IN ($ids);");
            $wpdb->query("DELETE FROM $table_postmeta WHERE post_id IN ($ids);");
            $wpdb->query("DELETE FROM $table_posts WHERE id IN ($ids);");
            $products = $wpdb->get_results("SELECT ID FROM $table_posts WHERE post_type IN ('wdwi_products') LIMIT $batch_deleting", ARRAY_A);
        }        
        // $wpdb->query("DELETE terms.*, taxes.* FROM $table_terms as terms INNER JOIN $table_term_taxonomy as taxes ON terms.term_id = taxes.term_id WHERE taxes.taxonomy = 'merchants';");
        $wpdb->query("UPDATE $table_term_taxonomy SET count = 0 WHERE taxonomy = 'wdwi_categories'");

        $comment = "Es wurden alle Produkte gelöscht;";

        return array(
            "count" => $count,
            "body" => $bodynew,
            "comment" => $comment,
            "state" => 'done',
        );

    }

}