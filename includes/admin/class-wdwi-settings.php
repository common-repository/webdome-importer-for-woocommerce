<?php
/**
 * WDWI Settings
 *
 * Registers all plugin settings with the WordPress Settings API.
 *
 * @link 
 * @package Webdome Importer for Woocommerce
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

class WDWI_Settings {

    private static $instance;

    private $options;

    public static function instance() {

		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

    public function __construct() {

		// Register settings.
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		// Merge Plugin Options Array from Database with Default Settings Array.
		$this->options = wp_parse_args( get_option( 'wdwi_settings' , array() ), $this->default_settings() );
	}

    public function get( $key, $default = false ) {
		$value = ! empty( $this->options[ $key ] ) ? $this->options[ $key ] : $default;
		return $value;
	}

    public function get_all() {
		return $this->options;
	}

    public function default_settings() {

		$default_settings = array(
			'wdwi_import_debugging' => __( 'off' ),
			'wdwi_license_key' => __( '' ),
			'wdwi_price_lower' => __( '' ),
			'wdwi_price_greater' => __( '' ),
			'wdwi_mail_notification' => __( '' ),
			'wdwi_marktplatz_products' => __( 100 ),
			'wdwi_cronjobs_batch_size' => __( 50 ),
			'wdwi_cronjob_deleting_batch_size' => __( 1000 ),
			'wdwi_yadore_api' => __( '' ),
			'wdwi_yadore_method' => __( 'fuzzy' ),
			'wdwi_yadore_placementid' => __( '' ),
			'wdwi_keywords' => __( '' ),
			'wdwi_merchant_mappings' => __( '' ),
			'wdwi_shop_button' => __( '' ),
			'wdwi_category_menu_title' => __( 'Kategorien' ),
			'wdwi_category_menu_uncategorized' => ( 0 ),
		);

		return $default_settings;
	}

    function register_settings() {

		// Make sure that options exist in database.
		if ( false === get_option( 'wdwi_settings' ) ) {
			add_option( 'wdwi_settings' );
		}

		// Add Sections.
		add_settings_section( 'wdwi_settings_general', 'WDWI Settings', '__return_false', 'wdwi_settings' );

		// Add Settings.
		foreach ( $this->get_registered_settings() as $key => $option ) :

			$name = isset( $option['name'] ) ? $option['name'] : '';
			$section = isset( $option['section'] ) ? $option['section'] : 'widgets';

			add_settings_field(
				'wdwi_settings[' . $key . ']',
				$name,
				is_callable( array( $this, $option['type'] . '_callback' ) ) ? array( $this, $option['type'] . '_callback' ) : array( $this, 'missing_callback' ),
				'wdwi_settings',
				'wdwi_settings_' . $section,
				array(
					'id'      => $key,
					'name'    => isset( $option['name'] ) ? $option['name'] : null,
					'desc'    => ! empty( $option['desc'] ) ? $option['desc'] : '',
					'size'    => isset( $option['size'] ) ? $option['size'] : null,
					'max'     => isset( $option['max'] ) ? $option['max'] : null,
					'min'     => isset( $option['min'] ) ? $option['min'] : null,
					'step'    => isset( $option['step'] ) ? $option['step'] : null,
					'options' => isset( $option['options'] ) ? $option['options'] : '',
					'default'     => isset( $option['default'] ) ? $option['default'] : '',
				)
			);

		endforeach;

		// Creates our settings in the options table.
		register_setting( 'wdwi_settings', 'wdwi_settings', array( $this, 'sanitize_settings' ) );
	}

    function sanitize_settings( $input = array() ) {

		if ( empty( $_POST['_wp_http_referer'] ) ) {
			return $input;
		}

		$saved = get_option( 'wdwi_settings', array() );
		if ( ! is_array( $saved ) ) {
			$saved = array();
		}

		$settings = $this->get_registered_settings();
		$input = $input ? $input : array();

		// Loop through each setting being saved and pass it through a sanitization filter.
		foreach ( $input as $key => $value ) :

			// Get the setting type (checkbox, select, etc).
			$type = isset( $settings[ $key ]['type'] ) ? $settings[ $key ]['type'] : false;

			// Sanitize user input based on setting type.
			if ( 'text' === $type ) :

				$input[ $key ] = sanitize_text_field( $value );

			elseif ( 'radio' === $type or 'select' === $type ) :

				$available_options = array_keys( $settings[ $key ]['options'] );
				$input[ $key ] = in_array( $value, $available_options, true ) ? $value : $settings[ $key ]['default'];

			elseif ( 'checkbox' === $type ) :

				$input[ $key ] = $value; // Validate Checkboxes later.

			elseif ( 'textbox' === $type ) :

				$input[ $key ] = $text = sanitize_text_field( str_replace( "\r\n", ';', trim( $value ) ) );

			else :

				// Default Sanitization.
				$input[ $key ] =  $value ;

			endif;

		endforeach;

		// Ensure a value is always passed for every checkbox.
		if ( ! empty( $settings ) ) :
			foreach ( $settings as $key => $setting ) :

				// Single checkbox.
				if ( isset( $settings[ $key ]['type'] ) && 'checkbox' == $settings[ $key ]['type'] ) :
					$input[ $key ] = ! empty( $input[ $key ] );
				endif;

			endforeach;
		endif;

		// Reset to default settings.
		if ( isset( $_POST['wdwi_reset_defaults'] ) ) {
			$input = $this->default_settings();
		}

		return array_merge( $saved, $input );
	}

    function get_registered_settings() {

		// Get default settings.
		$default_settings = $this->default_settings();

		// Create Settings array.
		$settings = array(
			'wdwi_import_debugging' => array(
				'name' => 'Debugging-Modus für Produktimport',
				'desc' => 'Debugging-Modus für Produktimport',
				'section' => 'general',
				'type' => 'radio',
				'size' => 'regular',
				'options' => [
					'on' => 'Debug-Modus AN',
					'off' => 'Debug-Modus AUS'
				],
				'default' => $default_settings['wdwi_import_debugging'],
			),
			'wdwi_license_key' => array(
				'name' => 'Plugin-Lizenz-Key',
				'desc' => 'Plugin-Lizenz-Key',
				'section' => 'general',
				'type' => 'password',
				'size' => 'regular',
				'default' => $default_settings['wdwi_license_key'],
			),
			'wdwi_price_lower' => array(
				'name' => 'Preisgrenze kleiner',
				'desc' => 'Preisgrenze zum Löschen der Produkte kleiner diesem Preis per Cronjob',
				'section' => 'general',
				'type' => 'number',
				'size' => 'regular',
				'default' => $default_settings['wdwi_price_lower'],
			),
            'wdwi_price_greater' => array(
				'name' => 'Preisgrenze größer',
				'desc' => 'Preisgrenze zum Löschen der Produkte größer diesem Preis per Cronjob',
				'section' => 'general',
				'type' => 'number',
				'size' => 'regular',
				'default' => $default_settings['wdwi_price_greater'],
			),
			'wdwi_mail_notification' => array(
				'name' => 'E-Mail für Benachrichtungen',
				'desc' => 'E-Mail-Adresse für Job-Benachrichtungen',
				'section' => 'general',
				'type' => 'text',
				'size' => 'regular',
				'default' => $default_settings['wdwi_mail_notification'],
			),
			'wdwi_marktplatz_products' => array(
				'name' => 'Anzahl Produkte auf Markptlatz Seite',
				'desc' => '',
				'section' => 'general',
				'type' => 'number',
				'size' => 'regular',
				'default' => $default_settings['wdwi_marktplatz_products'],
			),
            'wdwi_cronjobs_batch_size' => array(
				'name' => 'Anzahl Produkten pro Batch in Cronjobs',
				'desc' => 'Wird nur bei folgenden Cronjobs verwendet: "Products Import"',
				'section' => 'general',
				'type' => 'number',
				'size' => 'regular',
				'step' => '1',
				'default' => $default_settings['wdwi_cronjobs_batch_size'],
			),
            'wdwi_cronjob_deleting_batch_size' => array(
				'name' => 'Anzahl an Produkten, die pro Batch GELÖSCHT werden',
				'desc' => 'Wird nur bei folgenden Cronjobs verwendet: "Products Delete"',
				'section' => 'general',
				'type' => 'number',
				'size' => 'regular',
				'step' => '1',
				'default' => $default_settings['wdwi_cronjob_deleting_batch_size'],
			),
			'wdwi_yadore_api' => array(
				'name' => 'API für Yadore',
				'desc' => '',
				'section' => 'general',
				'type' => 'password',
				'size' => 'regular',
				'default' => $default_settings['wdwi_yadore_api'],
			),   
			'wdwi_yadore_method' => array(
				'name' => 'Methode für Yadore Crawling (strict oder fuzzy)',
				'desc' => '',
				'section' => 'general',
				'type' => 'select',
				'size' => 'regular',
				'default' => $default_settings['wdwi_yadore_method'],
				'options' => array(
					'strict' => 'strict',
					'fuzzy' => 'fuzzy',
				)
			),
			'wdwi_yadore_placementid' => array(
				'name' => 'Placementid (Domainname) für Abfragen',
				'desc' => '',
				'section' => 'general',
				'type' => 'text',
				'size' => 'regular',
				'default' => $default_settings['wdwi_yadore_placementid'],
			),
            'wdwi_keywords' => array(
				'name' => 'Keywords für Produktimport (Semikolon-separiert!!!)',
				'desc' => '',
				'section' => 'general',
				'type' => 'textbox',
				'size' => 'regular',
				'default' => $default_settings['wdwi_keywords'],
			),
			'wdwi_merchant_mappings' => array(
				'name' => 'Mappings Deeplink zu Merchant-Ids. (Trennung über ###)',
				'desc' => '',
				'section' => 'general',
				'type' => 'textbox',
				'size' => 'regular',
				'default' => $default_settings['wdwi_merchant_mappings'],
			),
			'wdwi_shop_button' => array(
				'name' => 'Text für den Kaufen-Button im SHop',
				'desc' => '',
				'section' => 'general',
				'type' => 'text',
				'size' => 'regular',
				'default' => $default_settings['wdwi_shop_button'],
			),
			'wdwi_category_menu_title' => array(
				'name' => 'Menü-Titel für das Kategorien-Menü',
				'desc' => '',
				'section' => 'general',
				'type' => 'text',
				'size' => 'regular',
				'default' => $default_settings['wdwi_category_menu_title'],
			),
            'wdwi_category_menu_uncategorized' => array(
				'name' => 'ID der Unkategorized-Kategorie',
				'desc' => '',
				'section' => 'general',
				'type' => 'number',
				'size' => 'regular',
				'step' => '1',
				'default' => $default_settings['wdwi_category_menu_uncategorized'],
			),
		);

		return apply_filters( 'wdwi_settings', $settings );
	}

	function checkbox_callback( $args ) {

		$checked = isset( $this->options[ $args['id'] ] ) ? checked( 1, $this->options[ $args['id'] ], false ) : '';
		echo '<input type="checkbox" id="wdwi_settings[' . esc_html ( $args['id'] ) . ']" name="wdwi_settings[' . esc_html ( $args['id'] ) . ']" value="1" ' . esc_html ( $checked ) . '/>';
		echo '<label for="wdwi_settings[' . esc_html ( $args['id'] ) . ']"> ' . esc_html ( $args['desc'] ) . '</label>';

	}

    function text_callback( $args ) {

		if ( isset( $this->options[ $args['id'] ] ) ) {
			$value = $this->options[ $args['id'] ];
		} else {
			$value = isset( $args['default'] ) ? $args['default'] : '';
		}

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		echo '<input type="text" class="' . esc_html ( $size ) . '-text" id="wdwi_settings[' . esc_html ( $args['id'] ) . ']" name="wdwi_settings[' . esc_html ( $args['id'] ) . ']" value="' . esc_html ( $value ) . '"/>';
		echo '<p class="description">' . esc_html ( $args['desc'] ) . '</p>';

	}

	function datetime_callback( $args ) {

		if ( isset( $this->options[ $args['id'] ] ) ) {
			$value = $this->options[ $args['id'] ];
		} else {
			$value = isset( $args['default'] ) ? $args['default'] : '';
		}

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		echo '<input type="datetime-local" class="' . esc_html ( $size ) . '-text" id="wdwi_settings[' . esc_html ( $args['id'] ) . ']" name="wdwi_settings[' . esc_html ( $args['id'] ) . ']" value="' . esc_html ( $value ) . '"/>';
		echo '<p class="description">' . esc_html ( $args['desc'] ) . '</p>';

	}

	function textbox_callback( $args ) {

		if ( isset( $this->options[ $args['id'] ] ) ) {
			$value = $this->options[ $args['id'] ];
		} else {
			$value = isset( $args['default'] ) ? $args['default'] : '';
		}

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		echo '<textarea type="text" rows="15" class="' . esc_html ( $size ) . '-text" id="wdwi_settings[' . esc_html ( $args['id'] ) . ']" name="wdwi_settings[' . esc_html ( $args['id'] ) . ']" />' . esc_html ( str_replace( ";", "\r\n", trim( $value ) ) ) . '</textarea>';
		echo '<p class="description">' . esc_html ( $args['desc'] ) . '</p>';

	}
	
	function number_callback( $args ) {

		if ( isset( $this->options[ $args['id'] ] ) ) {
			$value = $this->options[ $args['id'] ];
		} else {
			$value = isset( $args['default'] ) ? $args['default'] : '';
		}
		$step = ( isset( $args['step'] ) && ! is_null( $args['step'] ) ) ? $args['step'] : '0.01';
		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		echo '<input type="number" step="' . esc_html ( $step ) . '" min="' . esc_html ( $args['min'] ) . '" max="' . esc_html ( $args['max'] ) . '" class="' . esc_html ( $size ) . '-text" id="wdwi_settings[' . esc_html ( $args['id'] ) . ']" name="wdwi_settings[' . esc_html ( $args['id'] ) . ']" value="' . esc_html ( $value ) . '"/>';
		echo '<p class="description">' . esc_html ( $args['desc'] ) . '</p>';

	}

	function password_callback( $args ) {

		if ( isset( $this->options[ $args['id'] ] ) ) {
			$value = $this->options[ $args['id'] ];
		} else {
			$value = isset( $args['default'] ) ? $args['default'] : '';
		}

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		echo '<input type="password" class="' . esc_html ( $size ) . '-text" id="wdwi_settings[' . esc_html ( $args['id'] ) . ']" name="wdwi_settings[' . esc_html ( $args['id'] ) . ']" value="' . esc_html ( $value ) . '"/>';
		echo '<p class="description">' . esc_html ( $args['desc'] ) . '</p>';

	}

    function radio_callback( $args ) {

		if ( ! empty( $args['options'] ) ) :
			foreach ( $args['options'] as $key => $option ) :
				$checked = false;

				if ( isset( $this->options[ $args['id'] ] ) && $this->options[ $args['id'] ] == $key ) {
					$checked = true;
				} elseif ( isset( $args['default'] ) && $args['default'] == $key && ! isset( $this->options[ $args['id'] ] ) ) {
					$checked = true;
				}

				echo '<input name="wdwi_settings[' . esc_html ( $args['id'] ) . ']"" id="wdwi_settings[' . esc_html ( $args['id'] ) . '][' . esc_html ( $key ) . ']" type="radio" value="' . esc_html ( $key ) . '" ' . checked( true, $checked, false ) . '/>&nbsp;';
				echo '<label for="wdwi_settings[' . esc_html ( $args['id'] ) . '][' . esc_html ( $key ) . ']">' . esc_html ( $option ) . '</label><br/>';

			endforeach;
		endif;
		echo '<p class="description">' . esc_html ( $args['desc'] ) . '</p>';
	}

    function select_callback( $args ) {

		if ( isset( $this->options[ $args['id'] ] ) ) {
			$value = $this->options[ $args['id'] ];
		} else {
			$value = isset( $args['default'] ) ? $args['default'] : '';
		}

		echo '<select id="wdwi_settings[' . esc_html ( $args['id'] ) . ']" name="wdwi_settings[' . esc_html ( $args['id'] ) . ']"/>';

		foreach ( $args['options'] as $option => $name ) :
			$selected = selected( $option, $value, false );
			echo '<option value="' . esc_html ( $option ) . '" ' . esc_html ( $selected ) . '>' . esc_html ( $name ) . '</option>';
		endforeach;

		echo '</select>';
		echo '<p class="description">' . esc_html ( $args['desc'] ) . '</p>';

	}

    function reset_callback( $args ) {

		echo '<input type="submit" class="button" name="wdwi_reset_defaults" value="RESET"/>';
		echo '<p class="description">' . esc_html ( $args['desc'] ) . '</p>';

	}

    function missing_callback( $args ) {
		printf( 'The callback function used for the <strong>%s</strong> setting is missing.', $args['id'] );
	}
}

// Run Setting Class.
WDWI_Settings::instance();
