<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

include_once('ScuolasemplceContacts_LifeCycle.php');

class ScuolasemplceContacts_Plugin extends ScuolasemplceContacts_LifeCycle {

    /**
     * See: =31
     * @return array of option meta data.
     */
    public function getOptionMetaData() {
        //  =31
        return array(
            //'_version' => array('Installed Version'), // Leave this one commented-out. Uncomment to test upgrades.
            'Apihost' => array( __( 'Host', 'scuolasemplcecontacts' ) ),
            'ApiUsername' => array( __( 'Username', 'scuolasemplcecontacts' ) ),
            'ApiPassword' => array( __( 'Password', 'scuolasemplcecontacts' ) ),
        );
    }

//    protected function getOptionValueI18nString($optionValue) {
//        $i18nValue = parent::getOptionValueI18nString($optionValue);
//        return $i18nValue;
//    }

    protected function initOptions() {
        $options = $this->getOptionMetaData();

        if ( ! empty( $options ) ) {
            foreach ( $options as $key => $arr ) {
                if ( is_array( $arr ) && count( $arr > 1 ) ) {
                    $this->addOption( $key, $arr[1] );
                }
            }
        }
    }

    public function getPluginDisplayName() {
        return 'ScuolaSemplice Contacts';
    }

    protected function getMainPluginFileName() {
        return 'scuolasemplceContacts.php';
    }

    /**
     * See: =101
     * Called by install() to create any database tables if needed.
     * Best Practice:
     * (1) Prefix all table names with $wpdb->prefix
     * (2) make table names lower case only
     * @return void
     */
    protected function installDatabaseTables() {
        global $wpdb;

        $tableName = $this->prefixTableName( 'apiformfields' );

        $wpdb->query(
            "CREATE TABLE IF NOT EXISTS `$tableName` (
                `standardfields` text,
                `customfields` text,
                `fetcheddate` INTEGER
            )"
        );

        $tableName = $this->prefixTableName( 'apiforms' );

        $wpdb->query(
            "CREATE TABLE IF NOT EXISTS `$tableName` (
                `id` INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `formname` varchar(255) ,
                `formpreview` TEXT ,
                `formelement` TEXT,
                `formsettings` TEXT,
                `createdddate` INTEGER
            )"
        );

        $tableName = $this->prefixTableName( 'enquiry' );

        $wpdb->query(
            "CREATE TABLE IF NOT EXISTS `$tableName` (
                `id` INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `name` varchar(255) ,
                `formid` INTEGER ,
                `formdata` TEXT,
                `createdddate` INTEGER ,
                `returnid` INTEGER
            )"
        );
    }

    /**
     * See: =101
     * Drop plugin-created tables on uninstall.
     * @return void
     */
    protected function unInstallDatabaseTables() {
        global $wpdb;

        $tableName = $this->prefixTableName( 'apiformfields' );
        $optionMetaData = $this->getOptionMetaData();

        foreach ( $optionMetaData as $aOptionKey => $aOptionMeta ) {
            $$aOptionKey=$this->deleteOption( $aOptionKey );
        }

        $tableName = $this->prefixTableName( 'apiformfields' );
        $wpdb->query( "DROP TABLE IF EXISTS `$tableName`" );
        $tableName = $this->prefixTableName( 'apiforms' );
        $wpdb->query( "DROP TABLE IF EXISTS `$tableName`" );
        $tableName = $this->prefixTableName( 'enquiry' );
        $wpdb->query( "DROP TABLE IF EXISTS `$tableName`" );
    }

    /**
     * Perform actions when upgrading from version X to version Y
     * See: =35
     * @return void
     */
    public function upgrade() {
    }

    public function addActionsAndFilters() {
        // Add options administration page
        // =47
        add_action( 'admin_menu', array( &$this, 'pluginMenus' ) );
        add_action( 'admin_footer', array( &$this, 'custom_css_js' ) );

        // Example adding a script & style just for the options administration page
        // =47
        //        if (strpos($_SERVER['REQUEST_URI'], $this->getSettingsSlug()) !== false) {
        //            wp_enqueue_script('my-script', plugins_url('/js/my-script.js', __FILE__));
        //            wp_enqueue_style('my-style', plugins_url('/css/my-style.css', __FILE__));
        //        }


        // Add Actions & Filters
        // =37

        add_action( 'fetchfield_cron_hook', array( &$this,'fetchfield_cron_exec' ) );

        // Adding scripts & styles to all pages
        // Examples:
        //        wp_enqueue_script('jquery');
        //        wp_enqueue_style('my-style', plugins_url('/css/my-style.css', __FILE__));
        //        wp_enqueue_script('my-script', plugins_url('/js/my-script.js', __FILE__));


        // Register short codes
        //
        add_shortcode( 'ScuolasemplceForm', array( &$this,'outputapiform' ) );

        // Register AJAX hooks
        //
        add_action( 'wp_ajax_apiforms_datatables', array( &$this, 'apiforms_datatables_server_side_callback' ) );

        add_action( 'wp_ajax_apiforms_delete', array( &$this, 'apiforms_delete_server_side_callback' ) );

        add_action( 'wp_ajax_enquiry_datatables', array( &$this, 'enquiry_datatables_server_side_callback' ) );

        add_action( 'wp_ajax_enquiry_delete', array( &$this, 'enquiry_delete_server_side_callback' ) );

        add_action( 'wp_ajax_enquiry_view', array( &$this, 'enquiry_view_server_side_callback' ) );

        add_action( 'wp_ajax_apiforms_view', array( &$this, 'apiforms_view_server_side_callback' ) );

        add_action( 'wp_ajax_apiforms_submit', array( &$this, 'apiforms_submit_server_side_callback' ) );
        add_action( 'wp_ajax_nopriv_apiforms_submit', array( &$this, 'apiforms_submit_server_side_callback' ) );

        // Hooks supporting admin pages
        add_action( 'admin_head', array( $this, 'includeAdminStyles' ) );
        add_action( 'admin_init', array( $this, 'saveNewForm' ), 100 );
        add_action( 'admin_enqueue_scripts', [ $this, 'includeAdminAssets' ] );

        // Hook for additional user meta

        add_action('show_user_profile', array( &$this, 'apiforms_addition_user_meta_callback' ) );
        add_action('edit_user_profile', array( &$this, 'apiforms_addition_user_meta_callback' ) );

        // Hook for user email confirmation
        add_action( 'init', array( &$this, 'apiforms_verify_user_code' ) );
        add_filter( 'authenticate', array( &$this, 'apiforms_authenticate_user_code' ), 30, 3 );
        add_action('login_head', array( &$this, 'apiforms_activationmsg' ));

    }

    public function apiforms_activationmsg() {
        global $error;
        if( isset($_GET['actmsg']) && !empty($_GET['actmsg']))
            $error = $_GET['actmsg'];
    }

    public function apiforms_authenticate_user_code($user, $username, $password ){
      $user_obj = get_user_by('login', $username );
      $activation_code = get_user_meta($user_obj->ID, 'activation_code', true);

      if ($username!='' && $activation_code){
          if($activation_code!='confirmed'){
              $user = new WP_Error( 'authentication_failed', __("ERROR : You need to activate your account.") );
              remove_action('authenticate', array( &$this, 'apiforms_authenticate_user_code' ), 20); //key found - don't proceed!
          }
      }
      return $user;
    }

    public function apiforms_verify_user_code(){
        if(isset($_GET['act'])){
            $data = unserialize(base64_decode($_GET['act']));
            $code = get_user_meta($data['id'], 'activation_code', true);
            // verify whether the code given is the same as ours
            if(isset($data['id']) && $data['code']){
              if($code == $data['code']){
                  // update the user meta
                  update_user_meta($data['id'], 'activation_code', 'confirmed');
                  wp_redirect( wp_login_url().'/?actmsg='.__( 'Alert: Your Account Activated, Please Login here.', 'scuolasemplcecontacts' ));
                  exit;
              }
              else if($code=='confirmed'){
                wp_redirect( wp_login_url().'/?actmsg='.__( 'Alert: Your account has been already activated!, Please Login here.', 'scuolasemplcecontacts' ));
                exit;
              }
              else {
                wp_redirect( wp_login_url().'/?actmsg='.__( 'Error: Invalid operation!', 'scuolasemplcecontacts' ));
                exit;
              }
            }
            else{
              wp_redirect( wp_login_url().'/?actmsg='.__( 'Error: Invalid operation!', 'scuolasemplcecontacts' ));
              exit;
            }

        }
    }

    public function apiforms_addition_user_meta_callback($user) {
      if(isset($user->scuolasempliceid)){
    ?>
      <h3>Other</h3>
      <label for="scuolasempliceid">
        scuolasempliceid : <?=$user->scuolasempliceid?>
      </label>
    <?php
      }
    }

    public function custom_css_js() {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('#extlink').parent().attr('target','_blank');
            });
        </script>
        <?php
    }

    public function fetchfield_cron_exec() {
        global $wpdb;

        $tableName = $this->prefixTableName( 'apiformfields' );
        $optionMetaData = $this->getOptionMetaData();

        foreach ( $optionMetaData as $aOptionKey => $aOptionMeta ) {
            $$aOptionKey = $this->getOption($aOptionKey);
        }

        if ( empty( $Apihost ) || empty( $ApiUsername ) || empty( $ApiPassword ) ) {
            echo "no credential found";
            return;
        }

        $standardfields_r = $this->api_http_get( $Apihost . '/api/fetchstandardfields/students', $ApiUsername, $ApiPassword );

        if ( $standardfields_r['response']['code'] != 200 ) {
            echo "<span style='color:red;font-weight:bold'>" . __( $standardfields_r['body'] ) . "</span>";
            return;
        }

        $standardfields = $standardfields_r['body'];

        // Ensure standardFields is valid JSON array
        $standardFieldsDecoded = json_decode( $standardfields );

        if ( ! $standardFieldsDecoded ) {
            return;
        }

        $customfields_r = $this->api_http_get( $Apihost . '/api/fetchcustomfields/students', $ApiUsername, $ApiPassword );
        $customfields = $customfields_r['body'];

        // Ensure customFields is valid JSON array
        $customFieldsDecoded = json_decode( $customfields );

        if ( ! $customFieldsDecoded ) {
            return;
        }

        $fetcheddate = time();

        $results = $wpdb->get_results( "SELECT * FROM " . $tableName . " WHERE 1 " );
        $totalrecord = $wpdb->num_rows;

        if ( $totalrecord > 0 ) {
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE $tableName SET standardfields = %s, customfields = %s, fetcheddate = %d WHERE fetcheddate < $fetcheddate",
                    $standardfields,
                    $customfields,
                    $fetcheddate
                )
            );
        } else {
            $wpdb->insert(
                $tableName,
                array(
                    'standardfields' => $standardfields,
                    'customfields' => $customfields,
                    'fetcheddate' => $fetcheddate, // ... and so on
                )
            );
        }
    }

    public function apiforms_view_server_side_callback() {
        global $wpdb;

        $ID = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

        $tableName = $this->prefixTableName( 'apiforms' );
        $results = $wpdb->get_results(
            $wpdb->prepare( "SELECT *  FROM " . $tableName . " WHERE id = %d", $ID )
        );

        wp_send_json(
            array(
                'error' => 0,
                'data' => $results[0]
            )
        );
    }

    public function enquiry_view_server_side_callback(){
        global $wpdb;

        $ID = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

        $tableName = $this->prefixTableName( 'enquiry' );
        $results = $wpdb->get_results(
            $wpdb->prepare( "SELECT *  FROM " . $tableName . " WHERE id= %d ", $ID )
        );

        wp_send_json(
            array(
                'error' => 0,
                'data' => unserialize( $results[0]->formdata )
            )
        );
    }

    public function apiforms_submit_server_side_callback() {
        global $wpdb;

        $formId = isset( $_POST['formid'] ) ? absint( $_POST['formid'] ) : 0;

        $tableName = $this->prefixTableName( 'apiforms' );
        $efdata = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM " . $tableName . " WHERE id = %d", $formId ),
            ARRAY_A
        );

        $efdata_formsettings = json_decode( $efdata['formsettings'] );

        $tableName = $this->prefixTableName( 'enquiry' );

        $notFields = [ 'action', 'formid', '_cpassword','_password' ];

        if($efdata_formsettings->creatapiuser == 1){
          $_POST['password']=$_POST['_password'];
          $_POST['createuser']=1;
        }
        if($efdata_formsettings->creatwpuser == 1 && email_exists(sanitize_text_field( $_POST['email'] ))){
          wp_send_json(
              array(
                  'error' => 1,

                  'msg' => '<span style="color:red">'.__( 'ATTENTION! Email already exist!!', 'scuolasemplcecontacts' ).'</span>',
                  //'apimsg' => $postrdfields_r
              )
          );
          exit;
        }
        $formData = array();

        $apipst = array();
        foreach( $_POST as $key => $val ) {
            if ( in_array( $key, $notFields ) ) {
                continue;
            }

            $formData[ $key ] = $val;

            $field = array();

            if ( strpos( $key, 'field_' ) !== false ) {
                $field['type'] = 'custom';
                $field['id'] = str_replace( 'field_', '', $key );
            } else {
                $field['type'] = 'standard';
                $field['name'] = $key;
            }

            $field['value'] = $val;
            $apipst[] = $field;
        }

        $optionMetaData = $this->getOptionMetaData();
        foreach ( $optionMetaData as $aOptionKey => $aOptionMeta ) {
            $$aOptionKey=$this->getOption( $aOptionKey );
        }

        $endpoint = $efdata_formsettings->contacttp == 1 ? "/api/creatstudent" : "/api/createlead";
        $postrdfields_r = $this->api_http_post(
            $Apihost . $endpoint,
            $ApiUsername,
            $ApiPassword,
            json_encode( $apipst )
        );

        $postrdfields = $postrdfields_r['body'];
        $postrdfields_a = json_decode( $postrdfields );

        if($postrdfields_a->success == 0) {
            wp_send_json(
                array(
                    'error' => 2,
  
                    'msg' => '<span style="color:red">'.__( $postrdfields_a->error, 'scuolasemplcecontacts' ).'</span>',
                )
            );
            exit;
        }
        //print_r($postrdfields);
        //exit;
        $name = ( isset( $_POST['name'] ) && ! empty( $_POST['name'] ) ) ? sanitize_text_field( $_POST['name'] ) : 'na';
        $surname = ( isset( $_POST['surname'] ) && ! empty( $_POST['surname'] ) ) ? sanitize_text_field( $_POST['surname'] ) : 'na';
        $email = ( isset( $_POST['email'] ) && ! empty( $_POST['email'] ) ) ? sanitize_text_field( $_POST['email'] ) : 'na';
        $pswrd = ( isset( $_POST['_password'] ) && ! empty( $_POST['_password'] ) ) ? sanitize_text_field( $_POST['_password'] ) : 'na';


        if($efdata_formsettings->creatwpuser == 1){
          $user_id = wp_create_user( $email, $pswrd, $email );
          $usererrormsg='';
          if ( !is_wp_error( $user_id ) ) {
            wp_update_user( array( 'ID' => $user_id, 'first_name' => $name, 'last_name'=>$surname ) ) ;
            if($efdata_formsettings->creatapiuser == 1){
               add_user_meta( $user_id, 'scuolasempliceid', $postrdfields_a->id);
            }
            $code = md5(time());
            // make it into a code to send it to user via email
            $string = array('id'=>$user_id, 'code'=>$code);
            add_user_meta($user_id, 'activation_code', $code);
            // create the url
            $url = get_site_url(). '/?act=' .base64_encode( serialize($string));
            // basically we will edit here to make this nicer
            $html = __('Please click on following link to activate your account', 'scuolasemplcecontacts' ).'<br/><br/> <a href="'.$url.'">'.$url.'</a>';
            // send an email out to user
            $headers = array('Content-Type: text/html; charset=UTF-8');
            wp_mail( $email, __('Account Activation','scuolasemplcecontacts') , $html, $headers);
            wp_new_user_notification($user_id);
            $usererrormsg = __('We have sent you confirmation mail, pleases click on link in mail.','scuolasemplcecontacts');
          }
          //else{
            //$usererrormsg=__(' User already exist!!!', 'scuolasemplcecontacts' );
          //}
        }
        $wpdb->insert(
            $tableName,
            array(
                'name' => $name,
                'formid' => $formId,
                'formdata' => serialize( $formData ),
                'createdddate' => time(), // ... and so on
                'returnid' => ( $postrdfields_a->success == 1 ? $postrdfields_a->id : 404 )
            ),
            array( '%s','%d','%s','%d','%d' )
        );

        wp_send_json(
            array(
                'error' => 0,
                'afterpost' => $efdata_formsettings->afterpst,
                'redirect' => $efdata_formsettings->thanksurl,
                'msg' => __( $efdata_formsettings->thanksmsg." ".$usererrormsg, 'scuolasemplcecontacts' ),
                //'apimsg' => $postrdfields_r
            )
        );
    }

    public function outputapiform( $atts ) {
        global $wpdb;

        $a = shortcode_atts(
            array(
                'id' => ''
            ),
            $atts
        );

        wp_register_style( 'bootstrap-css', plugins_url() . '/' . $this->getPluginFolder() . '/css/bootstrap.min.css' );
        wp_register_style( 'font-awesome-css', plugins_url() . '/' . $this->getPluginFolder() . '/inc/fa/css/font-awesome.min.css' );
        wp_enqueue_style( 'bootstrap-css' );
        wp_enqueue_style( 'font-awesome-css' );
        wp_register_style( 'jquery-ui', plugins_url() . '/' . $this->getPluginFolder() . '/css/jquery-ui.css' );
        wp_enqueue_style( 'jquery-ui' );
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( 'form_datatables', plugins_url() . '/' . $this->getPluginFolder() . '/js/formembed.js', array(), '1.0', true );
        wp_localize_script( 'form_datatables', 'ajax_url', admin_url( 'admin-ajax.php' ) );
        wp_localize_script( 'form_datatables', 'plugin_url', plugins_url() . '/' . $this->getPluginFolder() . '/' );

        $tableName = $this->prefixTableName( 'apiforms' );
        $results = $wpdb->get_results( "SELECT * FROM " . $tableName . " WHERE id=" . absint( $a['id'] ) );

        $efdata_formsettings = json_decode( $results[0]->formsettings );

        if ( ! empty( trim( $efdata_formsettings->allowedip ) ) ) {
            $allowedips_t = explode( ',', trim( $efdata_formsettings->allowedip ) );
            $allowedips = array();

            foreach( $allowedips_t as $allowedip ) {
                $allowedips[] = gethostbyname( trim( $allowedip ) );
            }

            $ip = $this->getUserIpAddr();

            if ( ! in_array( $ip, $allowedips ) ) {
                return __( "This module it is not accessible from your current location", 'scuolasemplcecontacts' );
            }
        }

        ob_start();
        ?>
        <style>
            #submitloader{position:relative;}
            .fa-li {left: -0.142857em !important;}
            #fmsg{color:blue; font-weight:bold}
            .ui-datepicker-trigger img{width:15px;}
            button.ui-datepicker-trigger {position: absolute;top: 20px;right: 10px; background-color: transparent;}
            .form-group{position:relative}
            input.invalid, select.invalid, textarea.invalid {background-color: #ffdddd;}
            input[type="checkbox"].invalid {
              -webkit-appearance: none;
              -moz-appearance: none;
              position: relative;
              top: 2px;
              display: inline-block;
              margin: 0;
              width: 1.5rem;
              min-width: 1.5rem;
              height: 1.5rem;
              background: #ffdddd;
              border-radius: 0;
              border-style: solid;
              border-width: 0.1rem;
              border-color: #dcd7ca;
              box-shadow: none;
              cursor: pointer;
          }
        </style>
        <script type="text/javascript">
        function userjs(){
        <?=stripslashes_deep($efdata_formsettings->customjs);?>
        }
        </script>
        <form method="post" action="" name="apiform" id="apiform">
            <input type="hidden" name="action" value="apiforms_submit">
            <input type="hidden" name="formid" value="<?php echo esc_attr( $a['id'] ); ?>">
            <?php echo $results[0]->formpreview; ?>
            <?php
            if ( isset($efdata_formsettings->appconnect ) &&  $efdata_formsettings->appconnect==1){
            ?>
            <div class="col-md-12">
              <input type="hidden" name="appconnect" value="1">
              <p><i><?=$efdata_formsettings->smsdisclaimer?></i></p>
            </div>
            <?php
            }
            if ( isset($efdata_formsettings->tandc ) &&  $efdata_formsettings->tandc==1) {

            ?>
            <div class="col-md-12">
              <input type="checkbox" name="termsaccepted" id="termsaccepted" value="1" required> <a href="<?=$efdata_formsettings->tandc_url?>" target="_blank"> <?=$efdata_formsettings->tandc_label?> </a>

            </div>
            <?php
            }
            ?>
            <div class="col-md-12">
                <button name="submit" type="button" id="apiformsubmit" class="btn btn-primary">
                    <?php _e( 'Submit', 'scuolasemplcecontacts' ); ?>
                    <span id="submitloader" class="hide">&nbsp;&nbsp;&nbsp;<i class="fa-li fa fa-spinner fa-spin" aria-hidden="true"></i></span>
                </button>
                <span id="fmsg"></span>
            </div>
        </form>
        <?php
        return ob_get_clean();
    }

    public function apiforms_delete_server_side_callback() {
        global $wpdb;

        $ID = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

        $tableName = $this->prefixTableName( 'apiforms' );

        $results = $wpdb->get_results(
            $wpdb->prepare( "DELETE  FROM " . $tableName . " WHERE id = %d", $ID )
        );

        wp_send_json(
            array(
                'error' => 0
            )
        );
    }

    public function apiforms_datatables_server_side_callback() {
        global $wpdb;

        $columns = array( 'formname', 'formname', 'createdddate' );

        $tableName = $this->prefixTableName( 'apiforms' );

        $search_line = "%" . sanitize_text_field( $_GET['search']['value'] ) . "%";

        $results = $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM " . $tableName . " WHERE formname like %s", $search_line )
        );

        $totalrecord = $wpdb->num_rows;

        $search_value = "%" . sanitize_text_field( $_GET['search']['value'] ) . "%";

        $column = absint( $_GET['order'][0]['column'] );

        $value_1 = $columns[ $column ];
        $value_2 = sanitize_key( $_GET['order'][0]['dir'] );
        $value_3 = absint( $_GET['start'] );
        $value_4 = absint( $_GET['length'] );

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM " . $tableName . " WHERE formname like %s order by %s %s LIMIT %d, %d",
                $search_value,
                $value_1,
                $value_2,
                $value_3,
                $value_4
            )
        );

        $data = array();
        foreach( $results as $result ) {
            $nestedData = array();
            $nestedData[] = $result->formname;
            $nestedData[] = '[ScuolasemplceForm id=' . $result->id . ']';
            $nestedData[] = date( 'd-m-Y', $result->createdddate );

            $html = '<a href="javascript:deletethis(' . $result->id . ')" class="btn btn-danger">X</a>';
            $html .= '&nbsp;<a href="javascript:viewthis(' . $result->id . ')" class="btn btn-primary">' . __( 'View', 'scuolasemplcecontacts' ) . '</a>';

            $editLink = admin_url( 'admin.php' );
            $editLink = add_query_arg(
                array(
                    'page' => 'scuolasemplcecontacts_buildform',
                    'id' => $result->id
                ),
                $editLink
            );

            $html .= '&nbsp;<a href="' . esc_url( $editLink ) . '" class="btn btn-info">' . __( 'Edit', 'scuolasemplcecontacts' ) . '</a>';

            $nestedData[] = $html;
            $data[] = $nestedData;
        }

        wp_send_json(
            array(
                "draw" => intval( $_GET['draw'] ),
                "recordsTotal" => intval( $totalrecord ),
                "recordsFiltered" => intval( $totalrecord ),
                "data" => $data
            )
        );
    }

    public function enquiry_delete_server_side_callback() {
        global $wpdb;

        $id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

        $tableName = $this->prefixTableName( 'enquiry' );

        $results = $wpdb->get_results(
            $wpdb->prepare( "DELETE  FROM " . $tableName . " WHERE id = %d", $id )
        );

        wp_send_json(
            array(
                'error' => 0
            )
        );
    }

    public function enquiry_datatables_server_side_callback() {
        global $wpdb;

        $columns = array( 'a.name', 'a.formid', 'a.createdddate', 'a.returnid' );
        $cond = ( isset( $_GET['filterp'] ) && empty( $_GET['filterp'] ) ) ? '' : ' AND formid = ' . absint( $_GET['filterp'] );

        $tableName = $this->prefixTableName( 'enquiry' );
        $tableName2 = $this->prefixTableName( 'apiforms' );

        $search_line = '%' . sanitize_text_field( $_GET['search']['value'] ) . '%';

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM " . $tableName . " WHERE name like %s $cond",
                $search_line
            )
        );

        $totalrecord = $wpdb->num_rows;
        $column = absint( $_GET['order'][0]['column'] );

        $val_1 = '%' . sanitize_text_field( $_GET['search']['value'] ) . '%';
        $val_3 = $columns[ $column ];
        $val_4 = sanitize_key( $_GET['order'][0]['dir'] );
        $val_5 = absint( $_GET['start'] );
        $val_6 = absint( $_GET['length'] );

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT a.*, b.formname FROM " . $tableName . " as a left join " . $tableName2 . " as b on a.formid=b.id WHERE a.name like %s " . $cond . " order by %s %s LIMIT %d, %d",
                $val_1,
                $val_3,
                $val_4,
                $val_5,
                $val_6
            )
        );

        $data = array();
        foreach( $results as $result ) {
            $nestedData = array();
            $nestedData[] = $result->name;
            $nestedData[] = $result->formname;
            $nestedData[] = date( 'd-m-Y', $result->createdddate );
            $nestedData[] = $result->returnid;

            $html = '<a href="javascript:deletethis(' . $result->id . ')" class="btn btn-danger">X</a>';
            $html .= '&nbsp;<a href="javascript:viewthis(' . $result->id . ')" class="btn btn-primary">' . __( 'View', 'scuolasemplcecontacts' ) . '</a>';

            $nestedData[] = $html;
            $data[] = $nestedData;
        }

        wp_send_json(
            array(
                "draw" => absint( $_GET['draw'] ),
                "recordsTotal" => absint( $totalrecord ),
                "recordsFiltered" => absint( $totalrecord ),
                "data" => $data
            )
        );
    }
}
