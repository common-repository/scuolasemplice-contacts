<?php
/*
   Plugin Name: ScuolaSemplice Contacts
   Plugin URI: https://www.scuolasemplice.it/contacts-plugin
   Version: 1.6
   Author: BluCloud Srl
   Description: ScuolaSemplice Contacts
   Text Domain: scuolasemplcecontacts
   License: GPLv3
  */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
$ScuolasemplceContacts_minimalRequiredPhpVersion = '5.0';

/**
 * Check the PHP version and give a useful error message if the user's version is less than the required version
 * @return boolean true if version check passed. If false, triggers an error which WP will handle, by displaying
 * an error message on the Admin page
 */
function ScuolasemplceContacts_noticePhpVersionWrong() {
    global $ScuolasemplceContacts_minimalRequiredPhpVersion;

    printf(
        '<div class="updated fade">%s<br/>%s<strong>%s</strong><br/>%s<strong>%s</strong></div>',
        __( 'Error: plugin "ScuolaSemplce Contacts" requires a newer version of PHP to be running.',  'scuolasemplcecontacts' ),
        __( 'Minimal version of PHP required: ', 'scuolasemplcecontacts' ),
        $ScuolasemplceContacts_minimalRequiredPhpVersion,
        __( 'Your server\'s PHP version: ', 'scuolasemplcecontacts' ),
        phpversion()
    );
}


function ScuolasemplceContacts_PhpVersionCheck() {
    global $ScuolasemplceContacts_minimalRequiredPhpVersion;

    if ( version_compare( phpversion(), $ScuolasemplceContacts_minimalRequiredPhpVersion ) < 0 ) {
        add_action( 'admin_notices', 'ScuolasemplceContacts_noticePhpVersionWrong' );

        return false;
    }

    return true;
}


/**
 * Initialize internationalization (i18n) for this plugin.
 *
 * @see http://codex.wordpress.org/I18n_for_WordPress_Developers
 * @see http://www.wdmac.com/how-to-create-a-po-language-translation#more-631
 *
 * @return void
 */
function ScuolasemplceContacts_i18n_init() {
    $pluginDir = dirname( plugin_basename( __FILE__ ) );
    load_plugin_textdomain( 'scuolasemplcecontacts', false, $pluginDir . '/languages/' );
}


//////////////////////////////////
// Run initialization
/////////////////////////////////

// Initialize i18n
add_action( 'plugins_loaded', 'ScuolasemplceContacts_i18n_init' );

// Run the version check.
// If it is successful, continue with initialization for this plugin
if ( ScuolasemplceContacts_PhpVersionCheck() ) {
    // Only load and run the init function if we know PHP version can parse it
    include_once( 'scuolasemplceContacts_init.php' );
    ScuolasemplceContacts_init( __FILE__ );
}
