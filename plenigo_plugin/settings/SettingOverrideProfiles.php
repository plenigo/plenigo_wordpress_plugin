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
 * Setting class for test_mode
 *
 * @category WordPressPlugin
 * @package  plenigoPluginSettings
 * @author   Sebastian Dieguez <s.dieguez@plenigo.com>
 * @link     https://plenigo.com
 */
class SettingOverrideProfiles extends SettingCheckMetered
{

    //These should be overriden
    const SECTION_ID = 'plenigo_login_section';
    const SETTING_ID = 'override_profiles';

    /**
     * @see PlenigoWPSetting::getDefaultValue()
     */
    public function getDefaultValue($current = null)
    {
        if (!is_null($current)) {
            return $current;
        }
        return 0;
    }

    /**
     * @see PlenigoWPSetting::getTitle()
     */
    public function getTitle()
    {
        return __('Override WP Profile data', parent::PLENIGO_SETTINGS_GROUP);
    }

    /**
     * Returns the applicable labels for the given options
     * 
     * @param int $option the value for the option
     * @return string the translated label
     */
    protected function getOptionLabel($option)
    {
        if ($option === 1) {
            return __('Override Wordpress profile data with the Plenigo data', parent::PLENIGO_SETTINGS_GROUP);
        }
        if ($option === 0) {
            return __('Allow Wordpress users to modify their data', parent::PLENIGO_SETTINGS_GROUP);
        }
    }

}
