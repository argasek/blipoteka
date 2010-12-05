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
 * @package    Blipoteka_Service
 * @copyright  Copyright (c) 2010 Jakub Argasiński (argasek@gmail.com)
 * @license    http://blipoteka.pl/license Simplified BSD License
 */

/**
 * User related service class
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class Blipoteka_Service_User extends Blipoteka_Service {
	const DEFAULT_USER_NAME = 'Koziołek Matołek';
	const DEFAULT_CITY_NAME = 'Pacanów';

	/**
	 * Class of the record this service applies to
	 * @var string
	 */
	protected $_recordClass = 'Blipoteka_User';

	/**
	 * Auth adapter (required for password hashing/salting)
	 * @var Void_Auth_Adapter_Interface
	 */
	protected $_authAdapter;

	/**
	 * The constructor
	 *
	 * @param Zend_Controller_Request_Abstract $request
	 * @param Void_Auth_Adapter_Interface $authAdapter
	 */
	public function __construct(Zend_Controller_Request_Abstract $request = null, Void_Auth_Adapter_Interface $authAdapter = null) {
		parent::__construct($request);

		if ($authAdapter === null && Zend_Registry::isRegistered('auth-adapter')) {
			$authAdapter = Zend_Registry::get('auth-adapter')->getDefaultAdapter();
		}
		if ($authAdapter instanceof Void_Auth_Adapter_Interface) {
			$this->_authAdapter = $authAdapter;
		}
	}

	/**
	 * Process given user password hashing/salting algorithms
	 * if provided and store it in a record.
	 *
	 * @param string $password
	 * @param Blipoteka_User $user
	 * @return Blipoteka_Service_User
	 */
	public function setPassword($password, Blipoteka_User $user) {
		if ($this->_authAdapter instanceof Void_Auth_Adapter_Interface) {
			$this->_authAdapter->setCredential($password);
			$password = $this->_authAdapter->getTreatedCredential();
		}
		$user->password = $password;
		return $this;
	}

	/**
	 * Process given user password hashing/salting algorithms
	 * if provided and update user's password.
	 *
	 * @param string $password
	 * @param Blipoteka_User $user
	 * @return Blipoteka_Service_User
	 */
	public function updatePassword($password, Blipoteka_User $user) {
		$this->setPassword($password, $user);
		$user->save();
		return $this;
	}

	/**
	 * Create user account with reasonable default values.
	 *
	 * @param Blipoteka_User $user
	 * @return Blipoteka_Service_User
	 */
	public function createUser(Blipoteka_User $user, Blipoteka_Form_Account_Signup $form) {
		$user->blip = $form->getValue('login');
		$user->email = $form->getValue('email');
		$this->setPassword($form->getValue('password'), $user);
		// If user name not provided, use default
		if ($user->name === null) {
			$user->name = $this->getDefaultUserName();
		}
		// If user city not provided, use default
		if ($user->city === null) {
			$user->city = $this->getDefaultCity();
		}
		// Create user's account.
		$user->save();

		return $this;
	}

	/**
	 * Get user entity by identity.
	 * @todo Don't assume email is an identity field.
	 * @param string $identity
	 */
	public function getUserByIdentity($identity) {
		return Doctrine_Core::getTable('Blipoteka_User')->findOneBy('email', $identity);
	}

	/**
	 * Return default city.
	 *
	 * @todo This should be set up by some resource
	 * @return string
	 */
	protected function getDefaultCity() {
		$name = self::DEFAULT_CITY_NAME;
		return Doctrine_Query::getTable('Blipoteka_City')->findOneByName($name);
	}

	/**
	 * Return default user name.
	 *
	 * @todo This should be set up by some resource
	 * @return string
	 */
	protected function getDefaultUserName() {
		$name = self::DEFAULT_USER_NAME;
		return $name;
	}

}
