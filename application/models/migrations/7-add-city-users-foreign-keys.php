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
 * @package    Blipoteka_Migration
 * @copyright  Copyright (c) 2010-2011 Jakub Argasiński (argasek@gmail.com)
 * @license    http://blipoteka.pl/license Simplified BSD License
 */

/**
 * Add city_id foreign key on users table again.
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class Blipoteka_Migration_Add_City_Users_Foreign_Key extends Doctrine_Migration_Base {

	public function preUp() {
		// We need to correct all city_id entries in users table to be able to
		// apply a foreign key relationship. First we need to find some unequivocal
		// city. 'Pacanów' is a good example ;-)
		$city = Doctrine_Core::getTable('Blipoteka_City')->findOneBy('name', 'Pacanów');
		if ($city === false) {
			throw new Doctrine_Migration_Exception("Couldn't find 'Pacanów' city in the database. Maybe the importer wasn't run.");
		}
		// Reset all users cities to default one
		$query = Doctrine_Query::create();
		$query->update('Blipoteka_User')->set('city_id', $city->city_id)->execute();

	}

	public function up() {
		// Add Blipoteka_City foreign key
		$table = Doctrine_Core::getTable('Blipoteka_User');
		$definition = array();
		$definition['local'] = $table->getRelation('city')->getLocalColumnName();
		$definition['foreign'] = $table->getRelation('city')->getForeignColumnName();
		$definition['foreignTable'] = Doctrine::getTable('Blipoteka_City')->getTableName();
		$definition['onUpdate'] = 'CASCADE';
		$definition['onDelete'] = 'RESTRICT';

		$this->createForeignKey('users', $table->getRelation('city')->getForeignKeyName(), $definition);
	}

	public function down() {
		// Drop Blipoteka_City foreign key
		$table = Doctrine_Core::getTable('Blipoteka_User');
		$this->dropForeignKey($table->getTableName(), $table->getRelation('city')->getForeignKeyName());
	}

}
