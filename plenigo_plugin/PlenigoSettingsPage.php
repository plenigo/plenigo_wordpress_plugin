<?php

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

namespace plenigo_plugin;

require_once __DIR__ . '/settings/PlenigoWPSetting.php';
require_once __DIR__ . '/settings/SettingTestMode.php';
require_once __DIR__ . '/settings/SettingCompanyId.php';
require_once __DIR__ . '/settings/SettingCompanySecret.php';
require_once __DIR__ . '/settings/SettingCheckMetered.php';
require_once __DIR__ . '/settings/SettingMeteredURL.php';
require_once __DIR__ . '/settings/SettingMeteredExemptionTag.php';
require_once __DIR__ . '/settings/SettingUseNoscript.php';
require_once __DIR__ . '/settings/SettingNoscriptTitle.php';
require_once __DIR__ . '/settings/SettingNoscriptMessage.php';
require_once __DIR__ . '/settings/SettingAnalyticsCallback.php';
require_once __DIR__ . '/settings/SettingFeedParagraphs.php';
require_once __DIR__ . '/settings/SettingGACode.php';
require_once __DIR__ . '/settings/SettingUseLogin.php';
require_once __DIR__ . '/settings/SettingUseWPLogin.php';
require_once __DIR__ . '/settings/SettingOverrideProfiles.php';
require_once __DIR__ . '/settings/SettingRedirectURL.php';
require_once __DIR__ . '/settings/SettingLoginURL.php';
require_once __DIR__ . '/settings/SettingProductTagDB.php';
require_once __DIR__ . '/settings/SettingProductGroupOneDB.php';
require_once __DIR__ . '/settings/SettingProductGroupTwoDB.php';
require_once __DIR__ . '/settings/SettingCustomCurtainDB.php';
require_once __DIR__ . '/settings/SettingCategoryTagDB.php';
require_once __DIR__ . '/settings/SettingCurtainTitle.php';
require_once __DIR__ . '/settings/SettingCurtainText.php';
require_once __DIR__ . '/settings/SettingCurtainTitleMembers.php';
require_once __DIR__ . '/settings/SettingCurtainTextMembers.php';
require_once __DIR__ . '/settings/SettingCurtainMode.php';
require_once __DIR__ . '/settings/SettingCurtainCategoryMode.php';
require_once __DIR__ . '/settings/SettingPreventTag.php';
require_once __DIR__ . '/settings/SettingCurtainButtonBuy.php';
require_once __DIR__ . '/settings/SettingCurtainButtonLogin.php';
require_once __DIR__ . '/settings/SettingCurtainButtonCustom.php';
require_once __DIR__ . '/settings/SettingCurtainButtonCustomURL.php';
require_once __DIR__ . '/settings/SettingCurtainButtonCatCustom.php';
require_once __DIR__ . '/settings/SettingCurtainButtonCatCustomURL.php';
require_once __DIR__ . '/settings/SettingCurtainBuyTextDB.php';
require_once __DIR__ . '/settings/SettingUseWoo.php';
require_once __DIR__ . '/settings/SettingWooOrderTitle.php';
require_once __DIR__ . '/settings/SettingWooProductType.php';
require_once __DIR__ . '/settings/SettingUseQuietReport.php';
require_once __DIR__ . '/settings/SettingDebugMode.php';
require_once __DIR__ . '/settings/SettingWelcomeURL.php';
require_once __DIR__ . '/settings/SettingProfileURL.php';
require_once __DIR__ . '/settings/SettingUseRegister.php';
require_once __DIR__ . '/settings/LogTable.php';

use plenigo_plugin\settings\LogTable;

/**
 * PlenigoSettingsPage
 *
 * <b>
 * This class holds the functions needed to configure the plenigo plugin settings page(s).
 * </b>
 *
 * @category WordPressPlugin
 * @package  plenigoPlugin
 * @link     https://plenigo.com
 */
class PlenigoSettingsPage
{

    /**
     * Holds the values to be used in the fields callbacks.
     */
    private $options;

    const PLENIGO_SETTINGS_GROUP = 'plenigo';
    const PLENIGO_SETTINGS_NAME = 'plenigo_settings';
    const PLENIGO_SETTINGS_PAGE = 'plenigo_options';
    const PLENIGO_VERSION_OPT = 'plenigo_version';

    private $settings = array();

    /**
     * Start up
     */
    public function __construct() {
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_plugin_page'));
            add_action('admin_init', array($this, 'page_init'));
            add_action('admin_enqueue_scripts', array($this, 'add_scripts'));
            add_action('load-toplevel_page_' . self::PLENIGO_SETTINGS_PAGE, array($this, 'add_help_tab'));
            add_action('wp_ajax__ajax_fetch_custom_list', array($this, '_ajax_fetch_custom_list_callback'));
            add_action('wp_ajax__ajax_send_mail', array($this, '_ajax_send_mail_callback'));
            add_action('admin_footer', array($this, 'ajax_script'));
        }
        // Set class property
        $this->options = get_option(self::PLENIGO_SETTINGS_NAME, array());

        array_push($this->settings, new \plenigo_plugin\settings\SettingTestMode());
        array_push($this->settings, new \plenigo_plugin\settings\SettingCompanyId());
        array_push($this->settings, new \plenigo_plugin\settings\SettingCompanySecret());
        array_push($this->settings, new \plenigo_plugin\settings\SettingCheckMetered());
        array_push($this->settings, new \plenigo_plugin\settings\SettingMeteredURL());
        array_push($this->settings, new \plenigo_plugin\settings\SettingMeteredExemptionTag());
        array_push($this->settings, new \plenigo_plugin\settings\SettingUseNoscript());
        array_push($this->settings, new \plenigo_plugin\settings\SettingNoscriptTitle());
        array_push($this->settings, new \plenigo_plugin\settings\SettingNoscriptMessage());
        array_push($this->settings, new \plenigo_plugin\settings\SettingGACode());
        array_push($this->settings, new \plenigo_plugin\settings\SettingAnalyticsCallback());
        array_push($this->settings, new \plenigo_plugin\settings\SettingFeedParagraphs());
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
        array_push($this->settings, new \plenigo_plugin\settings\SettingCurtainCategoryMode());
        array_push($this->settings, new \plenigo_plugin\settings\SettingPreventTag());
        array_push($this->settings, new \plenigo_plugin\settings\SettingCustomCurtainDB());
        //Use this to check if you should show the groups based on toolset plugin
        if(function_exists('types_render_field')) {
            array_push($this->settings, new \plenigo_plugin\settings\SettingProductGroupOneDB());
            array_push($this->settings, new \plenigo_plugin\settings\SettingProductGroupTwoDB());
        }
        array_push($this->settings, new \plenigo_plugin\settings\SettingCurtainButtonBuy());
        array_push($this->settings, new \plenigo_plugin\settings\SettingCurtainButtonLogin());
        array_push($this->settings, new \plenigo_plugin\settings\SettingCurtainButtonCustom());
        array_push($this->settings, new \plenigo_plugin\settings\SettingCurtainButtonCustomURL());
        array_push($this->settings, new \plenigo_plugin\settings\SettingCurtainButtonCatCustom());
        array_push($this->settings, new \plenigo_plugin\settings\SettingCurtainButtonCatCustomURL());
        array_push($this->settings, new \plenigo_plugin\settings\SettingCurtainBuyTextDB());
        array_push($this->settings, new \plenigo_plugin\settings\SettingUseQuietReport());
        array_push($this->settings, new \plenigo_plugin\settings\SettingDebugMode());
        array_push($this->settings, new \plenigo_plugin\settings\SettingWelcomeURL());
        array_push($this->settings, new \plenigo_plugin\settings\SettingProfileURL());
        array_push($this->settings, new \plenigo_plugin\settings\SettingUseRegister());

        // Check the initialization of settings upon upgrade
        if (!isset($this->options[self::PLENIGO_VERSION_OPT]) || $this->options[self::PLENIGO_VERSION_OPT] !== PLENIGO_VERSION) {
            $this->options[self::PLENIGO_VERSION_OPT] = PLENIGO_VERSION;
            $this->initialize_defaults();
        }
    }

    /**
     * Add options page.
     */
    public function add_plugin_page() {
        // This page will be under "Settings"
        add_menu_page('Plenigo Options', 'Plenigo', 'manage_options', self::PLENIGO_SETTINGS_PAGE, array($this, 'create_admin_page'),
            plugins_url('plenigo_img/favicon.ico', dirname(__FILE__)), 79);
    }

    /**
     * Add Javascript imports.
     */
    public function add_scripts() {
        // Javascript
        wp_enqueue_script('jquery-ui-autocomplete');
        wp_enqueue_script('jquery-ui-tabs');
        wp_register_script('plenigo-settings-js', plugins_url('plenigo_js/pl_settings.js', dirname(__FILE__)));
        wp_enqueue_script('plenigo-settings-js');
        // CSS
        wp_enqueue_style("jquery-ui");
        wp_enqueue_style("jquery-ui-core");
        wp_enqueue_style("jquery-ui-tabs");
        wp_enqueue_style("jquery-ui",
            "//ajax.googleapis.com/ajax/libs/jqueryui/"
            . "1.10.4"
            . "/themes/smoothness/jquery-ui.min.css");
    }

    public function add_help_tab() {
        plenigo_log_message("CREATING HELP TAB");
        $screen = get_current_screen();
        $screen->add_help_tab(array(
            'id' => 'plenigo_help_tab',
            'title' => __('plenigo help', self::PLENIGO_SETTINGS_GROUP),
            'content' => '<p>In order to configure the plenigo Paywall, '
                . 'first got to the plenigo Website and register as a business. '
                . '</p>'
                . '<p>Obtain your <b>Company ID</b> and <b>Private Key</b>, we are almost there...'
                . '</p>'
                . '<p>Ok, last step, <a target="_blank" href="' . PLENIGO_SVC_URL
                . '/company/product/create">create one or more managed product</a> and copy the product id'
                . ', type the TAG, paste the product ID into the text field below and click ADD to append it to the tag list.'
                . '</p>'
        ,
        ));
        $screen->add_help_tab(array(
            'id' => 'plenigo_help_login',
            'title' => __('plenigo OAuth Help', self::PLENIGO_SETTINGS_GROUP),
            'content' => '<p>' . __('In order to configure plenigo OAuth Login: ', self::PLENIGO_SETTINGS_GROUP)
                . __('1 - Add Login redirect URL to plenigo (Usually: <b>{YOUR BLOG URL}/wp-login.php</b>) ', self::PLENIGO_SETTINGS_GROUP)
                . ' <a target="_blank" href="' . PLENIGO_SVC_URL . '/company/account/urls/show">' . __('clicking this link',
                    self::PLENIGO_SETTINGS_GROUP) . '</a><br/>'
                . __('2 - Fill the same URL in the <b>OAuth redirect URL</b> below', self::PLENIGO_SETTINGS_GROUP) . '<br/>'
                . __('3 - (Optional) Fill the URL in the <b>URL After Login</b> for login redirection', self::PLENIGO_SETTINGS_GROUP) . '<br/>'
                . __('4 - Enable the plenigo login clicking <b>Use plenigo Authentication Provider</b> ', self::PLENIGO_SETTINGS_GROUP) . '<br/>'
                . __('5 - Put the plenigo login widget in a widget area of the site ', self::PLENIGO_SETTINGS_GROUP)
                . ' <a target="_blank" href="' . admin_url('/widgets.php') . '">' . __('clicking this link', self::PLENIGO_SETTINGS_GROUP) . '</a><br/>'
                . __('6 - Enjoy logging in with plenigo! ', self::PLENIGO_SETTINGS_GROUP)
                . '</p>'
        ,
        ));
    }

    /**
     * Options page callback.
     */
    public function create_admin_page() {
        echo '<div class="wrap">';
        echo '<h2>plenigo integration</h2>';
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

        echo '<li role="presentation" class="active"><a href="#plenigo_metered_section" '
            . 'aria-controls="plenigo_metered_section" role="tab" data-toggle="tab">'
            . __('Metered Views', self::PLENIGO_SETTINGS_GROUP) . '</a></li>';

        echo '<li role="presentation" class="active"><a href="#plenigo_curtain_section" '
            . 'aria-controls="plenigo_curtain_section" role="tab" data-toggle="tab">'
            . __('Curtain Customization', self::PLENIGO_SETTINGS_GROUP) . '</a></li>';

        echo '<li role="presentation" class="active"><a href="#plenigo_advanced_section" '
            . 'aria-controls="plenigo_advanced_section" role="tab" data-toggle="tab">'
            . __('Advanced', self::PLENIGO_SETTINGS_GROUP) . '</a></li>';

        echo '<li role="presentation" class="active"><a href="#plenigo_error_logs_section" '
            . 'aria-controls="plenigo_error_logs_section" role="tab" data-toggle="tab">'
            . __('Error Logs', self::PLENIGO_SETTINGS_GROUP) . '</a></li>';

        echo '</ul>';

        echo '<form method="post" action="options.php">';
        settings_fields(self::PLENIGO_SETTINGS_GROUP);
        echo '<div class="tab-content">';
        do_settings_sections(self::PLENIGO_SETTINGS_PAGE);
        echo '</div>&nbsp;<div style="padding-left:1.4em;">';
        submit_button();
        ?>
        </div></form>
        </div></div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init() {
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
            'plenigo_metered_section', // ID
            "", // Title
            array($this, 'print_section_metered'), // Callback
            self::PLENIGO_SETTINGS_PAGE // Page
        );

        add_settings_section(
            'plenigo_curtain_section', // ID
            "", // Title
            array($this, 'print_section_curtain'), // Callback
            self::PLENIGO_SETTINGS_PAGE // Page
        );

        add_settings_section(
            'plenigo_advanced_section', // ID
            "", // Title
            array($this, 'print_section_advanced'), // Callback
            self::PLENIGO_SETTINGS_PAGE // Page
        );

        add_settings_section(
            'plenigo_error_logs_section', // ID
            "", // Title
            array($this, 'print_section_error_logs'), // Callback
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
     * Sanitize each setting field as needed.
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input) {
        $message = '';
        $type = 'updated';
        $new_input = array();
        if (!is_null($input)) {
            foreach ($this->settings as $setInstance) {
                if (isset($input[$setInstance::SETTING_ID])) {
                    $new_input[$setInstance::SETTING_ID] = $setInstance->sanitize($input);
                }
            }
            $message = __('plenigo settings saved!', self::PLENIGO_SETTINGS_GROUP);
        } else {
            $type = 'error';
            $message = __('Data can not be empty', self::PLENIGO_SETTINGS_GROUP);
        }
        $this->plenigo_settings_validation($new_input);
        if (function_exists("add_settings_error")) {
            add_settings_error(self::PLENIGO_SETTINGS_PAGE, "plenigo", $message, $type);
        }
        return $new_input;
    }

    /**
     * Print the Section text.
     */
    public function print_section_general() {
        print '<div role="tabpanel" class="tab-pane active" id="plenigo_general">'
            . '<h3>' . __('General', self::PLENIGO_SETTINGS_GROUP) . '</h3>'
            . 'These are the basic settings for using plenigo services. '
            . 'It allows you to set your company id, your encryption secret code, '
            . 'working in the test environment and also disabling '
            . 'the entire plenigo functionality alltogether.';
    }

    /**
     * Print the Section text.
     */
    public function print_section_login() {
        print '</div><div role="tabpanel" class="tab-pane active" id="plenigo_login_section">'
            . '<h3>' . __('OAuth Login', self::PLENIGO_SETTINGS_GROUP) . '</h3>'
            . "This section allows the Wordpress's users to login using plenigo authentication. "
            . "The data will be stored in the Wordpress database. If you disable this, "
            . "users may need to recover their passwords.";
    }

    /**
     * Print the Section text.
     */
    public function print_section_content() {
        print '</div><div role="tabpanel" class="tab-pane active" id="plenigo_content_section">'
            . '<h3>' . __('Premium Content settings', self::PLENIGO_SETTINGS_GROUP) . '</h3>'
            . 'Here you configure how plenigo detects the content that is behind a Paywall. '
            . 'Here you can set a TAG to use as &quot;Payable&quot; marker, and also you can configure the '
            . 'plenigo managed product(s) that represents the paywall for that particular tag.';
    }

    /**
     * Print the Section text.
     */
    public function print_section_metered() {
        print '</div><div role="tabpanel" class="tab-pane active" id="plenigo_metered_section">'
            . '<h3>' . __('Metered Views settings', self::PLENIGO_SETTINGS_GROUP) . '</h3>'
            . 'Metered Views is a way to handle certain amount of free views. '
            . 'Here you can set a TAG to use as Metered &quot;Exemption&quot; marker, and also you '
            . 'completely disable metered views.';
    }

    /**
     * Print the Section text.
     */
    public function print_section_curtain() {
        print '</div><div role="tabpanel" class="tab-pane active" id="plenigo_curtain_section">'
            . '<h3>' . __('Curtain Customization', self::PLENIGO_SETTINGS_GROUP) . '</h3>'
            . 'Here you can customize the curtain text and buttons. This is usefull to incentivize your customers to '
            . 'buy your product or join your blog. Be creative and personalize the existing templates.';
    }

    /**
     * Print the Section text.
     */
    public function print_section_woo() {
        print '</div><div role="tabpanel" class="tab-pane active" id="plenigo_woo_section">'
            . '<h3>' . __('Woo Commerce', self::PLENIGO_SETTINGS_GROUP) . '</h3>'
            . 'Here you can control the way plenigo integrates with '
            . '<a href="http://www.woothemes.com/woocommerce/" target="_blank">WooCommerce</a>. '
            . 'It allows you to use the powerful features in <a href="http://www.woothemes.com/woocommerce/" target="_blank">'
            . 'WooCommerce</a> and use plenigo as payment method.';
    }

    /**
     * Print the Section text.
     */
    public function print_section_advanced() {
        print '</div><div role="tabpanel" class="tab-pane active" id="plenigo_advanced_section">'
            . '<h3>' . __('Advanced settings', self::PLENIGO_SETTINGS_GROUP) . '</h3>'
            . '<h2 style="color:red;">HANDS OFF!!</h2>'
            . 'Please make sure you understand what it means to enable these settings because it can be dangerous to expose '
            . 'this information to the user, or the plugin could compromise the site\'s look & feel.';
    }

    /**
     * Print the Section text.
     */
    public function print_section_error_logs() {
        $logListTable = new LogTable();
        print '</div><div role="tabpanel" class="tab-pane active" id="plenigo_error_logs_section">'
            . '<h3>' . __('Error Logs', self::PLENIGO_SETTINGS_GROUP) . '</h3>'
            . '<p>This is where the error logs are, '
            . 'with this information we can give you better support for any issues you have with this plugin.</p>';
        $logListTable->prepare_items();
        $logListTable->display();
        ?>
        <div id="alert-area">

        </div>
        <p class="submit">
            <button type="button" id="mailLogBtn" class="button button-primary" name="mailLogButton">Send Mail Log
            </button>
        </p>
        <?php
    }

    /**
     * Print the Section text.
     */
    public function print_section_footer() {
        print '</div>';
    }

    /**
     * Validate plenigo options and create an error accordingly.
     *
     * @param array $inputOptions the options sanitized as it comes from the settings page
     */
    private function plenigo_settings_validation($inputOptions) {
        if (!is_null($inputOptions) || !is_array($inputOptions)) {
            return;
        }
        foreach ($this->settings as $setInstance) {
            if (isset($inputOptions[$setInstance::SETTING_ID])) {
                $resValid = $setInstance->getValidationForValue($inputOptions[$setInstance::SETTING_ID]);
                if ($resValid === false && function_exists("add_settings_error")) {
                    add_settings_error(self::PLENIGO_SETTINGS_PAGE, "plenigo",
                        sprintf(__('Validation failed: %s', self::PLENIGO_SETTINGS_GROUP), $setInstance->getTitle()), 'error');
                }
            }
        }
    }

    /**
     * Makes sure that all variables are present in the options,
     * if not, it obtains its default value and creates the setting.
     */
    private function initialize_defaults() {
        foreach ($this->settings as $setInstance) {
            if (!isset($this->options[$setInstance::SETTING_ID])) {
                $this->options[$setInstance::SETTING_ID] = $setInstance->getDefaultValue();
                if (function_exists("add_settings_error")) {
                    add_settings_error(self::PLENIGO_SETTINGS_PAGE, "plenigo",
                        sprintf(__('Setting has been set to default value: %s', $setInstance::SETTING_ID), $setInstance->getTitle()), 'updated');
                }
            }
        }
        update_option(self::PLENIGO_SETTINGS_NAME, $this->options);
    }

    /**
     * Callback function for 'wp_ajax__ajax_fetch_custom_list' action hook.
     *
     * Loads the Custom List Table Class and calls ajax_response method
     */
    public function _ajax_fetch_custom_list_callback() {
        $wp_list_table = new LogTable();
        $wp_list_table->ajaxResponse();
    }

    /**
     * Callback function for 'wp_ajax__ajax_fetch_custom_list' action hook.
     *
     * Loads the Custom List Table Class and calls ajax_response method
     */
    public function _ajax_send_mail_callback() {
        $plenigoSdk = PlenigoSDKManager::get()->getPlenigoSDK();
        $to = 'support@plenigo.com';
        $subject = "Error log file from company {$plenigoSdk->getCompanyId()}";
        $message = 'This is a log of the errors.';
        $logTable = LogTable::makeNewForLogMail();
        $tmpfname = tempnam(sys_get_temp_dir(), 'PLENIGO_LOG');
        $logData = $logTable->toString();
        $logFileName = "$tmpfname.log";
        file_put_contents($logFileName, $logData);
        wp_mail($to, $subject, $message, array(), array($logFileName));
        $response = array(result => 'success', message => 'email was sent');
        die(json_encode($response));
    }

    /**
     * Centralized custom ajax scripts.
     */
    public function ajax_script() {
        ?>
        <script type="text/javascript">
            (function ($) {
                if ($("#plenigo_error_logs_section").length > 0) {
                    list = {

                        init: function () {

                            var timer;
                            var delay = 500;

                            $('.tablenav-pages a, .manage-column.sortable a, .manage-column.sorted a').on('click', function (e) {
                                e.preventDefault();
                                var query = this.search.substring(1);

                                var data = {
                                    paged: list.__query(query, 'paged') || '1',
                                    order: list.__query(query, 'order') || 'asc',
                                    orderby: list.__query(query, 'orderby') || 'title'
                                };
                                list.update(data);
                            });

                            $('input[name=paged]').on('keyup', function (e) {

                                // If user hit enter, we don't want to submit the form
                                // We don't preventDefault() for all keys because it would
                                // also prevent to get the page number!
                                if (13 == e.which)
                                    e.preventDefault();

                                var data = {
                                    paged: parseInt($('input[name=paged]').val()) || '1',
                                    order: $('input[name=order]').val() || 'asc',
                                    orderby: $('input[name=orderby]').val() || 'title'
                                };

                                window.clearTimeout(timer);
                                timer = window.setTimeout(function () {
                                    list.update(data);
                                }, delay);
                            });
                        },

                        /** AJAX call
                         *
                         * Send the call and replace table parts with updated version!
                         *
                         * @param    object    data The data to pass through AJAX
                         */
                        update: function (data) {
                            $.ajax({
                                url: ajaxurl,
                                data: $.extend(
                                    {
                                        action: '_ajax_fetch_custom_list',
                                    },
                                    data
                                ),
                                // Handle the successful result
                                success: function (response) {

                                    var response = $.parseJSON(response);

                                    if (response.rows.length) {
                                        $('#the-list').html(response.rows);
                                    }
                                    if (response.column_headers.length) {
                                        $('thead tr, tfoot tr').html(response.column_headers);
                                    }
                                    if (response.pagination.bottom.length) {
                                        $('.tablenav.top .tablenav-pages').html($(response.pagination.top).html());
                                    }
                                    if (response.pagination.top.length) {
                                        $('.tablenav.bottom .tablenav-pages').html($(response.pagination.bottom).html());
                                    }
                                    list.init();
                                }
                            });
                        },

                        /**
                         * Filter the URL Query to extract variables
                         *
                         * @see http://css-tricks.com/snippets/javascript/get-url-variables/
                         *
                         * @param    string    query The URL query part containing the variables
                         * @param    string    variable Name of the variable we want to get
                         *
                         * @return   string|boolean The variable value if available, false else.
                         */
                        __query: function (query, variable) {

                            var vars = query.split("&");
                            for (var i = 0; i < vars.length; i++) {
                                var pair = vars[i].split("=");
                                if (pair[0] == variable)
                                    return pair[1];
                            }
                            return false;
                        },
                    }
                    list.init();
                    $("#mailLogBtn").click(function (event) {
                        event.preventDefault();
                        $.ajax({
                            url: ajaxurl,
                            data: {
                                action: '_ajax_send_mail'
                            },
                            success: function (response) {
                                var returnedData = JSON.parse(response);
                                if (returnedData.result === "success") {
                                    jQuery("#alert-area").after('<div class="update-nag">The email was sent successfully.</div>');
                                    setTimeout(function () {
                                        $('.update-nag').remove();
                                    }, 3000);
                                }
                            }
                        });
                    });

                    jQuery(document).ready(function () {
                        jQuery("#pl_load_settings").hide();
                        jQuery("#plenigo_tab_panel").show().tabs();
                        jQuery("#plenigo_tab_panel").tabs({
                            activate: function (event, ui) {
                                var active = jQuery('#plenigo_tab_panel').tabs('option', 'active');
                                var activeTab = jQuery("#plenigo_tab_panel ul>li a").eq(active).attr("href");
                                if (activeTab === '#plenigo_error_logs_section') {
                                    jQuery('#submit').hide();
                                } else {
                                    jQuery('#submit').show();
                                }
                            }
                        });
                    });
                }
            })(jQuery);
        </script>
        <?php
    }
}
