<?php
/**
 * WDWI Helper
 *
 * Functions
 *
 * @link 
 * @package Webdome Importer for Woocommerce
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }


class WDWI_Helper {

    static function wdwi_helper_cronjob_result( $args ) {
        ?>
        <h3>Cronjob ausgeführt: <?php echo esc_html( $args["name"] ); ?></h3>
        <p>Time Start: <?php echo esc_html( $args["time_start"] ); ?></p>
        <p>Time End: <?php echo esc_html( $args["time_end"] ); ?></p>
        <p>Duration: <?php echo esc_html( $args["duration"] ); ?></p>
        <p>Useragent: <?php echo esc_html( $args["useragent"] ); ?></p>
        <p>IP-Adresse: <?php echo esc_html( $args["ip"] ); ?></p>
        <p>Bot-Cat: <?php echo esc_html( $args["bot_cat"] ); ?></p>
        <p>Comment: <?php echo esc_html( $args["comment"] ); ?></p>
        <?php
    }

    static function wdwi_delete_products_by_merchant( $merchant_key ) {

        $query = new WP_Query( $args = array(
            'post_type'             => 'wdwi_products',
            'post_status'           => 'publish',
            'ignore_sticky_posts'   => 1,
            'posts_per_page'        => -1,
            'tax_query'             => array( array(
                'taxonomy'      => 'merchants',
                'field'         => 'term_id', // can be 'term_id', 'slug' or 'name'
                'terms'         => $merchant_key,
            ), ),
        ));
        if ( $query->have_posts() ):
            while( $query->have_posts() ): 
                $query->the_post();
                wp_delete_post( $query->post->ID );
            endwhile;
        endif;

    }

    static function wdwi_set_cronjob_log( $name, $start, $end, $duration, $user, $ip, $cat, $comment, $hash ) {
        global $wpdb;
        $table_logs = $wpdb->prefix . "wdwi_log_cronjobs"; 
        $state = $wpdb->query("INSERT INTO $table_logs (name, run_date, time_start, time_end, duration, useragent, ip, cat, comment, cron_hash) VALUES ('$name', CURRENT_TIMESTAMP, '$start', '$end', '$duration', '$user', '$ip', '$cat', '$comment', '$hash')");
    }

    static function wdwi_delete_product ( $product_id ) {
        
        wp_delete_post( $product_id, true );
        
    }

}
