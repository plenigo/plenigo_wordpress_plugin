<?php

namespace plenigo_plugin;

use \plenigo\services\UserService;
use \plenigo\services\AppManagementService;
use \plenigo\models\UserData;

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
    const PLENIGO_PROFILE_URL = "https://www.plenigo.com/customer/personal/data/show";
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
     * A list of tokens that has just been created
     * @var array
     */
    private $tokenList = array();
    private $delTokenList = array();

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
        add_shortcode('pl_snippet', array($this, 'plenigo_handle_snippet_shortcode'));
        add_shortcode('pl_mobile_admin', array($this, 'plenigo_handle_mobile_admin'));

        //TinyMCE
        // add new buttons
        add_filter('mce_buttons', array($this, 'plenigo_register_buttons'));
        // Load the TinyMCE plugin
        add_filter('mce_external_plugins', array($this, 'plenigo_register_tinymce_js'));

        // Enqueue TinyMCE CSS
        add_action('admin_enqueue_scripts', array($this, 'add_scripts'));
        add_action('admin_init', array($this, 'plenigo_add_editor_styles'));

        // Mobile Token Scripts
        add_action('wp_enqueue_scripts', array($this, 'add_mtoken_scripts'));
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
                array_push($buttons, 'separator', 'plenigo', 'plenigo_renew', 'plenigo_failed', 'plenigo_separator', 'plenigo_snippet');
            }
        }
        return $buttons;
    }

    function plenigo_register_tinymce_js($plugin_array) {
        $plugin_array['plenigo'] = plugins_url('../plenigo_js/tinymce-plenigo-plugin.js', __file__);
        $plugin_array['plenigo_renew'] = plugins_url('../plenigo_js/tinymce-plenigo_renew-plugin.js', __file__);
        $plugin_array['plenigo_failed'] = plugins_url('../plenigo_js/tinymce-plenigo_failed-plugin.js', __file__);
        $plugin_array['plenigo_separator'] = plugins_url('../plenigo_js/tinymce-plenigo_separator-plugin.js', __file__);
        $plugin_array['plenigo_snippet'] = plugins_url('../plenigo_js/tinymce-plenigo_snippet-plugin.js', __file__);
        // TODO add missing JS files
        //$plugin_array['plenigo_show'] = plugins_url('../plenigo_js/tinymce-plenigo_show-plugin.js', __file__);
        //$plugin_array['plenigo_hide'] = plugins_url('../plenigo_js/tinymce-plenigo_hide-plugin.js', __file__);
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
            'register' => "0"
                ), $atts);

        $btnTitle = $a['title'];
        $cssClass = $a['class'];
        $prodId = $a['prod_id'];
        $regCheck = $a['register'];
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
            if ($regCheck == "1") {
                $useRegister = false;
            } else {
                $useRegister = true;
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
                    $btnOnClick = $checkoutBuilder->build($coSettings, null, $useRegister);
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
        if (is_null($content) || $content === FALSE || !is_string($content)) {
            $content = "";
        }

        $isBought = ($prodId !== "" && PlenigoSDKManager::get()->plenigo_bought(explode(',', $prodId)));

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
                $returnString .= '<div class="' . $cssClass . '">';
            }

            $returnString .= do_shortcode($content);

            if ($cssClass != "") {
                $returnString .= '</div>';
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
            $user = PlenigoSDKManager::get()->getSessionValue("plenigo_user_data");
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

    public function plenigo_handle_snippet_shortcode($atts, $content = null, $tag = null) {
        $a = shortcode_atts(array(
            'name' => ""
                ), $atts);
        $arr_types = array();
        $arr_types[] = "plenigo.Snippet.PERSONAL_DATA";
        $arr_types[] = "plenigo.Snippet.ORDER";
        $arr_types[] = "plenigo.Snippet.SUBSCRIPTION";
        $arr_types[] = "plenigo.Snippet.PAYMENT_METHODS";
        $arr_types[] = "plenigo.Snippet.ADDRESS_DATA";

        $startTag = '<script type="text/javascript">' . "\n";
        $endTag = '</script>';
        $startJQuery = 'jQuery(document).ready(function($) {';
        $endJQuery = '});';
        if (stristr($a['name'], "all")) {
            foreach ($arr_types as $snippet) {
                $genID = uniqid("snip");
                $content.='<div id="' . $genID . '"></div>' . "\n";
                $content.=$startTag . "\n" . $startJQuery . "\n";
                $content.='console.log("rendering snippet: ' . $snippet . '");' . "\n";
                $content.='plenigo.renderSnippet("' . $genID . '","' . $snippet . '");' . "\n";
                $content.=$endJQuery . "\n" . $endTag;
            }
        } else {
            if (in_array($a['name'], $arr_types)) {
                $genID = uniqid("snip");
                $content.='<div id="' . $genID . '"></div>' . "\n";
                $content.=$startTag . "\n" . $startJQuery . "\n";
                $content.='console.log("rendering snippet: ' . $a['name'] . '");' . "\n";
                $content.='plenigo.renderSnippet("' . $genID . '","' . $a['name'] . '");' . "\n";
                $content.=$endJQuery . "\n" . $endTag;
            }
        }

        return do_shortcode($content);
    }

    /**
     * Draws a table with all the product bought and their Mobile Application Id
     * 
     * @param  array  $atts    an associative array of attributes, or an empty string if no attributes are given
     * @param  string $content the enclosed content (if the shortcode is used in its enclosing form)
     * @param  string $tag     the shortcode tag, useful for shared callback functions
     * @return string the contents of the shortcode or the mobile administration
     */
    public function plenigo_handle_mobile_admin($atts, $content = null, $tag = null) {
        plenigo_log_message("Plenigo Mobile Token Admin: START");
        $a = shortcode_atts(array(
            'class' => "",
                ), $atts);
        plenigo_log_message("Plenigo Mobile Token Admin: CHECKING LOGIN");
        $loggedIn = UserService::isLoggedIn();
        $notLoggedMesage = "The user is not logged in with plenigo";
        //If it's logged in the we should the user profile template
        if ($loggedIn) {
            plenigo_log_message("Plenigo Mobile Token Admin: LOGGED IN");
            $user = PlenigoSDKManager::get()->getSessionValue("plenigo_user_data");
            $userLoggedIn = UserService::getCustomerInfo();
            if (!is_null($user) && !is_null($userLoggedIn)) {
                plenigo_log_message("Plenigo Mobile Token Admin: RENDERING");
                return $this->render_mobile_admin($user, $a["class"]);
            } else {
                plenigo_log_message("Plenigo Mobile Token Admin: NOT PLENIGO?");
                return '(' . __($notLoggedMesage, self::PLENIGO_SETTINGS_GROUP) . ')';
            }
        } else { // Else we show the shortcode contents to allow customize the logged out message
            plenigo_log_message("Plenigo Mobile Token Admin: NOT LOGGED IN");
            return '(' . __($notLoggedMesage, self::PLENIGO_SETTINGS_GROUP) . ')';
        }
        plenigo_log_message("Plenigo Mobile Token Admin: END");
    }

    public function add_mtoken_scripts() {
        plenigo_log_message("Plenigo Mobile Token Admin: REGISTER SCRIPT");
        wp_register_script('plenigo-mtoken-js', plugins_url('plenigo_js/pl_mtoken.js', dirname(__FILE__)), array('jquery'), '5', true);
        wp_enqueue_script('plenigo-mtoken-js');
    }

    public function render_mobile_admin(UserData $user, $className) {
        $res = '';
        $customerID = $user->getId();
        $arrProducts = UserService::getProductsBought($customerID);
        $arrProdStruct = array();
        // Add Single Products
        if (isset($arrProducts['singleProducts']) && count($arrProducts['singleProducts']) > 0) {
            foreach ($arrProducts['singleProducts'] as $sglProduct) {
                // Product purchase has not been cancelled
                if (property_exists($sglProduct, "status") && $sglProduct->status == 'CANCELLED') {
                    continue;
                }
                // Other checks made in "has_bought" query to the services
                if (PlenigoSDKManager::get()->plenigo_bought($sglProduct->productId) === FALSE) {
                    continue;
                }
                //Create the product entry
                $arrProdStruct[$sglProduct->productId] = array(
                    "type" => "PRODUCT",
                    "title" => $sglProduct->title,
                    "date" => $sglProduct->buyDate,
                    "appids" => array(),
                    "token" => ""
                );
            }
        }
        // Add subcriptions
        if (isset($arrProducts['subscription']) && count($arrProducts['subscription']) > 0) {
            foreach ($arrProducts['subscription'] as $subProduct) {
                // Subscriptions are not cancelled
                if (property_exists($subProduct, "cancellationDate") &&
                        is_string($subProduct->cancellationDate) &&
                        strlen($subProduct->cancellationDate) != 0) {
                    continue;
                }
                // Check subscription period
                $startDate = (property_exists($subProduct, "startDate")) ? strtotime($subProduct->startDate) : strtotime("-1 day");
                $endDate = (property_exists($subProduct, "endDate")) ? strtotime($subProduct->endDate) : strtotime("+1 day");
                $today = strtotime("now");
                if ($today < $startDate || $today > $endDate) {
                    continue;
                }
                $arrProdStruct[$subProduct->productId] = array(
                    "type" => "SUBSCRIPTION",
                    "title" => $subProduct->title,
                    "date" => $subProduct->endDate,
                    "appids" => array(),
                    "token" => ""
                );
            }
        }
        if (count($arrProdStruct) > 0) {
            $arrAppids = AppManagementService::getCustomerApps($customerID);
            foreach ($arrAppids as $appid) {
                $currPID = $appid->getProductId();
                $currAID = $appid->getCustomerAppId();
                $currDES = $appid->getDescription();
                if (isset($arrProdStruct[$currPID])) {
                    $arrProdStruct[$currPID]["appids"][$currAID] = array(
                        "appid" => $currAID,
                        "desc" => $currDES,
                        "new" => false
                    );
                }
            }
            try {
                $arrProdStruct = $this->add_del_mobile_aid($customerID, $arrProdStruct);
            } catch (Exception $exc) {
                $res.='(' . __("Could not create or delete App ID.", self::PLENIGO_SETTINGS_GROUP) . ')<br/>';
            }
        }

        if (count($arrProdStruct) > 0) {
            $res.= $this->add_mobile_admin_row(
                    __("Product ID", self::PLENIGO_SETTINGS_GROUP)
                    , __("Product Name", self::PLENIGO_SETTINGS_GROUP)
                    , __("Mobile Code", self::PLENIGO_SETTINGS_GROUP)
                    , true, false);

            foreach ($arrProdStruct as $currPID => $currPIDdata) {
                $mobileAppIdCode = $this->get_mobile_admin_code($currPIDdata["appids"], $customerID, $currPID);
                $res.= $this->add_mobile_admin_row(
                        $this->elipsize($currPID)
                        , $this->elipsize($currPIDdata["title"])
                        , $mobileAppIdCode);
            }
            $res.= $this->add_mobile_admin_row(null, null, null, false, true);
        } else {
            $res.='(' . __("There are no products bought available for use", self::PLENIGO_SETTINGS_GROUP) . ')<br/>';
        }
        //Add javascript message, with translation support
        $res.= "\n" . '<script>var pl_mtoken_remove_msg = "' . __("Are you sure you want to remove this App ID?", self::PLENIGO_SETTINGS_GROUP) . '";</script>';

        return $res;
    }

    private function add_mobile_admin_row($idColumn, $nameColumn, $mobileColumn, $startTable = false, $endTable = false) {
        $res = "";
        $tdTag = "td";
        if ($startTable) {
            plenigo_log_message("Adding ROW First");
            $res.= '<table class="table table-bordered table-striped"><thead>';
            $tdTag = "th";
        }
        if (!is_null($idColumn)) {
            $res.='<tr><' . $tdTag . '>' . $idColumn . '</' . $tdTag . '><' . $tdTag . '>' . $nameColumn . '</' . $tdTag . '><' . $tdTag . '>' . $mobileColumn . '</' . $tdTag . '></tr>';
        }
        if ($startTable) {
            $res.='</thead><tbody>';
        }
        if ($endTable) {
            plenigo_log_message("Adding ROW Last");
            $res.='</tbody></table>';
        }
        return $res;
    }

    private function get_mobile_admin_code($arrAppID, $customerID, $productId) {
        $requestButton = '<button class="btn btn-success" type="button" onclick="plenigo_create_mtoken(\'' . $productId . '\',\'' . $customerID . '\');return false;">' . __("Create", self::PLENIGO_SETTINGS_GROUP) . '</button>';
        $deleteButton = '<button class="btn btn-danger" type="button" onclick="plenigo_remove_mtoken(\'' . $productId . '\',\'' . $customerID . '\',\'[REP-APP-ID]\');return false;">' . __("Remove", self::PLENIGO_SETTINGS_GROUP) . '</button>';
        $descInputName = 'plenigo_' . $productId . '_desc';
        $descInput = '<input type="text" class="form-control" id="' . $descInputName . '" maxlength="30" size="25" name="' . $descInputName . '" placeholder="' . __("Device Description", self::PLENIGO_SETTINGS_GROUP) . '"/>';
        $res = "";
        if (is_array($arrAppID) && count($arrAppID) > 0) {
            foreach ($arrAppID as $currAID => $currAIDdata) {
                $res.='<div class="plenigoMToken">';
                if ($currAIDdata["new"]) {
                    $res .= __("Your Token", self::PLENIGO_SETTINGS_GROUP) . ': <input type="text" class="form-control" readonly="true" value="' . $currAID . '"/> ' . $deleteButton;
                } else {
                    $res .= __("Created for", self::PLENIGO_SETTINGS_GROUP) . ": " . $currAIDdata["desc"] . " " . str_replace("[REP-APP-ID]", $currAID, $deleteButton);
                }
                $res.='</div>';
            }
        }
        $res.='<div class="plenigoMToken plenigoTokenCreate">';
        $res.= __("Create for", self::PLENIGO_SETTINGS_GROUP) . ": " . $descInput . " " . $requestButton;
        $res.='</div>';
        return $res;
    }

    private function add_del_mobile_aid($customerID, $arrProdStruct = array()) {
        $paramCID = filter_input(INPUT_GET, "mobileCID");
        $paramDEV = filter_input(INPUT_GET, "mobileDEV", FILTER_SANITIZE_SPECIAL_CHARS);
        $paramPID = filter_input(INPUT_GET, "mobilePID");
        $paramREM = filter_input(INPUT_GET, "removeAID");

        if ($paramCID == $customerID) {
            plenigo_log_message("Mobile App Editor: Customer check OK");
            if (!is_null($paramREM) && $paramREM !== FALSE) {
                plenigo_log_message("Mobile App Editor: Removing current App ID");
                AppManagementService::deleteCustomerApp($customerID, $paramREM);
                // Remove the AppID from the Struct
                if (isset($arrProdStruct[$paramPID]["appids"][$paramREM])) {
                    unset($arrProdStruct[$paramPID]["appids"][$paramREM]);
                }
            } else {
                plenigo_log_message("Mobile App Editor: Getting App Token CID=" . $customerID . " PID=" . $paramPID);
                $appToken = AppManagementService::requestAppToken($customerID, $paramPID, $paramDEV);
                if (!is_null($appToken)) {
                    $token = $appToken->getAppToken();
                    plenigo_log_message("Mobile App Editor: Generated Token: " . $token);
                    $this->tokenList[$paramPID] = $token;
                    $arrProdStruct[$paramPID]["appids"][$token] = array(
                        "appid" => $token,
                        "desc" => $paramDEV,
                        "new" => true
                    );
                } else {
                    plenigo_log_message("Mobile App Editor: Can't create App token");
                }
            }
        }
        return $arrProdStruct;
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
                $profileTpl = $this->replace_profile_tags($profileTpl, $user);
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

        if (!is_null($user) && $user instanceof UserData) {
            $html = str_ireplace(self::REPLACE_PROFILE_TITLE, __('User Profile', self::PLENIGO_SETTINGS_GROUP), $html);
            $html = str_ireplace(self::REPLACE_TITLE_CUSTNO, __('Customer ID:', self::PLENIGO_SETTINGS_GROUP), $html);
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

            $html = str_ireplace(self::REPLACE_CLICK_PLENIGO_PROFILE, self::PLENIGO_PROFILE_URL, $html);
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

    private function elipsize($strText) {
        return strlen($strText) > 50 ? substr($strText, 0, 47) . "..." : $strText;
    }

}
