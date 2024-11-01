<?php
/**
 * WDWI Import Logs
 *
 * Show a Log-Table for Product Imports in Admin
 *
 * @link 
 * @package Webdome Importer for Woocommerce
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }


class WDWI_Imports_Logs_Page {

    static function setup() {

		// Add settings page to admin menu.
		add_action( 'admin_menu', array( __CLASS__, 'wdwi_add_import_logs_page' ) );
        add_filter('set-screen-option', array( __CLASS__, 'wdwi_set_screen_option' ), 10, 3 );        

	}

    static function wdwi_add_import_logs_page() {
        
		$this_page = add_submenu_page('wdwi-settings', 'Import Logs', 'Import Logs', 'manage_options', 'wdwi-import-logs', array( __CLASS__, 'display_import_logs_page'));
        add_action("load-$this_page", array( __CLASS__ , 'wdwi_add_screen_option' ) );

	}

    static function wdwi_add_screen_option() {
        $args = array(
            'label' => 'Elemente pro Seite',
            'default' => 50,
            'option' => 'wdwi_elements_per_page'
        );
        add_screen_option( 'per_page', $args );
    }

    static function wdwi_set_screen_option($status, $option, $value) {
        if ( 'wdwi_elements_per_page' == $option ) return $value;
    }

    static function display_import_logs_page() {

		ob_start();
		?>
		<div id="wdwi-import-logs" class="wdwi-import-logs-wrap wrap">

			<h1>Import Logs</h1>

            <h2>Stati und Beschreibungen</h2>
            <ul>
                <li><strong>title-desc: </strong>   Titel und Kurzbeschreibung enthalten keines der Keywords</li>
                <li><strong>locked-merchant: </strong>   Das Merchant ist zwischenzeitlich gesperrt worden</li>
                <li><strong>sku: </strong>   Die SKU / EAN / GTIN existiert bereits als Woo-Produkt</li>
                <li><strong>woo-import-error: </strong>   Es trat ein Fehler beim Anlegen des Produktes in Woocommerce auf</li>
                <li><strong>new: </strong>   Das Produkt wurde erfolgreich importiert</li>
            </ul>

            <h2>Logs</h2>
            <!-- <p>Gesucht werden kann nach einem Status oder nach einem Hash-Code</p> -->
		
		<?php

		$exampleListTable = new WDWI_Imports_Logs_Table();
        // $exampleListTable->search_box('Suchen', 'search_id');
		$exampleListTable->prepare_items();
		?>
		
			<div id="icon-users" class="icon32"></div>
					
				<?php $exampleListTable->display(); ?>
	
			</div>
		<?php

		echo ob_get_clean();
	}
}

// Run Setting Class.
WDWI_Imports_Logs_Page::setup();

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WDWI_Imports_Logs_Table extends WP_List_Table
{

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        if ( isset($_GET['cron_hash']) ) {
            $data = $this->table_data($_GET['cron_hash']);
        } else {
            $data = $this->table_data();
        }
        usort( $data, array( &$this, 'sort_data' ) );

        $perPage = $this->get_items_per_page('wdwi_elements_per_page', 20);
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);

        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );

        $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    public function get_columns()
    {
        $columns = array(
            'tp_id'         => 'ID',
            'ean' 		    => 'GTIN',
            'woo_key'       => 'Woo-Product-ID',
            'cat'    	    => 'Kategorie',
            'name'          => 'Produktname',
            'price'      	=> 'Preis',
            'merchant_key'  => 'Merchant',
            'insert_date'   => 'Importiert am',
            'task'      	=> 'Status',
        );

        return $columns;
    }

    public function get_hidden_columns()
    {
        return array();
    }

    public function get_sortable_columns()
    {
        return array(
            'tp_id'         => array('tp_id', true),
            'ean' 		    => array('ean', true),
            'woo_key'       => array('woo_key', true),
            'cat'    	    => array('cat', true),
            'name'          => array('name', true),
            'price'      	=> array('price', true),
            'merchant_key'  => array('merchant_key', true),
            'insert_date'   => array('insert_date', true),
            'task'      	=> array('task', true),
		);
    }

    private function table_data( $cron_hash = '' )
    {
        $data = array();
        global $wpdb;
    	$table_logs = $wpdb->prefix . "wdwi_temp_products"; 
    	if ( !empty($cron_hash) ) {
            return $wpdb->get_results("SELECT * FROM " . $table_logs . " WHERE task <> 'crawl' and cron_hash_key = '$cron_hash'", ARRAY_A);
        } else {
            return $wpdb->get_results("SELECT * FROM " . $table_logs . " WHERE task <> 'crawl'", ARRAY_A);
        }
    }

    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
			case 'tp_id':
            case 'ean':
            case 'cat':
            case 'name':
            case 'price':
            case 'insert_date':
            case 'task':
                return $item[ $column_name ];
            case 'woo_key':
                return '<a href="/wp-admin/post.php?post=' . $item[ $column_name ] . '&action=edit">' . $item[ $column_name ] . '</a>';
            case 'merchant_key':
                if( !is_null( get_term( $item[ $column_name ] ) ) ) { return get_term( $item[ $column_name ] )->name; } else { return ''; }
            default:
                return print_r( $item, true ) ;
        }
    }

    private function sort_data( $a, $b )
    {
        // Set defaults
        $orderby = 'tp_id';
        $order = 'desc';

        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))
        {
            $orderby = sanitize_text_field( $_GET['orderby'] );
        }

        // If order is set use this as the order
        if(!empty($_GET['order']))
        {
            $order = sanitize_text_field( $_GET['order'] );
        }


        $result = strnatcmp( $a[$orderby], $b[$orderby] );

        if($order === 'asc')
        {
            return $result;
        }

        return -$result;
    }
}
