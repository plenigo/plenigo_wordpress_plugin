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

namespace plenigo_plugin\settings;

/**
 * Setting class for woo_order_title
 *
 * @category WordPressPlugin
 * @package  plenigoPluginSettings
 * @author   Sebastian Dieguez <s.dieguez@plenigo.com>
 * @link     https://plenigo.com
 */
class SettingWooOrderTitle extends SettingCurtainTitle {

    //These should be overriden
    const SECTION_ID = 'plenigo_woo_section';
    const SETTING_ID = 'woo_order_title';

    /**
     * @see PlenigoWPSetting::getTitle()
     */
    public function getTitle() {
        return __('Order Title Format', parent::PLENIGO_SETTINGS_GROUP);
    }

    /**
     * @see PlenigoWPSetting::getDefaultValue()
     */
    public function getDefaultValue($current = null) {
        if (!is_null($current)) {
            return $current;
        }
        return '%%SITE_TITLE%% order # %%ORDER_NO%%';
    }

    /**
     * @see PlenigoWPSetting::renderCallback()
     */
    public function renderCallback() {
        parent::renderCallback();
        echo '<br>'
        . __('Order Title Format: replacement variables', parent::PLENIGO_SETTINGS_GROUP)
        . '<br><br>'
            . '<b>%%SITE_TITLE%%</b> - The Site&quot;s title.<br>'
            . '<b>%%ORDER_ID%%</b> - This is the order ID.<br>'
            . '<b>%%ORDER_NO%%</b> - This is the order number (maybe same as ID, depending on other plugins).<br>'
            . '<b>%%ORDER_KEY%%</b> - This is the order identifier key (usually <i>wc_order_XXXXXXXXXX</i>).<br>'
            . '<b>%%PROD_NAMES%%</b> - A comma separated list of order&quot;s product names. <b>*</b><br>'
            . '<b>%%PROD_IDS%%</b> - A comma separated list of order&quot;s product IDs. <b>*</b><br>'
            . '<b>%%PROD_SKUS%%</b> - A comma separated list of order&quot;s product SKUs. <b>*</b><br>'
            . '<br><b>*</b> WARNING: Very long generated text are most likely to be trimmed by reports, invoices, credit card reports, etc.';
        
    }

}
