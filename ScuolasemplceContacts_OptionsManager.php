<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ScuolasemplceContacts_OptionsManager {

    public function getOptionNamePrefix() {
        return get_class( $this ) . '_';
    }

    /**
     * Define your options meta data here as an array, where each element in the array
     * @return array of key=>display-name and/or key=>array(display-name, choice1, choice2, ...)
     * key: an option name for the key (this name will be given a prefix when stored in
     * the database to ensure it does not conflict with other plugin options)
     * value: can be one of two things:
     *   (1) string display name for displaying the name of the option to the user on a web page
     *   (2) array where the first element is a display name (as above) and the rest of
     *       the elements are choices of values that the user can select
     * e.g.
     * array(
     *   'item' => 'Item:',             // key => display-name
     *   'rating' => array(             // key => array ( display-name, choice1, choice2, ...)
     *       'CanDoOperationX' => array('Can do Operation X', 'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber'),
     *       'Rating:', 'Excellent', 'Good', 'Fair', 'Poor')
	 *
	 * $this->getPluginFolder()
     */
    public function getOptionMetaData() {
        return array();
    }

    /**
     * @return array of string name of options
     */
    public function getOptionNames() {
        return array_keys( $this->getOptionMetaData() );
    }

    /**
     * Override this method to initialize options to default values and save to the database with add_option
     * @return void
     */
    protected function initOptions() {
    }

    /**
     * Cleanup: remove all options from the DB
     * @return void
     */
    protected function deleteSavedOptions() {
        $optionMetaData = $this->getOptionMetaData();

        if ( is_array( $optionMetaData ) ) {

            foreach ( $optionMetaData as $aOptionKey => $aOptionMeta ) {
                $prefixedOptionName = $this->prefix( $aOptionKey ); // how it is stored in DB
                delete_option( $prefixedOptionName );
            }

        }
    }

    /**
     * @return string display name of the plugin to show as a name/title in HTML.
     * Just returns the class name. Override this method to return something more readable
     */
    public function getPluginDisplayName() {
        return get_class( $this );
    }

    /**
     * Get the prefixed version input $name suitable for storing in WP options
     * Idempotent: if $optionName is already prefixed, it is not prefixed again, it is returned without change
     * @param  $name string option name to prefix. Defined in settings.php and set as keys of $this->optionMetaData
     * @return string
     */
    public function prefix( $name ) {
        $optionNamePrefix = $this->getOptionNamePrefix();

        if ( strpos( $name, $optionNamePrefix ) === 0 ) { // 0 but not false
            return $name; // already prefixed
        }

        return $optionNamePrefix . $name;
    }

    /**
     * Remove the prefix from the input $name.
     * Idempotent: If no prefix found, just returns what was input.
     * @param  $name string
     * @return string $optionName without the prefix.
     */
    public function &unPrefix( $name ) {
        $optionNamePrefix = $this->getOptionNamePrefix();

        if ( strpos( $name, $optionNamePrefix ) === 0 ) {
            return substr( $name, strlen( $optionNamePrefix ) );
        }

        return $name;
    }

    /**
     * A wrapper function delegating to WP get_option() but it prefixes the input $optionName
     * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
     * @param $optionName string defined in settings.php and set as keys of $this->optionMetaData
     * @param $default string default value to return if the option is not set
     * @return string the value from delegated call to get_option(), or optional default value
     * if option is not set.
     */
    public function getOption( $optionName, $default = null ) {
        $prefixedOptionName = $this->prefix( $optionName ); // how it is stored in DB
        $retVal = get_option( $prefixedOptionName );

        if ( ! $retVal && $default ) {
            $retVal = $default;
        }

        return $retVal;
    }

    /**
     * A wrapper function delegating to WP delete_option() but it prefixes the input $optionName
     * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
     * @param  $optionName string defined in settings.php and set as keys of $this->optionMetaData
     * @return bool from delegated call to delete_option()
     */
    public function deleteOption( $optionName ) {
        $prefixedOptionName = $this->prefix( $optionName ); // how it is stored in DB

        return delete_option( $prefixedOptionName );
    }

    /**
     * A wrapper function delegating to WP add_option() but it prefixes the input $optionName
     * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
     * @param  $optionName string defined in settings.php and set as keys of $this->optionMetaData
     * @param  $value mixed the new value
     * @return null from delegated call to delete_option()
     */
    public function addOption( $optionName, $value ) {
        $prefixedOptionName = $this->prefix( $optionName ); // how it is stored in DB

        return add_option( $prefixedOptionName, $value );
    }

    /**
     * A wrapper function delegating to WP add_option() but it prefixes the input $optionName
     * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
     * @param  $optionName string defined in settings.php and set as keys of $this->optionMetaData
     * @param  $value mixed the new value
     * @return null from delegated call to delete_option()
     */
    public function updateOption( $optionName, $value ) {
        $prefixedOptionName = $this->prefix( $optionName ); // how it is stored in DB

        return update_option( $prefixedOptionName,  $value );
    }

    /**
     * A Role Option is an option defined in getOptionMetaData() as a choice of WP standard roles, e.g.
     * 'CanDoOperationX' => array('Can do Operation X', 'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber')
     * The idea is use an option to indicate what role level a user must minimally have in order to do some operation.
     * So if a Role Option 'CanDoOperationX' is set to 'Editor' then users which role 'Editor' or above should be
     * able to do Operation X.
     * Also see: canUserDoRoleOption()
     * @param  $optionName
     * @return string role name
     */
    public function getRoleOption( $optionName ) {
        $roleAllowed = $this->getOption( $optionName );

        if ( ! $roleAllowed || $roleAllowed == '' ) {
            $roleAllowed = 'Administrator';
        }

        return $roleAllowed;
    }

    /**
     * Given a WP role name, return a WP capability which only that role and roles above it have
     * http://codex.wordpress.org/Roles_and_Capabilities
     * @param  $roleName
     * @return string a WP capability or '' if unknown input role
     */
    protected function roleToCapability( $roleName ) {
        switch ( $roleName ) {
            case 'Super Admin':
                return 'manage_options';
            case 'Administrator':
                return 'manage_options';
            case 'Editor':
                return 'publish_pages';
            case 'Author':
                return 'publish_posts';
            case 'Contributor':
                return 'edit_posts';
            case 'Subscriber':
                return 'read';
            case 'Anyone':
                return 'read';
        }

        return '';
    }

    /**
     * @param $roleName string a standard WP role name like 'Administrator'
     * @return bool
     */
    public function isUserRoleEqualOrBetterThan( $roleName ) {
        if ( 'Anyone' == $roleName ) {
            return true;
        }

        $capability = $this->roleToCapability( $roleName );

        return current_user_can( $capability );
    }

    /**
     * @param  $optionName string name of a Role option (see comments in getRoleOption())
     * @return bool indicates if the user has adequate permissions
     */
    public function canUserDoRoleOption( $optionName ) {
        $roleAllowed = $this->getRoleOption( $optionName );

        if ( 'Anyone' == $roleAllowed ) {
            return true;
        }

        return $this->isUserRoleEqualOrBetterThan( $roleAllowed );
    }

    public function includeAdminStyles() {
        if ( ! isset( $_GET['page'] ) ) {
            return;
        }

        if ( $_GET['page'] == $this->getMenuSlug( 'Settings' ) ) {
            ?>
            <style type="text/css">
                table.plugin-options-table {width: 100%; padding: 0;}
                table.plugin-options-table tr:nth-child(even) {background: #f9f9f9}
                table.plugin-options-table tr:nth-child(odd) {background: #FFF}
                table.plugin-options-table tr:first-child {width: 35%;}
                table.plugin-options-table td {vertical-align: middle;}
                table.plugin-options-table td+td {width: auto}
                table.plugin-options-table td > p {margin-top: 0; margin-bottom: 0;}
            </style>
            <?php
        } elseif ( $_GET['page'] == $this->getMenuSlug( 'buildform' ) ) {
            ?>
            <style type="text/css">
                button.ui-datepicker-trigger{ position: absolute; top: 30px; right: 18px;}
            </style>
            <?php
        } elseif ( $_GET['page'] == $this->getMenuSlug( 'apiFieldsPage' ) ) {
            ?>
            <style type="text/css">
                table.fieldstable {width: 100%; padding: 0;}
                table.fieldstable th{text-align: left;}
                table.fieldstable td {vertical-align: top;}
                table.fieldstable td+td {width: auto}
                table.fieldstable td > p {margin-top: 0; margin-bottom: 0;}
                .fieldstable input:not([type=checkbox]), .fieldstable select {width:250px}
                .ui-datepicker-trigger img{width:15px;}
            </style>
            <script>
                jQuery(document).ready(function() {
                    jQuery('.datepicker').datepicker({
                        showOn: 'both',
                        buttonImage: "<?php echo esc_url( plugins_url() . '/' . 'scuolasemplice-contacts' . '/' ); ?>images/calender.png",
                        buttonText : '<i class="dashicons-calendar-alt"></i>',
                    });
                });
            </script>
            <?php
        } elseif ( $_GET['page'] == $this->getMenuSlug( 'admin' ) ) {
            ?>
            <style>
                .ui-dialog{z-index:1000000 !important}
                .ui-datepicker-trigger img {width: 15px;}
                .form-group{position: relative;}
                button.ui-datepicker-trigger {position: absolute;top: 30px;right: 18px;}
            </style>
            <?php
        }
    }

    public function includeAdminAssets() {
        if ( ! isset( $_GET['page'] ) ) {
            return;
        }

        if ( $_GET['page'] == $this->getMenuSlug( 'buildform' ) ) {
            wp_register_style( 'jquery-ui', plugins_url() . '/' . 'scuolasemplice-contacts' . '/css/jquery-ui.css' );
            wp_register_style( 'bootstrap-css', plugins_url() . '/' . 'scuolasemplice-contacts' . '/css/bootstrap.min.css' );
            wp_register_style( 'font-awesome-css', plugins_url() . '/' . 'scuolasemplice-contacts' . '/inc/fa/css/font-awesome.min.css' );
            wp_enqueue_style( 'jquery-ui' );
            wp_enqueue_style( 'bootstrap-css' );
            wp_enqueue_style( 'font-awesome-css' );
            wp_enqueue_style( 'alister-custom', plugins_url() . '/' . 'scuolasemplice-contacts' . '/css/form_builder.css' );

            wp_enqueue_script( 'popper', plugins_url() . '/' . 'scuolasemplice-contacts' . '/js/popper.min.js', array(), '20151215', true );
            wp_enqueue_script( 'jquery-ui-draggable' );
            wp_enqueue_script( 'jquery-ui-droppable' );
            wp_enqueue_script( 'jquery-ui-sortable' );
            wp_enqueue_script( 'jquery-ui-datepicker' );

            wp_enqueue_script( 'form-builder', plugins_url() . '/' . 'scuolasemplice-contacts' . '/js/form_builder.js', array(), '20151215', true );
            wp_localize_script( 'form-builder', 'plugin_url', plugins_url() . '/' . 'scuolasemplice-contacts' . '/' );
            wp_localize_script(
                'form-builder',
                'form_builder_vars',
                array(
                    'enter_form' => __( 'Please Enter Form name', 'scuolasemplcecontacts' ),
                    'star_mark' => __( 'Please Include All required fields marked with *', 'scuolasemplcecontacts' ),
                    'thanks_msg_error' => __( 'Please Enter Thanks Message', 'scuolasemplcecontacts' ),
                    'thanks_url_error' => __( 'Please Enter Thanks Url', 'scuolasemplcecontacts' ),
                    'tandc_label_error' => __( 'Please Enter Terms & Conditions Label', 'scuolasemplcecontacts' ),
                    'tandc_url_error' => __( 'Please Enter Terms & Conditions url', 'scuolasemplcecontacts' ),
                    'label' => __( 'Label', 'scuolasemplcecontacts' ),
                    'layout' => __( 'Layout', 'scuolasemplcecontacts' ),
                    'remove' => __( 'Remove', 'scuolasemplcecontacts' ),
                    'showoption' => __( 'Show Options', 'scuolasemplcecontacts' ),
                    'cpassword' => __( 'Confirm Password', 'scuolasemplcecontacts' ),
                    'password' => __( 'Password', 'scuolasemplcecontacts' ),
                    'required' => __( 'Required', 'scuolasemplcecontacts' )
                )
            );
        } elseif ( $_GET['page'] == $this->getMenuSlug( 'apiFieldsPage' ) ) {
            wp_enqueue_script( 'jquery-ui-datepicker' );
            wp_register_style( 'jquery-ui', plugins_url() . '/' . 'scuolasemplice-contacts' . '/css/jquery-ui.css' );
            wp_enqueue_style( 'jquery-ui' );
        } elseif ( $_GET['page'] == $this->getMenuSlug( 'admin' ) ) {
            wp_register_script(
                'datatables',
                //'https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js'
                plugins_url() . '/' . 'scuolasemplice-contacts' . '/inc/dt/DataTables-1.10.20/js/jquery.dataTables.min.js',
                array( 'jquery' ),
                true
            );

            wp_enqueue_script( 'datatables' );

            wp_register_script(
                'datatables_bootstrap',
                plugins_url() . '/' . 'scuolasemplice-contacts' . '/inc/dt/DataTables-1.10.20/js/dataTables.bootstrap.min.js',
                //'https://cdn.datatables.net/1.10.13/js/dataTables.bootstrap.min.js'
                array( 'jquery' ),
                true
            );

            wp_enqueue_script( 'datatables_bootstrap' );
            wp_register_style( 'bootstrap_style', plugins_url() . '/' . 'scuolasemplice-contacts' . '/css/bootstrap.min.css' );
            wp_enqueue_style( 'bootstrap_style' );

            wp_register_style(
                'datatables_style',
                plugins_url() . '/' . 'scuolasemplice-contacts' . '/inc/dt/DataTables-1.10.20/css/dataTables.bootstrap.min.css'
                //'https://cdn.datatables.net/1.10.13/css/dataTables.bootstrap.min.css'
            );

            wp_enqueue_style( 'datatables_style' );
            wp_enqueue_script( 'form_datatables', plugins_url() . '/' . 'scuolasemplice-contacts' . '/js/dtable.js', array(), '1.0', true );
            wp_localize_script( 'form_datatables', 'ajax_url', admin_url( 'admin-ajax.php' ) );
            wp_localize_script( 'form_datatables', 'plugin_url', plugins_url() . '/' . 'scuolasemplice-contacts' . '/' );
            wp_localize_script( 'form_datatables', 'deletewarn', __( 'Do you want to delete?', 'scuolasemplcecontacts' ) );
            wp_register_style( 'jquery-ui', plugins_url() . '/' . 'scuolasemplice-contacts' . '/css/jquery-ui.css' );
            wp_enqueue_style( 'jquery-ui' );
            wp_enqueue_script( 'jquery-ui-dialog' );
            wp_enqueue_script( 'jquery-ui-datepicker' );
        } elseif ( $_GET['page'] == $this->getMenuSlug( 'Enquiry' ) ) {
            wp_register_script(
                'datatables',
	            plugins_url() . '/' . 'scuolasemplice-contacts' . '/inc/dt/DataTables-1.10.20/js/jquery.dataTables.min.js',
                //'https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js'
                array( 'jquery' ),
                true
            );

            wp_enqueue_script( 'datatables' );

            wp_register_script(
                'datatables_bootstrap',
                plugins_url() . '/' . 'scuolasemplice-contacts' . '/inc/dt/DataTables-1.10.20/js/dataTables.bootstrap.min.js',
                //'https://cdn.datatables.net/1.10.13/js/dataTables.bootstrap.min.js'
                array( 'jquery' ),
                true
            );

            wp_enqueue_script( 'datatables_bootstrap' );

            wp_register_style( 'bootstrap_style', plugins_url() . '/' . 'scuolasemplice-contacts' . '/css/bootstrap.min.css' );
            wp_enqueue_style( 'bootstrap_style' );
            wp_register_style(
                'datatables_style',
                plugins_url() . '/' . 'scuolasemplice-contacts' . '/inc/dt/DataTables-1.10.20/css/dataTables.bootstrap.min.css'
                //'https://cdn.datatables.net/1.10.13/css/dataTables.bootstrap.min.css'
            );

            wp_enqueue_style( 'datatables_style' );
            wp_enqueue_script( 'form_datatables', plugins_url() . '/' . 'scuolasemplice-contacts' . '/js/enquirydtable.js', array(), '1.0', true );

            wp_localize_script( 'form_datatables', 'ajax_url', admin_url( 'admin-ajax.php' ) );
            wp_localize_script( 'form_datatables', 'deletewarn', __( 'Do you want to delete?', 'scuolasemplcecontacts' ) );
            wp_register_style( 'jquery-ui', plugins_url() . '/' . 'scuolasemplice-contacts' . '/css/jquery-ui.css' );
            wp_enqueue_style( 'jquery-ui' );
            wp_enqueue_script( 'jquery-ui-dialog' );
        }
    }

    public function saveNewForm() {
        global $wpdb;

        if ( current_user_can( 'manage_options' ) && isset( $_POST['savepform'] ) ) {
            $tableName = $this->prefixTableName( 'apiforms' );

            $ipAddress = isset( $_POST['allowedip'] ) ? $_POST['allowedip'] : '';

            if ( $ipAddress ) {
                $ips = explode( ',', $ipAddress );
                $ips = array_map( 'trim', $ips );
                $ips = array_filter( $ips, function( $ip ) {
                    return filter_var( $ip, FILTER_VALIDATE_IP );
                } );

                $ipAddress = implode( ',', $ips );
            }

            $formSettings = array(
                'contacttp' => isset( $_POST['contacttp'] ) ? absint( $_POST['contacttp'] ) : 0,
                'afterpst' => isset( $_POST['afterpst'] ) ? absint( $_POST['afterpst'] ) : 0,
                'thanksurl' => isset( $_POST['thanksurl'] ) ? esc_url_raw( $_POST['thanksurl'] ) : '',
                'thanksmsg' => isset( $_POST['thanksmsg'] ) ? sanitize_textarea_field( $_POST['thanksmsg'] ) : '',
                'customjs' => isset( $_POST['customjs'] ) ? sanitize_textarea_field( $_POST['customjs'] ) : '',
                'allowedip' => $ipAddress ? $ipAddress : '',
                'tandc' => isset( $_POST['tandc'] ) ? absint( $_POST['tandc'] ) : 0,
                'tandc_url' => isset( $_POST['tandc_url'] ) ? esc_url_raw( $_POST['tandc_url'] ) : '',
                'tandc_label' => isset( $_POST['tandc_label'] ) ? sanitize_text_field( $_POST['tandc_label'] ) : '',
                'creatapiuser' => isset( $_POST['creatapiuser'] ) ? absint( $_POST['creatapiuser'] ) : 0,
                'creatwpuser' => isset( $_POST['creatwpuser'] ) ? absint( $_POST['creatwpuser'] ) : 0,
                'appconnect' => isset( $_POST['appconnect'] ) ? absint( $_POST['appconnect'] ) : 0,
                'smsdisclaimer' => isset( $_POST['smsdisclaimer'] ) ? sanitize_textarea_field( $_POST['smsdisclaimer'] ) : 0,
            );

            $apiForm = array(
                'formsettings' => json_encode( $formSettings ),
                'formname' => isset( $_POST['formname'] ) ? sanitize_text_field( $_POST['formname'] ) : '',
                'formpreview' => isset( $_POST['formpreview'] ) ? $this->ksesFormContent( $_POST['formpreview'] ) : '',
                'formelement' => isset( $_POST['formelement'] ) ? $this->ksesFormContent( $_POST['formelement'] ) : ''
            );

            if ( isset( $_POST['id'] ) && ! empty( $_POST['id'] ) ) {
                $wpdb->update(
                    $tableName,
                    array(
                        'formname' => $apiForm['formname'],
                        'formpreview' => $apiForm['formpreview'],
                        'formelement' => $apiForm['formelement'],
                        'formsettings' => $apiForm['formsettings'],
                        'createdddate' => time()
                    ),
                    array(
                        'id' => absint( $_POST['id'] )
                    ),
                    array( '%s','%s','%s','%s','%d' )
                );
            } else {
                $wpdb->insert(
                    $tableName,
                    array(
                        'formname' => $apiForm['formname'],
                        'formpreview' => $apiForm['formpreview'],
                        'formelement' => $apiForm['formelement'],
                        'formsettings' => $apiForm['formsettings'],
                        'createdddate' => time()
                    ),
                    array( '%s','%s','%s','%s','%d' )
                );
            }

            $url = admin_url( 'admin.php' );
            $url = add_query_arg( 'page', 'scuolasemplcecontacts_admin', $url );

            wp_redirect( $url );
            exit;
        }
    }

    /**
     * Creates HTML for the Administration page to set options for this plugin.
     * Override this method to create a customized page.
     * @return void
     */
    public function settingsPage( $slug ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'scuolasemplcecontacts' ) );
        }

        $optionMetaData = $this->getOptionMetaData();

        // Save Posted Options
        if ( $optionMetaData != null ) {
            foreach ( $optionMetaData as $aOptionKey => $aOptionMeta ) {
                if ( isset( $_POST[$aOptionKey] ) ) {
                    $optionValue = sanitize_text_field( $_POST[$aOptionKey] );

                    $this->updateOption( $aOptionKey, $optionValue );
                }
            }
        }

        // HTML for the page
        $settingsGroup = get_class( $this ) . '-settings-group';
        ?>
        <div class="wrap">
            <h2><?php echo $this->getPluginDisplayName(); ?></h2>
            <h2><?php _e( 'Settings', 'scuolasemplcecontacts' ); ?></h2>

            <form method="post" action="">
                <?php settings_fields( $settingsGroup ); ?>

                <table class="plugin-options-table">
                    <tbody>
                    <?php
                        if ( $optionMetaData != null ):
                            foreach ( $optionMetaData as $aOptionKey => $aOptionMeta ):
                                $displayText = is_array( $aOptionMeta ) ? $aOptionMeta[0] : $aOptionMeta;
                    ?>
                                <tr valign="top">
                                    <th scope="row">
                                        <p>
                                            <label for="<?php echo esc_attr( $aOptionKey ); ?>"><?php echo $displayText ?></label>
                                        </p>
                                    </th>
                                    <td>
                                        <?php $this->createFormControl( $aOptionKey, $aOptionMeta, $this->getOption( $aOptionKey ) ); ?>
                                    </td>
                                </tr>
                    <?php
                            endforeach;
                        endif;
                    ?>
                    </tbody>
                </table>

                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'scuolasemplcecontacts' ); ?>"/>
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Helper-function outputs the correct form element (input tag, select tag) for the given item
     * @param  $aOptionKey string name of the option (un-prefixed)
     * @param  $aOptionMeta mixed meta-data for $aOptionKey (either a string display-name or an array(display-name, option1, option2, ...)
     * @param  $savedOptionValue string current value for $aOptionKey
     * @return void
     */
    protected function createFormControl( $aOptionKey, $aOptionMeta, $savedOptionValue ) {
        if ( is_array( $aOptionMeta ) && count( $aOptionMeta ) >= 2 ) { // Drop-down list
            $choices = array_slice( $aOptionMeta, 1 );
        ?>
            <p>
                <select name="<?php echo esc_attr( $aOptionKey ); ?>" id="<?php echo esc_attr( $aOptionKey ); ?>">
            <?php
                foreach ( $choices as $aChoice ) {
                    $selected = ( $aChoice == $savedOptionValue ) ? 'selected' : '';
            ?>
                    <option value="<?php echo $aChoice ?>" <?php echo $selected ?>><?php echo $this->getOptionValueI18nString($aChoice) ?></option>
            <?php
                }
            ?>
                </select>
            </p>
        <?php
        } else { // Simple input field
            $inputType = ( $aOptionKey == 'ApiPassword' ) ? 'password' : 'text';
        ?>
            <p>
                <input type="<?php echo $inputType; ?>" name="<?php echo esc_attr( $aOptionKey ); ?>" id="<?php echo esc_attr( $aOptionKey ); ?>" value="<?php echo esc_attr( $savedOptionValue ); ?>" size="50" />
            </p>
        <?php
        }
    }

    /**
     * Override this method and follow its format.
     * The purpose of this method is to provide i18n display strings for the values of options.
     * For example, you may create a options with values 'true' or 'false'.
     * In the options page, this will show as a drop down list with these choices.
     * But when the the language is not English, you would like to display different strings
     * for 'true' and 'false' while still keeping the value of that option that is actually saved in
     * the DB as 'true' or 'false'.
     * To do this, follow the convention of defining option values in getOptionMetaData() as canonical names
     * (what you want them to literally be, like 'true') and then add each one to the switch statement in this
     * function, returning the "__()" i18n name of that string.
     * @param  $optionValue string
     * @return string __($optionValue) if it is listed in this method, otherwise just returns $optionValue
     */
    protected function getOptionValueI18nString($optionValue) {
        switch ($optionValue) {
            case 'true':
                return __( 'true', 'scuolasemplcecontacts' );
            case 'false':
                return __( 'false', 'scuolasemplcecontacts' );
            case 'Administrator':
                return __( 'Administrator', 'scuolasemplcecontacts' );
            case 'Editor':
                return __( 'Editor', 'scuolasemplcecontacts' );
            case 'Author':
                return __( 'Author', 'scuolasemplcecontacts' );
            case 'Contributor':
                return __( 'Contributor', 'scuolasemplcecontacts' );
            case 'Subscriber':
                return __( 'Subscriber', 'scuolasemplcecontacts' );
            case 'Anyone':
                return __( 'Anyone', 'scuolasemplcecontacts' );
        }

        return $optionValue;
    }

    /**
     * Query MySQL DB for its version
     * @return string|false
     */
    protected function getMySqlVersion() {
        global $wpdb;

        $rows = $wpdb->get_results( 'select version() as mysqlversion' );

        if ( ! empty( $rows ) ) {
            return $rows[0]->mysqlversion;
        }

        return false;
    }

    /**
     * If you want to generate an email address like "no-reply@your-site.com" then
     * you can use this to get the domain name part.
     * E.g.  'no-reply@' . $this->getEmailDomain();
     * This code was stolen from the wp_mail function, where it generates a default
     * from "wordpress@your-site.com"
     * @return string domain name
     */
    public function getEmailDomain() {
        // Get the site domain and get rid of www.
        $sitename = strtolower( $_SERVER['SERVER_NAME'] );

        if ( substr( $sitename, 0, 4 ) == 'www.' ) {
            $sitename = substr( $sitename, 4 );
        }

        return $sitename;
    }

    public function getUserIpAddr(){
        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            // ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            // ip pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }

    /**
     * Creates HTML for the Administration page to set options for this plugin.
     * Override this method to create a customized page.
     * @return void
     */
    public function buildForm() {
        global $wpdb;

        $tableName = $this->prefixTableName( 'apiformfields' );
        $fdata = $wpdb->get_row( "SELECT * FROM " . $tableName . " WHERE 1", ARRAY_A );
        
        $customfields = json_decode( $fdata['customfields'] );
        $standardfields = json_decode( $fdata['standardfields'] );
        
        $ID = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : '';

        if ( $ID ) {
            $tableName = $this->prefixTableName( 'apiforms' );

            $efdata = $wpdb->get_row(
                $wpdb->prepare( "SELECT * FROM " . $tableName . " WHERE id = %d ", $ID ),
                ARRAY_A
            );

            $efdata_formsettings = json_decode( $efdata['formsettings'] );
        } else {
            $efdata_formsettings = new stdClass();
            $efdata_formsettings->contacttp = 0;
            $efdata_formsettings->afterpst = 0;
            $efdata_formsettings->thanksurl = '';
            $efdata_formsettings->thanksmsg = '';
            $efdata_formsettings->allowedip = '';
            $efdata_formsettings->tandc = 0;
            $efdata_formsettings->tandc_label = '';
            $efdata_formsettings->tandc_url = '';
            $efdata_formsettings->creatwpuser = 0;
            $efdata_formsettings->creatapiuser = 0;
            $efdata_formsettings->appconnect = 0;
            $efdata_formsettings->smsdisclaimer = '';
            $efdata_formsettings->customjs = '';
        }

        ?>
        <div class="container">
            <h3 class="text-center"><?php echo $this->getPluginDisplayName(); ?></h3>
            <h4  class="text-center"><?php _e( 'Form Builder', 'scuolasemplcecontacts' ); ?></h4>

            <form method="post" action="" name="saveform" id="saveform" class="plugin">
                <input type="hidden" name="id" id="id" value="<?php echo esc_attr( $ID ); ?>">
                <input type="hidden" name="savepform" id="savepform" value="1">
                <input type="hidden" name="formname" id="formname" value="">
                <input type="hidden" name="formpreview" id="formpreview" value="">
                <input type="hidden" name="formelement" id="formelement" value="">

                <div class="row">
                    <div class="col-md-6">
                        <h4><?php _e( 'Form Name', 'scuolasemplcecontacts' ); ?> :
                            <input type="text" name="formnamef" id="formnamef" value="<?php echo isset( $efdata['formname'] ) ? esc_attr( $efdata['formname'] ) : ''; ?>" class="form-controll">
                            <a href="javascript:void(0)" class="btn btn-primary export_html <?php $ID ? '' : 'hide'; ?>">
                                <?php $ID ? _e( 'Update Form', 'scuolasemplcecontacts' ) : _e( 'Save Form', 'scuolasemplcecontacts' ); ?>
                            </a>
                        </h4>
                    </div>

                    <div class="col-md-6">
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=scuolasemplcecontacts_admin' ) ); ?>" class="btn btn-primary pull-right">
                            <?php _e( 'Back To Form List', 'scuolasemplcecontacts' ); ?>
                        </a>
                    </div>

                    <div class="col-md-12">
                        <h4><?php _e( 'Settings', 'scuolasemplcecontacts' ); ?>:</h4>
                    </div>

                    <div class="col-md-12">
                        <h5>
                            <?php _e( 'Kind of contact', 'scuolasemplcecontacts' ); ?>:  &nbsp;<?php _e( 'Students', 'scuolasemplcecontacts' ); ?>
                            &nbsp;<input type="radio" name="contacttp" value="1" <?php checked( $efdata_formsettings->contacttp, 1 ); ?>>
                            &nbsp;&nbsp;&nbsp;<?php _e( 'Leads', 'scuolasemplcecontacts' ); ?>
                            &nbsp;<input type="radio" name="contacttp" value="2" <?php checked( ( empty( $efdata_formsettings->contacttp ) || $efdata_formsettings->contacttp == 2 ) ); ?>>
                        </h5>
                    </div>

                    <div class="col-md-12">
                        <h5>
                            <?php _e( 'After Post', 'scuolasemplcecontacts' ); ?>:  &nbsp;<?php  _e('Redirect to a thank you page', 'scuolasemplcecontacts'); ?>
                            &nbsp;<input type="radio" name="afterpst" value="1" <?php checked( $efdata_formsettings->afterpst, 1 ); ?>>
                            &nbsp;&nbsp;&nbsp;<?php _e( 'Give a success message', 'scuolasemplcecontacts' ); ?>
                            &nbsp;<input type="radio" name="afterpst" value="2" <?php checked( ( empty( $efdata_formsettings->afterpst ) || $efdata_formsettings->afterpst == 2 ) ); ?>>
                            <br>
                            <br>
                            <span class="spthn <?php echo ( $efdata_formsettings->afterpst == 1 ) ? '' : 'hide'; ?>" id="spthn1">
                                <?php _e( 'Thanks Url', 'scuolasemplcecontacts' ); ?> <input type="text" name="thanksurl" id="thankurl" value="<?php echo $efdata_formsettings->thanksurl; ?>">
                            </span>
                            <span class="spthn <?php echo ( empty( $efdata_formsettings->afterpst ) || $efdata_formsettings->afterpst == 2 ) ? '' : 'hide'; ?>"  id="spthn2">
                                <?php _e( 'Thanks Message', 'scuolasemplcecontacts' ); ?>
                                <br>
                                <textarea name="thanksmsg" id="thanksmsg" style="width:100%"><?php echo isset( $efdata_formsettings ) ? $efdata_formsettings->thanksmsg : ''; ?></textarea>
                            </span>
                        </h5>
                    </div>

                    <div class="col-md-12">
                        <h5>
                            <?php _e( 'Allowed IP (comma Seperated, leave empty if don\'t want to apply IP restriction.)', 'scuolasemplcecontacts' ); ?>:   <input type="text" name="allowedip" style="width:100%" value="<?php echo isset( $efdata_formsettings ) ? $efdata_formsettings->allowedip : ''; ?>">
                        </h5>
                    </div>

                    <div class="col-md-12">
                        <h5>
                            <?php _e( 'Term & Conditions', 'scuolasemplcecontacts' ); ?>:  &nbsp;<?php  _e('Show CheckBox', 'scuolasemplcecontacts'); ?>
                            &nbsp;<input type="radio" name="tandc" value="1" <?php checked( ( empty( $efdata_formsettings->tandc ) || $efdata_formsettings->tandc == 1 ) ); ?> >
                            &nbsp;&nbsp;&nbsp;<?php _e( 'Don\'t Show CheckBox', 'scuolasemplcecontacts' ); ?>
                            &nbsp;<input type="radio" name="tandc" value="2" <?php checked( $efdata_formsettings->tandc, 2 ); ?>>
                            <br>
                            <br>
                            <span class="tandcsettings <?php echo ( empty( $efdata_formsettings->tandc) || $efdata_formsettings->tandc == 1  ) ? '' : 'hide'; ?>" id="tandcsettings1">
                                <?php _e( 'Term & Conditions label', 'scuolasemplcecontacts' ); ?> <input type="text" name="tandc_label" id="tandc_label" style="width:100%" value="<?php echo $efdata_formsettings->tandc_label; ?>">
                                <br><br>
                                <?php _e( 'Term & Conditions url (with http://)', 'scuolasemplcecontacts' ); ?> <input type="text" name="tandc_url" placeholder="http://" id="tandc_url" style="width:100%" value="<?php echo $efdata_formsettings->tandc_url; ?>">
                            </span>
                        </h5>
                    </div>
                    <div class="col-md-12">
                        <h5>
                            <input type="checkbox" name="creatwpuser" value="1" <?php checked( $efdata_formsettings->creatwpuser, 1 ); ?>> &nbsp; <?php _e( 'Create a wordpress user when sumbit information', 'scuolasemplcecontacts' ) ?>
                        </h5>
                    </div>
                    <div class="col-md-12 <?php echo ( $efdata_formsettings->contacttp == 1 ) ? '' : 'hide'; ?> creatapiuser" id="creatapiuser1">
                        <h5>
                            <input type="checkbox" name="creatapiuser" value="1" <?php checked( $efdata_formsettings->creatapiuser, 1 ); ?>> &nbsp; <?php _e( 'Create a ScuolaSemplice user when sumbit information', 'scuolasemplcecontacts' ) ?>
                        </h5>
                    </div>
                    <div class="col-md-12 <?php echo ( $efdata_formsettings->contacttp == 1 ) ? '' : 'hide'; ?> appconnect" id="appconnect1">
                        <h5>
                            <input type="checkbox" name="appconnect" value="1" <?php checked( $efdata_formsettings->appconnect, 1 ); ?>> &nbsp; <?php _e( 'Send SMS to the user to invite connecting to the App', 'scuolasemplcecontacts' ) ?>
                        </h5>
                    </div>
                    <div class="col-md-12 <?php echo ( $efdata_formsettings->appconnect == 1 ) ? '' : 'hide'; ?>" id="smsdisclaimer1">
                      <h5>
                          <?php _e( 'App SMS connection disclaimer', 'scuolasemplcecontacts' ); ?>:   <input type="text" name="smsdisclaimer" style="width:100%" value="<?php echo isset( $efdata_formsettings ) && !empty($efdata_formsettings->smsdisclaimer) ? $efdata_formsettings->smsdisclaimer : _e( 'By entering your mobile number, you will immediately receive an SMS inviting you to install the ScuolaSemplice App. Stay in sync at your school!', 'scuolasemplcecontacts' ); ?>">
                      </h5>
                    </div>
                    <div class="col-md-12">
                        <h5>
                          <?php _e( 'Custom js code', 'scuolasemplcecontacts' ); ?>
                          <br>
                          <textarea name="customjs" id="customjs" style="width:100%;height:100px;"><?php echo isset( $efdata_formsettings ) ? stripslashes_deep($efdata_formsettings->customjs) : ''; ?></textarea>
                          <span style="font-size:11px"><?php _e( 'should return true or false', 'scuolasemplcecontacts' ); ?></span>
                        </h5>
                    </div>

                </div>
            </form>

            <div class="form_builder" style="margin-top: 25px">
                <div class="row">
                    <div class="col-sm-3">
                        <h4><?php _e( 'Available Field', 'scuolasemplcecontacts' ); ?></h4>
                        <p><?php _e( 'Drag the fields to the included tables or click on + to add to the form', 'scuolasemplcecontacts' ); ?></p>

                        <nav class="nav-sidebar">
                            <ul class="nav">
                            <?php
                                $numbre = 0;
                                $requiredsFields=array();
                                foreach ( $standardfields->standardfields as $fvl ):
                                    if ( property_exists( $fvl, 'required' ) && $fvl->required == 1 ) {
                                        $numbre++;
                                        $requiredsFields[]=$fvl->key;
                                    }
                            ?>
                                <li
                                    class="form_bal_formfield"
                                    id="li_<?php echo $fvl->key; ?>"
                                    data-name="<?php echo $fvl->name; ?>"
                                    data-key="<?php echo $fvl->key; ?>"
                                    data-type="<?php echo $fvl->type; ?>"
                                    data-required="<?php echo property_exists( $fvl, 'required' ) ? $fvl->required : ''; ?>"
                                    data-standard="1"
                                    data-soption='<?php echo property_exists( $fvl, 'selectvalues' ) ? str_replace( "'", "", json_encode( $fvl->selectvalues ) ) : ''; ?>'>
                                    <a href="javascript:;">
                                        <span><?php echo $fvl->name; ?><?php echo ( property_exists( $fvl, 'required' ) && $fvl->required == 1 ) ? '*' : ""; ?></span>
                                        &nbsp;<i class="fa fa-plus-circle pull-right addtoinclude"></i>
                                    </a>
                                </li>
                            <?php
                                endforeach;
		
                                foreach ( $customfields->customfields as $fvl ):
                                    if ( ! in_array( $fvl->type , array( 'text', 'email', 'number', 'password', 'date', 'select', 'multiselect', 'checkbox','textarea' ) ) ) {
                                        continue;
                                    }

                                    if ( property_exists( $fvl, 'required' ) && $fvl->required == 1 ) {
                                        $numbre++;
                                    }
									
								 
                                ?>
                                    <li
                                        class="form_bal_formfield"
                                        id="li_field_<?php echo $fvl->id; ?>"
                                        data-name="<?php echo $fvl->name; ?>"
                                        data-key="field_<?php echo $fvl->id; ?>"
                                        data-type="<?php echo $fvl->type; ?>"
                                        data-required="<?php if (property_exists( $fvl, 'required' )) echo $fvl->required; else echo '0';?>"
                                        data-standard="0"
                                        data-soption='<?php if (property_exists( $fvl, 'selectvalues' )   ) echo str_replace( "'", "", json_encode( $fvl->selectvalues ) ); else echo '';?>'>
                                        <a href="javascript:;">
                                            <span><?php echo $fvl->name; ?><?php echo ( property_exists( $fvl, 'required' ) && $fvl->required == 1 ) ? '*' : ''; ?></span>
                                            &nbsp;<i class="fa fa-plus-circle pull-right addtoinclude"></i>
                                        </a>
                                    </li>
                                <?php
                                endforeach;
                                ?>
                            </ul>
                        </nav>
                    </div>

                    <div class="col-md-4 bal_builder">
                        <h4><?php _e( 'Included Field', 'scuolasemplcecontacts' ); ?></h4>

                        <p>
                            <?php _e( 'Drag the fields up or down to arrange.', 'scuolasemplcecontacts' ); ?>
                            <br>&nbsp;
                        </p>

                        <div class="form_builder_area">
                            <?php echo isset( $efdata['formelement'] ) ? $efdata['formelement'] : ''; ?>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <h4><?php _e( 'Preview', 'scuolasemplcecontacts' ); ?></h4>
                        <p>&nbsp;<br>&nbsp;</p>

                        <div class="col-md-12">
                            <form class="form-horizontal">
                                <div class="preview"></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="clearfix"></div>
        </div>
        <script>var numbre=<?php echo $numbre; ?>; var requiredsFields=<?=json_encode($requiredsFields)?>;</script>
        <?php
    }

    public function apiFieldsPage() {
        global $wpdb;

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'scuolasemplcecontacts' ) );
        }

        $tableName = $this->prefixTableName( 'apiformfields' );

        if ( isset( $_POST['syncnow'] ) ) {
            $this->fetchfield_cron_exec();
        }

        ?>
        <div class="wrap">
            <h2><?php echo $this->getPluginDisplayName(); ?></h2>
            <h2><?php _e( 'Api Fields', 'scuolasemplcecontacts' ); ?></h2>

            <form method="post" action="">
                <input type="hidden" name="syncnow" value="1">

                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php _e( 'Fetch Now', 'scuolasemplcecontacts' ); ?>"/>
                </p>
            </form>

            <?php
                $fdata = $wpdb->get_row( "SELECT * FROM " . $tableName . " WHERE 1", ARRAY_A );

                if ( empty( $fdata ) ) {
                    echo '<h3> ' . __( 'No Data found!!! Fetch Now.', 'scuolasemplcecontacts' ) . '</h3>';
                } else {
                    self::showapifields( $fdata );
                }
            ?>
        </div>
        <?php
    }

    public function showapifields( $fdata ) {
        echo '<h3> ' . __( 'Last Fetched', 'scuolasemplcecontacts' ) . ' : ' . date( 'd-m-Y H:i', $fdata['fetcheddate'] ) . '</h3>';
        
        $customfields = json_decode( $fdata['customfields'] );
        $standardfields = json_decode( $fdata['standardfields'] );
        ?>
        <table class="fieldstable">
            <tbody>
                <tr>
                    <th><?php _e( 'Standard fields', 'scuolasemplcecontacts' ); ?></th>
                    <th><?php _e( 'Custom fields', 'scuolasemplcecontacts' ); ?></th>
                </tr>
                <tr>
                    <td valign=top><?php self::renderSfields( $standardfields ); ?></td>
                    <td valign=top><?php self::renderCfields( $customfields ); ?></td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    public function api_http_get($url,$username,$password){
        $args = array(
            'headers' => array(
                'authorization' => 'Basic ' . base64_encode( $username . ':' . $password ),
                'Content-Type' => 'application/json'
            )
        );

        $return = wp_remote_get( $url, $args );
        return $return;
    }

    public function api_http_post( $url, $username, $password, $jsonpost ) {
        $args = array(
            'headers' => array(
                'authorization' => 'Basic ' . base64_encode( $username . ':' . $password ),
                'Content-Type' => 'application/json'
            ),
            'body' => $jsonpost,
            'method' => 'POST',
            'data_format' => 'body',
        );

        $return = wp_remote_post( $url, $args );
        return $return;
    }

    public function renderSfields( $data ) {
        foreach ( $data->standardfields as $fvl ) {
            self::renderfield( $fvl );
        }
    }

    public function renderCfields( $data ) {
        foreach ( $data->customfields as $fvl ) {
            $fvl->key = 'field_' . $fvl->id;
            self::renderfield( $fvl );
        }
    }

    public function renderfield( $filed ) {
        switch ( $filed->type ) {
            case 'text':
            case 'email':
            case 'number':
            case 'password':
                echo '<p>' . $filed->name . ( ( property_exists( $filed, 'required' ) && $filed->required == 1 ) ? '*' : '' ) . ' <br><input type="text" name="' . $filed->key . '" value="" size="30"/></p>';
                break;
            case 'select':
            case 'multiselect':
                $html = '<p>' . $filed->name . ( ( property_exists( $filed, 'required' ) && $filed->required == 1 ) ? '*' : '' ) . '<br><select name="' . $filed->key . '' . ( $filed->type == 'multiselect' ? '[]' : '' ) . '" ' . ( $filed->type == 'multiselect' ? 'multiple' : '' ) . '>';

                foreach( $filed->selectvalues as $v ) {
                    $html .= '<option  value="' . esc_attr( $v->id ) . '">' . $v->name . '</option>';
                }

                $html .= '</select> </p>';
                echo $html;
                break;
            case 'checkbox':
                if ( ! isset( $filed->selectvalues ) ) {
                    $html = '<p>' . $filed->name . ( ( property_exists( $filed, 'required' ) && $filed->required == 1 ) ? '*' : '' ) . '<br><input type="checkbox" value="1" name="' . $filed->key . '" />';
                } else {
                    $html = '<p>' . $filed->name . ( ( property_exists( $filed, 'required' ) && $filed->required == 1 ) ? '*' : '' ) . '<br>';

                    foreach( $filed->selectvalues as $v ) {
                        $html .= '<input type="checkbox" value="' . esc_attr( $v->id ) . '" name="' . $filed->key . '" />' . $v->name . ' ';
                    }
                }

                echo $html;
                break;
            case 'textarea':
                echo '<p>' . $filed->name . ( ( property_exists( $filed, 'required' ) && $filed->required == 1 ) ? '*' : '' ) . '<br> <textarea rows="5" cols="30" name="' . esc_attr( $filed->key ) . '"></textarea></p>';
                break;
            case 'date':
                echo '<p>' . $filed->name . ( ( property_exists( $filed, 'required' ) && $filed->required == 1 ) ? '*' : '' ) . '<br><input type="text" class="datepicker" name="' . $filed->key . '" value="" size="30"/></p>';
                break;
        }
    }

    public function listforms() {
        ?>
        <div class="container">
            <h3 class="text-center"><?php echo $this->getPluginDisplayName(); ?></h3>
            <h4><?php _e( 'API Forms', 'scuolasemplcecontacts' ); ?>  <a href="<?php echo esc_url( admin_url( 'admin.php?page=scuolasemplcecontacts_buildform' ) ); ?>" class="btn btn-primary "><?php _e( 'Create New Form', 'scuolasemplcecontacts' ); ?></a></h4>

            <table id="apiformtable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th><?php _e( 'Form Name', 'scuolasemplcecontacts' ); ?></th>
                        <th><?php _e( 'Short code', 'scuolasemplcecontacts' ); ?></th>
                        <th><?php _e( 'Form Creation Date', 'scuolasemplcecontacts' ); ?></th>
                        <th><?php _e( 'Action', 'scuolasemplcecontacts' ); ?></th>
                    </tr>
                </thead>
            </table>
        </div>

        <div class="hide">
            <div id="dialog" title="Title">
                <p id=dialogcontents></p>
            </div>
        </div>
        <?php
    }

    public function listenquiry() {
        global $wpdb;

        $tableName = $this->prefixTableName( 'apiforms' );
        $results = $wpdb->get_results( "SELECT * FROM " . $tableName . " WHERE 1" );
        ?>
        <div class="container">
            <h3 class="text-center"><?php echo $this->getPluginDisplayName(); ?></h3>
            <!--<h4><?php _e( 'Form enquiry', 'scuolasemplcecontacts' ); ?></h4>-->
            <div class="form-group">
                <label class="control-label"><?php _e( 'Form enquiry for', 'scuolasemplcecontacts' ); ?></label>
                <select class="form-control" name="aforms" id="aforms" onChange="redrawdatatable(this)">
                    <option value=""><?php _e( 'All', 'scuolasemplcecontacts' ); ?></option>
                    <?php foreach ( $results as $val ): ?>
                        <option value="<?php echo $val->id; ?>"><?php echo $val->formname; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <table id="enquirytable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th><?php _e( 'Name', 'scuolasemplcecontacts' ); ?></th>
                        <th><?php _e( 'Form Name', 'scuolasemplcecontacts' ); ?></th>
                        <th><?php _e( 'Post Date', 'scuolasemplcecontacts' ); ?></th>
                        <th><?php _e( 'API ID', 'scuolasemplcecontacts' ); ?></th>
                        <th><?php _e( 'Action', 'scuolasemplcecontacts' ); ?></th>
                    </tr>
                </thead>
            </table>
        </div>

        <div class="hide">
            <div id="dialog" title="Title">
                <p id=dialogcontents></p>
            </div>
        </div>
        <?php
    }

    public function ksesFormContent( $content ) {
        $allowed = wp_kses_allowed_html( 'post' );

        $dataAttributes = array(
            'data-name' => true,
            'data-span' => true,
            'data-layout' => true,
            'data-key' => true,
            'data-type' => true,
            'data-required' => true,
            'data-standard' => true,
            'data-soption' => true
        );

        $allowed['div']['class'] = true;
        $allowed['label']['class'] = true;
        $allowed['button']['class'] = true;

        $allowed['div'] = array_merge( $allowed['div'], $dataAttributes );
        $allowed['span'] = array_merge( $allowed['span'], $dataAttributes );
        $allowed['label'] = array_merge( $allowed['label'], $dataAttributes );
        $allowed['button'] = array_merge( $allowed['button'], $dataAttributes );

        $allowed['input'] = array(
            'type' => true,
            'name' => true,
            'class' => true,
            'placeholder' => true,
            'value' => true,
            'required' => true,
            'id' => true,
            'checked' => true,
            'disabled' => true
        );

        $allowed['select'] = array(
            'name' => true,
            'id' => true,
            'class' => true,
            'multiple' => true,
            'required' => true,
        );

        $allowed['textarea'] = array(
            'name' => true,
            'id' => true,
            'class' => true,
            'placeholder' => true,
            'required' => true,
        );

        $allowed['option'] = array(
            'value' => true,
            'selected' => true
        );

        return wp_kses( $content, $allowed );
    }

    
}


