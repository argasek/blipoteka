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
 * @copyright  Copyright (c) 2010-2011 Jakub Argasiński (argasek@gmail.com)
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
	 * Get identity cache
	 * @return Zend_Cache_Core|Zend_Cache_Frontend
	 */
	protected function getIdentityCache() {
		return $this->getCache('identity');
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
	 * Update user profile information from user profile form.
	 *
	 * @param Blipoteka_Form_Account_Profile $form
	 * @param Blipoteka_User $user
	 * @return Blipoteka_User|false
	 */
	public function updateAccountFromForm(Blipoteka_Form_Account $form, Blipoteka_User $user) {
		// Map gender field values ('', '0', '1') onto (NULL, false, true)
		$filter = new Zend_Filter();
		$filter->addFilter(new Zend_Filter_Null(Zend_Filter_Null::STRING));
		$filter->addFilter(new Zend_Filter_Boolean(array('type' => Zend_Filter_Boolean::INTEGER + Zend_Filter_Boolean::ZERO, 'casting' => false)));
		$gender = $filter->filter($form->getElement('gender')->getValue());

		// Map auto_accept_requests field from (NULL, '1') onto (false, true)
		$filter = new Zend_Filter_Boolean(Zend_Filter_Boolean::NULL + Zend_Filter_Boolean::ZERO);
		$auto_accept_requests = $filter->filter($form->getElement('auto_accept_requests')->getValue());

		// Assign form fields to record fields
		$user->email = $form->getElement('email')->getValue();
		$user->name = $form->getElement('name')->getValue();
		$user->gender = $gender;
		$user->auto_accept_requests = $auto_accept_requests;

		// If save successful, nothing to see here, move along.
		if ($user->trySave()) {
			return $user;
		}

		// Email errors handling
		$mappings = array(
			'unique' => 'W systemie istnieje już konto o takim adresie e-mail',
		);
		$user->errorStackToForm('email', $mappings, $form, 'email');

		return false;
	}

	/**
	 * Create $user account from $form with reasonable default values.
	 *
	 * @param Blipoteka_User $user A user entity
	 * @param Blipoteka_Form_Account_Signup $user The signup form
	 * @return bool
	 */
	public function createUserFromForm(Blipoteka_User $user, Blipoteka_Form_Account_Signup $form) {
		$user->blip = $form->getValue('login');
		$user->email = $form->getValue('email');
		$user->is_active = false;
		$this->setPassword($form->getValue('password'), $user);
		// If user name not provided, use default
		if ($user->name === null) {
			$user->name = $this->getDefaultUserName();
		}
		// If user city not provided, use default
		if ($user->city_id === null) {
			$user->city = $this->getDefaultCity();
		}

		// Generate token and send e-mail notification
		$subject = 'Potwierdzenie rejestracji w Blipotece';
		$user->addListener(new Blipoteka_Listener_User_Token());
		$user->addListener(new Blipoteka_Listener_User_Notification_Email('activation', $subject));

		// Begin transaction
		$connection = $user->getTable()->getConnection();
		$connection->beginTransaction();

		// An e-mail is sent in postInsert. It may fail under some rare conditions (mostly
		// connection problems), so user has no way to activate the account. We handle the
		// situation by rolling back the transaction.
		try {
			// If save successful, nothing to see here, move along.
			if ($user->trySave()) {
				$connection->commit();
				return true;
			}
		} catch (Zend_Mail_Exception $exception) {
			// In case of error, roll back and pass an error message to the form.
			$connection->rollback();
			$form->addError('Nie udało się wysłać e-maila potwierdzającego. Wygląda na to, że serwer pocztowy dostał zadyszki. Spróbuj ponownie za chwilę');
			return false;
		}

		// Email errors handling
		$mappings = array(
			'unique' => 'Konto o takim adresie e-mail już istnieje',
		);
		$user->errorStackToForm('email', $mappings, $form, 'email');

		// Login errors handling
		$mappings = array(
			'unique' => 'Konto takiego użytkownika już istnieje',
		);
		$user->errorStackToForm('blip', $mappings, $form, 'login');

		return false;
	}

	/**
	 * Populate $form with data from $user entity. If $user is not
	 * given, assumes currently authenticated user.
	 *
	 * @param Blipoteka_Form_Account $form
	 * @param Blipoteka_User $user (optional)
	 */
	public function accountFormFromUser(Blipoteka_Form_Account $form, Blipoteka_User $user = null) {
		// Get currently authenticated user, if none was given
		$user = ($user instanceof Blipoteka_User ? $user : $this->getAuthenticatedUser());
		// Map (NULL, false, true) onto ('', '0', '1')
		$gender = ($user->gender === null ? '' : (int) $user->gender);
		$form->getElement('email')->setValue($user->email);
		$form->getElement('name')->setValue($user->name);
		$form->getElement('gender')->setValue($gender);
		$form->getElement('auto_accept_requests')->setValue($user->auto_accept_requests);
		$form->getElement('city')->setValue($user->city->name);
		$form->getElement('lat')->setValue($user->city->lat);
		$form->getElement('lng')->setValue($user->city->lng);
		$form->getElement('city_id')->setValue($user->city_id);
	}

	/**
	 * Create $user account from parameters given.
	 *
	 * @param Blipoteka_User $user A user entity
	 * @param string $identity An identity (e-mail)
	 * @param string $blip A blip account
	 * @param string $credential A credential
	 * @param string $name A real name
	 * @param string $city_id An ID of user's city
	 * @param bool $activate If true, silently activate account (no e-mail activation) (default)
	 */
	public function createUser(Blipoteka_User $user, $identity, $blip, $credential, $name = null, $city_id = null, $activate = true) {
		$user->blip = $blip;
		$user->set(Blipoteka_User::IDENTITY_FIELD, $identity, false);
		$user->is_active = $activate;
		if ($activate === true) {
			$activated_at = new Zend_Date();
			$activated_at->addMinute(1);
			$user->activated_at = $activated_at->get(Zend_Date::W3C);
		}
		$this->setPassword($credential, $user);
		// If user name not provided, use default
		if ($user->name === null) {
			$user->name = $this->getDefaultUserName();
		}
		// If user city not provided, use default
		if ($user->city_id === null) {
			$user->city = $this->getDefaultCity();
		}

		// If we wish activation to take place...
		if ($activate === false) {
			// Generate token and send e-mail notification
			$subject = 'Potwierdzenie rejestracji w Blipotece';
			$user->addListener(new Blipoteka_Listener_User_Token());
			$user->addListener(new Blipoteka_Listener_User_Notification_Email('activation', $subject));
		}

		// If save successful, nothing to see here, move along.
		if ($user->trySave()) {
			return true;
		}

		// Some error occured, do nothing
		return false;
	}

	/**
	 * Get user entity by identity.
	 *
	 * @param string $identity
	 * @return Blipoteka_User
	 */
	public function getUserByIdentity($identity) {
		return Doctrine_Core::getTable('Blipoteka_User')->findOneBy(Blipoteka_User::IDENTITY_FIELD, $identity);
	}

	/**
	 * Get identity from user entity.
	 *
	 * @param Blipoteka_User $user
	 * @return string
	 */
	public function getUserIdentity(Blipoteka_User $user) {
		return $user->get(Blipoteka_User::IDENTITY_FIELD);
	}

	/**
	 * Get currently authenticated user entity.
	 *
	 * @param boolean $force Force reading data from the database and refreshing data in cache
	 * @return Blipoteka_User
	 */
	public function getAuthenticatedUser($force = false) {
		$cache = $this->getIdentityCache();
		$identity = Zend_Auth::getInstance()->getIdentity();
		$cache_id = $this->identityToCacheId($identity);
		if ($force === true || $cache === false || ($user = $cache->load($cache_id)) === false) {
			$user = $this->getUserByIdentity($identity);
			if ($cache) $cache->save($user, $cache_id);
		}
		return $user;
	}

	/**
	 * Return default city.
	 *
	 * @todo Default city name should be set up by some resource
	 * @return Blipoteka_City
	 */
	protected function getDefaultCity() {
		$name = self::DEFAULT_CITY_NAME;
		return Doctrine_Core::getTable('Blipoteka_City')->findOneByName($name);
	}

	/**
	 * Return default user name.
	 *
	 * @todo Default user name should be set up by some resource
	 * @return string
	 */
	protected function getDefaultUserName() {
		$name = self::DEFAULT_USER_NAME;
		return $name;
	}

	/**
	 * Activates user account pointed out by a token. Return
	 * Blipoteka_User entity if activation was successful, false
	 * if it failed.
	 *
	 * @return bool
	 */
	public function activateRegisteredAccount($token) {
		$user = Doctrine_Core::getTable('Blipoteka_User')->findOneByToken($token);
		// User with such token found, activate
		if ($user instanceof Blipoteka_User) {
			$activated_at = new Zend_Date();
			// Reset token
			$user->token = null;
			// Mark as active
			$user->is_active = true;
			$user->activated_at = $activated_at->get(Zend_Date::W3C);
			$user->save();
			// Authenticate user
			$this->authenticateUser($user);
		}
		return $user;
	}

	/**
	 * Authenticates user using his/her own credential data.
	 *
	 * @param Blipoteka_User $user
	 */
	public function authenticateUser(Blipoteka_User $user) {
		$auth = Zend_Auth::getInstance();

		$treatment = new Void_Auth_Credential_Treatment_None();

		$adapter = $this->_authAdapter;
		$adapter->setIdentity($user->get(Blipoteka_User::IDENTITY_FIELD));
		$adapter->setCredential($user->password);
		$adapter->setCredentialTreatment($treatment);
		$result = $auth->authenticate($adapter);

		return $result;
	}

	/**
	 * Try to sign in user using default adapter.
	 *
	 * @param Blipoteka_Form_Account_Signin $form
	 * @return Blipoteka_User|false
	 */
	public function signin(Blipoteka_Form_Account_Signin $form) {
		$auth = Zend_Auth::getInstance();

		$adapter = $this->_authAdapter;
		$adapter->setIdentity($form->getValue('email'));
		$adapter->setCredential($form->getValue('password'));
		$result = $auth->authenticate($adapter);
		// Check if authentication succeeded
		if ($result->isValid()) {
			// Get user entity by identity
			$user = $this->getUserByIdentity($auth->getIdentity());
			// Check if this account has been activated
			if ($this->isAccountActivated($user) === false) {
				$auth->clearIdentity();
				$form->addError("Najpierw musisz aktywować konto");
			} else {
				// Check if account is active (i.e. not blocked)
				if ($this->isAccountActive($user) === false) {
					$auth->clearIdentity();
					$form->addError("Twoje konto zostało zablokowane");
				} else {
					$this->signinSuccess($user);
				}
			}
		} else {
			$form->addError("Podano nieprawidłowy adres e-mail lub hasło");
		}
	}

	/**
	 * Update last successful login date and a number of
	 * successful logins.
	 *
	 * @param Blipoteka_User $user
	 * @return void
	 */
	protected function signinSuccess(Blipoteka_User $user) {
		// Get current date and time
		$date = new Zend_Date();
		$date = $date->get(Zend_Date::W3C);
		// Update user account data (without triggering Timestampable behavior)
		$query = Doctrine_Query::create();
		$query->update('Blipoteka_User');
		$query->set('log_date', '?', $date);
		$query->set('log_num', '?', $user->log_num + 1);
		$query->where('user_id = ?', $user->user_id);
		$query->execute();
	}

	/**
	 * Checks if user's account was activated
	 * (ie. activated_at is not NULL and token is NULL).
	 *
	 * @param Blipoteka_User $user
	 * @return bool
	 */
	public function isAccountActivated(Blipoteka_User $user) {
		return $user->activated_at !== null && $user->token === null;
	}

	/**
	 * Checks if user's account is active.
	 *
	 * @param Blipoteka_User $user
	 * @return bool
	 */
	public function isAccountActive(Blipoteka_User $user) {
		return $user->is_active;
	}

	/**
	 * Converts user's identity to Zend_Cache compatible cache_id.
	 *
	 * @param string $identity
	 * @return string
	 */
	protected function identityToCacheId($identity) {
		$cache_id = bin2hex($identity);
		return $cache_id;
	}

}
