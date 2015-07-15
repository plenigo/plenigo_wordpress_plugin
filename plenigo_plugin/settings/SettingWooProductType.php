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
 * Setting class for woo_product_type
 *
 * @category WordPressPlugin
 * @package  plenigoPluginSettings
 * @author   Sebastian Dieguez <s.dieguez@plenigo.com>
 * @link     https://plenigo.com
 */
class SettingWooProductType extends PlenigoWPSetting {

    //These should be overriden
    const SECTION_ID = 'plenigo_woo_section';
    const SETTING_ID = 'woo_product_type';

    // Available Product Types
    private $prodTypeList = array('EBOOK', 'DIGITALNEWSPAPER', 'DOWNLOAD', 'VIDEO', 'MUSIC', 'BOOK', 'NEWSPAPER');

    /**
     * @see PlenigoWPSetting::getSanitizedValue()
     */
    protected function getSanitizedValue($value = null) {
        $tempValue = trim(strtoupper($value));
        if (is_null($value) || empty($tempValue)) {
            return $this->getDefaultValue();
        }
        return trim(strtoupper($value));
    }

    /**
     * @see PlenigoWPSetting::getDefaultValue()
     */
    public function getDefaultValue($current = null) {
        if (!is_null($current)) {
            return $current;
        }
        return '';
    }

    /**
     * @see PlenigoWPSetting::getTitle()
     */
    public function getTitle() {
        return __('Product Type', parent::PLENIGO_SETTINGS_GROUP);
    }

    /**
     * @see PlenigoWPSetting::renderCallback()
     */
    public function renderCallback() {
        $currValue = $this->getDefaultValue($this->getStoredValue());
        printf('<select name="%s" id="%s" required size="1">'
                , self::PLENIGO_SETTINGS_NAME . '[' . static::SETTING_ID . ']'
                , static::SETTING_ID);
        foreach ($this->prodTypeList as $prodType) {
            $selValue = ($currValue == $prodType) ? ' selected' : '';
            printf('<option value="%s" %s>%s</option>', $prodType, $selValue, ucfirst($prodType));
        }
        echo '</select>';
    }

    /**
     * @see PlenigoWPSetting::getValidationForValue()
     */
    public function getValidationForValue($value = null) {
        if (!is_null($value) && strlen(trim($value)) > 2) {
            return in_array(trim(strtoupper($value)), $this->prodTypeList);
        }
        return false;
    }

}
