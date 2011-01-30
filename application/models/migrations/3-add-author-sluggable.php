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
 * Add sluggable behavior to Blipoteka_Author entity.
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class Blipoteka_Migration_Add_Author_Sluggable extends Doctrine_Migration_Base {

	private function getAuthorTable() {
		return Doctrine_Core::getTable('Blipoteka_Author');
	}

	public function up() {
		$table = $this->getAuthorTable();
		$this->addColumn($table->getTableName(), 'slug', 'string', 255, array('notnull' => false, 'unique' => true, 'default' => null));
	}

	public function down() {
		$table = $this->getAuthorTable();
		$this->removeColumn($table->getTableName(), 'slug');
	}

	public function postUp() {
		// Make slugs for all authors
		$items = $this->getAuthorTable()->findAll();
		foreach ($items as $item) {
			$item->state(Doctrine_Record::STATE_DIRTY);
			$item->save();
		}
	}
}
