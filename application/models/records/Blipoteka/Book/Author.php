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
 * @package    Blipoteka_Book
 * @copyright  Copyright (c) 2010 Jakub Argasiński (argasek@gmail.com)
 * @license    http://blipoteka.pl/license Simplified BSD License
 */

/**
 * Books-authors reference class
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class Blipoteka_Book_Author extends Doctrine_Record {

	/**
	 * Setup record, table name etc.
	 *
	 * @property integer $author_id Primary key
	 * @property string $name Surname and name of the author
	 */
	public function setTableDefinition() {
		$this->setTableName('books_authors');

		$this->hasColumn('id', 'integer', 4, array('primary' => true, 'autoincrement' => true));
		$this->hasColumn('book_id', 'integer', 4, array('notnull' => true));
		$this->hasColumn('author_id', 'integer', 4, array('notnull' => true));
	}

	/**
	 * Set up relationships and behaviors
	 * @see Doctrine_Record::setUp()
	 */
	public function setUp() {
		$this->hasOne('Blipoteka_Book as book', array('local' => 'book_id', 'foreign' => 'book_id', 'onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE'));
		$this->hasOne('Blipoteka_Author as author', array('local' => 'author_id', 'foreign' => 'author_id', 'onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE'));
	}

}
