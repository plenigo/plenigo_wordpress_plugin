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
 * Setting class for ga_code
 *
 * @category WordPressPlugin
 * @package  plenigoPluginSettings
 * @link     https://plenigo.com
 */
class SettingFeedParagraphs extends PlenigoWPSetting
{

    //These should be overriden
    const SECTION_ID = 'plenigo_general';
    const SETTING_ID = 'feed_paragraphs';

    /**
     * @see PlenigoWPSetting::getSanitizedValue()
     */
    protected function getSanitizedValue($value = 1)
    {
        if (empty($value) || !is_numeric($value)) {
            return $this->getDefaultValue();
        }
        return intval($value);
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
        return __('Number of paragraphs in feed', parent::PLENIGO_SETTINGS_GROUP);
    }

    /**
     * @see PlenigoWPSetting::renderCallback()
     */
    public function renderCallback()
    {
        $currValue = $this->getDefaultValue($this->getStoredValue());
        printf('<input type="number" id="' . static::SETTING_ID . '" name="' . self::PLENIGO_SETTINGS_NAME
            . '[' . static::SETTING_ID . ']" value="%s" placeholder="3"  size="65" />', esc_attr($currValue));
    }

    /**
     * @see PlenigoWPSetting::getValidationForValue()
     */
    public function getValidationForValue($value = null)
    {
        return true;
    }

}
