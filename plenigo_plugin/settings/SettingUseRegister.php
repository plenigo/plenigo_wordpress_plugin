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
 * Setting class for use_register
 *
 * @category WordPressPlugin
 * @package  plenigoPluginSettings
 * @link     https://plenigo.com
 */
class SettingUseRegister extends PlenigoWPSetting {

    //These should be overriden
    const SECTION_ID = 'plenigo_curtain_section';
    const SETTING_ID = 'use_register';

    /**
     * @see PlenigoWPSetting::getSanitizedValue()
     */
    protected function getSanitizedValue($value = null) {
        if (is_null($value)) {
            return $this->getDefaultValue();
        }
        return intval(trim($value));
    }

    /**
     * @see PlenigoWPSetting::getDefaultValue()
     */
    public function getDefaultValue($current = null) {
        if (!is_null($current)) {
            return $current;
        }
        return 0;
    }

    /**
     * @see PlenigoWPSetting::getTitle()
     */
    public function getTitle() {
        return __('Show Register Form', parent::PLENIGO_SETTINGS_GROUP);
    }

    /**
     * @see PlenigoWPSetting::renderCallback()
     */
    public function renderCallback() {
        $currValue = $this->getDefaultValue($this->getStoredValue());
        $useOnValue = '';
        $useOffValue = '';
        if (is_null($currValue) || ($currValue === 0 )) {
            $useOnValue = '';
            $useOffValue = ' checked';
        } else {
            $useOnValue = ' checked';
            $useOffValue = '';
        }

        echo '<input type="radio" id="' . static::SETTING_ID . '" name="' . self::PLENIGO_SETTINGS_NAME
        . '[' . static::SETTING_ID . ']" value="1" ' . $useOnValue . '><label for="' . static::SETTING_ID . '">'
        . $this->getOnTitle() . '</label><br>'
        . '<input type="radio" id="not_' . static::SETTING_ID . '" name="' . self::PLENIGO_SETTINGS_NAME
        . '[' . static::SETTING_ID . ']" value="0" ' . $useOffValue . '><label for="not_' . static::SETTING_ID . '">'
        . $this->getOffTitle() . '</label>';
    }

    /**
     * @see PlenigoWPSetting::getValidationForValue()
     */
    public function getValidationForValue($value = null) {
        if (!is_null($value) && (intval(trim($value)) === 1 || intval(trim($value)) === 0)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns the title of the ON option
     * 
     * @return string
     */
    protected function getOnTitle() {
        return __('Show register form to annonymous users when buying products', parent::PLENIGO_SETTINGS_GROUP);
    }

    /**
     * Returns the title of the OFF option
     * 
     * @return string
     */
    protected function getOffTitle() {
        return __('Do not show the registration form when buying products', parent::PLENIGO_SETTINGS_GROUP);
    }

}
