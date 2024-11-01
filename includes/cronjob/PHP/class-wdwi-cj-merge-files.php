<?php
/**
 * WDWI Cronjob Merge temp files
 *
 * 
 *
 * @link 
 * @package Webdome Firm Management
 */

class WDWI_CJ_Merge_Files {

    public static function setup( $bot ) {
        $args = self::wdwi_cj_main( $bot );
        return $args;
    }

    private static function wdwi_cj_head( $bot_cat ) {
        $name = "WDWI Merge Temp Files";
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
        $keywords_old = isset( $settings["wdwi_keywords"] ) ? explode( ';', strtolower( sanitize_text_field( $settings["wdwi_keywords"] ) ) ) : [];

        $keywords = [];
        foreach( $keywords_old as $key ) {
            $keywords = array_merge( $keywords, explode( ' ', $key ) );
        }

        self::wdwi_delete_json_files( WDWI_PLUGIN_DIR . "temp/", 'merged' );

        $data = []; 

        foreach (new DirectoryIterator( WDWI_PLUGIN_DIR . "temp/" ) as $file) {
            if ($file->getExtension() === 'json') {
                foreach( json_decode(file_get_contents($file->getPathname()), true) as $p ) {
                    if( isset( $data['ids'][$p['id']] ) ) {
                        continue;
                    }

                    if( isset( $data['eans'][$p['ean']] ) ) {
                        continue;
                    }
                    // $crawl = false;
                    // foreach( $keywords as $word ):
                    //     if( str_contains( strtolower( $p["name"] ), $word ) || str_contains( strtolower( $p["short_desc"] ), $word ) ):
                    //         $crawl = true;
                    //         break;
                    //     endif;
                    // endforeach;
                    // if( !$crawl ):
                    //     continue;
                    // endif;   

                    if( isset( $data['titles'][strtolower($p['title'])] ) ) {
                        if( $data['ids'][$data['titles'][strtolower($p['title'])]]['price'] <= $p['price'] ) {
                            continue;
                        } else {
                            $data['ids'][$p['id']] = $p;
                            continue;
                        }
                    }

                    $data['ids'][$p['id']] = $p;
                    $data['eans'][$p['ean']] = $p['id'];
                    $data['titles'][strtolower($p['title'])] = $p['id'];

                }
            }
        }

        self::wdwi_delete_json_files( WDWI_PLUGIN_DIR . "temp/", 'yadore' );

        unset($data['eans']);
        unset($data['titles']);
        
        file_put_contents(WDWI_PLUGIN_DIR . "temp/merged.json",json_encode($data));

        $comment = "Es wurden alle Dateien gemerged;";

        return array(
            "count" => $count,
            "body" => $bodynew,
            "comment" => $comment,
            "state" => 'done',
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

}