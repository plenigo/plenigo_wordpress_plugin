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

namespace plenigo_plugin\wooCommerce;

/**
 * Main class for interfacing as a WooCommerce Payment Gateway
 *
 * @category WordPressPlugin
 * @package  plenigoPluginWooCommerce
 * @link     https://plenigo.com
 */
class WC_Gateway_Plenigo extends \WC_Payment_Gateway {

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options = null;
    // Available product types
    private $prodTypeList = array('EBOOK', 'DIGITALNEWSPAPER', 'DOWNLOAD', 'VIDEO', 'MUSIC');

    //CONSTANTS
    //Plenigo settings group
    const PLENIGO_SETTINGS_GROUP = 'plenigo';
    const PLENIGO_SETTINGS_NAME = 'plenigo_settings';
    //Order title replacements
    const ORDER_SITE_TITLE = '%%SITE_TITLE%%';
    const ORDER_ORDER_ID = '%%ORDER_ID%%';
    const ORDER_ORDER_NO = '%%ORDER_NO%%';
    const ORDER_ORDER_KEY = '%%ORDER_KEY%%';
    const ORDER_PROD_NAMES = '%%PROD_NAMES%%';
    const ORDER_PROD_IDS = '%%PROD_IDS%%';
    const ORDER_PROD_SKUS = '%%PROD_SKUS%%';

    /**
     * Holds the String[] to be rendered for debug checklist.
     *
     * @var array
     */
    private $debugChecklist = array();

    public function __construct() {
        //Plenigo Options
        $this->options = get_option(self::PLENIGO_SETTINGS_NAME, array());

        // This plugin supports....
        $this->supports = array('products');

        //Definitions
        $this->id = 'plenigo_gateway';
        $this->icon = "https://www.plenigo.com/assets/favicon.ico";
        $this->has_fields = true;
        $this->enabled = true;
        $this->title = __('plenigo Payment', self::PLENIGO_SETTINGS_GROUP);
        $this->method_title = __('plenigo Payment', self::PLENIGO_SETTINGS_GROUP);
        $this->description = __('WooCommerce plenigo Description', self::PLENIGO_SETTINGS_GROUP);
        $this->method_description = __('WooCommerce plenigo Description', self::PLENIGO_SETTINGS_GROUP);
        $this->order_button_text = __('Pay with plenigo', self::PLENIGO_SETTINGS_GROUP);

        // Initialize administration
        $this->init_form_fields();
        $this->init_settings();
        // Saving options
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

        add_action('woocommerce_before_template_part', array($this, 'plenigo_finish'), 20);

        // Processing the redirection
        add_action('woocommerce_thankyou_' . $this->id, array($this, 'plenigo_checkout_process'), 20);
    }

    /**
     * Checks the conditions and triggers the process finalization, checking if the product is bought.
     */
    public function plenigo_finish() {
        $varCheckout = is_checkout();
        $checkout_param = is_wc_endpoint_url('order-received');
        $payment_param = filter_input(INPUT_GET, 'paymentState');

        if (is_null($payment_param)) {
            $payment_param = FALSE;
        }
        if (is_null($checkout_param)) {
            $checkout_param = FALSE;
        }

        //Payment finished
        if ($payment_param !== FALSE && $checkout_param !== FALSE && $varCheckout === TRUE) {
            plenigo_log_message("WOO: Finishing checkout order: " . var_export($checkout_param, true), E_USER_NOTICE);
            $this->addDebugLine("Finishing checkout order");
            $this->plenigo_buy_confirm($checkout_param);
            $this->printDebugChecklist();
        }
    }

    /**
     * Placeholder function for the settings panel inside the WooCommerce settings
     */
    public function process_admin_options() {
        $this->addDebugLine("Enteres options");
        $this->printDebugChecklist();
    }

    public function process_payment($order_id) {
        global $woocommerce;
        $order = new \WC_Order($order_id);

        // Mark as on-hold (we're awaiting the payment)
        $order->update_status('on-hold', __('Awaiting plenigo checkout process', self::PLENIGO_SETTINGS_GROUP));

        // Return thank you (receipt) page redirect
        return array(
            'result' => 'success',
            'redirect' => $this->get_return_url($order)
        );
    }

    /**
     * After the JavaScript SDk is loaded, if we have to process an order, let's generate the checkout snippet and execute it...
     */
    public function plenigo_checkout_process() {
        global $woocommerce;

        $checkout_param = is_wc_endpoint_url('order-received');
        $payment_param = filter_input(INPUT_GET, 'paymentState');

        if (is_null($payment_param)) {
            $payment_param = FALSE;
        }
        if (is_null($checkout_param)) {
            $checkout_param = FALSE;
        }

        $order_id = $this->get_current_order_id();

        plenigo_log_message('WOO: Checkout=' . var_export($checkout_param, true));
        plenigo_log_message('WOO: Payment=' . var_export($payment_param, true));

        //Checkout start
        if ($checkout_param !== FALSE && $payment_param === FALSE && $order_id !== FALSE) {
            plenigo_log_message("WOO: Processing checkout order: " . var_export($checkout_param, true), E_USER_NOTICE);
            $order = new \WC_Order($order_id);
            $this->addDebugLine("Order Received URL:" . var_export($order->get_checkout_order_received_url(), true));

            $user_bought = \plenigo_plugin\PlenigoSDKManager::get()->plenigo_bought($order->id . "");

            $this->addDebugLine("Processing checkout order");
            if (!$user_bought) {
                $this->addDebugLine("Order wasn't payed!");
                // Mark as processing (checkout process)
                $order->update_status('pending', __('plenigo checkout stating', self::PLENIGO_SETTINGS_GROUP));
                plenigo_log_message("WOO: Creating checkout snippet:", E_USER_NOTICE);

                //Let's create a unmanaged Plenigo product for this order
                $sdk = \plenigo_plugin\PlenigoSDKManager::get()->getPlenigoSDK();
                if (!is_null($sdk) && ($sdk instanceof \plenigo\PlenigoManager)) {
                    if (!isset($this->options['use_login']) || ($this->options['use_login'] == 0 )) {
                        $this->addDebugLine("Using OAuth login");
                        $useOauthLogin = false;
                    } else {
                        $useOauthLogin = true;
                    }
                    $csrfToken = \plenigo_plugin\PlenigoSDKManager::get()->get_csrf_token();
                    $product = $this->get_product_checkout($order);
                    if (!is_null($product)) {
                        $this->addDebugLine("Product is valid");
                    } else {
                        $this->addDebugLine("Product is NOT valid");
                    }

                    // creating the checkout snippet for this product
                    $checkoutBuilder = new \plenigo\builders\CheckoutSnippetBuilder($product);

                    $coSettings = array('csrfToken' => $csrfToken);
                    if ($useOauthLogin) {
                        // this url must be registered in plenigo
                        $current_url = \plenigo_plugin\PlenigoURLManager::get(array("paymentState"))->getSanitizedURL();
                        $coSettings['oauth2RedirectUrl'] = $current_url;
                    }

                    // checkout snippet
                    $checkoutSnippet = '';
                    try {
                        $checkoutSnippet.= $checkoutBuilder->build($coSettings);
                        $checkoutSnippet = htmlspecialchars ($checkoutSnippet, ENT_QUOTES, 'UTF-8',false);

                    } catch (Exception $exc) {
                        plenigo_log_message($exc->getMessage() . ': ' . $exc->getTraceAsString(), E_USER_WARNING);
                        error_log($exc->getMessage() . ': ' . $exc->getTraceAsString());
                        $this->addDebugLine("Error creating the cehckout snnippet: " . $exc->getMessage());
                        wc_add_notice($exc->getMessage(), 'error');
                    }
                    plenigo_log_message("WOO: Checkout snippet:" . var_export($checkoutSnippet, true), E_USER_NOTICE);
                    echo '<div style="width:100%;text-align:right;">'
                    . '<button class="checkout-button button alt wc-forward" onclick=\'' . $checkoutSnippet . '\'>'
                    . __('Continue to plenigo checkout', self::PLENIGO_SETTINGS_GROUP)
                    . '</button></div>';
                } else {
                    $errorMessge = __('plenigo not configured, contact the administrators', self::PLENIGO_SETTINGS_GROUP);
                    $this->addDebugLine("Failed: " . $errorMessge);
                    $order->update_status('failed', $errorMessge);
                    wc_add_notice($errorMessge, 'error');
                }
            } else {
                $order->add_order_note(__('You already purchased this order! Thank You!', self::PLENIGO_SETTINGS_GROUP));
                $order->payment_complete();
                $order->update_status('completed', __('plenigo payment complete. Thank you!', self::PLENIGO_SETTINGS_GROUP));
                $this->addDebugLine("Already payed!");
                // If its an anonymous user
                if ($order->customer_user == 0) {
                    $updArray = array();
                    $updArray['order_id'] = $order->id;
                    $updArray['customer_id'] = get_current_user_id();
                    wc_update_order($updArray);
                }
                echo '<div style="width:100%;text-align:right;">'
                . '<button class="checkout-button button alt wc-forward" onclick="javacript:location.href=\''
                . $order->get_view_order_url() . '\';">'
                . __('View your purchase', self::PLENIGO_SETTINGS_GROUP)
                . '</button></div>';
            }
        }
        $this->printDebugChecklist();
    }

    /**
     * Check if this gateway is enabled and available in the user's country
     *
     * @return bool
     */
    public function is_valid_for_use() {
        $prodType = isset($this->options['woo_product_type']) ? $this->options['woo_product_type'] : null;
        return (!is_null($prodType) && in_array($prodType, $this->prodTypeList));
    }

    /**
     * Check if the payment gateway is available and shows the options form
     * 
     */
    public function admin_options() {
        if ($this->is_valid_for_use()) {
            parent::admin_options();
        } else {
            echo '<div class="inline error"><p><strong>';
            echo __('Gateway Disabled', self::PLENIGO_SETTINGS_GROUP) . '</strong>: ';
            echo __('Please complete the configuration in the plenigo settings.', self::PLENIGO_SETTINGS_GROUP);
            echo '</p></div>';
        }
    }

    /**
     * set the form fields
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', self::PLENIGO_SETTINGS_GROUP),
                'type' => 'checkbox',
                'label' => __('Enable Cheque Payment', self::PLENIGO_SETTINGS_GROUP),
                'default' => 'yes'
            )
        );
    }

    /**
     * Detectes if the checkout process finished and then marks the order as paid if necessary
     * @param int $order_id The order ID to check for plenigo user bought
     */
    public function plenigo_buy_confirm($order_id) {
        global $woocommerce;
        $user_ID = get_current_user_id();
        $count = wc_get_customer_order_count($user_ID);
        if ($user_ID > 0 && $count > 0) {
            plenigo_log_message("WOO: The customer has an order!", E_USER_NOTICE);
            $this->addDebugLine("The customer has an order");
            $order = new \WC_Order($order_id);
            $user_bought = \plenigo_plugin\PlenigoSDKManager::get()->plenigo_bought($order_id);
            if ($user_bought === true) {
                plenigo_log_message("WOO: User bought it with plenigo!", E_USER_NOTICE);
                $this->addDebugLine("The order was purchased with plenigo");
                // Set Order as complete
                $order->payment_complete();
                $order->update_status('completed', __('plenigo payment complete. Thank you!', self::PLENIGO_SETTINGS_GROUP));
            } else {
                // Here could be maybe a payment timeout to set it as cancelled
                plenigo_log_message("WOO: User DID NOT buy this with plenigo...yet?!", E_USER_NOTICE);
                $this->addDebugLine("User DID NOT buy this with plenigo...yet?!");
            }
        }
    }

    /**
     * Creates a plenigo unmanaged product with the last product ID as the order ID. 
     * 
     * @param \WooCommerce\Classes\WC_Order $order the order to create the product from
     * @return \plenigo\models\ProductBase The plenigo product Object
     */
    private function get_product_checkout($order = null) {
        $res = null;

        if (!is_null($order) && ($order instanceof \WC_Order)) {
            $this->addDebugLine("There is a valid order!");
            $prodID = (string)$order->id;
            $this->addDebugLine("Order:" . var_export($order, true));
            $title = $this->get_order_title($order);
            $this->addDebugLine("Order Title:" . $title);
            $total = $order->get_total();
            $typeSetting = isset($this->options['woo_product_type']) ? $this->options['woo_product_type'] : null;
            $prodType = ($order->has_downloadable_item() === true) ? \plenigo\models\ProductBase::TYPE_DOWNLOAD : $typeSetting;
            if (!is_null($prodType) && in_array($typeSetting, $this->prodTypeList)) {
                $this->addDebugLine("Product Type:" . $prodType);
            } else {
                $errorMessge = __('Product type is not configured, contact the administrators', self::PLENIGO_SETTINGS_GROUP);
                $order->update_status('failed', $errorMessge);
                $this->addDebugLine("Product NOT configured! Failing order!");
                wc_add_notice($errorMessge, 'error');
            }
            $currency = $order->get_order_currency();

            $res = new \plenigo\models\ProductBase($prodID, $title, $total, $currency);
            if (!is_null($prodType)) {
                plenigo_log_message("WOO: Setting product type:" . $prodType, E_USER_NOTICE);
                $res->setType($prodType);
            }
        }
        return $res;
    }

    /**
     * 
     * @param \WooCommerce\Classes\WC_Order $order the order to create the title
     * @return string the generated title
     */
    private function get_order_title($order) {
        $blog_title = get_bloginfo('name');
        $res = '(' . $blog_title . ') WooCommerce order ID:' . $order->id;
        if (isset($this->options['woo_order_title']) && ($this->options['woo_order_title'] != '')) {
            $format = trim($this->options['woo_order_title']);

            if (stristr($format, '%%') !== FALSE) {
                $rtags = array(
                    self::ORDER_SITE_TITLE,
                    self::ORDER_ORDER_ID,
                    self::ORDER_ORDER_NO,
                    self::ORDER_ORDER_KEY,
                    self::ORDER_PROD_IDS,
                    self::ORDER_PROD_NAMES,
                    self::ORDER_PROD_SKUS);
                $rvalues = array(
                    $blog_title,
                    $order->id,
                    $order->get_order_number(),
                    $order->order_key,
                    $this->get_product_list(0, $order),
                    $this->get_product_list(1, $order),
                    $this->get_product_list(2, $order));

                $res = str_replace($rtags, $rvalues, $format);
            }
        }
        return $res;
    }

    /**
     * 
     * Creates a list for the current order products using the titles (0) the SKU (1) or otherwise the ID 
     * 
     * @param int $field the field to use 0 is the title, 1 is the SKU, otherwise is the ID
     * @param \WooCommerce\Classes\WC_Order $order the order to create the title
     * @return string the generated list
     */
    public function get_product_list($field, $order) {
        $res = '';
        $items = $order->get_items();
        foreach ($items as $curr_item) {
            $curr_prod = $order->get_product_from_item($curr_item);
            if (!is_null($curr_prod)) {
                if ($res != '') {
                    $res.=',';
                }
                switch ($field) {
                    case 1:
                        $res.=$curr_prod->get_title();
                        break;
                    case 2:
                        $res.=$curr_prod->get_sku();
                        break;
                    default:
                        $res.=$curr_prod->id;
                        break;
                }
            }
        }
        return $res;
    }

    /**
     * Adds a line at the end of the debug checklist
     * 
     * @param string $row
     */
    private function addDebugLine($row = null) {
        $res = '';
        if (!is_null($row)) {
            if (!is_string($row) && !is_numeric($row)) {
                $res.=print_r($row, TRUE);
            } else {
                $res.=$row;
            }
        }
        array_push($this->debugChecklist, $res);
    }

    /**
     * Outputs the debug checklist as a HTML comment for debugging purposes, then it clears the array
     */
    private function printDebugChecklist() {
        if (isset($this->options['quiet_report_enabled'])) {
            if (is_array($this->debugChecklist) && count($this->debugChecklist) > 0) {
                echo "<!-- *** Plenigo WooCommerce debug checklist ***\n";
                foreach ($this->debugChecklist as $debugRow) {
                    echo "## - " . $debugRow . " \n";
                }
                echo "// *** Plenigo WooCommerce debug checklist *** -->";
            }
        }
        $this->debugChecklist = array();
    }

    /**
     * Uses several method to assess the current order ID  for this payment page. 
     * This is no simple task since the user may be looking at the order list. 
     * Also there are multiple ways to detect the URL format to parse.
     * 
     * @return int the order id or FALSE if not found
     */
    public function get_current_order_id() {
        // Lets try the order pay method first
        $order_id = absint(get_query_var('order-pay'));

        if ($order_id <= 0) {
            // The try the order key method
            $oKey = filter_input(INPUT_GET, 'key');
            $order_id = absint(wc_get_order_id_by_order_key($oKey));
            if ($order_id <= 0) {
                // Try from the order received query parameter
                $order_id = bsint(filter_input(INPUT_GET, 'order-received'));
                if ($order_id <= 0) {
                    //Try from the order-received path variable
                    $urlArray = parse_url(\plenigo_plugin\PlenigoURLManager::get()->getSanitizedURL());
                    $useNext = false;
                    foreach (split('/', $urlArray['path']) as $value) {
                        if ($useNext) {
                            $order_id = absint($value);
                            break;
                        }
                        if ($value == 'order-received') {
                            $useNext = true;
                        }
                    }
                    if ($order_id <= 0) {
                        $this->addDebugLine("Order ID not found");
                        return FALSE;
                    } else {
                        $this->addDebugLine("Order ID from 'order-received' path variable");
                    }
                } else {
                    $this->addDebugLine("Order ID from 'order-received' query param");
                }
            } else {
                $this->addDebugLine("Order ID from 'key' query param");
            }
        } else {
            $this->addDebugLine("Order ID from 'order-pay'");
        }
        $this->addDebugLine("Order ID :" . var_export($order_id, true));
        return $order_id;
    }

}
