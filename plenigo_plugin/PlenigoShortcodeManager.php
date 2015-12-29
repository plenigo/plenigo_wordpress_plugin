<?php

namespace plenigo_plugin;

use \plenigo\services\UserService;
use \plenigo\models\UserData;
use \plenigo\internal\models\Address;

/**
 * PlenigoShortcodeManager
 * 
 * <b>
 * This class holds the functions needed to configure the plenigo shortcodes
 * </b>
 *
 * @category SDK
 * @package  plenigo_plugin
 * @author   Sebastian Dieguez <s.dieguez@plenigo.com>
 * @link     https://plenigo.com
 */
class PlenigoShortcodeManager {

    const PLENIGO_SETTINGS_GROUP = 'plenigo';
    const PLENIGO_SETTINGS_NAME = 'plenigo_settings';
    const PLENIGO_META_NAME = 'plenigo_uid';

        //Replacement tags
    const REPLACE_PLUGIN_DIR = "<!--[PLUGIN_DIR]-->";
    const REPLACE_PROFILE_TITLE = "<!--[PROFILE_TITLE]-->";
    const REPLACE_TITLE_CUSTNO = "<!--[TITLE_CUSTNO]-->";
    const REPLACE_TITLE_EMAIL = "<!--[TITLE_EMAIL]-->";
    const REPLACE_TITLE_USERNAME = "<!--[TITLE_USERNAME]-->";
    const REPLACE_TITLE_GENDER = "<!--[TITLE_GENDER]-->";
    const REPLACE_TITLE_NAME = "<!--[TITLE_NAME]-->";
    const REPLACE_TITLE_FIRST = "<!--[TITLE_FIRST]-->";
    const REPLACE_TITLE_LAST = "<!--[TITLE_LAST]-->";
    const REPLACE_TITLE_STREET = "<!--[TITLE_STREET]-->";
    const REPLACE_TITLE_ADDINFO = "<!--[TITLE_ADDINFO]-->";
    const REPLACE_TITLE_ZIP = "<!--[TITLE_ZIP]-->";
    const REPLACE_TITLE_CITY = "<!--[TITLE_CITY]-->";
    const REPLACE_TITLE_COUNTRY = "<!--[TITLE_COUNTRY]-->";
    const REPLACE_TITLE_PLENIGO_PROFILE = "<!--[TITLE_PLENIGO_PROFILE]-->";
    const REPLACE_CLICK_PLENIGO_PROFILE = "<!--[CLICK_PLENIGO_PROFILE]-->";
    const REPLACE_VALUE_CUSTNO = "<!--[VALUE_CUSTNO]-->";
    const REPLACE_VALUE_EMAIL = "<!--[VALUE_EMAIL]-->";
    const REPLACE_VALUE_USERNAME = "<!--[VALUE_USERNAME]-->";
    const REPLACE_VALUE_GENDER = "<!--[VALUE_GENDER]-->";
    const REPLACE_VALUE_FIRST = "<!--[VALUE_FIRST]-->";
    const REPLACE_VALUE_LAST = "<!--[VALUE_LAST]-->";
    const REPLACE_VALUE_STREET = "<!--[VALUE_STREET]-->";
    const REPLACE_VALUE_ADDINFO = "<!--[VALUE_ADDINFO]-->";
    const REPLACE_VALUE_ZIP = "<!--[VALUE_ZIP]-->";
    const REPLACE_VALUE_CITY = "<!--[VALUE_CITY]-->";
    const REPLACE_VALUE_COUNTRY = "<!--[VALUE_COUNTRY]-->";
    const REPLACE_VALUE_COUNTRY_LCASE = "<!--[VALUE_COUNTRY_LCASE]-->";
    
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options = null;

    /**
     * Default constructor, called from the main php file
     */
    public function __construct() {
        $this->options = get_option(self::PLENIGO_SETTINGS_NAME, array());

        //Adding the shortcodes
        add_shortcode('pl_checkout', array($this, 'plenigo_handle_shortcode'));
        add_shortcode('pl_checkout_button', array($this, 'plenigo_handle_shortcode'));
        add_shortcode('pl_renew', array($this, 'plenigo_handle_shortcode'));
        add_shortcode('pl_failed', array($this, 'plenigo_handle_shortcode'));
        add_shortcode('pl_content_show', array($this, 'plenigo_handle_content_shortcode'));
        add_shortcode('pl_content_hide', array($this, 'plenigo_handle_content_shortcode'));
        add_shortcode('pl_user_profile', array($this, 'plenigo_handle_user_shortcode'));

        //TinyMCE
        // add new buttons
        add_filter('mce_buttons', array($this, 'plenigo_register_buttons'));
        // Load the TinyMCE plugin
        add_filter('mce_external_plugins', array($this, 'plenigo_register_tinymce_js'));

        // Enqueue TinyMCE CSS
        add_action('admin_enqueue_scripts', array($this, 'add_scripts'));
        add_action('admin_init', array($this, 'plenigo_add_editor_styles'));
    }

    /**
     * Adding the editor css style for the plenigo custom tags
     */
    function plenigo_add_editor_styles() {
        add_editor_style(plugins_url('plenigo_css/pl_tinymce.css', dirname(__FILE__)));
    }

    /**
     * Add CSS imports
     */
    public function add_scripts() {
        wp_register_style('plenigo-tinymce-css', plugins_url('plenigo_css/pl_tinymce.css', dirname(__FILE__)));
        wp_enqueue_style('plenigo-tinymce-css');
    }

    /**
     * Filter method for adding the plenigo buttons to TinyMCE.
     * 
     * @param array $buttons The current list of buttons
     * @return array The new list of buttons
     */
    function plenigo_register_buttons($buttons) {
        // We are attempting to only allow the shortcode appearance to editors.
        if (\current_user_can('edit_posts') || \current_user_can('edit_pages')) {
            if (\get_user_option('rich_editing') == 'true') {
                array_push($buttons, 'separator', 'plenigo', 'plenigo_renew', 'plenigo_failed', 'plenigo_separator', 'plenigo_show', 'plenigo_hide');
            }
        }
        return $buttons;
    }

    function plenigo_register_tinymce_js($plugin_array) {
        $plugin_array['plenigo'] = plugins_url('../plenigo_js/tinymce-plenigo-plugin.js', __file__);
        $plugin_array['plenigo_renew'] = plugins_url('../plenigo_js/tinymce-plenigo_renew-plugin.js', __file__);
        $plugin_array['plenigo_failed'] = plugins_url('../plenigo_js/tinymce-plenigo_failed-plugin.js', __file__);
        $plugin_array['plenigo_separator'] = plugins_url('../plenigo_js/tinymce-plenigo_separator-plugin.js', __file__);
        $plugin_array['plenigo_show'] = plugins_url('../plenigo_js/tinymce-plenigo_show-plugin.js', __file__);
        $plugin_array['plenigo_hide'] = plugins_url('../plenigo_js/tinymce-plenigo_hide-plugin.js', __file__);
        return $plugin_array;
    }

    /**
     * Handles the short code content generation. This method is the responsible for
     * asking the user for payment, evaluating if user has bought the product and show
     * the contents of the short code if the user has bought the product or free views are left.
     *
     * @param  array  $atts    an associative array of attributes, or an empty string if no attributes are given
     * @param  string $content the enclosed content (if the shortcode is used in its enclosing form)
     * @param  string $tag     the shortcode tag, useful for shared callback functions
     * @return string the contents of the shortcode or a button to pay for it
     */
    public function plenigo_handle_shortcode($atts, $content = null, $tag = null) {
        $a = shortcode_atts(array(
            'title' => "",
            'prod_id' => "",
            'class' => "",
                ), $atts);

        $btnTitle = $a['title'];
        $cssClass = $a['class'];
        $prodId = $a['prod_id'];
        $isIgnoringTag = ($tag == 'pl_checkout_button' || $tag == 'pl_renew');

        //evaluate the condition
        $renderButton = true;
        if ($isIgnoringTag) {
            plenigo_log_message("Got ignoring shortcode, rendering button", E_USER_NOTICE);
            $renderButton = true;
        } else {
            plenigo_log_message("Shortcode not ignoring, checking if bought", E_USER_NOTICE);
            $isBought = ($prodId !== "" && PlenigoSDKManager::get()->plenigo_bought($prodId));
            if ($isBought) {
                plenigo_log_message("Product bought: " . $prodId, E_USER_NOTICE);
                $renderButton = false;
            } else {
                plenigo_log_message("Product NOT bought: " . $prodId, E_USER_NOTICE);
                $renderButton = true;
            }
        }

        if ($renderButton) { //Do the plenigo checkout button
            if (!isset($this->options['test_mode']) || ($this->options['test_mode'] == 1 )) {
                $testMode = 'true';
            } else {
                $testMode = 'false';
            }
            if (!isset($this->options['use_login']) || ($this->options['use_login'] == 0 )) {
                $useOauthLogin = false;
            } else {
                $useOauthLogin = true;
            }
            $btnOnClick = "alert('The button was not configured correctly')";

            // If failed payment tag, the product ID doesnt make sense 
            // and we should not make a search for the title based in this fake ID
            if ($tag === 'pl_failed') {
                $btnTitle = ($btnTitle === "") ? __('Failed Payments', self::PLENIGO_SETTINGS_GROUP) : $btnTitle;
                $prodId = "FAKE_PROD_ID";
            }

            if ($prodId !== "") {
                if ($btnTitle === "") {
                    $btnTitle = $this->getButtonTitle($prodId);
                }

                // creating a plenigo-managed product
                if ($tag === 'pl_failed') {
                    $product = \plenigo\models\ProductBase::buildFailedPaymentProduct();
                } else {
                    $product = new \plenigo\models\ProductBase($prodId);
                }

                if ($tag === 'pl_renew') {
                    $product->setSubscriptionRenewal(true);
                }

                // getting the CSRF Token
                $csrfToken = PlenigoSDKManager::get()->get_csrf_token();
                try {

                    // creating the checkout snippet for this product
                    $checkoutBuilder = new \plenigo\builders\CheckoutSnippetBuilder($product);

                    $coSettings = array('csrfToken' => $csrfToken, 'testMode' => $testMode);
                    if ($useOauthLogin) {
                        // this url must be registered in plenigo
                        $coSettings['oauth2RedirectUrl'] = $this->options['redirect_url'];
                        plenigo_log_message("url: " . $coSettings['oauth2RedirectUrl']);
                    }

                    // checkout snippet
                    $btnOnClick = $checkoutBuilder->build($coSettings);
                } catch (\Exception $exc) {
                    plenigo_log_message($exc->getMessage() . ': ' . $exc->getTraceAsString(), E_USER_WARNING);
                    error_log($exc->getMessage() . ': ' . $exc->getTraceAsString());
                }
            }
            return '<input type="button" id="submit" '
                    . ' class="button button-primary ' . $cssClass . '" '
                    . ' value="' . $btnTitle . '" '
                    . ' onclick="' . $btnOnClick . '" />';
        } else { //Return the content untouched
            return do_shortcode($content);
        }
    }

    public function plenigo_handle_content_shortcode($atts, $content = null, $tag = null) {
        $a = shortcode_atts(array(
            'prod_id' => "",
            'class' => "",
                ), $atts);

        $cssClass = $a['class'];
        $prodId = $a['prod_id'];
        $showContent = false;
        $returnString = "";
        $isBought = ($prodId !== "" && PlenigoSDKManager::get()->plenigo_bought($prodId));

        if ($tag == 'pl_content_show') {
            if ($isBought) {
                $showContent = true;
            } else {
                $showContent = false;
            }
        }
        if ($tag == 'pl_content_hide') {
            if ($isBought) {
                $showContent = false;
            } else {
                $showContent = true;
            }
        }
        if ($showContent) {
            if ($cssClass != "") {
                $returnString+='<div class="' . $cssClass . '">';
            }

            $returnString+=do_shortcode($content);

            if ($cssClass != "") {
                $returnString+='</div>';
            }
        }

        return $returnString;
    }

    /**
     * This shortcode allows to show the user profile template with the data provided 
     * from the plenigo SDK for the current, logged in, user. The content of the shortcode 
     * can be used to customize the message for logged out users or users that doesnt have 
     * plenigo information attached to it.
     * 
     * @param  array  $atts    an associative array of attributes, or an empty string if no attributes are given
     * @param  string $content the enclosed content (if the shortcode is used in its enclosing form)
     * @param  string $tag     the shortcode tag, useful for shared callback functions
     * @return string the contents of the shortcode or the user profile
     */
    public function plenigo_handle_user_shortcode($atts, $content = null, $tag = null) {
        $a = shortcode_atts(array(
            'class' => "",
                ), $atts);

        $loggedIn = UserService::isLoggedIn();
        //If it's logged in the we should the user profile template
        if ($loggedIn) {
            $user = PlenigoSDKManager::get()->getCacheValue("plenigo_user_data");
            $userLoggedIn = UserService::getCustomerInfo();
            if (!is_null($user) && !is_null($userLoggedIn)) {
                return $this->get_profile_code($user);
            } else {
                return do_shortcode($content);
            }
        } else { // Else we show the shortcode contents to allow customize the logged out message
            return do_shortcode($content);
        }
    }

    /**
     * Fancy method to get the Button Title from the product with a backend call to obtain the managed product's information
     * 
     * @param string $prodId
     * @return string
     */
    private function getButtonTitle($prodId) {
        $prodName = 'Unknown product';
        $prodPrice = '??.??';
        // get product data
        try {
            $productData = \plenigo\services\ProductService::getProductData($prodId);
            $prodName = $productData->getTitle();
            if ($productData->isPriceChosen()) {
                $prodPrice = __('Choose payment!', self::PLENIGO_SETTINGS_GROUP);
            } else {
                $prodPrice = $productData->getCurrency() . ' ' . sprintf("%06.2f", $productData->getPrice());
            }
        } catch (\Exception $exc) {
            plenigo_log_message($exc->getMessage() . ': ' . $exc->getTraceAsString(), E_USER_WARNING);
            error_log($exc->getMessage() . ': ' . $exc->getTraceAsString());
        }
        return $prodName . " (" . $prodPrice . ")";
    }

    /**
     * Get the Profile Template, replaces the tags with appropiate data from the 
     * UserData parameter and return the HTML for rendering.
     * 
     * @param UserData $user The userdata to replace the Tags in the HTML
     * @return string teh HTML template to be shown in the content
     */
    private function get_profile_code($user) {
        $profile_file = $this->locate_plenigo_template('plenigo-user-profile.html');
        $profileTpl = 'ERROR:not found(' . $profile_file . ')';
        if (!is_null($profile_file)) {
            $profileTpl = file_get_contents($profile_file);
            if ($profileTpl !== FALSE) {
                $profileTpl = $this->replace_profile_tags($profileTpl,$user);
            }
        }

        return $profileTpl;
    }
    
    /**
     * This method locates the file in theme directories if overriden, or gets it from the template directory
     *
     * @param  string $fileName name of the file that's needed and will be located
     * @return string The located filename with full path in order to read the file, NULL if there was a problem
     */
    private function locate_plenigo_template($fileName) {
        if (!is_null($fileName)) {
            $themed_template = locate_template($fileName);
            if (!is_null($themed_template) && is_string($themed_template) && $themed_template !== '') {
                plenigo_log_message("Template from Theme: " + $fileName);
                return $themed_template;
            } else {
                plenigo_log_message("Template from Plugin: " + $fileName);
                return dirname(__FILE__) . '/../plenigo_template/' . $fileName;
            }
        }

        return null;
    }

    /**
     * 
     * 
     * @param string $profileTpl The template to replace Tags from
     * @param \plenigo\models\UserData $user The UserData with the customer information
     */
    public function replace_profile_tags($profileTpl, $user) {
        $html = $profileTpl;
        
        $html = str_ireplace(self::REPLACE_PLUGIN_DIR, plugins_url('', dirname(__FILE__)), $html);
        
        if(!is_null($user) && $user instanceof UserData){
            $html = str_ireplace(self::REPLACE_PROFILE_TITLE, __('User Profile', self::PLENIGO_SETTINGS_GROUP), $html);
            $html = str_ireplace(self::REPLACE_TITLE_CUSTNO, __('Customer No.:', self::PLENIGO_SETTINGS_GROUP), $html);
            $html = str_ireplace(self::REPLACE_TITLE_EMAIL, __('Email Address:', self::PLENIGO_SETTINGS_GROUP), $html);
            $html = str_ireplace(self::REPLACE_TITLE_USERNAME, __('User Name:', self::PLENIGO_SETTINGS_GROUP), $html);
            $html = str_ireplace(self::REPLACE_TITLE_GENDER, __('Gender:', self::PLENIGO_SETTINGS_GROUP), $html);
            $html = str_ireplace(self::REPLACE_TITLE_NAME, __('Full Name:', self::PLENIGO_SETTINGS_GROUP), $html);
            $html = str_ireplace(self::REPLACE_TITLE_FIRST, __('First Name:', self::PLENIGO_SETTINGS_GROUP), $html);
            $html = str_ireplace(self::REPLACE_TITLE_LAST, __('Last Name:', self::PLENIGO_SETTINGS_GROUP), $html);
            $html = str_ireplace(self::REPLACE_TITLE_STREET, __('Street Address:', self::PLENIGO_SETTINGS_GROUP), $html);
            $html = str_ireplace(self::REPLACE_TITLE_ADDINFO, __('Additional Information:', self::PLENIGO_SETTINGS_GROUP), $html);
            $html = str_ireplace(self::REPLACE_TITLE_ZIP, __('ZIP Code:', self::PLENIGO_SETTINGS_GROUP), $html);
            $html = str_ireplace(self::REPLACE_TITLE_CITY, __('City:', self::PLENIGO_SETTINGS_GROUP), $html);
            $html = str_ireplace(self::REPLACE_TITLE_COUNTRY, __('Country:', self::PLENIGO_SETTINGS_GROUP), $html);
            $html = str_ireplace(self::REPLACE_TITLE_PLENIGO_PROFILE, __('Edit this profile at plenigo', self::PLENIGO_SETTINGS_GROUP), $html);
            
            $html = str_ireplace(self::REPLACE_CLICK_PLENIGO_PROFILE, "", $html);
            $html = str_ireplace(self::REPLACE_VALUE_CUSTNO, $user->getId(), $html);
            $html = str_ireplace(self::REPLACE_VALUE_EMAIL, $user->getEmail(), $html);
            $html = str_ireplace(self::REPLACE_VALUE_USERNAME, $user->getUsername(), $html);
            $html = str_ireplace(self::REPLACE_VALUE_GENDER, $user->getGender(), $html);
            $html = str_ireplace(self::REPLACE_VALUE_FIRST, $user->getFirstName(), $html);
            $html = str_ireplace(self::REPLACE_VALUE_LAST, $user->getLastName(), $html);
            $html = str_ireplace(self::REPLACE_VALUE_STREET, $user->getAddress()->getStreet(), $html);
            $html = str_ireplace(self::REPLACE_VALUE_ADDINFO, $user->getAddress()->getAdditionalAddressInfo(), $html);
            $html = str_ireplace(self::REPLACE_VALUE_ZIP, $user->getAddress()->getPostCode(), $html);
            $html = str_ireplace(self::REPLACE_VALUE_CITY, $user->getAddress()->getCity(), $html);
            $html = str_ireplace(self::REPLACE_VALUE_COUNTRY, strtoupper($user->getAddress()->getCountry()), $html);
            $html = str_ireplace(self::REPLACE_VALUE_COUNTRY_LCASE, strtolower($user->getAddress()->getCountry()), $html);
        }
        
        return $html;
    }
}