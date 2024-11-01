<?php
/**
 * WDWI Rest
 *
 * Functions
 *
 * @link 
 * @package Webdome Importer for Woocommerce
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

class WDWI_REST_Controller extends WP_REST_Controller {

    protected $namespace;

    protected $rest_base;

    public function __construct() {

		$this->namespace = 'wdwi/v1';
		$this->rest_base = 'jobs';
	
    }

    public function register_routes() {

		register_rest_route( $this->namespace, '/' . $this->rest_base, array(

			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'wdwi_api_endpoint_callback' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'            	  => $this->get_endpoint_args_for_item_schema( false ),
			),
			'schema' => array( $this, 'get_public_item_schema' ),

		) );

	}

    public function setup() {

        // add_action( 'rest_api_init', array( $this, 'register_routes' ) );

    }

    public function wdwi_api_endpoint_callback( $request ) {

		$param = $request->get_params();

		// $args = WDWI_CJ_Yadore_Merchants::setup( 'cron' );

		if ( ! isset( $param['activity'] ) ) {
			return new WP_Error( 'invalid_data', __( 'No Activity.' ), array( 'status' => 400 ) );
		}

		if ( ! isset( $param['min_wdwi_version'] ) ) {
			return new WP_Error( 'invalid_data', __( 'No min_wdwi_version.' ), array( 'status' => 400 ) );
		}

		if( WDWI_VERSION != $param['min_wdwi_version'] ) {
			return new WP_Error( 'invalid_data', __( 'Importer Plugin invalid version ' . WDWI_VERSION . ' to requirement: ' . $param['min_wdwi_version'] ), array( 'status' => 400 ) );
		}

		$activity = $param['activity'];

		switch( $activity ) {
			case 'none':
                $args = WDWI_CJ_Yadore_Merchants::setup( 'cron' );
				break;
            case 'merchants':
                $args = WDWI_CJ_Yadore_Merchants::setup( 'cron' );
				break;
            case 'crawler':
                $args = WDWI_CJ_Yadore_Products::setup( 'cron' );
				break;
            case 'merge':
                $args = WDWI_CJ_Merge_Files::setup( 'cron' );
				break;
            case 'clean':
                $args = WDWI_CJ_Delete_Products::setup( 'cron' );
				break;
			case 'import':
				$args = WDWI_CJ_Products_Import::setup( 'cron' );
				break;
			case 'atf-clean':
				if( file_exists( ABSPATH . 'wp-content/plugins/automatic-taxonomy-filler/cronjob/clean.php' ) ) {
					require_once( ABSPATH . 'wp-content/plugins/automatic-taxonomy-filler/cronjob/clean.php' );
					$args =  ATF_Clean::init();
				} else {
					return new WP_Error( 'missing ATF-File', __( 'ATF File clean.php is missing' ), array( 'status' => 400 ) );
				}
				break;
			case 'atf-prepare':
				if( file_exists( ABSPATH . 'wp-content/plugins/automatic-taxonomy-filler/cronjob/prepare-terms.php' ) ) {
					require_once( ABSPATH . 'wp-content/plugins/automatic-taxonomy-filler/cronjob/prepare-terms.php' );
					$args =  ATF_Prepare::init();
				} else {
					return new WP_Error( 'missing ATF-File', __( 'ATF File prepare-terms.php is missing' ), array( 'status' => 400 ) );
				}
				break;
			case 'atf-assign':
				if( file_exists( ABSPATH . 'wp-content/plugins/automatic-taxonomy-filler/cronjob/assign.php' ) ) {
					require_once( ABSPATH . 'wp-content/plugins/automatic-taxonomy-filler/cronjob/assign.php' );
					$args =  ATF_Assign::init();
				} else {
					return new WP_Error( 'missing ATF-File', __( 'ATF File assign.php is missing' ), array( 'status' => 400 ) );
				}
				break;
            default:
            	$args = WDWI_CJ_Yadore_Merchants::setup( 'cron' );
				break;
        }

        return new WP_REST_Response( $args, 200 );

    }
    
    public function get_items_permissions_check( $request ) {
		if ( ! current_user_can( 'read' ) ) {
			return new WP_Error( 'rest_forbidden', esc_html__( 'You are not logged in or you got no rights.' ), array( 'status' => $this->authorization_status_code() ) );
		}
		return true;
	}

    public function authorization_status_code() {

		$status = 401;

		if ( is_user_logged_in() ) {
			$status = 403;
		}

		return $status;
	}

}

function wdwi_register_rest_controller() {
	$controller = new WDWI_REST_Controller();
	$controller->register_routes();
}

add_action( 'rest_api_init', 'wdwi_register_rest_controller' );
