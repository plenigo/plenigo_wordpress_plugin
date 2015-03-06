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
 * Setting class for test_mode
 *
 * @category WordPressPlugin
 * @package  plenigoPluginSettings
 * @author   Sebastian Dieguez <s.dieguez@plenigo.com>
 * @link     https://plenigo.com
 */
class SettingCheckMetered extends PlenigoWPSetting
{

    //These should be overriden
    const SECTION_ID = 'plenigo_general';
    const SETTING_ID = 'check_metered';

    /**
     * @see PlenigoWPSetting::getSanitizedValue()
     */
    protected function getSanitizedValue($value = null)
    {
        if (is_null($value)) {
            return $this->getDefaultValue();
        }
        return intval(trim($value));
    }

    /**
     * @see PlenigoWPSetting::getDefaultValue()
     */
    public function getDefaultValue($current = null)
    {
        if (!is_null($current)) {
            return $current;
        }
        return 1;
    }

    /**
     * @see PlenigoWPSetting::getTitle()
     */
    public function getTitle()
    {
        return __('Metered Views switch', parent::PLENIGO_SETTINGS_GROUP);
    }

    /**
     * @see PlenigoWPSetting::renderCallback()
     */
    public function renderCallback()
    {
        $currValue = $this->getDefaultValue($this->getStoredValue());
        $checkValue = '';
        $normalValue = '';
        if (is_null($currValue) || ($currValue === 1 )) {
            $checkValue = ' checked';
            $normalValue = '';
        } else {
            $checkValue = '';
            $normalValue = ' checked';
        }

        echo '<input type="radio" id="' . static::SETTING_ID . '" name="' . self::PLENIGO_SETTINGS_NAME
        . '[' . static::SETTING_ID . ']" value="1" ' . $checkValue . '><label for="' . static::SETTING_ID . '">'
        . $this->getOptionLabel(1) . '</label><br>'
        . '<input type="radio" id="' . static::SETTING_ID . '_2" name="' . self::PLENIGO_SETTINGS_NAME
        . '[' . static::SETTING_ID . ']" value="0" ' . $normalValue . '><label for="' . static::SETTING_ID . '_2">'
        . $this->getOptionLabel(0) . '</label>';
    }

    /**
     * Returns the applicable labels for the given options
     * 
     * @param int $option the value for the option
     * @return string the translated label
     */
    protected function getOptionLabel($option)
    {
        if ($option === 1) {
            return __('Check for Metered Views', parent::PLENIGO_SETTINGS_GROUP);
        }
        if ($option === 0) {
            return __('Disable Metered Views', parent::PLENIGO_SETTINGS_GROUP);
        }
    }

    /**
     * @see PlenigoWPSetting::getValidationForValue()
     */
    public function getValidationForValue($value = null)
    {
        if (!is_null($value) && (intval(trim($value)) === 1 || intval(trim($value)) === 0)) {
            return true;
        } else {
            return false;
        }
    }

}
