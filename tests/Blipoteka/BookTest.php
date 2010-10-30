<?php

/**
 * Blipoteka.pl
 *
 * LICENSE
 *
 * This source file is subject to the Simplified BSD License that is
 * bundled with this package in the file docs/LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://blipoteka.pl/license
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to blipoteka@gmail.com so we can send you a copy immediately.
 *
 * @category   Blipoteka
 * @package    Blipoteka_Tests
 * @copyright  Copyright (c) 2010 Jakub Argasiński (argasek@gmail.com)
 * @license    http://blipoteka.pl/license Simplified BSD License
 */

/**
 * Book entity test case
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class Blipoteka_BookTest extends PHPUnit_Framework_TestCase {
	/**
	 * @var Blipoteka_Book
	 */
	private $book;

	/**
	 * Set up an example book with minimum information required
	 * @see PHPUnit_Framework_TestCase::setUp()
	 */
	protected function setUp() {
		$this->book = new Blipoteka_Book();
		$this->book->title = 'Przykładowa, bardzo interesująca książka';
		$this->book->city_id = 756135;
		$this->book->publisher_id = 1;
		$this->book->user_id = 1;
	}

	/**
	 * We expect user_id to be not NULL when inserting.
	 * @expectedException Doctrine_Record_Exception
	 */
	public function testPreInsertUserIdNotNull() {
		$this->book->user_id = null;
		$this->book->save();
	}

	/**
	 * We expect holder_id to be NULL when inserting.
	 * @expectedException Doctrine_Record_Exception
	 */
	public function testPreInsertHolderIdNull() {
		$this->book->holder_id = 2;
		$this->book->save();
	}

	/**
	 * We expect owner_id to be set to the same value as user_id when inserting (and after insertion, too).
	 * @depends testPreInsertUserIdNotNull
	 */
	public function testPreInsertOwnerEqualsUser() {
		$this->book->save();
		$this->assertEquals($this->book->user_id, $this->book->owner_id, 'A book after insertion should have the same user and owner');
	}

	/**
	 * We expect holder_id to be not the same as owner_id (unless owner_id is NULL) when inserting or updating.
	 * @expectedException Doctrine_Record_Exception
	 */
	public function testPreSaveNotNullOwnerCannotEqualHolder() {
		$this->book->holder_id = 1;
		$this->book->save();
	}

	/**
	 * We expect holder_id can be the same as owner_id when updating only when owner_id is NULL.
	 * @depends testPreInsertOwnerEqualsUser
	 */
	public function testPreSaveNullOwnerCanEqualHolder() {
		$this->book->save();
		$this->book->owner_id = null;
		$this->book->holder_id = null;
		$this->book->save();
		$this->assertNull($this->book->owner_id);
		$this->assertNull($this->book->holder_id);
	}

}
