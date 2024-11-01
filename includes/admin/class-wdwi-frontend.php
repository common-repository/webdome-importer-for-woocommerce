<?php
/**
 * WDWI Frontend SHortcodes
 *
 * Functions
 *
 * @link 
 * @package Webdome Importer for Woocommerce
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }


class WDWI_Frontend {

    static function setup( ) {

        add_shortcode('wdwi-show-products', array( __CLASS__, 'wdwi_frontend_marktplatz') );
        add_shortcode('wdwi_products_navigation', array( __CLASS__, 'wdwi_display_navigation' ) );

    }

    static function wdwi_frontend_marktplatz( ) {

        $settings = get_option('wdwi_settings');
        $button = isset( $settings["wdwi_shop_button"] ) ? $settings["wdwi_shop_button"] : 'Zum Shop';
        $products_per_page = isset( $settings["wdwi_marktplatz_products"] ) ? sanitize_text_field( $settings["wdwi_marktplatz_products"] ) : 100;

        $prods = wp_count_posts( 'wdwi_products' );

        global $paged;
        // $current_page = $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $current_page = $paged;
        $offset = ($current_page - 1) * $products_per_page;
        if( $offset < 0 ) {
            $offset = 0;
        }

        $term_cat = get_queried_object();

        if( isset( $term_cat->taxonomy ) ) {
            if( $term_cat->taxonomy == 'wdwi_categories' ) {
                $args = array(
                    'post_type' => 'wdwi_products',
                    'posts_per_page' => $products_per_page,
                    'offset' => $offset,
                    'tax_query' => array(
                        array (
                            'taxonomy' => 'wdwi_categories',
                            'field' => 'term_id',
                            'terms' => $term_cat->term_id,
                        )
                    ),
                );
            } else {
                $args = array(
                    'post_type' => 'wdwi_products',
                    'posts_per_page' => $products_per_page,
                    'offset' => $offset,
                );
            }
        } else {
            $args = array(
                'post_type' => 'wdwi_products',
                'posts_per_page' => $products_per_page,
                'offset' => $offset,
            );
        }

        $query = new WP_Query($args);

        $prods = $query->found_posts;

        $count = $query->found_posts;
        $products_per_page_info = ( $count >= $products_per_page ) ? $products_per_page : $count;

        ob_start();

        if( isset( $term_cat->taxonomy ) ) {
            echo '<p class="wdwi_products_cat_meta">' . single_cat_title( '', false ) . ' - Durchstöbern Sie die besten Angebote aus verschiedenen Onlineshops ✓ Wir haben bereits die Preise verglichen, sodass Sie nur die günstigsten Produkte sehen ✓ Klicken Sie auf ein Produkt, um mehr auf der Webseite des Anbieters zu erfahren.</p><br />';
        }
        echo '<p style="font-size: 12px; float: right; width: 100%; margin-bottom: 1.5em; text-align: right;">' . $products_per_page_info . ' Produkte von insgesamt ' . $prods . '</p>';
        echo '<div class="wdwi_products">';

        $loop = 0;

        echo '<style>
        .site{
			width:100vw !important;
			margin:0 !important;
			background-color:#ebedf4 !important;
        }
		.grid-container{
			max-width:none !important;
		}
        .wdwi_sp_merchant { 
            padding: 2px 3px 2px 3px!important;
            color: #000!important;
            text-align: center;
            text-shadow: 1.4px 1.4px #eee!important;
            min-width: 50%!important;
            max-width: 70%!important;
            float: right;
            font-style: italic;
            background-color: #fff!important;
            border: 1px solid #bbb;
            border-radius: 3px!important; } 
            .wdwi_sp_price {
            color: black;
            font-weight: 600;
            width: auto !important;
            float: left;
            font-size: 15px;
            }
            .wdwi_single_product button {
                position: relative;
                overflow: visible;
                margin-left: 0;
                margin-right: auto;
                display: inline-block;
                left: auto;
                border: 0;
                color: white;
                font-weight: 600;
                font-size: 14px;
                padding: 5px 15px 5px 15px;
                margin-top: 7px;
                border-radius: 3px;
                background-color: rgb(198, 31, 31);
            }

            .wdwi_single_product button:hover {
                background-color: rgba(199, 32, 32, 0.6)
            }

            .wdwi_single_product:hover {
                box-shadow: 3px 4px 20px 4px rgb(234, 234, 243);
                border: none;
            }

            .wdwi_single_product p { text-align: left; color: black; display: block; position: relative; height: 70px; overflow: hidden; font-size: 12.5px; line-height: 1.4; margin-bottom: 20px; } .wdwi_single_product img {display: block; align-self: center !important; cursor: pointer; height: auto !important; width: auto !important; max-height: 190px; } .wdwi_single_product h2 { font-weight: 600; color: black; line-height: 1.25; font-size: 14px; margin-bottom: 5px; max-height: 36px; overflow: hidden; text-align: left; } .wdwi_products { cursor: pointer; display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 15px; list-style: none outside; clear: both; } .wdwi_single_product { padding: 20px; display: flex; float: none; width: auto; flex-direction: column; }

            @media (max-width: 1100px) {
                .wdwi_products {
                    grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
                }
            }
            @media (max-width: 900px) {
                .wdwi_products {
                    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
                }
            }
            @media (max-width: 700px) {
                .wdwi_products {
                    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                }
            }

             </style>';

        // Schleife durch die Produkte
        while ($query->have_posts()) {
            $query->the_post();

            $img = get_post_meta(
                get_the_ID(),
                'wdwi_product_img_url',
                true
            );

            $url = get_post_meta(
                get_the_ID(),
                'wdwi_url',
                true
            );

            $price = get_post_meta(
                get_the_ID(),
                'wdwi_price',
                true
            );
            $term_id = get_the_terms( get_the_ID(), 'merchants')[0]->term_id;
            $term = get_term_meta($term_id, 'wdwi_merchant_shop', true );
            $merchant = $firm = substr($term, 0, strrpos( $term, '.', -0) );
            $size = 0.875 - ( ( strlen($merchant) - 8 ) * 0.022);

            if( $loop == 15 && get_term_meta( $term_cat->term_id, 'seconddesc', true ) ) {
                ?>
                </div>
                <div class="custom-produktkasten_single">
                    <div class="custom_produktdetails">
                        <div style="padding: 25px;">
                            <h2 class="custom_produktdetails_title">Produktdetails: <?php echo $term_cat->name; ?></h2>
                            <div class="custom_produktdetails_p"><?php echo  wpautop ( wptexturize ( htmlspecialchars_decode( wp_trim_words ( get_term_meta( $term_cat->term_id, 'seconddesc', true ), 99, "<br /><br /><a href=\"#produktdetails\">» Mehr</a>" )))); ?></div>
                        </div>
                        <div class="custom-produktdetails_bildhg">
                            <div class="custom-produktdetails_inlay">
                                <?php 
                                $children = get_term_children($term_cat->term_id, 'wdwi_categories');
                                $slice = array_slice($children, 0, 25, true);
                                if(count($slice) > 0):
                                ?>
                                <div class="custom-produktdetails_tags">
                                    <span>
                                        Verfeinern Sie Ihre Suche:
                                    </span>
                                    <?php
                                        $slice = array_slice($slice, 0, 20);
                                        foreach($slice as $s) { 
                                            if( get_term( $s )->count > 0 ) {                                         
                                        ?>
                                                <a class="custom_produktdetails_button" href="<?php echo get_term( $s )->slug; ?>"><?php echo get_term( $s )->name; ?></a>
                                        <?php
                                            }
                                        }
                                    ?>
                                </div>	
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="wdwi_products">
                <?php
            }
            
            echo '<a class="wdwi_single_product" href="' . $url . '" rel="nofollow" target="_blank">';
            
            echo '<div style="height: 190px; position: relative; overflow: hidden; display: flex; flex-direction: column; justify-content: center;"><img width="300" height="300" src="' . esc_url($img) . '" alt="' . esc_attr(get_the_title()) . '" loading="lazy" /></div>';
            echo '<h2>' . wp_trim_words( get_the_title(), '7', '...' ) . '</h2>';
            echo '<p>' . get_the_excerpt() . '</p>';
            echo '<div><span class="wdwi_sp_merchant" style="font-size: ' . $size . 'em !important">' . $merchant . '</span>';
            echo '<span class="wdwi_sp_price">' . str_replace( '.', ',', $price ) . ' €</span></div>';
            echo '<button>' . $button . '*</button>';
            echo '</a>';

            $loop += 1;
        }

        // Beende die Ausgabe der Produkte
        echo '</div>';

        if( get_term_meta( $term_cat->term_id, 'seconddesc', true ) ) {
            echo '<div class="custom-produktkasten" id="produktdetails">';
			echo '<h2 class="custom_produktdetails_title">Produktdetails: ';
            echo $term_cat->name;
            echo '</h2>';
			echo '<div class="custom_produktdetails_p">';
            echo  wpautop ( wptexturize ( htmlspecialchars_decode( wp_trim_words ( get_term_meta( $term_cat->term_id, "seconddesc", true ), 999, "<br /><br /><a href=\"#produktdetails\">» Mehr</a>" ))));
            echo '</div>';
			echo '</div>';
        }

        // Zeige die Pagination an

        $pagination = paginate_links( array(
            'base' => get_pagenum_link(1) . '%_%',
            'format' => 'page/%#%',
            'type' => 'array', //instead of 'list'
            'total' => $query->max_num_pages,
            'current' => $current_page,
            'end_size' => 2,
            'mid_size' => 2,
        ));

        echo '<div class="pagination" style="margin-top: 20px;">';
        ?>
        
        <?php if ( ! empty( $pagination ) ) : ?>
       
            <ul class="page-numbers">
            <?php foreach ( $pagination as $key => $page_link ) : ?>
                <li><?php echo $page_link ?></li>
            <?php endforeach ?>
            </ul>
        <?php endif;

        // echo '<div class="pagination" style="margin-top: 20px;">';
        // echo paginate_links(array(
        //     'total' => $query->max_num_pages,
        //     'current' => $current_page,
        // ));
        echo '</div>';

        wp_reset_postdata();

        echo ob_get_clean();

    }

    static function wdwi_display_navigation( ) {

        $settings = get_option('wdwi_settings');
        $menu_title = isset( $settings["wdwi_category_menu_title"] ) ? $settings["wdwi_category_menu_title"] : 'Kategorien';
        $uncategorized = isset( $settings["wdwi_category_menu_uncategorized"] ) ? $settings["wdwi_category_menu_uncategorized"] : '';

        $args = array(
            'taxonomy'   => 'wdwi_categories',
            'orderby'    => 'name',
            // 'parent'     => $parent_id,
            'hide_empty' => true,
            'exclude' => $uncategorized
        );
    
        $categories = get_terms($args);
    
        if (!$categories) return;

        $parents = [];
        $all = [];

        // print("<pre>");
        // print_r($all);
        // print_r($categories);

        foreach( $categories as $cat ) {
            if( $cat->parent == 0 ) {
                $parents[$cat->term_id] = $cat->name;
            }
            $all[$cat->parent][$cat->term_id] = $cat->name;
        }

        ob_start();

        $level = 1;
    
        $output = '<div class="wdwi-nav-container"><h3 class="category_nav_title">' . $menu_title . '</h3><div class="navs">';
    
        foreach ($parents as $key => $name) {
            $group_active_link = false;
            $current_url = home_url( $_SERVER['REQUEST_URI'] );
            $output .= '<ul class="nav">';

            $category_link = str_replace('category/', '', get_term_link($key));
            // if ($current_url ===  $category_link ) {
            if( str_starts_with( $current_url, $category_link ) ) {
                $output .= '<li class="nav-level-1 active"> <a href="' . $category_link . '" class="nav-link">' . $name . '</a>';
                $group_active_link = true;
            }
            else{
                $output .= '<li class="nav-level-1"> <a href="' . $category_link . '" class="nav-link">' . $name . '</a>';
            }

            if( isset( $all[ $key ] ) ) {
                $output .= self::wdwi_child_navigation( $level + 1, $all, $key,$group_active_link ) . '</ul>';


                // $output .= '<ul class="nav-level nav-level-' . $level . '">';
            } else {
                $output .= '</li></ul>';
            }
    
            // if (!empty(get_terms(['taxonomy' => 'wdwi_categories', 'parent' => $category->term_id, 'hide_empty' => false]))) {
            //     $output .= '<span class="toggle-arrow"></span>';
            // }
    
            // $output .= '</a>';
    
            // $output .= self::wdwi_products_navigation($category->term_id, $level + 1);
            // $output .= '</li>';
        }
    
        $output .= '</div></div>'; 

        echo $output;

        echo ob_get_clean();
        
    }

    

        
    static function wdwi_child_navigation( $level = 1, $all = [], $parent = 0 ,$group_active_link ) {
        $current_url = home_url( $_SERVER['REQUEST_URI'] );
        $output = '<div class="rotate-element" style="margin-left: auto;">
        <img width="10.5" src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA0NDggNTEyIiBoZWlnaHQ9IjE4Ij48cGF0aCBkPSJNMjI0IDQxNmMtOC4xODggMC0xNi4zOC0zLjEyNS0yMi42Mi05LjM3NWwtMTkyLTE5MmMtMTIuNS0xMi41LTEyLjUtMzIuNzUgMC00NS4yNXMzMi43NS0xMi41IDQ1LjI1IDBMMjI0IDMzOC44bDE2OS40LTE2OS40YzEyLjUtMTIuNSAzMi43NS0xMi41IDQ1LjI1IDBzMTIuNSAzMi43NSAwIDQ1LjI1bC0xOTIgMTkyQzI0MC40IDQxMi45IDIzMi4yIDQxNiAyMjQgNDE2eiIvPjwvc3ZnPg=="/>
        </div></li>';


            if( isset( $all[ $parent ] ) ) {
                
            $output .=  '<ul class="nav-level-' . $level . '" style="display: none;">';
            $active_group = false;
            
                foreach( $all[$parent] as $key => $name ) {
                    
                $active_link = false;
                
                $category_link = str_replace('category/', '', get_term_link($key));

                if ($current_url === $category_link) {
                    $active_link = true;
                    $active_group = true;
                }
            

                if ($active_link) {
                    $output .= '<li class="active"><a href="' . $category_link . '" class="nav-link">' . $name;
                }
                else{
                    $output .= '<li><a href="' . $category_link . '" class="nav-link">' . $name;
    }

                    // $output .= '</a></li>';
                    if( isset( $all[ $key ] ) ) {
                        $output .= '<span class="toggle-arrow"></span></a>';
                        $output .= self::wdwi_child_navigation( $level + 1, $all, $key, $group_active_link );
                        // $output .= '<ul class="nav-level nav-level-' . $level . '">';
                    } else {
                        $output .= '';
                    }

                }
                $output .= '</ul>';

            if ($active_group || $group_active_link ) {
                $output = str_replace('style="display: none;"', 'style="display: block;"', $output);
                $output = str_replace('<img width="10.5"', '<img class="rotated" width="10.5"', $output);
            }

                return $output;
            } else {
                return '';
            }

        }

    }

// Run Setting Class.
WDWI_Frontend::setup();
