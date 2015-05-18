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
 * Setting class for curtain_text
 *
 * @category WordPressPlugin
 * @package  plenigoPluginSettings
 * @author   Sebastian Dieguez <s.dieguez@plenigo.com>
 * @link     https://plenigo.com
 */
class SettingCurtainText extends PlenigoWPSetting
{

    //These should be overriden
    const SECTION_ID = 'plenigo_curtain_section';
    const SETTING_ID = 'curtain_text';

    /**
     * @see PlenigoWPSetting::getSanitizedValue()
     */
    protected function getSanitizedValue($value = null)
    {
        $tempValue = trim($value);
        if (is_null($value) && !empty($tempValue)) {
            return $this->getDefaultValue();
        }
        return trim(wp_kses_post(wpautop($value)));
    }

    /**
     * @see PlenigoWPSetting::getDefaultValue()
     */
    public function getDefaultValue($current = null)
    {
        if (!is_null($current)) {
            return $current;
        }
        return __('In order to continue delivering <b>valuable</b> content for our readers, '
            . 'we ask a single everlasting fee from you.<br>'
            . 'Please join us and receive our premium content for ever.', parent::PLENIGO_SETTINGS_GROUP);
    }

    /**
     * @see PlenigoWPSetting::getTitle()
     */
    public function getTitle()
    {
        return __('Curtain Message', parent::PLENIGO_SETTINGS_GROUP);
    }

    /**
     * @see PlenigoWPSetting::renderCallback()
     */
    public function renderCallback()
    {
        $currValue = $this->getDefaultValue($this->getStoredValue());

        $settings = array(
            'teeny' => true,
            'textarea_rows' => 15,
            'media_buttons' => false,
            'quicktags' => false,
            'textarea_name' => self::PLENIGO_SETTINGS_NAME . '[' . static::SETTING_ID . ']',
            'tinymce' => array(
                'width' => 700
            )
        );
        echo '<style type="text/css">.wp-editor-container {width: 700px;}</style>';
        wp_editor($currValue, static::SETTING_ID, $settings);
    }

    /**
     * @see PlenigoWPSetting::getValidationForValue()
     */
    public function getValidationForValue($value = null)
    {
        if (!is_null($value) && strlen(trim($value)) > 5) {
            return true;
        }
        return false;
    }

}
