<?php
/**
 * WDWI Cronjob Load Merchants from Yadore
 *
 * 
 *
 * @link 
 * @package Webdome Firm Management
 */

class WDWI_CJ_Yadore_Merchants {

    public static function setup( $bot ) {
        $args = self::wdwi_cj_main( $bot );
        update_option( 'wdwi_merchants_log', date('Y-m-d H:i:s') );
        return $args;
    }

    private static function wdwi_cj_head( $bot_cat ) {
        $name = "WDWI Load Merchants from Yadore";
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
        $count_delete = 0;
        $count_insert = 0;
        $deleted_terms = [];
        $bodynew = "";
        $comment = "";

        $merchants = json_decode( self::wdwi_get_yadore_merchants() );
        $merchant_ids = [];

        foreach( $merchants->merchants as $m ):

            array_push( $merchant_ids, sanitize_text_field( $m->id ) );

            $term = get_term_by( 'name', sanitize_text_field( $m->name ) . '_YADORE', 'merchants' );
            
            // $args = [
            //     'taxonomy' => 'merchants',
            //     'hide_empty' => false,
            //     'meta_key' => 'wdwcc_merchant_ext_id',
            //     'meta_value' => $m->id
            // ];

            // if( !empty( get_terms( $args ) ) ):
            //     foreach( get_terms( $args ) as $t ):
            //         wp_delete_term( $t->term_id, 'merchants' );
            //     endforeach;
            // endif;

            $args = [
                'taxonomy' => 'merchants',
                'hide_empty' => false,
                'meta_key' => 'wdwi_merchant_ext_id',
                'meta_value' => $m->id
            ];

            if( empty( get_terms( $args ) ) ):
                $count_insert = $count_insert + 1;
                if( $term == False ) {
                    $term_id = wp_insert_term( sanitize_text_field( $m->name ) . '_YADORE', 'merchants' );
                    if( !is_wp_error( $term_id ) ) {
                        update_term_meta(
                            $term_id['term_id'],
                            'wdwi_merchant_ext_id',
                            sanitize_text_field( $m->id )
                        );
                        update_term_meta(
                            $term_id['term_id'],
                            'wdwi_merchant_shop',
                            sanitize_text_field( $m->name )
                        );
                        update_term_meta(
                            $term_id['term_id'],
                            'wdwi_merchant_category',
                            'yadore'
                        );
                        update_term_meta(
                            $term_id['term_id'],
                            'wdwi_merchant_locked',
                            'no'
                        );
                    }
                } else {
                    if( isset($term->term_id ) ):
                        update_term_meta(
                            $term->term_id,
                            'wdwi_merchant_ext_id',
                            sanitize_text_field( $m->id )
                        );
                        update_term_meta(
                            $term->term_id,
                            'wdwi_merchant_shop',
                            sanitize_text_field( $m->name )
                        );
                        update_term_meta(
                            $term->term_id,
                            'wdwi_merchant_category',
                            'yadore'
                        );
                        update_term_meta(
                            $term->term_id,
                            'wdwi_merchant_locked',
                            'no'
                        );
                    endif;
                }  
            else:
                update_term_meta(
                    $term->term_id,
                    'wdwi_merchant_locked',
                    'no'
                );
            endif;
        endforeach;

        $args = [
            'taxonomy' => 'merchants',
            'hide_empty' => false,
            'meta_key' => 'wdwi_merchant_category',
            'meta_value' => 'yadore',
            'meta_key' => 'wdwi_merchant_locked',
            'meta_value' => 'no'
        ];
        $terms = get_terms( $args );

        foreach( $terms as $t ):
            $ext_id = get_term_meta(
                $t->term_id,
                'wdwi_merchant_ext_id',
                true
            );
            if( !in_array( $ext_id, $merchant_ids) ):
                $count_delete = $count_delete + 1;
                $deleted_terms[] = $t->name;
                update_term_meta(
                    $t->term_id,
                    'wdwi_merchant_locked',
                    'yes'
                );

                WDWI_Helper::wdwi_delete_products_by_merchant( $t->term_id );

            endif;
        endforeach;

        $comment = "Es wurden " . strval($count_insert) . " von " . $merchants->total . " YADORE-Merchants hinzugefuegt;" . "Es wurden " . strval($count_delete) . " von " . count($terms) . " YADORE-Merchants deaktiviert: " . implode(', ', $deleted_terms);

        $mail_body = "Automatische Benachrichtigung von Website: " . get_home_url() . "
        
        Job: Load Merchants von Yadore-API:
        
        Es wurden Merchants deaktiviert: " . strval($count_delete) . " von " . count($terms) . "
        
        Folgende Merchants wurden deaktiviert:
        " . implode(', ', $deleted_terms);

        $settings = get_option('wdwi_settings');
        $to = isset( $settings["wdwi_mail_notification"] ) ? sanitize_email( $settings["wdwi_mail_notification"] ) : '';

        if( count( $deleted_terms ) > 0 && $to <> '' ) {
            wp_mail($to, "Benachrichtung Yadore-Cronjob", $mail_body);
        }

        return array(
            "count" => $count_insert,
            "body" => $bodynew,
            "comment" => $comment,
            "state" => 'done',
        );

    }

    private static function wdwi_get_yadore_merchants() {
        $settings = get_option('wdwi_settings');
        $api = isset( $settings["wdwi_yadore_api"] ) ? sanitize_text_field( $settings["wdwi_yadore_api"] ) : '';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.yadore.com/v2/merchant?market=de');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:text/xml'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "API-Key: $api"
        ));
        
        // Save all leads in array and close connection
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

}