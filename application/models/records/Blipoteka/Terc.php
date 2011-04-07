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
 * @package    Blipoteka
 * @copyright  Copyright (c) 2010-2011 Jakub Argasiński (argasek@gmail.com)
 * @license    http://blipoteka.pl/license Simplified BSD License
 */

/**
 * Polish administrative district entity, based on GUS TERYT database (TERC).
 *
 * @property string $terc_id Primary key = $province . $district . $borough . $type
 * @property string $name Name of province, district or borough
 * @property string $asciiname Name of province, district or borough as ASCII string
 * @property string $province Province code
 * @property string $district District code
 * @property string $borough Borough code
 * @property string $type Borough type
 * @property string $modified_at Date of the last update of the data
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class Blipoteka_Terc extends Void_Doctrine_Record {

	/**
	 * Setup record, table name etc.
	 */
	public function setTableDefinition() {
		$this->setTableName('terc');

		$this->hasColumn('terc_id', 'string', 7, array('primary' => true, 'autoincrement' => false));
		$this->hasColumn('name', 'string', 64, array('notnull' => true));
		$this->hasColumn('asciiname', 'string', 64, array('notnull' => true));
		$this->hasColumn('province', 'string', 2, array('notnull' => true));
		$this->hasColumn('district', 'string', 2, array('notnull' => true));
		$this->hasColumn('borough', 'string', 2, array('notnull' => true));
		$this->hasColumn('type', 'string', 1, array('notnull' => true));
		$this->hasColumn('modified_at', 'date', null, array('notnull' => true));
	}

	/**
	 * Set up relationships and behaviors
	 * @see Doctrine_Record::setUp()
	 */
	public function setUp() {
		parent::setUp();
	}

}
