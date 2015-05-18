<?php

namespace plenigo_plugin;

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
                array_push($buttons, 'separator', 'plenigo', 'plenigo_renew', 'plenigo_separator');
            }
        }
        return $buttons;
    }

    function plenigo_register_tinymce_js($plugin_array) {
        $plugin_array['plenigo'] = plugins_url('../plenigo_js/tinymce-plenigo-plugin.js', __file__);
        $plugin_array['plenigo_renew'] = plugins_url('../plenigo_js/tinymce-plenigo_renew-plugin.js', __file__);
        $plugin_array['plenigo_separator'] = plugins_url('../plenigo_js/tinymce-plenigo_separator-plugin.js', __file__);
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
        if ($tag !== 'pl_checkout_button' && $prodId !== "" && PlenigoSDKManager::get()->plenigo_bought($prodId)) { //Return the content untouched
            return do_shortcode($content);
        } else { //Do the plenigo checkout button
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
            if ($prodId !== "") {
                if ($btnTitle === "") {
                    $btnTitle = $this->getButtonTitle($prodId);
                }

                // creating a plenigo-managed product
                $product = new \plenigo\models\ProductBase($prodId);

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

}
