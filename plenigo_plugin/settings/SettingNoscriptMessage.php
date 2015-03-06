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

require_once __DIR__ . '/SettingCurtainButtonBuy.php';

/**
 * Setting class for noscript_message
 *
 * @category WordPressPlugin
 * @package  plenigoPluginSettings
 * @author   Sebastian Dieguez <s.dieguez@plenigo.com>
 * @link     https://plenigo.com
 */
class SettingNoscriptMessage extends SettingCurtainButtonBuy {

    //These should be overriden
    const SECTION_ID = 'plenigo_general';
    const SETTING_ID = 'noscript_message';

    /**
     * @see PlenigoWPSetting::getDefaultValue()
     */
    public function getDefaultValue($current = null) {
        if (!is_null($current)) {
            return $current;
        }
        return __("In order to provide you with the best experience, "
                . "this site requires that you allow JavaScript to run. "
                . "Please correct that and try again.", self::PLENIGO_SETTINGS_GROUP);
    }

    /**
     * @see PlenigoWPSetting::getTitle()
     */
    public function getTitle() {
        return __('no-Javacript overlay Message', parent::PLENIGO_SETTINGS_GROUP);
    }

    /**
     * @see PlenigoWPSetting::renderCallback()
     */
    public function renderCallback() {
        $currValue = $this->getDefaultValue($this->getStoredValue());
        printf('<textarea cols="70" rows="5" id="%s" name="%s">%s</textarea>', static::SETTING_ID
                , self::PLENIGO_SETTINGS_NAME . '[' . static::SETTING_ID . ']', $currValue);
    }

}
