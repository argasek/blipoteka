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
 * Book lending history
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class Blipoteka_Book_History extends Doctrine_Record {

	/**
	 * Setup record, table name etc.
	 *
	 * @property integer $id Primary key
	 * @property integer $book_id ID of a book
	 * @property integer $borrower_id ID of borrowing user
	 * @property integer $lender_id ID of lending user
	 * @property string $requested_at Date and time a borrower requested a book
	 * @property string $received_at Date and time a borrower received a book (may be NULL, if book is still being delivered)
	 */
	public function setTableDefinition() {
		$this->setTableName('books_history');

		$this->hasColumn('id', 'integer', 4, array('primary' => true, 'autoincrement' => true));
		$this->hasColumn('book_id', 'integer', 4, array('notnull' => true));
		$this->hasColumn('borrower_id', 'integer', 4, array('notnull' => true));
		$this->hasColumn('lender_id', 'integer', 4, array('notnull' => true));
		$this->hasColumn('received_at', 'timestamp', null, array('notnull' => false));
	}

	/**
	 * Set up relationships and behaviors
	 * @see Doctrine_Record::setUp()
	 */
	public function setUp() {
		// A book
		$this->hasOne('Blipoteka_Book as book', array('local' => 'book_id', 'foreign' => 'book_id', 'onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE'));
		// User who borrows a book
		$this->hasOne('Blipoteka_User as borrower', array('local' => 'borrower_id', 'foreign' => 'user_id', 'onUpdate' => 'CASCADE', 'onDelete' => 'CASCADE'));
		// User who lends a book
		$this->hasOne('Blipoteka_User as lender', array('local' => 'lender_id', 'foreign' => 'user_id', 'onUpdate' => 'CASCADE', 'onDelete' => 'CASCADE'));
		// This record is created when lender accepts borrower's request
		$this->actAs('Timestampable', array('created' =>  array('name' => 'requested_at'), 'updated' => array('disabled' => true)));
	}

}
