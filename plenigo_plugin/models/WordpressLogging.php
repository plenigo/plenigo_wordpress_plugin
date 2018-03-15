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

use plenigo\models\Loggable;


/**
 * Wordpress logging class.
 *
 * @category WordPressPlugin
 * @package  plenigoPluginModels
 * @link     https://plenigo.com
 */
class WordpressLogging implements Loggable {
	private $wpdb;
	private $tableName;

	/**
	 * WordpressLogging constructor.
	 *
	 * @param $wpdb wordpress database object
	 * @param $tableName table name
	 */
	public function __construct( $wpdb, $tableName ) {
		$this->wpdb      = $wpdb;
		$this->tableName = $tableName;
	}

	/**
	 * Log the message to the database.
	 *
	 * @param \plenigo\models\data $msg message to send
	 *
	 */
	public function logData( $msg ) {
		$this->wpdb->insert( $this->tableName,
			array(
				'creation_date' => current_time( 'mysql' ),
				'log'  => $msg
			) );
	}
}
