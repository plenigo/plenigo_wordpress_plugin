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
 * Setting class for ga_code
 *
 * @category WordPressPlugin
 * @package  plenigoPluginSettings
 * @author   Sebastian Dieguez <s.dieguez@plenigo.com>
 * @link     https://plenigo.com
 */
class SettingPreventTag extends PlenigoWPSetting
{

    //These should be overriden
    const SECTION_ID = 'plenigo_content_section';
    const SETTING_ID = 'plenigo_prevent_tag';

    /**
     * Holds values for the SQL requests, so they are mdae just once per request
     */
    private $reqCache = array();

    /**
     * @see PlenigoWPSetting::getSanitizedValue()
     */
    protected function getSanitizedValue($value = null)
    {
        if (is_null($value)) {
            return $this->getDefaultValue();
        }
        return trim($value);
    }

    /**
     * @see PlenigoWPSetting::getDefaultValue()
     */
    public function getDefaultValue($current = null)
    {
        if (!is_null($current)) {
            return $current;
        }
        return '';
    }

    /**
     * @see PlenigoWPSetting::getTitle()
     */
    public function getTitle()
    {
        return __('Prevent Payment Tag', parent::PLENIGO_SETTINGS_GROUP);
    }

    /**
     * @see PlenigoWPSetting::renderCallback()
     */
    public function renderCallback()
    {
        $currValue = $this->getDefaultValue($this->getStoredValue());
        printf('<input type="text" id="' . static::SETTING_ID . '" name="' . self::PLENIGO_SETTINGS_NAME
            . '[' . static::SETTING_ID . ']" value="%s" placeholder="' . __('Enter Tag name...',
            parent::PLENIGO_SETTINGS_GROUP) . '"  size="65" />',
            esc_attr($currValue));

        echo '<script type="text/javascript">'
        . 'jQuery(document).ready(function(){'
        . 'var data = "' . $this->get_term_data() . '".split(",");'
        . 'jQuery("#' . static::SETTING_ID . '").autocomplete({source:data,autoFocus:true});});'
        . '</script>';
    }

    /**
     * @see PlenigoWPSetting::getValidationForValue()
     */
    public function getValidationForValue($value = null)
    {
        if ((!is_null($value) && strlen(trim($value)) > 5) || $value === '') {
            return true;
        }
        return false;
    }

    private function get_term_data()
    {
        if (isset($this->reqCache['term-query'])) {
            return $this->reqCache['term-query'];
        }
        global $wpdb;
        $res = '';

        $search_tags = $wpdb->get_results("SELECT a.name,a.slug FROM " . $wpdb->terms
            . " a," . $wpdb->term_taxonomy . " b WHERE a.term_id=b.term_id "
            . " and b.taxonomy='post_tag' ");
        foreach ($search_tags as $mytag) {
            if (strlen($res) !== 0) {
                $res.=",";
            }
            $res.= $mytag->name . "{" . $mytag->slug . "}";
        }
        $this->reqCache['term-query'] = $res;
        return $res;
    }

}