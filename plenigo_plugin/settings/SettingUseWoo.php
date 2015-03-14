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

require_once __DIR__ . '/SettingUseLogin.php';

/**
 * Setting class for use_woo
 *
 * @category WordPressPlugin
 * @package  plenigoPluginSettings
 * @author   Sebastian Dieguez <s.dieguez@plenigo.com>
 * @link     https://plenigo.com
 */
class SettingUseWoo extends SettingUseLogin {

    //These should be overriden
    const SECTION_ID = 'plenigo_woo_section';
    const SETTING_ID = 'use_woo';

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
        return __('Use Woo Commerce Payment Gateway', parent::PLENIGO_SETTINGS_GROUP);
    }

    /**
     * Returns the title of the ON option
     * 
     * @return string
     */
    protected function getOnTitle() {
        return __('Enable Payment Gateway', parent::PLENIGO_SETTINGS_GROUP);
    }

    /**
     * Returns the title of the OFF option
     * 
     * @return string
     */
    protected function getOffTitle() {
        return __('Disable Payment Gateway', parent::PLENIGO_SETTINGS_GROUP);
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

}
