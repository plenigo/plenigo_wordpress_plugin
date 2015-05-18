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
 * Setting class for curtain_buy_text_db
 *
 * @category WordPressPlugin
 * @package  plenigoPluginSettings
 * @author   Sebastian Dieguez <s.dieguez@plenigo.com>
 * @link     https://plenigo.com
 */
class SettingCurtainBuyTextDB extends PlenigoWPSetting
{

    //These should be overriden
    const SECTION_ID = 'plenigo_curtain_section';
    const SETTING_ID = 'curtain_buy_text_db';

    /**
     * Holds values for storing values, so they are generated just once per request
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
        return __('Buy Button Text (based on tag)', parent::PLENIGO_SETTINGS_GROUP);
    }

    /**
     * @see PlenigoWPSetting::renderCallback()
     */
    public function renderCallback()
    {
        $currValue = $this->getDefaultValue($this->getStoredValue());
        echo '<input type="text" id="tag_buy_text_adder" name="ignore_buy_text_tag_adder" size="35" placeholder="' . __('Enter Tag name...',
            parent::PLENIGO_SETTINGS_GROUP) . '" /> -&gt; '
        . '<input type="text" id="buy_text_adder" name="ignore_buy_text_adder" size="35" placeholder="' . __('Buy button text...',
            parent::PLENIGO_SETTINGS_GROUP) . '" /> '
        . '<input type="button" onclick="addValuesToBuyTextArea();" value="' . __("Add values", self::PLENIGO_SETTINGS_GROUP) . '" /><br/><br/>';

        printf('<textarea cols="100" wrap="off" rows="10" id="' . static::SETTING_ID . '" name="' . self::PLENIGO_SETTINGS_NAME
            . '[' . static::SETTING_ID . ']">%s</textarea>', $currValue);

        echo '<script type="text/javascript">'
        . 'jQuery(document).ready(function(){'
        . 'var data = "' . $this->get_term_data() . '".split(",");'
        . 'jQuery("#tag_buy_text_adder").autocomplete({source:data,autoFocus:true});});'
        . 'function addValuesToBuyTextArea(){'
        . 'var strPrev=jQuery("#' . static::SETTING_ID . '").val();'
        . 'if(strPrev!==""){strPrev+="\n"}'
        . 'jQuery("#' . static::SETTING_ID . '").val('
        . 'strPrev+jQuery("#tag_buy_text_adder").val()+"->"+jQuery("#buy_text_adder").val()'
        . ');'
        . 'jQuery("#tag_buy_text_adder").val("");'
        . 'jQuery("#buy_text_adder").val("");'
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
                __("There is a problem with the BUY Button Text TAG list."
                    . " <br/> The correct format is lines of [TAG->Buy button text] pairs",
                    self::PLENIGO_SETTINGS_GROUP), 'error'
            );
            return false;
        }
    }

    /**
     * This method allows the autocomplete field to populate with all the current tag values
     * 
     * @global type $wpdb The WordPress database object
     * @return string A comma separated list of all existing tags
     */
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