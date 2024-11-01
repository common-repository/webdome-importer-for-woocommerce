<?php
/**
 * WDWI Products
 *
 * Functions
 *
 * @link 
 * @package Webdome Importer for Woocommerce
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }


class WDWI_Products {

    static function setup() {

        add_action( 'init', array( __CLASS__, 'wdwi_cpt_products' ) );
        // if ( is_admin() ) {
		// 	add_action( 'load-post.php',     array( __CLASS__, 'wdwi_init_metabox' ) );
		// 	add_action( 'load-post-new.php', array( __CLASS__, 'wdwi_init_metabox' ) );
		// }

    }

    // public function wdwi_init_metabox() {

	// 	add_action( 'add_meta_boxes', array( __CLASS__, 'wdwi_add_metabox' )         );
	// 	add_action( 'save_post', array( __CLASS__, 'wdwi_add_metabox' ), 10, 2 );

	// }

    // public function wdwi_add_metabox() {

	// 	add_meta_box(
	// 		'archive_home',
	// 		__( 'Archive Home', 'webdome-club-management-theatre' ),
	// 		array( $this, 'render_metabox' ),
	// 		'webdome_archive',
	// 		'advanced',
	// 		'high'
	// 	);

	// }

    static function wdwi_cpt_products() {

        $labels = array(
            'name'                  => _x( 'Produkte', 'Post Type General Name', 'text_domain' ),
            'singular_name'         => _x( 'Produkt', 'Post Type Singular Name', 'text_domain' ),
            'menu_name'             => __( 'Produkte', 'text_domain' ),
            'name_admin_bar'        => __( 'Produkte', 'text_domain' ),
            'archives'              => __( 'Produkte Archiv', 'text_domain' ),
            'attributes'            => __( 'Produkte Attribute', 'text_domain' ),
            'parent_item_colon'     => __( 'Parent Item:', 'text_domain' ),
            'all_items'             => __( 'Alle Produkte', 'text_domain' ),
            'add_new_item'          => __( 'Neues Produkt', 'text_domain' ),
            'add_new'               => __( 'Add New', 'text_domain' ),
            'new_item'              => __( 'Neues Produkt', 'text_domain' ),
            'edit_item'             => __( 'Produkt bearbeiten', 'text_domain' ),
            'update_item'           => __( 'Produkt updaten', 'text_domain' ),
            'view_item'             => __( 'View Produkt', 'text_domain' ),
            'view_items'            => __( 'View Produkte', 'text_domain' ),
            'search_items'          => __( 'Produkte durchsuchen', 'text_domain' ),
            'not_found'             => __( 'Nicht gefunden', 'text_domain' ),
            'not_found_in_trash'    => __( 'Gefunden im Papierkorb', 'text_domain' ),
            'featured_image'        => __( 'Featured Image', 'text_domain' ),
            'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
            'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
            'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
            'insert_into_item'      => __( 'Insert into Produkt', 'text_domain' ),
            'uploaded_to_this_item' => __( 'Uploaded to this Produkt', 'text_domain' ),
            'items_list'            => __( 'Liste aller Produkte', 'text_domain' ),
            'items_list_navigation' => __( 'Items list navigation', 'text_domain' ),
            'filter_items_list'     => __( 'Filter items list', 'text_domain' ),
        );
        $rewrite = array(
            'slug'                  => 'produkte',
            'with_front'            => false,
            'pages'                 => false,
            'feeds'                 => false,
        );
        $args = array(
            'label'                 => __( 'Produkt', 'text_domain' ),
            'description'           => __( 'Produkte', 'text_domain' ),
            'labels'                => $labels,
            'supports'              => array( 'title', 'custom-fields', 'excerpt' ),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 10,
            'menu_icon'             => 'dashicons-products',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => 'wdwi_products',
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'rewrite'               => $rewrite,
            'capability_type'       => 'post',
            'show_in_rest'          => false,
        );
        register_post_type( 'wdwi_products', $args );
    
    }

}


// Run Setting Class.
WDWI_Products::setup();