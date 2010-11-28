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
			$authAdapter = Zend_Registry::get('auth-adapter');
		}
		if ($authAdapter instanceof Void_Auth_Adapter_Interface) {
			$this->_authAdapter = $authAdapter;
		}
	}

	/**
	 * Process given user password hashing/salting algorithms
	 * if provided and store it in a database.
	 *
	 * @param string $password
	 * @param Blipoteka_User $user
	 */
	public function setPassword($password, Blipoteka_User $user) {
		if ($this->_authAdapter instanceof Void_Auth_Adapter_Interface) {
			$this->_authAdapter->setCredential($password);
			$password = $this->_authAdapter->getTreatedCredential();
		}
		$user->password = $password;
		$user->save();
	}

}
