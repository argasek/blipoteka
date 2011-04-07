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
 * Add terc table.
 * Remove city_id foreign key on users table.
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class Blipoteka_Migration_Add_Terc extends Doctrine_Migration_Base {

	public function up() {
		$columns = array(
			'terc_id' => array('type' => 'string', 'length' => 7, 'notnull' => true, 'primary' => true),
			'name' => array('type' => 'string', 'length' => 64, 'notnull' => true),
			'asciiname' => array('type' => 'string', 'length' => 64, 'notnull' => true),
			'province' => array('type' => 'string', 'length' => 2, 'notnull' => true),
			'district' => array('type' => 'string', 'length' => 2, 'notnull' => true),
			'borough' => array('type' => 'string', 'length' => 2, 'notnull' => true),
			'type' => array('type' => 'string', 'length' => 1, 'notnull' => true),
			'modified_at' => array('type' => 'date', 'notnull' => true),
		);

		$options = array();

		$this->createTable('terc', $columns, $options);

		// Drop Blipoteka_City foreign key
		$table = Doctrine_Core::getTable('Blipoteka_User');
		$this->dropForeignKey($table->getTableName(), $table->getRelation('city')->getForeignKeyName());
	}

	public function down() {
		$this->dropTable('terc');

		$table = Doctrine_Core::getTable('Blipoteka_User');
		$definition = array();
		$definition['local'] = $table->getRelation('city')->getLocalColumnName();
		$definition['foreign'] = $table->getRelation('city')->getForeignColumnName();
		$definition['foreignTable'] = Doctrine::getTable('Blipoteka_City')->getTableName();
		$definition['onUpdate'] = 'CASCADE';
		$definition['onDelete'] = 'RESTRICT';

		$this->createForeignKey('users', $table->getRelation('city')->getForeignKeyName(), $definition);
	}

}
