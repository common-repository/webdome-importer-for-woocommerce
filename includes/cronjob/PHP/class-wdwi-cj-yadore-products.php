<?php
/**
 * WDWI Cronjob Load Products from Yadore in Temp Table
 *
 * 
 *
 * @link 
 * @package Webdome Firm Management
 */

class WDWI_CJ_Yadore_Products {

    public static function setup( $bot ) {
        $args = self::wdwi_cj_main( $bot );
        return $args;
    }

    private static function wdwi_cj_head( $bot_cat ) {
        $name = "WDWI Load Products from Yadore in Temp Table";
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
        $v_main = self::wdwi_cj_main_task( $v_head["hash"], $bot );

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

    private static function wdwi_cj_main_task( $hash, $bot ) {
        global $wpdb;
        $settings = get_option( 'wdwi_settings' );
        $yadore_api = isset( $settings["wdwi_yadore_api"] ) ? sanitize_text_field( $settings["wdwi_yadore_api"] ) : '';
        $placementid = isset( $settings["wdwi_yadore_placementid"] ) ? sanitize_text_field( $settings["wdwi_yadore_placementid"] ) : '';
        $method = isset( $settings["wdwi_yadore_method"] ) ? sanitize_text_field( $settings["wdwi_yadore_method"] ) : 'fuzzy';
        $keywords = isset( $settings["wdwi_keywords"] ) ? explode( ';', sanitize_text_field( $settings["wdwi_keywords"] ) ) : [];
        $keywords_rest = get_option( 'wdwi_rest_keywords' );

        if( $keywords_rest ) {
            $keywords = unserialize( $keywords_rest );
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

        $count = 0;
        $count2 = 0;
        $comment = '';
        $bodynew = '';

        $args = [
            'taxonomy' => 'merchants',
            'hide_empty' => false,
            'meta_key' => 'wdwi_merchant_category',
            'meta_value' => 'yadore'
        ];
        $merchants = [];
        $terms = get_terms( $args );
        foreach( $terms as $t ):
            $merchants[get_term_meta( $t->term_id, 'wdwi_merchant_ext_id', true )]["id"] = $t->term_id;
            $merchants[get_term_meta( $t->term_id, 'wdwi_merchant_ext_id', true )]["slug"] = $t->slug;
        endforeach;

        if (!is_dir( WDWI_PLUGIN_DIR . "temp/" )) {
            mkdir( WDWI_PLUGIN_DIR . "temp/" );
        }

        if( !$keywords_rest ) {
            self::wdwi_delete_json_files( WDWI_PLUGIN_DIR . "temp/", 'yadore' );
        }

        $count2 = count( $keywords );
        $count_file = self::wdwi_scan_json_files( WDWI_PLUGIN_DIR . "temp/", 'yadore' );;

        foreach($keywords as $key => $id):
            $count += 1;
            $count_file += 1;
            $export = array();
            if( $id != '' ) {
                $products = self::wdwi_get_yadore_offers( $yadore_api, $id, $method, $placementid );
            }

            if( isset( $products['errors'] ) ) {
                $comment = $comment . "Keyword: $id - FEHLER IM API-ABRUF: " . json_encode( $products["errors"] );
                continue;
            }
            if( isset( $products['count'] ) ) {
                if( $products['count'] > 0 ) {
                    foreach( $products["offers"] as $p ):

                        array_push( $export, array(
                            'id' => $p['id'],
                            'ean' => $p['ean'],
                            'cat' => 'yadore',
                            'title' => $p['title'],
                            'description' => $post_post_excerpt = wp_trim_words( $p['description'], '20', '' ),
                            'price' => $p['price']['amount'],
                            'merchant' => $merchants[$p["merchant"]["id"]]["id"],
                            'image' => $p["image"]["url"],
                            'url' => $p["clickUrl"]
                        ));

                    endforeach;
                    if(!file_exists( WDWI_PLUGIN_DIR . "temp/yadore_$count_file.json" )){
                        file_put_contents( WDWI_PLUGIN_DIR . "temp/yadore_$count_file.json", json_encode( $export ) );
                    }else{
                        file_put_contents( WDWI_PLUGIN_DIR . "temp/temp.json", json_encode( $export ) );
                        $x = json_decode( file_get_contents( WDWI_PLUGIN_DIR . "temp/yadore_$count_file.json" ), true );
                        $y = json_decode( file_get_contents( WDWI_PLUGIN_DIR . "temp/temp.json" ), true );
                        $result = array_merge( $x, $y );
                        file_put_contents( WDWI_PLUGIN_DIR . "temp/yadore_$count_file.json" ,json_encode( $result ));
                        unlink( WDWI_PLUGIN_DIR . "temp/temp.json" );
                    }

                } else {
                    $comment = $comment . " ### Keyword: $id - No Products";
                }
            } else {
                $comment = $comment . " ### Keyword: $id - No Products";
            }

            unset( $keywords[$key] );
            sleep( 1 );

            if ( $count >= 100 ) { break; }

        endforeach;

        if( count( $keywords ) > 0 ) {
            update_option( 'wdwi_rest_keywords', serialize( $keywords ) );
            $state = 'crawl';
        } else {
            delete_option( 'wdwi_rest_keywords' );
            $state = 'done';
        }

        $comment = $comment . " ### Count: $count von $count2";

        return array(
            "count" => $count,
            "body" => $bodynew,
            "comment" => $comment,
            "state" => $state,
        );
    }

    private static function wdwi_delete_json_files( $verzeichnis, $pre ) {

        if (!is_dir($verzeichnis) || !is_readable($verzeichnis)) {
            die('Das angegebene Verzeichnis existiert nicht oder kann nicht gelesen werden.');
        }

        $dateien = scandir($verzeichnis);

        foreach ($dateien as $datei) {
            $dateipfad = $verzeichnis . $datei;

            if (is_file($dateipfad) && pathinfo($dateipfad, PATHINFO_EXTENSION) === 'json' && strpos($datei, $pre) === 0 ) {
                unlink($dateipfad);
            }
        }

    }

    private static function wdwi_scan_json_files( $verzeichnis, $pre ) {

        if (!is_dir($verzeichnis) || !is_readable($verzeichnis)) {
            die('Das angegebene Verzeichnis existiert nicht oder kann nicht gelesen werden.');
        }

        $dateien = scandir($verzeichnis);

        $i = 0;

        foreach ($dateien as $datei) {
            $dateipfad = $verzeichnis . $datei;

            if (is_file($dateipfad) && pathinfo($dateipfad, PATHINFO_EXTENSION) === 'json' && strpos($datei, $pre) === 0 ) {
                $i++;
            }
        }

        return $i;

    }

    private static function wdwi_get_yadore_offers( $yadore_api, $keyword, $method, $placementid ) {
        $keyword = rawurlencode( $keyword );
        $placementid = rawurlencode( $placementid );

        $url = 'https://api.yadore.com/v2/offer?market=de&keyword=' . $keyword . '&precision=' . $method . '&sort=rel_desc&limit=1000&placementId=' . $placementid;

        $headers = array(
            'API-Key: '. $yadore_api
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_URL,$url);

        $result=curl_exec($ch);

        curl_close($ch);

        return json_decode($result, true);
    }

}