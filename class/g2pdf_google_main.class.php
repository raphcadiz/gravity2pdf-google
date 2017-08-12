<?php
class G2PDF_Google {
    private static $instance;

    // plugin version
    private $version = '1.0';
    
    public static function get_instance()
    {
        if( null == self::$instance ) {
            self::$instance = new G2PDF_Google();
            self::$instance->licensing();
        }

        return self::$instance;
    }

    private function licensing() {
        if ( class_exists( 'Gravity_Merge_License' ) ) {
            $license = new Gravity_Merge_License( __FILE__, 'Gravity 2 PDF - Google', $this->version, 'Raph Cadiz' );
        }
    }

    public function __construct() {
        add_filter('gmerge_integrations_ajax_paths', array( $this, 'register_integrations_path' ), 1);
        add_action('admin_init', array($this, 'getGoogleDriveAccessToken'));
        add_action('g2pdf_after_merge', array($this, 'process_integration'), 10, 4);
        add_action('wp_ajax_googleDriveIntegrationTemplate' , array( $this , 'ajax_integration_template' ));
        add_action('google_integration_template' , array( $this , 'integration_template' ), 10, 2);
      }

    public function register_integrations_path($paths) {
        $dropbox_path = array(
            'google' => 'googleDriveIntegrationTemplate'
        );

        return array_merge( $paths, $dropbox_path );
    }

    public function ajax_integration_template() {
        ob_start();
        ?>
        <div class="integration-wrapper">
            <a href="javascript:;" class="integration-remove"><span class="dashicons dashicons-minus"></span></a>
            <label><strong>Google Drive</strong></label><br /><br />
            <label>Google Drive Delivery</label><br />
            <input type="text" name="integrations[%key%][google][integration_googledrive]" />
            <span><i>Specified Folder name where the pdf must be uploaded. You can use the following user variables also %date% , %user_email% , %user_firstname% , %user_lastname% for logged in user. You can use gravity form , form entry ids also %form_id% , %form_entry_id%</i></span>
        </div>
        <?php
        $template = ob_get_contents();
        ob_end_clean();

        echo $template;
        die();
    } 

    public function integration_template($index = 0, $value = array()) {
        ob_start();
        ?>
        <div class="integration-wrapper">
            <a href="javascript:;" class="integration-remove"><span class="dashicons dashicons-minus"></span></a>
            <label><strong>Google Drive</strong></label><br /><br />
            <label>Google Drive Delivery</label><br />
            <input type="text" name="integrations[<?= $index ?>][google][integration_googledrive]" value="<?= $value->integration_googledrive ?>"/>
            <span><i>Specified Folder name where the pdf must be uploaded. You can use the following user variables also %date% , %user_email% , %user_firstname% , %user_lastname% for logged in user. You can use gravity form , form entry ids also %form_id% , %form_entry_id%</i></span>
        </div>
        <?php
        $template = ob_get_contents();
        ob_end_clean();

        echo $template;
    }

    public function getGoogleDriveAccessToken(){
        if (isset($_REQUEST['integration']) && $_REQUEST['integration'] == 'googledrive' ):
            $gmergegoogle_settings_options = get_option('gmergegoogle_settings_options');
            $client_id            = isset($gmergegoogle_settings_options['client_id']) ? $gmergegoogle_settings_options['client_id'] : '';
            $client_secret        = isset($gmergegoogle_settings_options['client_secret']) ? $gmergegoogle_settings_options['client_secret'] : '';

            if(!empty($client_id) && !empty($client_secret)) {
                session_start();
                $google_keys = array( 
                        'client_id' => $client_id, 
                        'client_secret' => $client_secret
                    );
                $client = new Google_Client();
                $client->setAuthConfig($google_keys);
                $client->addScope(Google_Service_Drive::DRIVE);
                $client->setRedirectUri(admin_url( 'admin.php?page=gravitymergegoogle&integration=googledrive' ));
                $client->setAccessType('offline');
                $client->setApprovalPrompt('force');

                if (! isset($_GET['code'])) {
                    $auth_url = $client->createAuthUrl();
                    header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
                } else {
                    $client->authenticate($_GET['code']);
                    $response = $client->getAccessToken();
                    $gmergegoogle_settings_options['token'] = json_encode($response);
                    update_option( 'gmergegoogle_settings_options', $gmergegoogle_settings_options );
                    header('Location: ' . admin_url( 'admin.php?page=gravitymergegoogle' ));
                }
                
            }
            
        endif;
    }

    public function process_integration($final_file, $file_name, $entry, $integrations){
        if(!property_exists($integrations, 'google'))
            return;

        $integration = $integrations->google;
        $gmergegoogle_settings_options = get_option('gmergegoogle_settings_options');
        $accessToken = json_decode($gmergegoogle_settings_options['token']);
        $drivePath = '/'.ltrim($integration->integration_googledrive, '/');

        $drivePath = str_replace( "%form_id%" , rgar( $entry, 'form_id' ) , $drivePath );
        $drivePath = str_replace( "%form_entry_id%" , rgar( $entry, 'id' ) , $drivePath );
        $drivePath = str_replace( "%date%" , time() , $drivePath );

        if( is_user_logged_in() ) {
            $current_user = wp_get_current_user();
            $drivePath = str_replace( "%user_email%" , $current_user->user_email , $drivePath );
            $drivePath = !empty($current_user->user_firstname) ? str_replace( "%user_firstname%" , $current_user->user_firstname , $drivePath ) : str_replace( "%user_firstname%" , 'userfirstname' , $drivePath );
            $drivePath = !empty($current_user->user_lastname) ? str_replace( "%user_lastname%" , $current_user->user_lastname , $drivePath ) : str_replace( "%user_lastname%" , 'userlastname' , $drivePath );
        }
        
        $client_id = isset($gmergegoogle_settings_options['client_id']) ? $gmergegoogle_settings_options['client_id'] : '';
        $client_secret = isset($gmergegoogle_settings_options['client_secret']) ? $gmergegoogle_settings_options['client_secret'] : '';
        $access_token = isset($gmergegoogle_settings_options['token']) ? $gmergegoogle_settings_options['token'] : '';
        $accessToken_array = json_decode($access_token);

        if(!empty($client_id) && !empty($client_secret)):
            $google_keys = array( 
                'client_id' => $client_id, 
                'client_secret' => $client_secret
            );

            $client = new Google_Client();
            $client->setAuthConfig($google_keys);
            $client->setScopes( array('https://www.googleapis.com/auth/drive') );
            $client->setRedirectUri( admin_url('admin.php?page=gravitymergeintegrations&integration=googledrive') );
            $client->setAccessType('offline');
            $client->setApprovalPrompt('force');
            $access_token = $client->refreshToken($accessToken_array->refresh_token);
            $client->setAccessToken($access_token);
            
            if($client->getAccessToken()){
                $service = new Google_Service_Drive($client);
                $meta_data = array();
                if(!empty($drivePath)){
                    $explode_paths = explode("/", $drivePath);
                    $parent_folder = '';
                    foreach($explode_paths as $path){
                        if(!empty($path)){
                            $new_parent_folder = $this->createFolderGoogleDrive($service, $path, $parent_folder);
                            $parent_folder = $new_parent_folder;
                        }
                    }
                    $meta_data = array(
                      'name' => $file_name,
                      'parents' => array($parent_folder)
                    );
                } else {
                    $meta_data = array(
                      'name' => $file_name
                    );
                }
                

                $filemetadata = new Google_Service_Drive_DriveFile($meta_data);
                $result = $service->files->create($filemetadata, array(
                      'data' => file_get_contents($final_file),
                      'mimeType' => 'application/pdf',
                    ));
                // exit..
            }
        endif;
    }

    private function createFolderGoogleDrive($service, $folder_name, $parent = null){
        $data_file = array();
        if($parent == null){
            $data_file = array(
                'name'      => $folder_name,
                'mimeType'  => 'application/vnd.google-apps.folder'
            );
        }
        else {
            $data_file = array(
                'name'      => $folder_name,
                'mimeType'  => 'application/vnd.google-apps.folder',
                'parents'   => array($parent)
            );
        }

        $fileMetadata = new Google_Service_Drive_DriveFile($data_file);
        $file = $service->files->create($fileMetadata, array(
                'fields' => 'id')
            );

        return $file->id;
    }
}