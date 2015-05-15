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
 * Setting class for curtain_cat_mode
 *
 * @category WordPressPlugin
 * @package  plenigoPluginSettings
 * @author   Sebastian Dieguez <s.dieguez@plenigo.com>
 * @link     https://plenigo.com
 */
class SettingCurtainCategoryMode extends PlenigoWPSetting {

    //These should be overriden
    const SECTION_ID = 'plenigo_curtain_section';
    const SETTING_ID = 'curtain_cat_mode';

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
        return 1;
    }

    /**
     * @see PlenigoWPSetting::getTitle()
     */
    public function getTitle() {
        return __('Curtain Button Scheme (Category tag)', parent::PLENIGO_SETTINGS_GROUP);
    }

    /**
     * @see PlenigoWPSetting::renderCallback()
     */
    public function renderCallback() {
        $currValue = $this->getDefaultValue($this->getStoredValue());
        $arrDefaults = array(
            1 => '',
            2 => '',
            3 => '',
            4 => ''
        );
        $arrDefaults[$currValue] = ' checked';

        echo '<input type="radio" id="curtain_cat_mode_1" name="' . self::PLENIGO_SETTINGS_NAME
        . '[' . self::SETTING_ID . ']" value="1" ' . $arrDefaults[1] . '>'
        . '<label for="curtain_cat_mode_1">' . $this->getButtons(true, false, true) . '</label><br>';
        echo '<input type="radio" id="curtain_cat_mode_2" name="' . self::PLENIGO_SETTINGS_NAME
        . '[' . self::SETTING_ID . ']" value="2" ' . $arrDefaults[2] . '>'
        . '<label for="curtain_cat_mode_2">' . $this->getButtons(false, true, true) . '</label><br>';
        echo '<input type="radio" id="curtain_cat_mode_3" name="' . self::PLENIGO_SETTINGS_NAME
        . '[' . self::SETTING_ID . ']" value="3" ' . $arrDefaults[3] . '>'
        . '<label for="curtain_cat_mode_3">' . $this->getButtons(true, true, true) . '</label><br>';
        echo '<input type="radio" id="curtain_cat_mode_4" name="' . self::PLENIGO_SETTINGS_NAME
        . '[' . self::SETTING_ID . ']" value="4" ' . $arrDefaults[4] . '>'
        . '<label for="curtain_cat_mode_4">' . $this->getButtons(false, true, false) . '</label><br>';
    }

    /**
     * Returns the stylized buttons to be used as examples on the mode selection
     * 
     * @param bool $buy true if you want to render the Buy button
     * @param bool $custom true if you want to render the Custom button
     * @param bool $login true if you want to render the Login button
     * @return string that represents the 3 buttons to echo
     */
    private function getButtons($buy = true, $custom = true, $login = true) {
        $res = '';
        if ($login) {
            $res.= ' <span class="button button-small">[LOGIN BUTTON]</span>';
        }
        if ($custom) {
            $res.= '<span class="button button-small">[CUSTOM BUTTON]</span>';
        }
        if ($buy) {
            $res.= '<span class="button button-small">[BUY CATEGORY PRODUCT BUTTON]</span> ';
        }

        return $res;
    }

    /**
     * @see PlenigoWPSetting::getValidationForValue()
     */
    public function getValidationForValue($value = null) {
        if (!is_null($value) && (intval(trim($value)) >= 1 && intval(trim($value)) <= 4)) {
            return true;
        } else {
            return false;
        }
    }

}
