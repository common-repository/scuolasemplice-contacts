<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

include_once( 'ScuolasemplceContacts_InstallIndicator.php' );

class ScuolasemplceContacts_LifeCycle extends ScuolasemplceContacts_InstallIndicator {

    public function install() {

        // Initialize Plugin Options
        $this->initOptions();

        // Initialize DB Tables used by the plugin
        $this->installDatabaseTables();

        // Other Plugin initialization - for the plugin writer to override as needed
        $this->otherInstall();

        // Record the installed version
        $this->saveInstalledVersion();

        // To avoid running install() more then once
        $this->markAsInstalled();
    }

    public function uninstall() {
        $this->otherUninstall();
        $this->unInstallDatabaseTables();
        $this->deleteSavedOptions();
        $this->markAsUnInstalled();
    }

    /**
     * Perform any version-upgrade activities prior to activation (e.g. database changes)
     * @return void
     */
    public function upgrade() {
    }

    /**
     * See: =105
     * @return void
     */
    public function activate() {
        $this->installDatabaseTables();

        if ( ! wp_next_scheduled( 'fetchfield_cron_hook' ) ) {
            wp_schedule_event( time(), 'daily', 'fetchfield_cron_hook' );
        }
    }

    /**
     * See: =105
     * @return void
     */
    public function deactivate() {
        $this->unInstallDatabaseTables();
        wp_clear_scheduled_hook( 'fetchfield_cron_hook' );
    }

    /**
     * See: =31
     * @return void
     */
    protected function initOptions() {
    }

    public function addActionsAndFilters() {
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
    }

    /**
     * See: =101
     * Drop plugin-created tables on uninstall.
     * @return void
     */
    protected function unInstallDatabaseTables() {
    }

    /**
     * Override to add any additional actions to be done at install time
     * See: =33
     * @return void
     */
    protected function otherInstall() {
    }

    /**
     * Override to add any additional actions to be done at uninstall time
     * See: =33
     * @return void
     */
    protected function otherUninstall() {
    }

    /**
     * Puts the configuration page in the Plugins menu by default.
     * Override to put it elsewhere or create a set of submenus
     * Override with an empty implementation if you don't want a configuration page
     * @return void
     */
    public function pluginMenus() {
        global $submenu;

        $displayName = $this->getPluginDisplayName();

        $this->menuPage();
        $this->addFormsSubMenuPage();
        $this->listFormsSubMenuPage();
        $this->addEnquirySubMenuPage();
        $this->addApiFieldsSubMenuPage();
        $this->addSettingsSubMenuPage();

        $menuSlug = $this->getMenuSlug();
        if( current_user_can( 'administrator' ) ){
          $submenu[$menuSlug][] = array( '<div id="extlink">Support</div>', 'manage_options', 'https://www.scuolasemplice.it/contacts-plugin' );
        }
    }

    protected function requireExtraPluginFiles() {
        require_once( ABSPATH . 'wp-includes/pluggable.php' );
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }

    protected function getMenuSlug( $key = '' ) {
        return 'scuolasemplcecontacts_' . ( empty( $key ) ? 'admin' : $key );
    }

    protected function menuPage() {
        $displayName = $this->getPluginDisplayName();

        add_menu_page(
            $displayName,
            $displayName,
            'manage_options',
            $this->getMenuSlug( 'admin' ),
            array( &$this, 'listforms' ),
            'dashicons-rest-api',
            25
        );
    }

    protected function listFormsSubMenuPage() {
        $displayName = $this->getPluginDisplayName();

        add_submenu_page(
            $this->getMenuSlug(),
            $displayName,
            __( 'Forms', 'scuolasemplcecontacts' ),
            'manage_options',
            $this->getMenuSlug( 'admin' ),
            array( &$this, 'listforms' )
        );
    }

    protected function addFormsSubMenuPage() {
        $displayName = $this->getPluginDisplayName();

        add_submenu_page(
            null,
            $displayName,
            __( 'Forms', 'scuolasemplcecontacts' ),
            'manage_options',
            $this->getMenuSlug( 'buildform' ),
            array( &$this, 'buildForm' )
        );
    }

    protected function addEnquirySubMenuPage() {
        $displayName = $this->getPluginDisplayName();

        add_submenu_page(
            $this->getMenuSlug(),
            $displayName,
            __( 'Enquiry', 'scuolasemplcecontacts' ),
            'manage_options',
            $this->getMenuSlug( 'Enquiry' ),
            array( &$this, 'listenquiry' )
        );
    }

    protected function addApiFieldsSubMenuPage() {
        $displayName = $this->getPluginDisplayName();

        add_submenu_page(
            $this->getMenuSlug(),
            $displayName,
            __( 'Api Fields', 'scuolasemplcecontacts' ),
            'manage_options',
            $this->getMenuSlug( 'apiFieldsPage' ),
            array( &$this, 'apiFieldsPage' )
        );
    }

    protected function addSettingsSubMenuPage() {
        $displayName = $this->getPluginDisplayName();

        add_submenu_page(
            $this->getMenuSlug(),
            $displayName,
            __( 'Settings', 'scuolasemplcecontacts' ),
            'manage_options',
            $this->getMenuSlug( 'Settings' ),
            array( &$this, 'settingsPage' )
        );
    }

    /**
     * @param  $name string name of a database table
     * @return string input prefixed with the WordPress DB table prefix
     * plus the prefix for this plugin (lower-cased) to avoid table name collisions.
     * The plugin prefix is lower-cases as a best practice that all DB table names are lower case to
     * avoid issues on some platforms
     */
    protected function prefixTableName( $name ) {
        global $wpdb;

        return $wpdb->prefix .  strtolower( $this->prefix( $name ) );
    }

    /**
     * Convenience function for creating AJAX URLs.
     *
     * @param $actionName string the name of the ajax action registered in a call like
     * add_action('wp_ajax_actionName', array(&$this, 'functionName'));
     *     and/or
     * add_action('wp_ajax_nopriv_actionName', array(&$this, 'functionName'));
     *
     * If have an additional parameters to add to the Ajax call, e.g. an "id" parameter,
     * you could call this function and append to the returned string like:
     *    $url = $this->getAjaxUrl('myaction&id=') . urlencode($id);
     * or more complex:
     *    $url = sprintf($this->getAjaxUrl('myaction&id=%s&var2=%s&var3=%s'), urlencode($id), urlencode($var2), urlencode($var3));
     *
     * @return string URL that can be used in a web page to make an Ajax call to $this->functionName
     */
    public function getAjaxUrl( $actionName ) {
        $url = admin_url( 'admin-ajax.php' );
        $url = add_query_arg( 'action', $actionName, $url );

        return $url;
    }

}
