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
 * @package    Blipoteka_Form_Account
 * @copyright  Copyright (c) 2010 Jakub Argasiński (argasek@gmail.com)
 * @license    http://blipoteka.pl/license Simplified BSD License
 */

/**
 * Signup for a new account form
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class Blipoteka_Form_Account_Signup extends Zend_Form {

	public function init() {
		$this->setMethod('post');

		$user = Doctrine_Core::getTable('Blipoteka_User')->getRecordInstance();

		$email = $this->createElement('text', 'email');
		$email->setLabel('E-mail');
		$email->setFilters(array('StringTrim', 'StringToLower'));
		$email->setValidators($user->getColumnValidatorsArray('email'));
		$email->setRequired(true);
		$this->addElement($email);

		$login = $this->createElement('text', 'login');
		$login->setLabel('Login na Blip');
		$login->setFilters(array('StringTrim', 'StringToLower'));
		$login->setValidators($user->getColumnValidatorsArray('blip'));
		$login->setRequired(true);
		$this->addElement($login);

		$password = $this->createElement('password', 'password');
		$password->setLabel('Hasło');
		$password->setFilters(array('StringTrim'));
		$password->setValidators($user->getColumnValidatorsArray('password'));
		$password->setRequired(true);
		$this->addElement($password);

		$passwordconfirm = $this->createElement('password', 'passwordconfirm');
		$passwordconfirm->setLabel('Hasło');
		$passwordconfirm->setFilters(array('StringTrim'));
		$passwordconfirm->setValidators($user->getColumnValidatorsArray('password'));
		$passwordconfirm->setRequired(true);
		$this->addElement($passwordconfirm);

		$viewScript = new Zend_Form_Decorator_ViewScript();
		$viewScript->setViewScript('forms/signup.phtml');
		$this->clearDecorators()->addDecorator($viewScript);


	}
}