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
 * Add has_cover flag to Blipoteka_Book entity.
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class Blipoteka_Migration_Add_Book_HasCover extends Doctrine_Migration_Base {

	private function getBookTable() {
		return Doctrine_Core::getTable('Blipoteka_Book');
	}

	public function up() {
		$table = $this->getBookTable();
		$this->addColumn($table->getTableName(), 'has_cover', 'boolean', null, array('notnull' => true, 'default' => false));
	}

	public function down() {
		$table = $this->getBookTable();
		$this->removeColumn($table->getTableName(), 'has_cover');
	}

}
