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

/**
 * PlenigoWooCManager
 * 
 * <b>
 * This class creates and implements a WooCommerce Payment Gateway
 * </b>
 *
 * @category WordPressPlugin
 * @package  plenigoPlugin
 * @author   Sebastian Dieguez <s.dieguez@plenigo.com>
 * @link     https://plenigo.com
 */
class PlenigoWooCManager {

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options = null;

    /**
     * Holds values for the SDK requests, so they are mdae just once per request
     */
    private $reqCache = array();

    //CONSTANTS
    //Plenigo settings group
    const PLENIGO_SETTINGS_GROUP = 'plenigo';
    const PLENIGO_SETTINGS_NAME = 'plenigo_settings';

    /**
     * COnstructor method
     */
    public function __construct() {
        //Init the Gateway class
        add_action('plugins_loaded', array($this, 'plenigo_gateway_class'));
        //Register the actual Gateway
        add_filter('woocommerce_payment_gateways', array($this, 'add_gateway_class'));

        //Plenigo Options
        $this->options = get_option(self::PLENIGO_SETTINGS_NAME);
    }

    public function plenigo_gateway_class() {
        require_once __DIR__ . '/wooCommerce/WC_Gateway_Plenigo.php';
    }

    public function add_gateway_class($methods) {
        $methods[] = '\plenigo_plugin\wooCommerce\WC_Gateway_Plenigo';
        return $methods;
    }

}
