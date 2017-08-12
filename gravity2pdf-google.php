<?php
/**
 * Plugin Name: Gravity 2 PDF - Google
 * Plugin URI:  https://www.gravity2pdf.com
 * Description: Deiliver completed merge to Google
 * Version:     1.0
 * Author:      gravity2pdf
 * Author URI:  https://github.com/raphcadiz
 * Text Domain: g2pdf-google
 */

define( 'GMG_PATH', dirname( __FILE__ ) );
define( 'GMG_PATH_CLASS', dirname( __FILE__ ) . '/class' );
define( 'GMG_PATH_INCLUDES', dirname( __FILE__ ) . '/includes' );
define( 'GMG_FOLDER', basename( GMG_PATH ) );
define( 'GMG_URL', plugins_url() . '/' . GMG_FOLDER );
define( 'GMG_URL_INCLUDES', GMG_URL . '/includes' );

if(!class_exists('G2PDF_Google')):

    register_activation_hook( __FILE__, 'g2pdf_google_activation' );
    function g2pdf_google_activation(){
        if ( ! class_exists('Gravity_Merge') ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
            wp_die('Sorry, but this plugin requires the Gravity2PDF to be installed and active.');
        }
    }

    register_deactivation_hook( __FILE__, 'g2pdf_google_deactivation' );
    function g2pdf_google_deactivation(){
        // deactivation block
    }

    add_action( 'admin_init', 'g2pdf_google_plugin_activate' );
    function g2pdf_google_plugin_activate(){
        if ( ! class_exists('Gravity_Merge') ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
        }
    }

    require_once(GMG_PATH.'/vendor/autoload.php');
    
    // include classes
    include_once(GMG_PATH_CLASS.'/g2pdf_google_main.class.php');
    include_once(GMG_PATH_CLASS.'/g2pdf_google_pages.class.php');

    add_action( 'plugins_loaded', array( 'G2PDF_Google', 'get_instance' ) );
endif;