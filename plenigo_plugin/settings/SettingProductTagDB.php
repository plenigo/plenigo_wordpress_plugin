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

include_once __DIR__ . '../PlenigoSDKManager.php';

use \plenigo_plugin\PlenigoSDKManager;

/**
 * Setting class for plenigo_tag_db
 *
 * @category WordPressPlugin
 * @package  plenigoPluginSettings
 * @author   Sebastian Dieguez <s.dieguez@plenigo.com>
 * @link     https://plenigo.com
 */
class SettingProductTagDB extends PlenigoWPSetting {

    //These should be overriden
    const SECTION_ID = 'plenigo_content_section';
    const SETTING_ID = 'plenigo_tag_db';
    const PREFIX_ID = 'plenigo_tag';

    /**
     * Holds values for the SQL requests, so they are made just once per request
     */
    private $reqCache = array();

    /**
     * @see PlenigoWPSetting::getSanitizedValue()
     */
    protected function getSanitizedValue($value = null) {
        if (is_null($value)) {
            return $this->getDefaultValue();
        }
        return trim($value);
    }

    /**
     * @see PlenigoWPSetting::getDefaultValue()
     */
    public function getDefaultValue($current = null) {
        if (!is_null($current)) {
            return $current;
        }
        return "";
    }

    /**
     * @see PlenigoWPSetting::getTitle()
     */
    public function getTitle() {
        return __('Premium Content Products', parent::PLENIGO_SETTINGS_GROUP);
    }

    /**
     * @see PlenigoWPSetting::renderCallback()
     */
    public function renderCallback() {
        $currValue = $this->getDefaultValue($this->getStoredValue());
        $tagList = $this->get_term_data();
        $itemList = $this->get_product_data();
        include __DIR__ . '/SettingTagDB_TPL.php';
        echo '<script type="text/javascript">jQuery(document).ready(function(){plenigoSettings.init("plenigo_tag");});</script>';
    }

    /**
     * @see PlenigoWPSetting::getValidationForValue()
     */
    public function getValidationForValue($value = null) {
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
            if (function_exists("add_settings_error")) {
                add_settings_error(
                        self::PLENIGO_SETTINGS_PAGE, "plenigo", __("There is a problem with the TAG list."
                                . " <br/> The correct format is lines of TAG->PRODUCTID[,PRODUCTID...] pairs", self::PLENIGO_SETTINGS_GROUP), 'error'
                );
            }
            return false;
        }
    }

    private function get_term_data() {
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

    private function get_product_data() {
        $res = "";
        $sdk = PlenigoSDKManager::get();

        try {
            $prodArray = $sdk->getProductList();
        } catch (Exception $exc) {
            $prodArray = array();
        }
        foreach ($prodArray as $prodItem) {
            if ($res != "") {
                $res.="|";
            }
            $res.=$prodItem->productId . "," . $prodItem->title . " (" . $prodItem->productId . ")";
        }

        return $res;
    }

}
