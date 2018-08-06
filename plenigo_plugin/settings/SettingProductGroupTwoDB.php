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
 * Setting class for plenigo_custom_curtain_db
 *
 * @category WordPressPlugin
 * @package  plenigoPluginSettings
 * @link     https://plenigo.com
 */
class SettingProductGroupTwoDB extends PlenigoWPSetting {

	//These should be overriden
	const SECTION_ID = 'plenigo_content_section';
	const SETTING_ID = 'plenigo_product_group_two_db';

	/**
	 * Holds values for the SQL requests, so they are made just once per request
	 */
//	private $reqCache = array();

	/**
	 * @see PlenigoWPSetting::getSanitizedValue()
	 */
	protected function getSanitizedValue( $value = null ) {
		if ( is_null( $value ) ) {
			return $this->getDefaultValue();
		}

		return trim( $value );
	}

	/**
	 * @see PlenigoWPSetting::getDefaultValue()
	 */
	public function getDefaultValue( $current = null ) {
		if ( ! is_null( $current ) ) {
			return $current;
		}

		return "";
	}

	/**
	 * @see PlenigoWPSetting::getTitle()
	 */
	public function getTitle() {
		return __( 'Produkte mit Teilzugriff', parent::PLENIGO_SETTINGS_GROUP );
	}

	/**
	 * @see PlenigoWPSetting::renderCallback()
	 */
	public function renderCallback() {
		$currValue = $this->getDefaultValue( $this->getStoredValue() );
		printf( '<textarea cols="100" wrap="off" rows="10" id="plenigo_product_group_two" name="' . self::PLENIGO_SETTINGS_NAME
		        . '[' . static::SETTING_ID . ']" placeholder="' . __( 'Produkte mit Teilzugriff...',
				parent::PLENIGO_SETTINGS_GROUP ) . '">%s</textarea>', $currValue );
	}

	/**
	 * @see PlenigoWPSetting::getValidationForValue()
	 */
	public function getValidationForValue( $value = null ) {
		return true;
	}
}
