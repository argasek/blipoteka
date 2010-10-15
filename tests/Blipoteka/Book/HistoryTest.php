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

class Blipoteka_Book_HistoryTest extends PHPUnit_Framework_TestCase {
	/**
	 * @var Zend_Date
	 */
	private $requested_at;

	/**
	 * @var Zend_Date
	 */
	private $received_at;

	/**
	 * Initialization.
	 * @see PHPUnit_Framework_TestCase::setUp()
	 */
	protected function setUp() {
		$this->requested_at = new Zend_Date();
		$this->received_at = new Zend_Date();
		$this->received_at->addDay(14)->addHour(1)->addMinute(15);
	}

	/**
	 * Set common record values
	 * @param Blipoteka_Book_History $history
	 * @return Blipoteka_Book_History $history
	 */
	private function prepareHistoryRecord(Blipoteka_Book_History $history) {
		$history->borrower_id = 1;
		$history->lender_id = 2;
		$history->book_id = 1;
		$history->requested_at = $this->requested_at->get(Zend_Date::W3C);
		$history->received_at = $this->received_at->get(Zend_Date::W3C);

		return $history;
	}

	/**
	 * We expect lender_id and borrower_id to have different values.
	 * @expectedException Doctrine_Record_Exception
	 */
	public function testConstraintUsers() {
		$history = $this->prepareHistoryRecord(new Blipoteka_Book_History());
		$history->lender_id = $history->borrower_id;
		$history->save();
	}

	/**
	 * We expect received_at to be a later date than requested_at
	 * @expectedException Doctrine_Record_Exception
	 */
	public function testConstraintTimestamps() {
		$history = $this->prepareHistoryRecord(new Blipoteka_Book_History());
		$history->received_at = $history->requested_at;
		$history->save();
	}

	/**
	 * Test entry with receival date specified.
	 */
	public function testEntryReceived() {
		$history = $this->prepareHistoryRecord(new Blipoteka_Book_History());
		$history->save();

		$this->assertTrue($history->exists());
	}

	/**
	 * Test entry without receival date specified.
	 */
	public function testEntryNotReceived() {
		$history = $this->prepareHistoryRecord(new Blipoteka_Book_History());
		$history->received_at = null;
		$history->save();

		$this->assertTrue($history->exists());
	}

}