<?php

/*
  Copyright (C) 2014 Plenigo

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

namespace plenigo_plugin;

require_once __DIR__ . '/settings/PlenigoWPSetting.php';
require_once __DIR__ . '/settings/SettingTestMode.php';
require_once __DIR__ . '/settings/SettingCompanyId.php';
require_once __DIR__ . '/settings/SettingCompanySecret.php';
require_once __DIR__ . '/settings/SettingCheckMetered.php';
require_once __DIR__ . '/settings/SettingUseLogin.php';
require_once __DIR__ . '/settings/SettingUseWPLogin.php';
require_once __DIR__ . '/settings/SettingOverrideProfiles.php';
require_once __DIR__ . '/settings/SettingRedirectURL.php';
require_once __DIR__ . '/settings/SettingLoginURL.php';
require_once __DIR__ . '/settings/SettingProductTagDB.php';
require_once __DIR__ . '/settings/SettingCategoryTagDB.php';
require_once __DIR__ . '/settings/SettingCurtainTitle.php';
require_once __DIR__ . '/settings/SettingCurtainText.php';
require_once __DIR__ . '/settings/SettingCurtainTitleMembers.php';
require_once __DIR__ . '/settings/SettingGACode.php';
require_once __DIR__ . '/settings/SettingCurtainTextMembers.php';
require_once __DIR__ . '/settings/SettingCurtainMode.php';
require_once __DIR__ . '/settings/SettingPreventTag.php';
require_once __DIR__ . '/settings/SettingCurtainButtonBuy.php';
require_once __DIR__ . '/settings/SettingCurtainButtonLogin.php';
require_once __DIR__ . '/settings/SettingCurtainButtonCustom.php';
require_once __DIR__ . '/settings/SettingCurtainButtonCustomURL.php';

/**
 * PlenigoSettingsPage
 * 
 * <b>
 * This class holds the functions needed to configure the Plenigo Plugin settings page(s).
 * </b>
 *
 * @category WordPressPlugin
 * @package  plenigoPlugin
 * @author   Sebastian Dieguez <s.dieguez@plenigo.com>
 * @link     https://plenigo.com
 */
class PlenigoSettingsPage
{

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    const PLENIGO_SETTINGS_GROUP = 'plenigo';
    const PLENIGO_SETTINGS_NAME = 'plenigo_settings';
    const PLENIGO_SETTINGS_PAGE = 'plenigo_options';

    private $settings = array();

    /**
     * Start up
     */
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
        add_action('admin_enqueue_scripts', array($this, 'add_scripts'));
        add_action('load-toplevel_page_' . self::PLENIGO_SETTINGS_PAGE, array($this, 'add_help_tab'));
        // Set class property
        $this->options = get_option(self::PLENIGO_SETTINGS_NAME);

        array_push($this->settings, new \plenigo_plugin\settings\SettingTestMode());
        array_push($this->settings, new \plenigo_plugin\settings\SettingCompanyId());
        array_push($this->settings, new \plenigo_plugin\settings\SettingCompanySecret());
        array_push($this->settings, new \plenigo_plugin\settings\SettingCheckMetered());
        array_push($this->settings, new \plenigo_plugin\settings\SettingUseLogin());
        array_push($this->settings, new \plenigo_plugin\settings\SettingUseWPLogin());
        array_push($this->settings, new \plenigo_plugin\settings\SettingOverrideProfiles());
        array_push($this->settings, new \plenigo_plugin\settings\SettingRedirectURL());
        array_push($this->settings, new \plenigo_plugin\settings\SettingLoginURL());
        array_push($this->settings, new \plenigo_plugin\settings\SettingProductTagDB());
        array_push($this->settings, new \plenigo_plugin\settings\SettingCategoryTagDB());
        array_push($this->settings, new \plenigo_plugin\settings\SettingCurtainTitle());
        array_push($this->settings, new \plenigo_plugin\settings\SettingCurtainText());
        array_push($this->settings, new \plenigo_plugin\settings\SettingCurtainTitleMembers());
        array_push($this->settings, new \plenigo_plugin\settings\SettingCurtainTextMembers());
        array_push($this->settings, new \plenigo_plugin\settings\SettingCurtainMode());
        array_push($this->settings, new \plenigo_plugin\settings\SettingPreventTag());
        array_push($this->settings, new \plenigo_plugin\settings\SettingCurtainButtonBuy());
        array_push($this->settings, new \plenigo_plugin\settings\SettingCurtainButtonLogin());
        array_push($this->settings, new \plenigo_plugin\settings\SettingCurtainButtonCustom());
        array_push($this->settings, new \plenigo_plugin\settings\SettingCurtainButtonCustomURL());
        array_push($this->settings, new \plenigo_plugin\settings\SettingGACode());
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_menu_page('Plenigo Options', 'Plenigo', 'manage_options', self::PLENIGO_SETTINGS_PAGE,
            array($this, 'create_admin_page'), plugins_url('plenigo_img/favicon.ico', dirname(__FILE__)), 79);
    }

    /**
     * Add Javascript imports
     */
    public function add_scripts()
    {
        wp_enqueue_script('jquery-ui-autocomplete');
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_style("jquery-ui");
        wp_enqueue_style("jquery-ui-core");
        wp_enqueue_style("jquery-ui-tabs");
        wp_enqueue_style("jquery-ui",
            "//ajax.googleapis.com/ajax/libs/jqueryui/"
            . "1.10.4"
            . "/themes/smoothness/jquery-ui.min.css");
    }

    public function add_help_tab()
    {
        plenigo_log_message("CREATIGN HELP TAB");
        $screen = get_current_screen();
        $screen->add_help_tab(array(
            'id' => 'plenigo_help_tab',
            'title' => __('Plenigo Help', self::PLENIGO_SETTINGS_GROUP),
            'content' => '<p>In order to configure the Plenigo Paywall, '
            . 'first got to the Plenigo Website and register as a business. '
            . '</p>'
            . '<p>Obtain your <b>Company ID</b> and <b>Private Key</b>, we are almost there...'
            . '</p>'
            . '<p>Ok, last step, <a target="_blank" href="' . PlenigoContentManager::JS_BASE_URL_NOAUTH
            . '/company/product/create">create one or more managed product</a> and copy the Product ID'
            . ', type the TAG, paste the product ID into the text field below and click ADD to append it to the tag list.'
            . '</p>'
            ,
        ));
        $screen->add_help_tab(array(
            'id' => 'plenigo_help_login',
            'title' => __('Plenigo OAuth Help', self::PLENIGO_SETTINGS_GROUP),
            'content' => '<p>' . __('In order to configure Plenigo OAuth Login: ', self::PLENIGO_SETTINGS_GROUP)
            . __('1 - Add Login redirect URL to Plenigo (Usually: <b>{YOUR BLOG URL}/wp-login.php</b>) ',
                self::PLENIGO_SETTINGS_GROUP)
            . ' <a target="_blank" href="' . PlenigoContentManager::JS_BASE_URL_NOAUTH . '/company/account/urls/show">' . __('clicking this link',
                self::PLENIGO_SETTINGS_GROUP) . '</a><br/>'
            . __('2 - Fill the same URL in the <b>OAuth redirect URL</b> below', self::PLENIGO_SETTINGS_GROUP) . '<br/>'
            . __('3 - (Optional) Fill the URL in the <b>URL After Login</b> for login redirection',
                self::PLENIGO_SETTINGS_GROUP) . '<br/>'
            . __('4 - Enable the Plenigo Login clicking <b>Use Plenigo Authentication Provider</b> ',
                self::PLENIGO_SETTINGS_GROUP) . '<br/>'
            . __('5 - Put the Plenigo Login Widget in a widget area of the site ', self::PLENIGO_SETTINGS_GROUP)
            . ' <a target="_blank" href="' . admin_url('/widgets.php') . '">' . __('clicking this link',
                self::PLENIGO_SETTINGS_GROUP) . '</a><br/>'
            . __('6 - Enjoy Loggin in with Plenigo! ', self::PLENIGO_SETTINGS_GROUP)
            . '</p>'
            ,
        ));
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        echo '<div class="wrap">';
        screen_icon();
        echo '<h2>Plenigo integration</h2>';
        settings_errors(self::PLENIGO_SETTINGS_PAGE);

        //Loading
        echo '<div class="well" id="pl_load_settings">'
        . '<span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> '
        . __('Loading...', self::PLENIGO_SETTINGS_GROUP) . '</div>';

        //Hide Section titles
        echo '<div role="tabpanel" id="plenigo_tab_panel" style="display:none;">';
        echo '<ul class="nav nav-tabs" role="tablist">';

        echo '<li role="presentation" class="active"><a href="#plenigo_general" '
        . 'aria-controls="plenigo_general" role="tab" data-toggle="tab">'
        . __('General', self::PLENIGO_SETTINGS_GROUP) . '</a></li>';

        echo '<li role="presentation" class="active"><a href="#plenigo_login_section" '
        . 'aria-controls="plenigo_login_section" role="tab" data-toggle="tab">'
        . __('OAuth Login', self::PLENIGO_SETTINGS_GROUP) . '</a></li>';

        echo '<li role="presentation" class="active"><a href="#plenigo_content_section" '
        . 'aria-controls="plenigo_content_section" role="tab" data-toggle="tab">'
        . __('Premium Content', self::PLENIGO_SETTINGS_GROUP) . '</a></li>';

        echo '<li role="presentation" class="active"><a href="#plenigo_curtain_section" '
        . 'aria-controls="plenigo_curtain_section" role="tab" data-toggle="tab">'
        . __('Curtain Customization', self::PLENIGO_SETTINGS_GROUP) . '</a></li>';

        echo '</ul>';

        echo '<form method="post" action="options.php">';
        settings_fields(self::PLENIGO_SETTINGS_GROUP);
        echo '<div class="tab-content">';
        do_settings_sections(self::PLENIGO_SETTINGS_PAGE);
        echo '</div>';
        submit_button();
        echo '</form>';
        echo "</div></div>\n";
        echo "<script>jQuery( document ).ready(function() {\n"
        . "jQuery(\"#pl_load_settings\").hide();\n"
        . "jQuery(\"#plenigo_tab_panel\").show().tabs();\n"
        . '});</script>';
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
        register_setting(
            self::PLENIGO_SETTINGS_GROUP, // Option group
            self::PLENIGO_SETTINGS_NAME, // Option name
            array($this, 'sanitize') // Sanitize / Validate
        );

        add_settings_section(
            'plenigo_general', // ID
            "", // Title
            array($this, 'print_section_general'), // Callback
            self::PLENIGO_SETTINGS_PAGE // Page
        );

        add_settings_section(
            'plenigo_login_section', // ID
            "", // Title
            array($this, 'print_section_login'), // Callback
            self::PLENIGO_SETTINGS_PAGE // Page
        );

        add_settings_section(
            'plenigo_content_section', // ID
            "", // Title
            array($this, 'print_section_content'), // Callback
            self::PLENIGO_SETTINGS_PAGE // Page
        );

        add_settings_section(
            'plenigo_curtain_section', // ID
            "", // Title
            array($this, 'print_section_curtain'), // Callback
            self::PLENIGO_SETTINGS_PAGE // Page
        );

        add_settings_section(
            'plenigo_footer_section', // ID
            "", // Title
            array($this, 'print_section_footer'), // Callback
            self::PLENIGO_SETTINGS_PAGE // Page
        );

        foreach ($this->settings as $setInstance) {
            add_settings_field(
                $setInstance::SETTING_ID, // ID
                $setInstance->getTitle(), // Title
                array($setInstance, 'renderCallback'), // Callback
                self::PLENIGO_SETTINGS_PAGE, // pAge
                $setInstance::SECTION_ID // Section
            );
        }
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input)
    {
        $message.= '';
        $type = 'updated';
        $new_input = array();
        if (!is_null($new_input)) {
            foreach ($this->settings as $setInstance) {
                if (isset($input[$setInstance::SETTING_ID])) {
                    $new_input[$setInstance::SETTING_ID] = $setInstance->sanitize($input);
                }
            }
            $message = __('Plenigo settings saved!', self::PLENIGO_SETTINGS_GROUP);
        } else {
            $type = 'error';
            $message = __('Data can not be empty', self::PLENIGO_SETTINGS_GROUP);
        }
        $this->plenigo_settings_validation($new_input);
        add_settings_error(self::PLENIGO_SETTINGS_PAGE, "plenigo", $message, $type);
        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function print_section_general()
    {
        print '<div role="tabpanel" class="tab-pane active" id="plenigo_general">'
            . '<h3>' . __('General', self::PLENIGO_SETTINGS_GROUP) . '</h3>'
            . 'These are the basic settings for using plenigo Services. '
            . 'It allow you to set your Company ID, your encryption secret code, '
            . 'working in the test environment and also disabling '
            . 'the entire plenigo functionality alltogether.';
    }

    /**
     * Print the Section text
     */
    public function print_section_login()
    {
        print '</div><div role="tabpanel" class="tab-pane active" id="plenigo_login_section">'
            . '<h3>' . __('OAuth Login', self::PLENIGO_SETTINGS_GROUP) . '</h3>'
            . "This section allows this Wordpress's users to login using plenigo authentication. "
            . "The data will be stored in the Wordpress database. If you disable this, "
            . "users may need to recover their passwords.";
    }

    /**
     * Print the Section text
     */
    public function print_section_content()
    {
        print '</div><div role="tabpanel" class="tab-pane active" id="plenigo_content_section">'
            . '<h3>' . __('Premium Content settings', self::PLENIGO_SETTINGS_GROUP) . '</h3>'
            . 'Here you configure how plenigo detects the content that is behind a Paywall. '
            . 'Here you can set a TAG to use as &quot;Payable&quot; marker, and also you can configure the '
            . 'plenigo managed product(s) that represents the paywall for that particular tag.';
    }

    /**
     * Print the Section text
     */
    public function print_section_curtain()
    {
        print '</div><div role="tabpanel" class="tab-pane active" id="plenigo_curtain_section">'
            . '<h3>' . __('Curtain Customization', self::PLENIGO_SETTINGS_GROUP) . '</h3>'
            . 'Here you can customize the curtain text and buttons. This is usefull to incentivize your customers to '
            . 'buy your product or join your blog. Be creative and personalize the existing templates.';
    }

    /**
     * Print the Section text
     */
    public function print_section_footer()
    {
        print '</div>';
    }

    /**
     * Validate Plenigo options and create an error accordingly
     * 
     * @param array $inputOptions the options sanitized as it comes from the settings page
     */
    private function plenigo_settings_validation($inputOptions)
    {
        foreach ($this->settings as $setInstance) {
            if (isset($inputOptions[$setInstance::SETTING_ID])) {
                $resValid = $setInstance->getValidationForValue($inputOptions[$setInstance::SETTING_ID]);
                if ($resValid === false) {
                    add_settings_error(self::PLENIGO_SETTINGS_PAGE, "plenigo",
                        sprintf(__('Validation failed: %s', self::PLENIGO_SETTINGS_GROUP), $setInstance->getTitle()),
                        'error');
                }
            }
        }
    }

}
