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

namespace plenigo_plugin\settings;

/**
 * Setting class for redirect_url
 *
 * @category WordPressPlugin
 * @package  plenigoPluginSettings
 * @author   Sebastian Dieguez <s.dieguez@plenigo.com>
 * @link     https://plenigo.com
 */
class SettingRedirectURL extends PlenigoWPSetting
{

    //These should be overriden
    const SECTION_ID = 'plenigo_login_section';
    const SETTING_ID = 'redirect_url';

    /**
     * @see PlenigoWPSetting::getSanitizedValue()
     */
    protected function getSanitizedValue($value = null)
    {
        $tempValue = trim($value);
        if (is_null($value) && !empty($tempValue)) {
            return $this->getDefaultValue();
        }
        return trim(esc_url($value, "http, https"));
    }

    /**
     * @see PlenigoWPSetting::getDefaultValue()
     */
    public function getDefaultValue($current = null)
    {
        if (!is_null($current)) {
            return esc_url($current);
        }
        return esc_url(wp_login_url());
    }

    /**
     * @see PlenigoWPSetting::getTitle()
     */
    public function getTitle()
    {
        return __('OAuth redirect URL', parent::PLENIGO_SETTINGS_GROUP);
    }

    /**
     * @see PlenigoWPSetting::renderCallback()
     */
    public function renderCallback()
    {
        $currValue = $this->getDefaultValue($this->getStoredValue());
        $currHint = $this->getHint();

        printf('<input type="text" id="' . static::SETTING_ID . '" name="' . self::PLENIGO_SETTINGS_NAME
            . '[' . static::SETTING_ID . ']" value="%s" placeholder="%s" size="90" />', $currValue, $currHint);
    }

    /**
     * Returns the text to be used as a hint for the text box
     */
    protected function getHint()
    {
        return __('http://', parent::PLENIGO_SETTINGS_GROUP);
    }

    /**
     * @see PlenigoWPSetting::getValidationForValue()
     */
    public function getValidationForValue($value = null)
    {
        if (!is_null($value) && strlen(trim($value)) > 9) {
            return true;
        }
        return false;
    }

}
