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
 * @package    Blipoteka_User
 * @copyright  Copyright (c) 2010 Jakub Argasiński (argasek@gmail.com)
 * @license    http://blipoteka.pl/license Simplified BSD License
 */

/**
 * User entity
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class Blipoteka_User extends Doctrine_Record {

	/**
	 * Setup record, table name etc.
	 *
	 * @property integer $user_id Primary key
	 * @property string $email E-mail address
	 * @property string $password Salted hash of users's password
	 * @property string $name Last name and first name of user
	 * @property string $log_date The date and time user last logged in
	 * @property integer $lognum How many times user logged in
	 * @property bool $is_active Is user's account active?
	 * @property bool $accept_friends_borrow_requests Automatically accept all borrow requests from user's friends
	 * @property string $activated_at Date and time the account was activated by user
	 * @property string $created_at Date and time the account was created
	 * @property string $updated_at Date and time the record was updated
	 */
	public function setTableDefinition() {
		$this->setTableName('users');

		$this->hasColumn('user_id', 'integer', 4, array('primary' => true, 'autoincrement' => true));
		$this->hasColumn('email', 'string', 128, array('notnull' => true, 'unique' => true));
		$this->hasColumn('password', 'string', 128, array('notnull' => true));
		$this->hasColumn('name', 'string', 64, array('notnull' => true));
		$this->hasColumn('log_date', 'timestamp', null, array('notnull' => false));
		$this->hasColumn('log_num', 'integer', 4, array('notnull' => true, 'default' => 0));
		$this->hasColumn('is_active', 'boolean', null, array('default' => true, 'notnull' => true));
		$this->hasColumn('activated_at', 'timestamp', null, array('notnull' => false));
		$this->hasColumn('accept_friends_borrow_requests', 'boolean', null, array('default' => true, 'notnull' => true));
	}

	/**
	 * Set up relationships and behaviors
	 * @see Doctrine_Record::setUp()
	 */
	public function setUp() {
		$this->hasMany('Blipoteka_User as friends', array(
			'local' => 'user_id',
			'foreign' => 'friend_id',
			'refClass' => 'Blipoteka_User_FriendRef',
			'equal' => true
		));
		// FIXME: this Doctrine behaviour doesn't suit our needs very well -- actually,
		// we are interested only of user's triggered record updates (ie. updated_at
		// shouldn't be touched when, for example, we are increasing log_num)
		$this->actAs('Timestampable');
	}

}
