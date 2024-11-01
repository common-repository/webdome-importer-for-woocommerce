<?php
/**
 * WDWI Customizations Frontend
 *
 * Registers the database
 *
 * @link 
 * @package Webdome Importer for Woocommerce
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }


class WDWI_Customizations {

    static function setup() {

        add_action( 'product_cat_add_form_fields', array( __CLASS__, 'wdwi_wp_editor_1'), 10, 2 ); // 1. Display field on "Add new product category" admin page
        add_action( 'product_cat_edit_form_fields', array( __CLASS__, 'wdwi_wp_editor_2'), 10, 2 ); // 2. Display field on "Edit product category" admin page
        add_action( 'edit_term', array( __CLASS__, 'wdwi_wp_editor_save'), 10, 3 ); // 3. Save field @ admin page
        add_action( 'created_term', array( __CLASS__, 'wdwi_wp_editor_save'), 10, 3 ); // 3. Save field @ admin page
        add_action( 'woocommerce_after_shop_loop', array( __CLASS__, 'wdwi_wp_editor_display'), 5 ); // 4. Display field under products @ Product Category pages 
        add_filter( 'woocommerce_short_description', array( __CLASS__, 'prefix_filter_woocommerce_short_description') );
        add_filter( 'the_title', array( __CLASS__, 'short_woocommerce_product_titles_words'), 10, 2 );
        add_action('woocommerce_before_shop_loop_item', array( __CLASS__, 'woocommerce_add_aff_link_open'), 10, 2); /* 1. Link all products to external affiliate page (title + picture); link is in button "zum Shop" */
        add_action('woocommerce_after_shop_loop_item', array( __CLASS__, 'woocommerce_add_aff_link_close'), 10, 2); /* 1. Link all products to external affiliate page (title + picture); link is in button "zum Shop" */
        add_filter( 'woocommerce_get_price_html', array( __CLASS__, 'wd_woo_custom_price_html'), 100, 2 );
        add_action('woocommerce_shop_loop', array( __CLASS__, 'wd_woo_shop_loop'), 10, 2); /* Zusatzbox alle 15 Produkte */
        add_action('woocommerce_before_shop_loop', array( __CLASS__, 'wd_woo_before_shop_loop'), 10, 2);
        add_action( 'woocommerce_product_get_image', array( __CLASS__, 'modify_shop_product_image'), 10, 5 ); // Shop Preis kein DIV nutzen
        add_filter( 'woocommerce_loop_add_to_cart_link', array( __CLASS__, 'bbloomer_loop_add_cart_open_new_tab'), 9999, 3 ); /* Add to Cart Button in new Tab */
        add_filter( 'woocommerce_product_get_image', array( __CLASS__, 'mein_produktbild_anpassen'), 10, 2);

    }

    static function mein_produktbild_anpassen($image, $product) {

        $foo = get_post_meta(
            $product->get_ID(),
            'wdwi_product_img_url',
            true
        );
        
        if( $foo != '' ) {
            $image = '<img src="' . esc_url($foo) . '" alt="' . esc_attr($product->get_name()) . '" loading="lazy" />';
        }

        return $image;
    }

    static function wdwi_wp_editor_1() {
        ?>
        <div class="form-field">
            <label for="seconddesc"><?php echo __( 'Second Description', 'woocommerce' ); ?></label>
           
          <?php
          $settings = array(
             'textarea_name' => 'seconddesc',
             'quicktags' => array( 'buttons' => 'em,strong,link' ),
             'tinymce' => array(
                'theme_advanced_buttons1' => 'bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator',
                'theme_advanced_buttons2' => '',
             ),
             'editor_css' => '<style>#wp-excerpt-editor-container .wp-editor-area{height:175px; width:100%;}</style>',
          );
     
          wp_editor( '', 'seconddesc', $settings );
          ?>
           
            <p class="description"><?php echo __( 'This is the description that goes BELOW products on the category page', 'woocommerce' ); ?></p>
        </div>
        <?php
    }

    static function wdwi_wp_editor_2( $term ) {
        $second_desc = htmlspecialchars_decode( get_woocommerce_term_meta( $term->term_id, 'seconddesc', true ) );
        ?>
        <tr class="form-field">
            <th scope="row" valign="top"><label for="second-desc"><?php echo __( 'Second Description', 'woocommerce' ); ?></label></th>
            <td>
                <?php
              
             $settings = array(
                'textarea_name' => 'seconddesc',
                'quicktags' => array( 'buttons' => 'em,strong,link' ),
                'tinymce' => array(
                   'theme_advanced_buttons1' => 'bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator',
                   'theme_advanced_buttons2' => '',
                ),
                'editor_css' => '<style>#wp-excerpt-editor-container .wp-editor-area{height:175px; width:100%;}</style>',
             );
     
             wp_editor( $second_desc, 'seconddesc', $settings );
             ?>
           
                <p class="description"><?php echo __( 'This is the description that goes BELOW products on the category page', 'woocommerce' ); ?></p>
            </td>
        </tr>
        <?php
    }

    static function wdwi_wp_editor_save( $term_id, $tt_id = '', $taxonomy = '' ) {
        if ( isset( $_POST['seconddesc'] ) && 'product_cat' === $taxonomy ) {
           update_woocommerce_term_meta( $term_id, 'seconddesc', esc_attr( $_POST['seconddesc'] ) );
        }
    }

    static function wdwi_wp_editor_display() {
        if ( is_product_taxonomy() ) {
           $term = get_queried_object();
           if ( $term && ! empty( get_woocommerce_term_meta( $term->term_id, 'seconddesc', true ) ) ) {
              if(!isset($_GET["filter_material"]) && !isset($_GET["filter_farbe"]) && !isset($_GET["filter_marke"])) {
                   //echo '<div id="produktdetails" class="term-description">' . html_entity_decode( htmlspecialchars_decode( get_woocommerce_term_meta( $term->term_id, 'seconddesc', true ) ) ) . '</div>';
                   echo '<div id="produktdetails" class="term-description">' .  wpautop ( wptexturize ( htmlspecialchars_decode ( get_woocommerce_term_meta( $term->term_id, 'seconddesc', true ) ) ) ) . '</div>';
              } else {
                  echo '';
              }
           }
        }
    }

    /*  Automatically shortens WooCommerce product description on the main shop, category, and tag pages  */
    /*  to a specific number of words */
    static function prefix_filter_woocommerce_short_description( $post_post_excerpt ) { 
        // make filter magic happen here... 
        $term = get_queried_object();
        if(! is_product() ) { // add in conditionals
            if(is_product_taxonomy() && preg_replace("#[\r|\n]#", '', get_term_meta( $term->term_id, 'seconddesc', true )) != preg_replace("#[\r|\n]#", '', strip_tags($post_post_excerpt)) ) {		
                $text = $post_post_excerpt; 
                $words = '1000'; // change word length
                $more = ''; // add a more cta
    
                $post_post_excerpt = wp_trim_words( $text, $words, $more );
            }
        }
        return $post_post_excerpt; 
    }


    /*  Automatically shortens WooCommerce product titles on the main shop, category, and tag pages  */
    /*  to a specific number of words */
    static function short_woocommerce_product_titles_words( $title, $id ) {
        if ( ( is_shop() || is_product_tag() || is_product_category() ) && get_post_type( $id ) === 'product' ) {
          $title_words = explode(" ", $title);
          if ( count($title_words) > 5 ) { // Kicks in if the product title is longer than 5 words
            // Shortens the title to 5 words and adds ellipsis at the end
            return implode(" ", array_slice($title_words, 0, 7)) . '...';
          } else {
            return $title; // If the title isn't longer than 5 words, it will be returned in its full length without the ellipsis
          }
        } else {
          return $title;
        }
    }

    static function woocommerce_add_aff_link_open(){
        $product = wc_get_product(get_the_ID());
    
        if( $product->is_type( 'external' ) ) {
            // woocommerce_product_add_to_cart_url
            // echo '<a href="' . $product->get_product_url() . '" target="_blank" class="">';
            echo '<a aria-label="' . get_the_title() . '" rel="sponsored nofollow" target="_blank" href="' . apply_filters( 'woocommerce_product_add_to_cart_url', $product->get_product_url(), $product ) . '" class="custom_aff_link">';
        }
    }

    static function woocommerce_add_aff_link_close(){
        $product = wc_get_product(get_the_ID());
    
        if( $product->is_type( 'external' ) ) {
            echo '</a>';
        }
    }
    
    static function wd_woo_custom_price_html( $price, $product ){
        // 	global $logos;
        // 	$url = $product->get_product_url();
        // 	print("<pre>");
        // 	print_r($product);
            if ($price == '') {
                $price = 'k. A.';
            }
            $terms = get_the_terms( $product->get_id(), 'merchants' );
            
        // 	var_dump($terms);
            if( $terms != false ) {
                $term = get_term_meta( $terms[0]->term_id, 'wdwi_merchant_shop', true );
                $firm = substr($term, 0, strpos( $term, '.', -0) );
        // 		print_r($term);
        // 		print_r(strpos( $term, '.', -0));
        // 		print_r("#");
                $size = 0.875 - ( ( strlen($firm) - 8 ) * 0.022);
            } else {
                $firm = '';
                $size = 0.875;
            }
            return '<span style="font-size: ' . $size . 'em !important" class="custom_af_logo">' . $firm . '</span>' . str_replace('<span class="woocommerce-Price-currencySymbol">', '', $price);
        // 	foreach ($logos as $key => $value) {
        // 		if(strpos($url, $key) !== FALSE) {
                    //return '<img style="width: 30%; float: right;" class="custom_af_logo" src="' . $value . '" alt="Produkt von ' . $key . '">' . str_replace('<span class="woocommerce-Price-currencySymbol">', '', $price); //str_replace("&nbsp;", " ", str_replace("del", "s", $price));
        // 		}
        // 	}
    }

    static function wd_woo_shop_loop() {
        if ( is_product_category() ) {
            global $loop_count;
            $term = get_queried_object();
            $children = get_term_children($term->term_id, 'product_cat');
            $slice = array_slice($children, 0, 25, true);
            if($loop_count === 15 && get_woocommerce_term_meta( $term->term_id, 'seconddesc', true ) != "" && !isset($_GET["filter_material"]) && !isset($_GET["filter_farbe"]) && !isset($_GET["filter_marke"])) {
                ?>
                </ul>
                <div class="custom-produktkasten">
                    <div class="custom_produktdetails">
                    <div style="padding: 25px;">
                        <h2 class="custom_produktdetails_title">Produktdetails: <?php echo $term->name; ?></h2>
                        <div class="custom_produktdetails_p"><?php echo  wpautop ( wptexturize ( htmlspecialchars_decode( wp_trim_words ( get_woocommerce_term_meta( $term->term_id, 'seconddesc', true ), 99, "<br /><br /><a href=\"#produktdetails\">» Mehr</a>" )))); ?></div>
                    </div>
                    <div class="custom-produktdetails_bildhg">
                        <div class="custom-produktdetails_inlay">
                            <?php if(count($slice) > 0): ?>
                            <div class="custom-produktdetails_tags">
                                <span>
                                    Verfeinern Sie Ihre Suche:
                                </span>
                                <?php
                                    $slice = array_slice($slice, 0, 20);
                                    foreach($slice as $s) {
                                    ?>
                                        <a class="custom_produktdetails_button" href="<?php echo get_term( $s )->slug; ?>"><?php echo get_term( $s )->name; ?></a>
                                    <?php
                                    }
                                ?>
                            </div>	
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
    </div>
                <ul class="products">
                <?php
            }
            $loop_count = $loop_count + 1;	
        }
    }
    
    static function wd_woo_before_shop_loop() {
        global $loop_count;
        $loop_count = 0;	
    }

    static function modify_shop_product_image ( $img, $product, $size, $attr, $placeholder ) {
        $alt_tag = 'alt=';
        $pos = stripos( $img, 'alt=' ) + strlen( $alt_tag ) + 1;
        return substr_replace($img, $product->get_name(), $pos, 0);
    }

    static function bbloomer_loop_add_cart_open_new_tab( $html, $product, $args ) {
        return sprintf( '<a href="%s" data-quantity="%s" class="%s" %s target="_blank">%s</a>',
           esc_url( $product->add_to_cart_url() ),
           esc_attr( isset( $args['quantity'] ) ? $args['quantity'] : 1 ),
           esc_attr( isset( $args['class'] ) ? $args['class'] : 'button' ),
           isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',
           esc_html( $product->add_to_cart_text() )
        );
     }

}

// Run Setting Class.
WDWI_Customizations::setup();