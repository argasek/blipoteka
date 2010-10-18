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
 * Book entity
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class Blipoteka_Book extends Doctrine_Record {

	/**
	 * The book is available for lending
	 * @var integer
	 */
	const STATUS_AVAILABLE = 0;

	/**
	 * The book is borrowed and currently read by someone
	 * @var integer
	 */
	const STATUS_BORROWED = 1;

	/**
	 * The book has been requested, but needs courier assignment
	 * @var integer
	 */
	const STATUS_COURIER = 2;

	/**
	 * The book is being delivered by a courier
	 * @var integer
	 */
	const STATUS_DELIVERED = 3;

	/**
	 * The book has been lost (due to some accident)
	 * @var integer
	 */
	const STATUS_LOST = 4;

	/**
	 * The book is temporarily unavailable
	 * @var integer
	 */
	const STATUS_UNAVAILABLE = 5;

	/**
	 * Owner of this book wishes to get it back
	 * @var integer
	 */
	const TYPE_OWNED = 0;

	/**
	 * Owner of this book releases it to the public
	 * @var integer
	 */
	const TYPE_FREE = 1;

	/**
	 * Setup record, table name etc.
	 *
	 * @property integer $book_id Primary key
	 * @property integer $type Type (actually: a model of distribution) of the book. May be owned or free.
	 * @property integer $user_id Foreign key of user being provider of the book
	 * @property integer $owner_id Foreign key of user being owner of the book
	 * @property integer $holder_id Foreign key of user being current holder of the book
	 * @property integer $author_id Foreign key of default (main) author of the book
	 * @property integer $status What's going on with the book? (awaits courier, being read, delivered etc.)
	 * @property string $title Title of the book in Polish language
	 * @property string $original_title Title of the book in original language
	 * @property integer $city_id Foreign key of edition's city
	 * @property integer $year Foreign key of edition's year
	 * @property integer $pages Number of pages
	 * @property string $isbn ISBN-10 or ISBN-13 number
	 * @property string $description Description of the book
	 * @property bool $auto_accept_requests Automatically accept borrow requests from any user
	 * @property string $created_at Date and time the book was added to library
	 */
	public function setTableDefinition() {
		$this->setTableName('books');

		$this->hasColumn('book_id', 'integer', 4, array('primary' => true, 'autoincrement' => true));
		$this->hasColumn('type', 'integer', 1, array('notnull' => true, 'default' => self::TYPE_OWNED));
		$this->hasColumn('user_id', 'integer', 4, array('notnull' => false));
		$this->hasColumn('owner_id', 'integer', 4, array('notnull' => false));
		$this->hasColumn('holder_id', 'integer', 4, array('notnull' => false));
		$this->hasColumn('author_id', 'integer', 4, array('notnull' => true));
		$this->hasColumn('status', 'integer', 1, array('notnull' => true, 'default' => self::STATUS_AVAILABLE));
		$this->hasColumn('title', 'string', 256, array('notnull' => true));
		$this->hasColumn('original_title', 'string', 256, array('notnull' => false));
		$this->hasColumn('city_id', 'integer', 4, array('notnull' => false));
		$this->hasColumn('publisher_id', 'integer', 4, array('notnull' => true));
		$this->hasColumn('year', 'integer', 2, array('notnull' => false));
		$this->hasColumn('pages', 'integer', 2, array('notnull' => false));
		$this->hasColumn('isbn', 'string', 13, array('notnull' => false));
		$this->hasColumn('description', 'string', 2048, array('notnull' => false));
		$this->hasColumn('auto_accept_requests', 'boolean', null, array('notnull' => true, 'default' => false));
	}

	/**
	 * Set up relationships and behaviors
	 * @see Doctrine_Record::setUp()
	 */
	public function setUp() {
		// We assume each book has at least one author and we consider him/her a main (default) author.
		// If this author gets deleted, all books by him/her are deleted as well.
		$this->hasOne('Blipoteka_Author as author', array('local' => 'author_id', 'foreign' => 'author_id', 'onUpdate' => 'CASCADE', 'onDelete' => 'CASCADE'));
		// Book may have many additional authors
		$this->hasMany('Blipoteka_Author as authors', array(
			'local' => 'book_id',
			'foreign' => 'author_id',
			'refClass' => 'Blipoteka_Book_Author'
		));

		// Each book is provided by one user. NULL means unknown (deleted account, etc.)
		$this->hasOne('Blipoteka_User as user', array('local' => 'user_id', 'foreign' => 'user_id', 'onUpdate' => 'CASCADE', 'onDelete' => 'SET NULL'));
		// Each book is possesed by one user. NULL means unknown (deleted account, etc.)
		$this->hasOne('Blipoteka_User as owner', array('local' => 'owner_id', 'foreign' => 'user_id', 'onUpdate' => 'CASCADE', 'onDelete' => 'SET NULL'));
		// Each book may be held by one user. NULL means the book is not being borrowed
		$this->hasOne('Blipoteka_User as holder', array('local' => 'holder_id', 'foreign' => 'user_id', 'onUpdate' => 'CASCADE', 'onDelete' => 'SET NULL'));

		// Each book may have one city when it was printed. NULL means unknown.
		$this->hasOne('Blipoteka_City as city', array('local' => 'city_id', 'foreign' => 'city_id', 'onUpdate' => 'CASCADE', 'onDelete' => 'SET NULL'));

		// Each book entity must have an exactly one publisher.
		// If the publisher gets deleted, all books published by him/her are deleted as well.
		$this->hasOne('Blipoteka_Publisher as publisher', array('local' => 'publisher_id', 'foreign' => 'publisher_id', 'onUpdate' => 'CASCADE', 'onDelete' => 'CASCADE'));
		$this->actAs('Timestampable', array('updated' => array('disabled' => true)));
	}

	/**
	 * Check if saved data is right
	 * @see Doctrine_Record::preSave()
	 */
	public function preSave($event) {
		$invoker = $event->getInvoker();

		// One cannot be a current owner and a holder of a book at the same time.
		if ($invoker->owner_id !== null && $invoker->owner_id === $invoker->holder_id) {
			throw new Doctrine_Record_Exception("Owner and holder of a book can't be the same person", Doctrine_Core::ERR_CONSTRAINT);
		}
	}

	/**
	 * Preparation of record
	 * @see Doctrine_Record::preInsert()
	 */
	public function preInsert($event) {
		$invoker = $event->getInvoker();

		// A book has to be added by some user
		if ($invoker->user_id === null) {
			throw new Doctrine_Record_Exception("Tried to add a book not bound to any user", Doctrine_Core::ERR_CONSTRAINT);
		}

		// At the moment of insertion, a book cannot be held by anyone
		if ($invoker->holder_id !== null) {
			throw new Doctrine_Record_Exception("Tried to add a book being held by somebody", Doctrine_Core::ERR_CONSTRAINT);
		}

		// When a book is added to a user's pool, he or she becomes an owner automatically
		$invoker->owner_id = $invoker->user_id;
	}

}
