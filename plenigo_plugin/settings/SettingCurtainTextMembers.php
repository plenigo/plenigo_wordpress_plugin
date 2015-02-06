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
 * Setting class for curtain_text_members
 *
 * @category WordPressPlugin
 * @package  plenigoPluginSettings
 * @author   Sebastian Dieguez <s.dieguez@plenigo.com>
 * @link     https://plenigo.com
 */
class SettingCurtainTextMembers extends SettingCurtainText
{

    //These should be overriden
    const SECTION_ID = 'plenigo_curtain_section';
    const SETTING_ID = 'curtain_text_members';

    /**
     * @see PlenigoWPSetting::getTitle()
     */
    public function getTitle()
    {
        return __('Curtain Message (for members)', parent::PLENIGO_SETTINGS_GROUP);
    }
}
