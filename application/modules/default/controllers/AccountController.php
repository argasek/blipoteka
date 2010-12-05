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
		$signinForm = new Blipoteka_Form_Account_Signup(array('action' => $this->view->url(array(), 'signin')));
		$this->view->signinForm = $signinForm;
	}

	/**
	 * Register action
	 *
	 * @return void
	 */
	public function registerAction() {

	}

	/**
	 * Signin action
	 *
	 * @return void
	 */
	public function signinAction() {

	}

	/**
	 * Signout action
	 *
	 * @return void
	 */
	public function signoutAction() {
		$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();

		$this->_redirector->gotoRoute(array(), 'index');
	}

	/**
	 * Sigup action
	 *
	 * @return void
	 */
	public function signupAction() {
		$signupForm = new Blipoteka_Form_Account_Signup(array('action' => $this->view->url(array(), 'account-register')));
		$this->view->signupForm = $signupForm;
	}

}
