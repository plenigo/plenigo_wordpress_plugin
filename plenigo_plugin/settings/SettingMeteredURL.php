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

require_once __DIR__ . '/SettingRedirectURL.php';

/**
 * Setting class for metered_url
 *
 * @category WordPressPlugin
 * @package  plenigoPluginSettings
 * @author   Sebastian Dieguez <s.dieguez@plenigo.com>
 * @link     https://plenigo.com
 */
class SettingMeteredURL extends SettingRedirectURL {

    //These should be overriden
    const SECTION_ID = 'plenigo_general';
    const SETTING_ID = 'metered_url';

    /**
     * @see PlenigoWPSetting::getDefaultValue()
     */
    public function getDefaultValue($current = null) {
        if (!is_null($current)) {
            return esc_url($current);
        }
        return '';
    }

    /**
     * @see PlenigoWPSetting::getTitle()
     */
    public function getTitle() {
        return __('Metered explanation URL', parent::PLENIGO_SETTINGS_GROUP);
    }

    /**
     * @see SettingRedirectURL::getHint()
     */
    protected function getHint() {
        return __('https://', parent::PLENIGO_SETTINGS_GROUP);
    }

    /**
     * @see PlenigoWPSetting::getValidationForValue()
     */
    public function getValidationForValue($value = null) {
        if ((!is_null($value) && strlen(trim($value)) > 9) || $value === '') {
            if ($value !== '' && filter_var(trim($value), FILTER_VALIDATE_URL) === false) {
                return false;
            } else {
                return true;
            }
        }
        return false;
    }

}
