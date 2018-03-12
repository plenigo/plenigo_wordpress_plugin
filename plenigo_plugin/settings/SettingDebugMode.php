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
 * Setting class for debug_mode
 *
 * @category WordPressPlugin
 * @package  plenigoPluginSettings
 * @link     https://plenigo.com
 */
class SettingDebugMode extends PlenigoWPSetting {

    //These should be overriden
    const SECTION_ID = 'plenigo_advanced_section';
    const SETTING_ID = 'debug_mode';

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
        return __('Debug Mode', parent::PLENIGO_SETTINGS_GROUP);
    }

    /**
     * @see PlenigoWPSetting::renderCallback()
     */
    public function renderCallback() {
        $currValue = $this->getDefaultValue($this->getStoredValue());
        $visibleDebugValue = '';
        $noDebugValue = '';
        $logDebugValue = '';

        if (is_null($currValue) || ($currValue === 1 )) {
            $visibleDebugValue = ' checked';
            $noDebugValue = '';
            $logDebugValue = '';
        } else if ($currValue === 0) {
            $visibleDebugValue = '';
            $noDebugValue = ' checked';
            $logDebugValue = '';
        } else {
            $visibleDebugValue = '';
            $noDebugValue = '';
            $logDebugValue = ' checked';
        }

        $tomorrow = strtotime("+1 day");

        echo '<input type="radio" id="no_debug_mode" name="' . self::PLENIGO_SETTINGS_NAME
        . '[' . self::SETTING_ID . ']" value="0" ' . $noDebugValue . '><label for="no_debug_mode">' . __('NO DEBUG mode', parent::PLENIGO_SETTINGS_GROUP) . '</label><br>'
        . '<input type="radio" id="visible_mode" name="' . self::PLENIGO_SETTINGS_NAME
        . '[' . self::SETTING_ID . ']" value="1" ' . $visibleDebugValue . '><label for="visible_mode">' . __('VERBOSE DEBUG mode', parent::PLENIGO_SETTINGS_GROUP) . '</label><br>'
        . '<input type="radio" id="debug_log_mode" name="' . self::PLENIGO_SETTINGS_NAME
        . '[' . self::SETTING_ID . ']" value="' . $tomorrow . '" ' . $logDebugValue . '><label for="debug_log_mode">' . __('VERBOSE LOG FILE mode. Up to: ', parent::PLENIGO_SETTINGS_GROUP) . date('Y-m-d', $tomorrow) . '</label><br>'
                . '<br>NOTE: <em>This works if there is not pre-defined WP_DEBUG,WP_DEBUG_LOG or WP_DEBUG_DISPLAY constants in your wp-config.php or any other plugins that may set those variables.</em>';
    }

    /**
     * @see PlenigoWPSetting::getValidationForValue()
     */
    public function getValidationForValue($value = null) {
        if (!is_null($value) && (intval(trim($value)) === 1 || intval(trim($value)) === 0)) {
            return true;
        } else
            if (date('Y-m-d', $value)) {
            return true;
        } else {
            return false;
        }
    }

}
