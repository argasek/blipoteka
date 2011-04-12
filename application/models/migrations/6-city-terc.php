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
 * Add cities <-> terc tables relationship.
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class Blipoteka_Migration_City_Terc extends Doctrine_Migration_Base {

	public function up() {
		$table = Doctrine_Core::getTable('Blipoteka_City');
		$this->removeColumn($table->getTableName(), 'feature_code');
		$this->removeColumn($table->getTableName(), 'admin1_code');
		$this->removeColumn($table->getTableName(), 'admin2_code');
		$this->addColumn($table->getTableName(), 'province_id', 'string', 7, array('notnull' => true, 'default' => ''));
		$this->addColumn($table->getTableName(), 'district_id', 'string', 7, array('notnull' => true, 'default' => ''));
		$this->addColumn($table->getTableName(), 'borough_id', 'string', 7, array('notnull' => true, 'default' => ''));
	}

	public function down() {
		$table = Doctrine_Core::getTable('Blipoteka_City');
		$this->removeColumn($table->getTableName(), 'province_id');
		$this->removeColumn($table->getTableName(), 'district_id');
		$this->removeColumn($table->getTableName(), 'borough_id');
		$this->addColumn($table->getTableName(), 'feature_code', 'string', 10, array('notnull' => true));
		$this->addColumn($table->getTableName(), 'admin1_code', 'string', 20, array('notnull' => true));
		$this->addColumn($table->getTableName(), 'admin2_code', 'string', 80, array('notnull' => false));
	}

}
