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
 * @copyright  Copyright (c) 2010-2011 Jakub Argasiński (argasek@gmail.com)
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

		$form = new Blipoteka_Form_Account(array('action' => $this->view->url(array(), 'account-update')));

		$session = new Zend_Session_Namespace('account');
		if ($session->form instanceof Blipoteka_Form_Account) {
			$this->view->accountForm = $session->form;
			$this->view->accountUpdateSuccess = $session->success;
		} else {
			$service = new Blipoteka_Service_User();
			$service->accountFormFromUser($form);
			$this->view->accountForm = $form;
		}
	}

	/**
	 * Register action
	 *
	 * @return void
	 */
	public function registerAction() {
		$this->view->headTitle('Rejestracja pomyślna');
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
					$result = $service->createUserFromForm($user, $form);
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
		// If this is POST request, try to authenticate using form credentials
		if ($this->getRequest()->isPost()) {
			$session = new Zend_Session_Namespace('signin');
			$form = $session->form;
			// Check for validity of form instance (one could delete cookie etc.)
			if ($form instanceof Blipoteka_Form_Account_Signin) {
				$session->setExpirationHops(1, null, true);
				if ($form->isValid($this->getRequest()->getParams())) {
					$service = new Blipoteka_Service_User($this->getRequest());
					$identity = $service->signin($form);
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
	 * Signup action
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
	 * Account activation action
	 *
	 * @todo Take care of page refreshes using session
	 * @return void
	 */
	public function activateAction() {
		$this->view->headTitle('Aktywacja konta');
		$this->view->success = false;
		$service = new Blipoteka_Service_User($this->getRequest());
		$user = $service->activateRegisteredAccount($this->getRequest()->token);
		if ($user instanceof Blipoteka_User) {
			$this->view->success = true;
		}
	}

	/**
	 * Sigin via OAuth action
	 *
	 * @return void
	 */
	public function signinOauthAction() {

	}

	/**
	 * Account update action
	 *
	 * @return void
	 */
	public function updateAction() {
		$success = false;
		// If this is POST request, proceed
		if ($this->getRequest()->isPost()) {
			$service = new Blipoteka_Service_User();
			$form = new Blipoteka_Form_Account(array('action' => $this->view->url(array(), 'account-update')));
			$form->populate($this->getRequest()->getParams());
			$session = new Zend_Session_Namespace('account');
			$session->setExpirationHops(1);
			// If form is valid
			if ($form->isValid($this->getRequest()->getParams())) {
				// Get currently authenticated user
				$user = $service->getAuthenticatedUser();
				// Update user's account from form data
				$service->updateAccountFromForm($form, $user);
				// There could be some errors even though form is valid, ie. e-mail not unique etc.
				if ($form->isErrors() === false) {
					// Invalidate authenticated user cache by force-read
					$user = $service->getAuthenticatedUser(true);
					// E-mail might have changed, so update currently authenticated identity
					Zend_Auth::getInstance()->getStorage()->write($service->getUserIdentity($user));
					$success = true;
				}
			}
			// A hack for problematic (notorious string/int conversion problem) tri-state gender field
			$gender = ($this->getRequest()->getParam('gender') === '' ? '' : (int) $this->getRequest()->getParam('gender'));
			$form->getElement('gender')->setValue($gender);
			// Save form in session
			$session->form = $form;
			$session->success = $success;
		}
		$this->_redirect($this->view->url(array(), 'account'));
	}

}
