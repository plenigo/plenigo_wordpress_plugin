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
 * Setting class for company_secret
 *
 * @category WordPressPlugin
 * @package  plenigoPluginSettings
 * @author   Sebastian Dieguez <s.dieguez@plenigo.com>
 * @link     https://plenigo.com
 */
class SettingCategoryTagDB extends PlenigoWPSetting
{

    //These should be overriden
    const SECTION_ID = 'plenigo_content_section';
    const SETTING_ID = 'plenigo_cat_tag_db';

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
        return "";
    }

    /**
     * @see PlenigoWPSetting::getTitle()
     */
    public function getTitle()
    {
        return __('Premium Content Categories', parent::PLENIGO_SETTINGS_GROUP);
    }

    /**
     * @see PlenigoWPSetting::renderCallback()
     */
    public function renderCallback()
    {
        $currValue = $this->getDefaultValue($this->getStoredValue());
        echo '<input type="text" id="tag_cat_adder" name="ignore_cat_tag_adder" size="35" placeholder="' . __('Enter Tag name...',
            parent::PLENIGO_SETTINGS_GROUP) . '" /> -&gt; '
        . '<input type="text" id="category_adder" name="ignore_cat_adder" size="35" placeholder="' . __('Enter Category ID(s)...',
            parent::PLENIGO_SETTINGS_GROUP) . '" /> '
        . '<input type="button" onclick="addValuesToCatArea();" value="' . __("Add values", self::PLENIGO_SETTINGS_GROUP) . '" /><br/><br/>';

        printf('<textarea cols="100" wrap="off" rows="10" id="plenigo_cat_tag_db" name="' . self::PLENIGO_SETTINGS_NAME
            . '[' . static::SETTING_ID . ']">%s</textarea>', $currValue);

        echo '<script type="text/javascript">'
        . 'jQuery(document).ready(function(){'
        . 'var data = "' . $this->get_term_data() . '".split(",");'
        . 'jQuery("#tag_cat_adder").autocomplete({source:data,autoFocus:true});});'
        . 'function addValuesToCatArea(){'
        . 'var strPrev=jQuery("#plenigo_cat_tag_db").val();'
        . 'if(strPrev!==""){strPrev+="\n"}'
        . 'jQuery("#plenigo_cat_tag_db").val('
        . 'strPrev+jQuery("#tag_cat_adder").val()+"->"+jQuery("#category_adder").val()'
        . ');'
        . 'jQuery("#tag_cat_adder").val("");'
        . 'jQuery("#category_adder").val("");'
        . '}'
        . '</script>';
    }

    /**
     * @see PlenigoWPSetting::getValidationForValue()
     */
    public function getValidationForValue($value = null)
    {
        if (empty($value)) {
            return true;
        }
        $booDBFormatError = false;
        if (stristr("\n", $value)) {
            $arrRows = explode("\n", $value);
        } else {
            $arrRows = array($value);
        }
        if ($arrRows === false || count($arrRows) == 0) {
            $booDBFormatError = true;
        } else {
            foreach ($arrRows as $row) {
                $arrRow = explode("->", $row);
                if ($arrRow === false || count($arrRow) < 2 || empty($arrRow[0]) || empty($arrRow[1])) {
                    $booDBFormatError = true;
                    break;
                }
                if (stristr(',', $arrRow[1])) {
                    $arrPID = explode(",", $arrRow[1]);
                    foreach ($arrPID as $pid) {
                        if (empty($pid)) {
                            $booDBFormatError = true;
                            break;
                        }
                    }
                }
            }
        }
        if ($booDBFormatError === true) {
            add_settings_error(
                self::PLENIGO_SETTINGS_PAGE, "plenigo",
                __("There is a problem with the Category TAG list."
                    . " <br/> The correct format is lines of TAG->CATEGORYID[,CATEGORYID...] pairs",
                    self::PLENIGO_SETTINGS_GROUP), 'error'
            );
            return false;
        }
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
