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
 * This Abstract class allows ever Settings class to implement common methods used to create the setting in the setting page
 *
 * @category WordPressPlugin
 * @package  plenigoPluginSettings
 * @author Sebastian Dieguez <s.dieguez@plenigo.com>
 */
abstract class PlenigoWPSetting
{

    const PLENIGO_SETTINGS_GROUP = 'plenigo';
    const PLENIGO_SETTINGS_NAME = 'plenigo_settings';
    const PLENIGO_SETTINGS_PAGE = 'plenigo_options';
    //These should be overriden
    const SECTION_ID = 'plenigo_general';
    const SETTING_ID = '';

    private $store = array();

    public function __construct()
    {
        $this->store = get_option(self::PLENIGO_SETTINGS_NAME);
    }

    /**
     * Abstract method to return the localized Title
     * @return string The Internationalized Setting Title
     */
    abstract public function getTitle();

    /**
     * Abstract method to return the localized default value. This will be used for validation and new values
     * @param mixed $current OPTIONAL the current value to return if no default should be given
     * @return string The default value for this setting
     */
    abstract public function getDefaultValue($current = null);

    /**
     * Abstract method to <b>echo</b> the HTML to render the setting in the form. 
     * Usually a form field but complex can have Javascript or WYSIWYG fields
     * 
     */
    abstract public function renderCallback();

    /**
     * Abstract method that sanitizes the particular value given by the overriden SETTING_ID in the class
     * 
     * @param string $value The incomming value from the HTML form
     * @return mixed The sanitized value after sanitization
     */
    abstract protected function getSanitizedValue($value = null);

    /**
     * Overridable method that validates the value and return if the value is valid. 
     * Optionally this method can trigger add_settings_error() errors for the user.
     * 
     * @param string $value The incomming value from the HTML form
     * @return bool TRUE if the value given is valid, FALSE otherwise
     */
    public function getValidationForValue($value = null)
    {
        if (is_null($value)) {
            return false;
        }
        return true;
    }

    /**
     * Sanitizes the new value for storing it to the database
     * 
     * @param array $options the array with the complete set of new values
     * @return mixed The sanitized value after sanitization or NULL if there was a problem
     */
    final public function sanitize($options = null)
    {
        if (is_null($options) || !is_array($options) || !isset($options[static::SETTING_ID])) {
            add_settings_error(self::PLENIGO_SETTINGS_PAGE, self::PLENIGO_SETTINGS_GROUP,
                "Could not sanitize the setting:" . static::SETTING_ID, 'error');
            return null;
        }
        return $this->getSanitizedValue($options[static::SETTING_ID]);
    }

    /**
     * This is a convenience method that allows to get the value stores in the DB for this setting
     * 
     * @return mixed The value stored in the DB as is or NULL if the setting hasn't been set.
     */
    final protected function getStoredValue()
    {
        if (!isset($this->store[static::SETTING_ID])) {
            return null;
        }
        return $this->store[static::SETTING_ID];
    }

}
