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
 * Setting class for profile_url
 *
 * @category WordPressPlugin
 * @package  plenigoPluginSettings
 * @link     https://plenigo.com
 */
class SettingProfileURL extends SettingRedirectURL
{

    //These should be overriden
    const SECTION_ID = 'plenigo_login_section';
    const SETTING_ID = 'profile_url';

    /**
     * @see PlenigoWPSetting::getDefaultValue()
     */
    public function getDefaultValue($current = null)
    {
        if (!is_null($current)) {
            return esc_url($current);
        }
        return '';
    }

    /**
     * @see PlenigoWPSetting::getTitle()
     */
    public function getTitle()
    {
        return __('User Profile URL', parent::PLENIGO_SETTINGS_GROUP);
    }
    
    /**
     * @see SettingRedirectURL::getHint()
     */
    protected function getHint()
    {
        return __('Leave empty for default WordPress profile...', parent::PLENIGO_SETTINGS_GROUP);
    }
}
