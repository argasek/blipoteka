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
 * @package    Blipoteka_Form
 * @copyright  Copyright (c) 2010-2011 Jakub Argasiński (argasek@gmail.com)
 * @license    http://blipoteka.pl/license Simplified BSD License
 */

/**
 * User's profile settings form.
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class Blipoteka_Form_Account extends Zend_Form {

	public function init() {
		$this->setMethod('post');

		$user = Doctrine_Core::getTable('Blipoteka_User')->getRecordInstance();

		$validators = $user->getColumnValidatorsArray('email');
		$validators['email']->setMessage('Nieprawidłowy adres e-mail', Void_Validate_Email::INVALID);
		$email = $this->createElement('text', 'email');
		$email->setLabel('E-mail');
		$email->setFilters(array('StringTrim', 'StringToLower', 'StripNewlines', 'StripTags'));
		$email->addValidator('NotEmpty', true, array('messages' => array('isEmpty' => 'Adres e-mail nie może być pusty')));
		$email->addValidators($validators);
		$email->setRequired(true);
		$this->addElement($email);

		$validators = $user->getColumnValidatorsArray('name');
		$name = $this->createElement('text', 'name');
		$name->setLabel('Imię i nazwisko');
		$name->setFilters(array('StringTrim', 'StripNewlines', 'StripTags'));
		$name->addValidators($validators);
		$name->setRequired(true);
		$this->addElement($name);

		$city = $this->createElement('text', 'city');
		$city->setLabel('Miejscowość');
		$city->setFilters(array('StringTrim', 'StripNewlines', 'StripTags'));
		$city->setRequired(true);
		$this->addElement($city);

		$city_id = $this->createElement('hidden', 'city_id');
		$city_id->setFilters(array('Int'));
		$city_id->setRequired(true);
		$this->addElement($city_id);

		$lat = $this->createElement('hidden', 'lat');
		$lat->setFilters(array('LocalizedToNormalized'));
		$this->addElement($lat);

		$lng = $this->createElement('hidden', 'lng');
		$lng->setFilters(array('LocalizedToNormalized'));
		$this->addElement($lng);

		$validators = $user->getColumnValidatorsArray('gender');
		$gender = $this->createElement('radio', 'gender');
		$gender->setLabel('Jestem');
		$gender->addValidators($validators);
		$gender->setRequired(false);
		$gender->addMultiOptions(array('' => 'nie powiem', '0' => 'kobietą', '1' => 'mężczyzną'));
		$this->addElement($gender);

		$validators = $user->getColumnValidatorsArray('auto_accept_requests');
		$auto_accept_requests = $this->createElement('checkbox', 'auto_accept_requests');
		$auto_accept_requests->setLabel('Automatycznie akceptuj zamówienia');
		$auto_accept_requests->addValidators($validators);
		$auto_accept_requests->setRequired(false);
		$this->addElement($auto_accept_requests);

		$viewScript = new Zend_Form_Decorator_ViewScript();
		$viewScript->setViewScript('forms/account.phtml');
		$this->clearDecorators()->addDecorator($viewScript);
	}

}