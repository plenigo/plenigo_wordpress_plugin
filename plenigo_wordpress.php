<?php

/*
  Plugin Name: Plenigo
  Plugin URI: http://wordpress.org/plugins/plenigo/
  Description: So far, the technical implementation of paid content has been time-consuming and costly for publishing houses and media companies. plenigo puts an end to this.
  Version: 1.1.28
  Author: Plenigo
  Author URI: https://www.plenigo.com
  Text Domain: plenigo
  License: GPLv2
 */
/*
  Copyright (C) 2014 plenigo

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 2
  of the License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

define('PLENIGO_DEBUG', false);
if (PLENIGO_DEBUG === true) {
    define('WP_DEBUG', true);
    define('WP_DEBUG_LOG', true);
    define('WP_DEBUG_DISPLAY', true);
    @ini_set('display_errors', 1);
    error_reporting(E_ALL | E_STRICT);
}

// Plenigo JavaScript SDK / Services
define('PLENIGO_SVC_URL', "https://www.plenigo.com");
define('PLENIGO_JSSDK_URL', "https://www.plenigo.com");

// Plenigo PHP SDK
require_once dirname(__FILE__) . '/plenigo_sdk/plenigo/Plenigo.php';
require_once dirname(__FILE__) . '/plenigo_plugin/PlenigoSDKManager.php';

// Internationalization and upgrade
add_action('plugins_loaded', function() {
    load_plugin_textdomain('plenigo', FALSE, basename(dirname(__FILE__)) . '/plenigo_i18n/');
    $upgraded = plenigo_plugin_upgrade();
    if ($upgraded) {
        plenigo_log_message("Plenigo setting Updated!!!");
    }
});

//setup//
if (is_admin()) {
    require_once dirname(__FILE__) . '/plenigo_plugin/PlenigoSettingsPage.php';

    $settingsPage = new \plenigo_plugin\PlenigoSettingsPage();
}

//ContentManager
require_once dirname(__FILE__) . '/plenigo_plugin/PlenigoContentManager.php';

$contentManager = new \plenigo_plugin\PlenigoContentManager();

//ShortcodeManager
require_once dirname(__FILE__) . '/plenigo_plugin/PlenigoShortcodeManager.php';

$shortcodeManager = new \plenigo_plugin\PlenigoShortcodeManager();

//Plenigo Login
$plenigoOptions = get_option('plenigo_settings');
//LoginWidget
if (isset($plenigoOptions['use_login']) && ($plenigoOptions['use_login'] == 1 )) {
    require_once dirname(__FILE__) . '/plenigo_plugin/PlenigoLoginWidget.php';
    require_once dirname(__FILE__) . '/plenigo_plugin/PlenigoLoginManager.php';
    add_action('widgets_init', function() {
        register_widget('plenigo_plugin\PlenigoLoginWidget');
    });

    $loginManager = new \plenigo_plugin\PlenigoLoginManager();
}

/**
 * Upgrades settings from older versions to current
 * 
 * @return boolean true if it was upgraded
 */
function plenigo_plugin_upgrade() {
    $options = get_option('plenigo_settings');
    $res = false;

    //Option changes
    $arrOptNameChanges = array(
        'courtain_title' => 'curtain_title',
        'courtain_text' => 'curtain_text',
        'courtain_buy' => 'curtain_buy',
        'courtain_login' => 'curtain_login',
    );
    foreach ($arrOptNameChanges as $oldVal => $newVal) {
        if (isset($options[$oldVal])) {
            $res = true;
            $options[$newVal] = $options[$oldVal];
            unset($options[$oldVal]);
        }
    }

    //Deprecated Options
    $arrOptDeprecated = array(
        'plenigo_text'
    );
    foreach ($arrOptDeprecated as $oldVal) {
        if (isset($options[$oldVal])) {
            $res = true;
            unset($options[$oldVal]);
        }
    }

    //If options changed
    if ($res) {
        update_option('plenigo_settings', $options);
    }

    return $res;
}

/**
 * Centralized  method for showing error messages
 * 
 * @param string $message
 * @param string $error_type
 */
function plenigo_log_message($message, $error_type = E_USER_NOTICE) {
    if (PLENIGO_DEBUG === true) {
        trigger_error($message, $error_type);
    }
}

?>
