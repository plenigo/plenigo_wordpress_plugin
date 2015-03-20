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
 * @author   Sebastian Dieguez <s.dieguez@plenigo.com>
 * @link     https://plenigo.com
 */
class WC_Gateway_Plenigo extends \WC_Payment_Gateway {

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options = null;

    //CONSTANTS
    //Plenigo settings group
    const PLENIGO_SETTINGS_GROUP = 'plenigo';
    const PLENIGO_SETTINGS_NAME = 'plenigo_settings';
    //Order Title Replacements
    const ORDER_SITE_TITLE = '%%SITE_TITLE%%';
    const ORDER_ORDER_ID = '%%ORDER_ID%%';
    const ORDER_ORDER_NO = '%%ORDER_NO%%';
    const ORDER_PROD_NAMES = '%%PROD_NAMES%%';
    const ORDER_PROD_IDS = '%%PROD_IDS%%';
    const ORDER_PROD_SKUS = '%%PROD_SKUS%%';

    public function __construct() {
        //Plenigo Options
        $this->options = get_option(self::PLENIGO_SETTINGS_NAME);

        // This plugin supports....
        $this->supports = array('products');

        //Definitions
        $this->id = 'plenigo_gateway';
        $this->icon = "https://www.plenigo.com/assets/favicon.ico";
        $this->has_fields = true;
        $this->enabled = true;
        $this->title = __('Payment with plenigo', self::PLENIGO_SETTINGS_GROUP);
        $this->description = __('WooCommerce', self::PLENIGO_SETTINGS_GROUP);
        $this->order_button_text = __('Pay with plenigo', self::PLENIGO_SETTINGS_GROUP);

        // Initialize administration
        $this->init_form_fields();
        $this->init_settings();
        // Saving options
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

        // Processing the redirection
        add_action('wp_footer', array($this, 'plenigo_checkout_process'), 20);
        add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));
    }

    public function process_payment($order_id) {
        global $woocommerce;
        $order = new WC_Order($order_id);

        // Mark as on-hold (we're awaiting the payment)
        $order->update_status('on-hold', __('Awaiting Plenigo payment', self::PLENIGO_SETTINGS_GROUP));

        /* // Reduce stock levels
          // $order->reduce_order_stock();
          // Remove cart
          // $woocommerce->cart->empty_cart();
         */

        // Return thank you (receipt) page redirect
        return array(
            'result' => 'success',
            'redirect' => $this->get_return_url($order)
        );
    }

    /**
     * After the JavaScript SDk is loaded, if we have to process an order, let's enerate the checkout snippet and execute it...
     */
    public function plenigo_checkout_process() {
        global $woocommerce;

        $checkout_param = defined('PLENIGO_WOO_RECEIPT_ORDER') ? PLENIGO_WOO_RECEIPT_ORDER : FALSE;
        if (!is_null($checkout_param) && $checkout_param !== FALSE) {
            $order = new WC_Order($checkout_param);

            //Let's create a unmanaged Plenigo Product for this order
            $sdk = PlenigoSDKManager::get()->getPlenigoSDK();
            if (!is_null($sdk) && ($sdk instanceof \plenigo\PlenigoManager)) {
                if (!isset($this->options['use_login']) || ($this->options['use_login'] == 0 )) {
                    $useOauthLogin = false;
                } else {
                    $useOauthLogin = true;
                }
                $csrfToken = PlenigoSDKManager::get()->get_csrf_token();
                $product = $this->get_product_checkout($order);
                // creating the checkout snippet for this product
                $checkoutBuilder = new \plenigo\builders\CheckoutSnippetBuilder($product);

                $coSettings = array('csrfToken' => $csrfToken);
                if ($useOauthLogin) {
                    // this url must be registered in plenigo
                    $coSettings['oauth2RedirectUrl'] = $this->options['redirect_url'];
                }

                // checkout snippet
                $checkoutSnippet = '';
                try {
                    $checkoutSnippet.= $checkoutBuilder->build($coSettings);
                } catch (Exception $exc) {
                    plenigo_log_message($exc->getMessage() . ': ' . $exc->getTraceAsString(), E_USER_WARNING);
                    error_log($exc->getMessage() . ': ' . $exc->getTraceAsString());
                    wc_add_notice($exc->getMessage(), 'error');
                }

                echo '<script>' . $checkoutSnippet . '</script>';
            }
        }
    }

    /**
     * Hendles the hook for the receipt page, this will allow the Javscript to be shown. 
     * Also, if the paymenState parameter is present, it will trigger the user 
     * boiught check for plenigo
     * @param int $order_id the order id being shown
     */
    public function receipt_page($order_id) {
        define('PLENIGO_WOO_RECEIPT_ORDER', $order_id);
        $order = new WC_Order($order_id);
        // Mark as processing (checkout process)
        $order->update_status('processing', __('Plenigo checkout stating', self::PLENIGO_SETTINGS_GROUP));
        if (filter_input(INPUT_GET, 'paymentState') !== FALSE) {
            $this->plenigo_buy_confirm($order_id);
        }
    }

    /**
     * Check if this gateway is enabled and available in the user's country
     *
     * @return bool
     */
    public function is_valid_for_use() {
        return in_array(get_woocommerce_currency(), array('AUD', 'BRL', 'CAD', 'MXN', 'NZD', 'HKD', 'SGD', 'USD', 'EUR', 'JPY', 'TRY', 'NOK', 'CZK', 'DKK', 'HUF', 'ILS', 'MYR', 'PHP', 'PLN', 'SEK', 'CHF', 'TWD', 'THB', 'GBP', 'RMB', 'RUB'));
    }

    /**
     * Check if the payment gateway is available and shows the options form
     * 
     */
    public function admin_options() {
        if ($this->is_valid_for_use()) {
            parent::admin_options();
        } else {
            ?>
            <div class="inline error"><p><strong><?php _e('Gateway Disabled', self::PLENIGO_SETTINGS_GROUP); ?></strong>: <?php _e('PayPal does not support your store currency.', self::PLENIGO_SETTINGS_GROUP); ?></p></div>
            <?php
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
     * Detectes if the icheckout process finished and then marks the order as paid if necessary
     * @param int $order_id The order ID to check for pleigo user bought
     */
    public function plenigo_buy_confirm($order_id) {
        global $woocommerce;
        $user_ID = get_current_user_id();
        $count = wc_get_customer_order_count($user_ID);
        if ($user_ID > 0 && $count > 0) {
            $order = new WC_Order($order_id);
            $user_bought = \plenigo_plugin\PlenigoSDKManager::get()->plenigo_bought($order_id);
            if ($user_bought === true) {
                $order->add_order_note(__('Plenigo payment complete. Thank you!', self::PLENIGO_SETTINGS_GROUP));
                // Set Order as complete
                $order->payment_complete();
            }
        }
    }

    /**
     * Creates a plenigo unmanaged product with the last Product ID as the order ID. 
     * 
     * @param \WooCommerce\Classes\WC_Order $order the order to create the Product from
     * @return \plenigo\models\ProductBase The Plenigo Product Object
     */
    private function get_product_checkout($order = null) {
        $res = null;

        if (!is_null($order) && ($order instanceof \WooCommerce\Classes\WC_Order)) {
            $prodID = $order->id;
            $title = $this->get_order_title($order);
            $total = $order->get_total();
            $prodType = ($order->has_downloadable_item() === true) ? \plenigo\models\ProductBase::TYPE_DOWNLOAD : null;
            $currency = $order->get_order_currency();

            $res = new \plenigo\models\ProductBase($prodID, $title, $total, $currency);
            if (!is_null($prodType)) {
                $res->setType($prodType);
            }
        }
        return $res;
    }

    /**
     * 
     * @param \WooCommerce\Classes\WC_Order $order the order to create the title
     * @return string the generated Title
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
                    self::ORDER_PROD_IDS,
                    self::ORDER_PROD_NAMES,
                    self::ORDER_PROD_SKUS);
                $rvalues = array(
                    $blog_title,
                    $order->id,
                    $order->get_order_number(),
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
     * @param int $field
     * @param \WooCommerce\Classes\WC_Order $order the order to create the title
     * @return string the generated List
     */
    public function get_product_list($field, $order) {
        $res = '';
        $items = $order->get_items();
        foreach ($items as $curr_item) {
            $curr_prod = $order->get_product_from_item($items);
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

}
