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
class WC_Gateway_Plenigo extends WC_Payment_Gateway {

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options = null;

    //CONSTANTS
    //Plenigo settings group
    const PLENIGO_SETTINGS_GROUP = 'plenigo';
    const PLENIGO_SETTINGS_NAME = 'plenigo_settings';

    public function __construct() {
        //Plenigo Options
        $this->options = get_option(self::PLENIGO_SETTINGS_NAME);

        // This plugin supports....
        $this->supports = array('products');

        //Definitions
        $this->id = 'plenigo_gateway';
        $this->icon = "https://www.plenigo.com/assets/favicon.ico";
        $this->has_fields = true;
        $this->method_title = __('Payment with Plenigo', self::PLENIGO_SETTINGS_GROUP);
        $this->method_description = __('WooCommerce', self::PLENIGO_SETTINGS_GROUP);

        // Initialize administration
        $this->init_form_fields();
        $this->init_settings();
        // Saving options
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

        // Processing the redirection
        add_action('wp_footer', array($this, 'plenigo_checkout_process'), 20);
    }

    /**
     * Process the administration option fields and store them somehow
     */
    public function process_admin_options() {
        
    }

    public function process_payment($order_id) {
        global $woocommerce;
        $order = new WC_Order($order_id);

        // Mark as on-hold (we're awaiting the payment)
        $order->update_status('on-hold', __('Awaiting Plenigo payment', self::PLENIGO_SETTINGS_GROUP));

        // Reduce stock levels
        // $order->reduce_order_stock();
        // Remove cart
        // $woocommerce->cart->empty_cart();

        $checkout_url = \plenigo_plugin\PlenigoURLManager::get()->getSanitizedURL();
        if (stristr($checkout_url, '?') === FALSE) {
            $checkout_url.='?';
        } else {
            $checkout_url.='&';
        }
        $checkout_url.='plenigoWooPay=' . $order_id;

        return array(
            'result' => 'success',
            'redirect' => $checkout_url
        );
    }

    /**
     * After the JavaScript SDk is loaded, if we have to process an order, let's enerate the checkout snippet and execute it...
     */
    public function plenigo_checkout_process() {
        global $woocommerce;
        $checkout_param = filter_input(INPUT_GET, "plenigoWooPay");
        if (!is_null($checkout_param) && $checkout_param !== FALSE) {
            $order = new WC_Order($checkout_param);
            // Mark as processing (checkout process)
            $order->update_status('processing', __('Plenigo checkout stating', self::PLENIGO_SETTINGS_GROUP));
            
            //Let's create a unmanaged Plenigo Product for this order
        }
    }

}
