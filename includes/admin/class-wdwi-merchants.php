<?php
/**
 * WDWI Merchants Extensions
 *
 * Add Custom Taxonomy "Merchants" to CPT Products
 *
 * @link 
 * @package Webdome Importer for Woocommerce
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }


class WDWI_Merchants {

    static function setup() {
        add_action( 'wp_head', array(__CLASS__, 'wdwi_change_meta'), 4 );

        add_action( 'merchants_add_form_fields', array(__CLASS__, 'wdwi_add_fields_to_merchants_taxonomy'), 10, 2 );
        add_action( 'merchants_edit_form_fields', array(__CLASS__, 'wdwi_edit_fields_to_merchants_taxonomy'), 10, 2 );
        add_action( 'created_merchants', array(__CLASS__, 'wdwi_merchants_taxonomy_fields_save'), 10, 3 );
        add_action( 'edited_merchants', array(__CLASS__, 'wdwi_merchants_taxonomy_fields_save'), 10, 3 );
        
        add_action( 'wdwi_categories_add_form_fields', array(__CLASS__, 'wdwi_add_fields_to_category_taxonomy'), 10, 2 );
        add_action( 'wdwi_categories_edit_form_fields', array(__CLASS__, 'wdwi_edit_fields_to_category_taxonomy'), 10, 2 );
        add_action( 'created_wdwi_categories', array(__CLASS__, 'wdwi_category_taxonomy_fields_save'), 10, 3 );
        add_action( 'edited_wdwi_categories', array(__CLASS__, 'wdwi_category_taxonomy_fields_save'), 10, 3 );
        
        add_action( 'init', array( __CLASS__, 'wdwi_add_merchants_taxonomy' ) );
        add_action( 'init', array( __CLASS__, 'wdwi_add_category_taxonomy' ) );
        add_action( 'init', array( __CLASS__, 'wdwi_add_tag_taxonomy' ) );
        
        add_action('restrict_manage_posts', array( __CLASS__, 'wdwi_filter_products_by_merchants') );
        add_filter('parse_query', array( __CLASS__, 'wdwi_convert_id_to_term_in_query') );

    }

    static function wdwi_add_merchants_taxonomy() {

        $labels = array(
            'name'                       => _x( 'Merchants', 'Taxonomy General Name', 'text_domain' ),
            'singular_name'              => _x( 'Merchant', 'Taxonomy Singular Name', 'text_domain' ),
            'menu_name'                  => __( 'Merchants', 'text_domain' ),
            'all_items'                  => __( 'All Merchants', 'text_domain' ),
            'parent_item'                => __( 'Parent Merchant', 'text_domain' ),
            'parent_item_colon'          => __( 'Parent Merchant:', 'text_domain' ),
            'new_item_name'              => __( 'New Merchant Name', 'text_domain' ),
            'add_new_item'               => __( 'Add New Merchant', 'text_domain' ),
            'edit_item'                  => __( 'Edit Merchant', 'text_domain' ),
            'update_item'                => __( 'Update Merchant', 'text_domain' ),
            'view_item'                  => __( 'View Merchant', 'text_domain' ),
            'separate_items_with_commas' => __( 'Separate Merchants with commas', 'text_domain' ),
            'add_or_remove_items'        => __( 'Add or remove Merchants', 'text_domain' ),
            'choose_from_most_used'      => __( 'Choose from the most used', 'text_domain' ),
            'popular_items'              => __( 'Popular Merchants', 'text_domain' ),
            'search_items'               => __( 'Search Merchants', 'text_domain' ),
            'not_found'                  => __( 'Not Found', 'text_domain' ),
            'no_terms'                   => __( 'No Merchants', 'text_domain' ),
            'items_list'                 => __( 'Merchants list', 'text_domain' ),
            'items_list_navigation'      => __( 'Merchants list navigation', 'text_domain' ),
        );
        $rewrite = array(
            'slug'                       => 'merchants',
            'with_front'                 => true,
            'hierarchical'               => false,
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => false,
            'public'                     => false,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
            'rewrite'                    => $rewrite,
        );
        register_taxonomy( 'merchants', array( 'wdwi_products' ), $args );

    }

    static function wdwi_add_category_taxonomy() {

        $labels = array(
            'name'                       => _x( 'Kategorien', 'Taxonomy General Name', 'text_domain' ),
            'singular_name'              => _x( 'Kategorie', 'Taxonomy Singular Name', 'text_domain' ),
            'menu_name'                  => __( 'Kategorien', 'text_domain' ),
            'all_items'                  => __( 'All Kategorien', 'text_domain' ),
            'parent_item'                => __( 'Parent Kategorie', 'text_domain' ),
            'parent_item_colon'          => __( 'Parent Kategorie:', 'text_domain' ),
            'new_item_name'              => __( 'New Kategorie Name', 'text_domain' ),
            'add_new_item'               => __( 'Add New Kategorie', 'text_domain' ),
            'edit_item'                  => __( 'Edit Kategorie', 'text_domain' ),
            'update_item'                => __( 'Update Kategorie', 'text_domain' ),
            'view_item'                  => __( 'View Kategorie', 'text_domain' ),
            'separate_items_with_commas' => __( 'Separate Kategorien with commas', 'text_domain' ),
            'add_or_remove_items'        => __( 'Add or remove Kategorien', 'text_domain' ),
            'choose_from_most_used'      => __( 'Choose from the most used', 'text_domain' ),
            'popular_items'              => __( 'Popular Kategorien', 'text_domain' ),
            'search_items'               => __( 'Search Kategorien', 'text_domain' ),
            'not_found'                  => __( 'Not Found', 'text_domain' ),
            'no_terms'                   => __( 'No Kategorien', 'text_domain' ),
            'items_list'                 => __( 'Kategorien list', 'text_domain' ),
            'items_list_navigation'      => __( 'Kategorien list navigation', 'text_domain' ),
        );
        $rewrite = array(
            'slug'                       => 'marktplatz',
            'with_front'                 => true,
            'hierarchical'               => true,
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
            'has_archive'                => true,
            'rewrite'                    => $rewrite,
        );
        register_taxonomy( 'wdwi_categories', array( 'wdwi_products' ), $args );

    }

    static function wdwi_add_tag_taxonomy() {

        $labels = array(
            'name'                       => _x( 'Tags', 'Taxonomy General Name', 'text_domain' ),
            'singular_name'              => _x( 'Tag', 'Taxonomy Singular Name', 'text_domain' ),
            'menu_name'                  => __( 'Tags', 'text_domain' ),
            'all_items'                  => __( 'All Tags', 'text_domain' ),
            'parent_item'                => __( 'Parent Tag', 'text_domain' ),
            'parent_item_colon'          => __( 'Parent Tag:', 'text_domain' ),
            'new_item_name'              => __( 'New Tag Name', 'text_domain' ),
            'add_new_item'               => __( 'Add New Tag', 'text_domain' ),
            'edit_item'                  => __( 'Edit Tag', 'text_domain' ),
            'update_item'                => __( 'Update Tag', 'text_domain' ),
            'view_item'                  => __( 'View Tag', 'text_domain' ),
            'separate_items_with_commas' => __( 'Separate Tags with commas', 'text_domain' ),
            'add_or_remove_items'        => __( 'Add or remove Tags', 'text_domain' ),
            'choose_from_most_used'      => __( 'Choose from the most used', 'text_domain' ),
            'popular_items'              => __( 'Popular Tags', 'text_domain' ),
            'search_items'               => __( 'Search Tags', 'text_domain' ),
            'not_found'                  => __( 'Not Found', 'text_domain' ),
            'no_terms'                   => __( 'No Tags', 'text_domain' ),
            'items_list'                 => __( 'Tags list', 'text_domain' ),
            'items_list_navigation'      => __( 'Tags list navigation', 'text_domain' ),
        );
        $rewrite = array(
            'slug'                       => 'tags',
            'with_front'                 => true,
            'hierarchical'               => false,
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => false,
            'public'                     => false,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
            'rewrite'                    => $rewrite,
        );
        register_taxonomy( 'wdwi_tags', array( 'wdwi_products' ), $args );

    }

    static function wdwi_add_fields_to_category_taxonomy( ) {
        ?>
        <div class="form-field">
            <label for="seconddesc">Second Description</label>
           
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
           
            <p class="description">This is the description that goes BELOW products on the category page</p>
        </div>
        <?php
    }

    static function wdwi_add_fields_to_merchants_taxonomy( ) {
        ?>
    	<div class="form-field">
			<label for="wdwi_merchant_ext_id">External ID</label>
			<input type="text" name="wdwi_merchant_ext_id" id="wdwi_merchant_ext_id" />
			<p>ID der externen Quelle (bspw. Yadore-ID)</p>
		</div>
        <div class="form-field">
			<label for="wdwi_merchant_shop">Shop Name</label>
			<input type="text" name="wdwi_merchant_shop" id="wdwi_merchant_shop" />
			<p>Shop Name (gleich wie Name, nur ohne Kategorie-Zuweisung)</p>
		</div>
        <div class="form-field">
			<label for="wdwi_merchant_category">Category</label>
            <select name="wdwi_merchant_category" id="wdwi_merchant_category">
                <option value="none">NONE</option>
                <option value="awin">AWIN</option>
                <option value="yadore">YADORE</option>
                <option value="adcell">ADCELL</option>
                <option value="belboon">BELBOON</option>
                <option value="amazon">AMAZON</option>
                <option value="tradedoubler">TRADEDOUBLER</option>
            </select>
			<p>Kategory zur Cronjob und Import Steuerung</p>
		</div>
        <div class="form-field">
            <label for="wdwi_merchant_locked">Locked</label>
            <input type="radio" id="wdwi_merchant_locked_yes" name="wdwi_merchant_locked" value="yes" />
            <label for="wdwi_merchant_locked_yes">YES</label><br>
            <input type="radio" id="wdwi_merchant_locked_no" name="wdwi_merchant_locked" value="no" />
            <label for="wdwi_merchant_locked_no">NO</label><br>
        </div>
        <?php
    }

    static function wdwi_edit_fields_to_category_taxonomy( $term ) {
        $seconddesc = get_term_meta( $term->term_id, 'seconddesc', true );
        ?>
        <tr class="form-field">
            <th scope="row" valign="top"><label for="seconddesc">Second Description</label></th>
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
     
             wp_editor( $seconddesc, 'seconddesc', $settings );
             ?>
           
                <p class="description">This is the description that goes BELOW products on the category page</p>
            </td>
        </tr>
        <?php
    }

    static function wdwi_edit_fields_to_merchants_taxonomy( $term ) {
        $wdwi_merchant_ext_id = get_term_meta( $term->term_id, 'wdwi_merchant_ext_id', true );
        $wdwi_merchant_shop = get_term_meta( $term->term_id, 'wdwi_merchant_shop', true );
        $wdwi_merchant_category = get_term_meta( $term->term_id, 'wdwi_merchant_category', true );
        $wdwi_merchant_locked = get_term_meta( $term->term_id, 'wdwi_merchant_locked', true );
        ?>
    	<tr class="form-field">
			<th><label for="wdwi_merchant_ext_id">External ID</label></th>
            <td>
			    <input type="text" name="wdwi_merchant_ext_id" id="wdwi_merchant_ext_id" value="<?php echo esc_attr( $wdwi_merchant_ext_id ) ?>" />
			    <p>ID der externen Quelle (bspw. Yadore-ID)</p>
            </td>
		</tr>
        <tr class="form-field">
			<th><label for="wdwi_merchant_shop">Shop Name</label></th>
            <td>
			    <input type="text" name="wdwi_merchant_shop" id="wdwi_merchant_shop" value="<?php echo esc_attr( $wdwi_merchant_shop ) ?>" />
			    <p>Shop Name (gleich wie Name, nur ohne Kategorie-Zuweisung)</p>
            </td>
		</tr>
        <tr class="form-field">
            <th><label for="wdwi_merchant_category">Text Field</label></th>
            <td>
                <select name="wdwi_merchant_category" id="wdwi_merchant_category">
                    <option value="none" <?php if(esc_attr( $wdwi_merchant_category )=="none") echo 'selected="selected"'; ?> >NONE</option>
                    <option value="awin" <?php if(esc_attr( $wdwi_merchant_category )=="awin") echo 'selected="selected"'; ?> >AWIN</option>
                    <option value="yadore" <?php if(esc_attr( $wdwi_merchant_category )=="yadore") echo 'selected="selected"'; ?> >YADORE</option>
                    <option value="adcell" <?php if(esc_attr( $wdwi_merchant_category )=="adcell") echo 'selected="selected"'; ?> >ADCELL</option>
                    <option value="belboon" <?php if(esc_attr( $wdwi_merchant_category )=="belboon") echo 'selected="selected"'; ?> >BELBOON</option>
                    <option value="amazon" <?php if(esc_attr( $wdwi_merchant_category )=="amazon") echo 'selected="selected"'; ?> >AMAZON</option>
                    <option value="tradedouble" <?php if(esc_attr( $wdwi_merchant_category )=="tradedouble") echo 'selected="selected"'; ?> >TRADEDOUBLER</option>
                </select>
                <p>Kategory zur Cronjob und Import Steuerung</p>
            </td>
		</tr>
        <tr class="form-field">
            <th><label for="wdwi_merchant_locked">Locked</label></th>
            <td>
                <input type="radio" id="wdwi_merchant_locked_yes" name="wdwi_merchant_locked" value="yes" <?php if(esc_attr( $wdwi_merchant_locked )=="yes") echo 'checked="checked"'; ?> />
                <label for="wdwi_merchant_locked_yes">YES</label><br>
                <input type="radio" id="wdwi_merchant_locked_no" name="wdwi_merchant_locked" value="no" <?php if(esc_attr( $wdwi_merchant_locked )=="no") echo 'checked="checked"'; ?> />
                <label for="wdwi_merchant_locked_no">NO</label><br>
                <p>Beim Sperren des Merchants (Wert NO), so werden alle bereits importierten und auch zu importierende Produkte dieses Merchants gelöscht.</p>
            </td>
        </tr>
        <?php
    }

    static function wdwi_category_taxonomy_fields_save( $term_id ) {
        if ( isset( $_POST[ 'seconddesc' ] ) ) {
            update_term_meta(
                $term_id,
                'seconddesc',
                wp_kses( $_POST[ 'seconddesc' ], 'post' )
            );
        }
    }

    static function wdwi_merchants_taxonomy_fields_save( $term_id ) {
	
        if ( isset( $_POST[ 'wdwi_merchant_ext_id' ] ) ) {
            update_term_meta(
                $term_id,
                'wdwi_merchant_ext_id',
                sanitize_text_field( $_POST[ 'wdwi_merchant_ext_id' ] )
            );
        }
        if ( isset( $_POST[ 'wdwi_merchant_shop' ] ) ) {
                update_term_meta(
                $term_id,
                'wdwi_merchant_shop',
                sanitize_text_field( $_POST[ 'wdwi_merchant_shop' ] )
            );
        }
        if ( isset( $_POST[ 'wdwi_merchant_category' ] ) ) {
            update_term_meta(
                $term_id,
                'wdwi_merchant_category',
                sanitize_text_field( $_POST[ 'wdwi_merchant_category' ] )
            );
        }
        if ( isset( $_POST[ 'wdwi_merchant_locked' ] ) ) {
            update_term_meta(
                $term_id,
                'wdwi_merchant_locked',
                sanitize_text_field( $_POST[ 'wdwi_merchant_locked' ] )
            );
            if( sanitize_text_field( $_POST[ 'wdwi_merchant_locked' ] ) == 'yes' ) {

                WDWI_Helper::wdwi_delete_products_by_merchant( $term_id );

            }
        }
    }

    static function wdwi_filter_products_by_merchants() {
        global $typenow;
        $post_type = 'wdwi_products';
        $taxonomy  = 'merchants';
        if ($typenow == $post_type) {
            $selected      = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
            $info_taxonomy = get_taxonomy($taxonomy);
            wp_dropdown_categories(array(
                'show_option_all' => 'Alle Anzeigen',
                'taxonomy'        => $taxonomy,
                'name'            => $taxonomy,
                'orderby'         => 'name',
                'selected'        => $selected,
                'show_count'      => true,
                'hide_empty'      => true,
            ));
        };
    }

    static function wdwi_convert_id_to_term_in_query($query) {
        global $pagenow;
        $post_type = 'wdwi_products'; // change to your post type
        $taxonomy  = 'merchants'; // change to your taxonomy
        $q_vars    = &$query->query_vars;
        if ( $pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0 ) {
            $term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
            $q_vars[$taxonomy] = $term->slug;
        }
    }

    static function wdwi_change_meta(){

        if( is_archive() && ( get_query_var('taxonomy') == 'wdwi_categories' ) ){
            $title = single_cat_title( '', false ) . " - großes Angebot diverser Hersteller";
            $des = single_cat_title( '', false ) . " kaufen ✓ riesen Angebot aus zig Onlineshops ✓ automatischer Preisvergleich für günstigste Produkte";
            echo '<meta name="title" content="' . $title . '" />';
            echo '<meta name="description" content="' . $des . '" />';
        }
        
    }

}

// Run Setting Class.
WDWI_Merchants::setup();