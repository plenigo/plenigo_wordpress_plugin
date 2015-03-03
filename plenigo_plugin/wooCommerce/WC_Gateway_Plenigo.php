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

        //Definitions
        $this->id = 'plenigo_gateway';
        $this->icon = "https://www.plenigo.com/assets/favicon.ico";
        $this->has_fields = true;
        $this->method_title = __('Payment with Plenigo', self::PLENIGO_SETTINGS_GROUP);
        $this->method_description = __('WooCommerce', self::PLENIGO_SETTINGS_GROUP);
    }

}
