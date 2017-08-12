<?php
class G2PDF_Google_Pages {

    public function __construct() {
        add_action('admin_init', array( $this, 'settings_options_init' ));
        add_action('admin_menu', array( $this, 'admin_menus'), 12 );
    }

    public function settings_options_init() {
        register_setting( 'gmergegoogle_settings_options', 'gmergegoogle_settings_options', '' );
    }

    public function admin_menus() {
        add_submenu_page ( 'gravitymerge' , 'Google Drive' , 'Google Drive' , 'manage_options' , 'gravitymergegoogle' , array( $this , 'gravity2pdf_google' ));
    }

    public function gravity2pdf_google() {
        include_once(GMG_PATH_INCLUDES.'/gravity_merge_google.php');
    }
}

new G2PDF_Google_Pages();