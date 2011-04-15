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
 * Add borough_id, province_id and district_id foreign key on cities table.
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class Blipoteka_Migration_Add_City_Terc_Foreign_Keys extends Doctrine_Migration_Base {

	protected function getRelationships() {
		$relationships = array('borough', 'district', 'province');
		return $relationships;
	}

	public function up() {
		// Add Blipoteka_City foreign key
		$table = Doctrine_Core::getTable('Blipoteka_City');
		$relationships = $this->getRelationships();

		foreach ($relationships as $relationship) {
			$relation = $table->getRelation($relationship);
			$definition = array();
			$definition['local'] = $relation->getLocalColumnName();
			$definition['foreign'] = $relation->getForeignColumnName();
			$definition['foreignTable'] = Doctrine::getTable('Blipoteka_Terc')->getTableName();
			$definition['onUpdate'] = 'CASCADE';
			$definition['onDelete'] = 'CASCADE';
			$this->createForeignKey($table->getTableName(), $relation->getForeignKeyName(), $definition);
		}
	}

	public function down() {
		// Drop Blipoteka_City foreign key
		$table = Doctrine_Core::getTable('Blipoteka_City');
		$relationships = $this->getRelationships();

		foreach ($relationships as $relationship) {
			$relation = $table->getRelation($relationship);
			$this->dropForeignKey($table->getTableName(), $relation->getForeignKeyName());
		}
	}

}
