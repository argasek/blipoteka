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
 * @package    Blipoteka_Account
 * @copyright  Copyright (c) 2010 Jakub Argasiński (argasek@gmail.com)
 * @license    http://blipoteka.pl/license Simplified BSD License
 */

/**
 * The user's account controller.
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class AccountController extends Blipoteka_Controller {

	/**
	 * Index action
	 *
	 * @return void
	 */
	public function indexAction() {
		$this->view->headTitle('Twój profil');
	}

	/**
	 * Register action
	 *
	 * @return void
	 */
	public function registerAction() {
		// If this is POST request, try to authenticate using form credentials
		if ($this->getRequest()->isPost()) {
			$session = new Zend_Session_Namespace('signup');
			$form = $session->form;
			// Check for validity of form instance (one could delete cookie etc.)
			if ($form instanceof Blipoteka_Form_Account_Signup) {
				$session->setExpirationHops(1, null, true);
				// If form data is valid, try to create a new user account
				if ($form->isValid($this->getRequest()->getParams())) {
					$user = new Blipoteka_User();
					$service = new Blipoteka_Service_User($this->getRequest());
					$result = $service->createUser($user, $form);
					// If user account was created successfuly, pass account
					// data through session with one hop validity
					if ($result === true) {
						$session = new Zend_Session_Namespace('signupsuccess');
						$session->user = $user->toArray();
						$session->setExpirationHops(1, null, true);
						$this->_redirect($this->view->url(array(), 'account-register'));
					}
				}
			}
		// This is GET request
		} else {
			// If user data found within signupsuccess session namespace,
			// render registration success view
			$session = new Zend_Session_Namespace('signupsuccess');
			if (is_array($session->user)) {
				$this->view->user = $session->user;
				return;
			}
		}

		$this->_redirect($this->view->url(array(), 'signup'));
	}

	/**
	 * Signin action
	 *
	 * @return void
	 */
	public function signinAction() {
		$auth = Zend_Auth::getInstance();
		$adapter = Zend_Registry::get('auth-adapter');
		// If this is POST request, try to authenticate using form credentials
		if ($this->getRequest()->isPost()) {
			$session = new Zend_Session_Namespace('signin');
			$form = $session->form;
			// Check for validity of form instance (one could delete cookie etc.)
			if ($form instanceof Blipoteka_Form_Account_Signin) {
				$session->setExpirationHops(1, null, true);
				if ($form->isValid($this->getRequest()->getParams())) {
					$default = $adapter->getDefaultAdapter();
					$default->setIdentity($form->getValue('email'));
					$default->setCredential($form->getValue('password'));
					$result = $auth->authenticate($adapter);
					if ($result->isValid()) {
					} else {
						$form->addError("Podano nieprawidłowy adres e-mail lub hasło");
					}
				}
			}
		}
		$this->_redirect($this->view->url(array(), 'index'));
	}

	/**
	 * Signout action
	 *
	 * @return void
	 */
	public function signoutAction() {
		$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();

		$this->_redirect($this->view->url(array(), 'index'));
	}

	/**
	 * Sigup action
	 *
	 * @return void
	 */
	public function signupAction() {
		$this->view->headTitle('Zarejestruj się');
		$form = new Blipoteka_Form_Account_Signup(array('action' => $this->view->url(array(), 'account-register')));
		$session = new Zend_Session_Namespace('signup');
		if ($session->form instanceof Blipoteka_Form_Account_Signup) {
			$this->view->form = $session->form;
		} else {
			$this->view->form = $form;
		}
		$session->form = $form;
	}

	/**
	 * Sigin via OAuth action
	 *
	 * @return void
	 */
	public function signinOauthAction() {

	}

}
