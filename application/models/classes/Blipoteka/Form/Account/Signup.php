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

		$validators = $user->getColumnValidatorsArray('email');
		$validators['email']->setMessage('Nieprawidłowy adres e-mail', Void_Validate_Email::INVALID);
		$email = $this->createElement('text', 'email');
		$email->setLabel('E-mail');
		$email->setFilters(array('StringTrim', 'StringToLower'));
		$email->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => 'Adres e-mail nie może być pusty')));
		$email->addValidators($validators);
		$email->setRequired(true);
		$this->addElement($email);

		$validators = $user->getColumnValidatorsArray('blip');
		$login = $this->createElement('text', 'login');
		$login->setLabel('Login na Blip');
		$login->setFilters(array('StringTrim', 'StringToLower'));
		$login->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => 'Login nie może być pusty')));
		$login->addValidators($validators);
		$login->setRequired(true);
		$this->addElement($login);

		$validators = $user->getColumnValidatorsArray('password');
		$password = $this->createElement('password', 'password');
		$password->setLabel('Hasło');
		$password->setFilters(array('StringTrim'));
		$password->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => 'Hasło nie może być puste')));
		$password->addValidators($validators);
		$password->setRequired(true);
		$this->addElement($password);

		$validators = $user->getColumnValidatorsArray('password');
		$passwordconfirm = $this->createElement('password', 'passwordconfirm');
		$passwordconfirm->setLabel('Powtórz hasło');
		$passwordconfirm->setFilters(array('StringTrim'));
		$passwordconfirm->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => 'Hasło nie może być puste')));
		$passwordconfirm->addValidators($validators);
		$passwordconfirm->setRequired(true);
		$this->addElement($passwordconfirm);

		$viewScript = new Zend_Form_Decorator_ViewScript();
		$viewScript->setViewScript('forms/signup.phtml');
		$this->clearDecorators()->addDecorator($viewScript);


	}
}