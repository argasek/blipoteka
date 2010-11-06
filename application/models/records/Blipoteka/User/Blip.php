<?php

/**
 * Blipoteka.pl
 *
 * LICENSE
 *
 * This source file is subject to the Simplified BSD License
 * that is bundled with this package in the file docs/LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://blipoteka.pl/license
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to blipoteka@gmail.com so we can send you a copy immediately.
 *
 * @category   Blipoteka
 * @package    Blipoteka_User_Blip
 * @copyright  Copyright (c) 2010 Jakub Argasiński (argasek@gmail.com)
 * @license    http://blipoteka.pl/license Simplified BSD License
 */

/**
 * User's Blip account entity
 *
 * @property integer $blip_id Primary key
 * @property integer $user_id Foreign key of user's entity
 * @property string $blip_login User's blip login (Blip account name)
 * @property Blipoteka_User $user User's account entity
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class Blipoteka_User_Blip extends Doctrine_Record {

	/**
	 * Setup record, table name etc.
	 */
	public function setTableDefinition() {
		$this->setTableName('users_blip');

		$this->hasColumn('blip_id', 'integer', 4, array('primary' => true, 'autoincrement' => true));
		$this->hasColumn('user_id', 'integer', 4, array('notnull' => true, 'unique' => true));
		$this->hasColumn('blip_login', 'string', 30, array('notnull' => true, 'unique' => true));
	}

	/**
	 * Set up relationships
	 */
	public function setUp() {
		// One Blip account is owned by one user
		$this->hasOne('Blipoteka_User as user', array('local' => 'user_id', 'foreign' => 'user_id', 'onUpdate' => 'CASCADE', 'onDelete' => 'CASCADE'));
	}
}