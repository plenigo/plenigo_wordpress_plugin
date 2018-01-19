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

namespace plenigo_plugin\models;

/**
 *
 *
 * @category WordPressPlugin
 * @package  plenigoPluginModels
 */

use \plenigo\models\Loggable;

class WordpressLogging implements Loggable {
	private $wpdb;
	private $tableName;

	public function __construct( $wpdb, $tableName ) {
		$this->wpdb      = $wpdb;
		$this->tableName = $tableName;
	}

	public function logData( $msg ) {
		$this->wpdb->insert( $this->tableName,
			array(
				'time' => current_time( 'mysql' ),
				'log'  => $msg
			) );
	}
}
