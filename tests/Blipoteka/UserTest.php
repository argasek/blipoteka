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
 * User entity test case
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class Blipoteka_UserTest extends PHPUnit_Framework_TestCase {
	/**
	 * @var Blipoteka_User
	 */
	private $user;

	/**
	 * Set up an example user with minimum information required
	 * @see PHPUnit_Framework_TestCase::setUp()
	 */
	protected function setUp() {
		$this->user = new Blipoteka_User();
		$this->user->city_id = 756135;
		$this->user->password = 'password';
		$this->user->name = 'user_' . Void_Util_Base62::encode(time());
		$this->user->email = $this->user->name . '@blipoteka.pl';
	}

	/**
	 * Test if e-mail validation works as expected
	 */
	public function testValidateEmail() {
		try {
			$this->user->email = 'invalid_email_address';
			$this->user->save();
		} catch (Doctrine_Validator_Exception $e) {
			$this->assertEquals($this->user->getErrorStack()->count(), 1);
			$this->assertEquals(count($this->user->getErrorStack()->get('email')), 1);
			$this->assertStringEndsWith('is not a valid email address', current($this->user->getErrorStack()->get('email')));
			return;
		}
		$this->fail('Doctrine_Validator_Exception has not been raised.');
	}

}