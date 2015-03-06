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
 * Setting class for use_wp_login
 *
 * @category WordPressPlugin
 * @package  plenigoPluginSettings
 * @author   Sebastian Dieguez <s.dieguez@plenigo.com>
 * @link     https://plenigo.com
 */
class SettingUseWPLogin extends PlenigoWPSetting
{

    //These should be overriden
    const SECTION_ID = 'plenigo_login_section';
    const SETTING_ID = 'use_wp_login';

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
        return __('Enable Wordpress Login', parent::PLENIGO_SETTINGS_GROUP);
    }

    /**
     * @see PlenigoWPSetting::renderCallback()
     */
    public function renderCallback()
    {
        $currValue = $this->getDefaultValue($this->getStoredValue());

        $showWPLogin = '';
        $hideWPLogin = '';
        if (is_null($currValue) || ($currValue === 1 )) {
            $showWPLogin = ' checked';
            $hideWPLogin = '';
        } else {
            $showWPLogin = '';
            $hideWPLogin = ' checked';
        }

        echo '<input type="radio" id="use_wp_login" name="' . self::PLENIGO_SETTINGS_NAME
        . '[use_wp_login]" value="1" ' . $showWPLogin . '><label for="use_wp_login">' . __('Show the Worpress login form',
            parent::PLENIGO_SETTINGS_GROUP) . '</label><br>'
        . '<input type = "radio" id = "not_wp_use_login" name = "' . self::PLENIGO_SETTINGS_NAME
        . '[use_wp_login]" value = "0" ' . $hideWPLogin . '><label for = "not_wp_use_login">' . __('Only show plenigo login Button',
            parent::PLENIGO_SETTINGS_GROUP) . '</label>

        ';
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
