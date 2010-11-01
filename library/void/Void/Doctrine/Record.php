<?php

/**
 * Void
 *
 * LICENSE
 *
 * This source file is subject to the Simplified BSD License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://tekla.art.pl/license/void-simplified-bsd-license.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to argasek@gmail.com so I can send you a copy immediately.
 *
 * @category   Void
 * @package    Void_Doctrine
 * @copyright  Copyright (c) 2010 Jakub Argasiński (argasek@gmail.com)
 * @license    http://tekla.art.pl/license/void-simplified-bsd-license.txt Simplified BSD License
 */

/**
 * Extended Doctrine Record abstract class with custom features,
 * like Zend_Validate based validators, etc.
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
abstract class Void_Doctrine_Record extends Doctrine_Record {

	/**
	 * Get an option value for a column (null, if option is not set)
	 *
	 * @param string $field
	 * @param string $option
	 *
	 * @return mixed|null
	 */
	public function getColumnOption($columnName, $option) {
        $column = $this->getTable()->getColumnDefinition($columnName);
        return (array_key_exists($option, $column) ? $column[$option] : null);
	}

	/**
	 * Attach a Zend_Validate validator chain to a field
	 *
	 * @param string $field
	 * @param Zend_Validate $validators
	 */
	protected function setColumnValidators($field, Zend_Validate $validators) {
		$extra = $this->getColumnOption($field, 'extra');
		if (is_array($extra)) {
			$extra = array_merge($extra, array('validators' => $validators));
		} elseif ($extra === null) {
			$extra = array('validators' => $validators);
		} else {
			throw new Doctrine_Record_Exception("Column '%s' 'extra' option is neighter an array nor NULL, don't know what to do.", Doctrine_Core::ERR_UNSUPPORTED);
		}
		$this->setColumnOption($field, 'extra', $extra);
	}

	/**
	 * Returns validator chain for a given field (false, if none set)
	 *
	 * @param string $field
	 * @return Zend_Validate|false
	 */
	protected function getColumnValidators($field) {
		$extra = $this->getColumnOption($field, 'extra');
		return (isset($extra['validators']) ? $extra['validators'] : false);
	}

	/**
	 * Validate fields using a more flexible Zend_Validate validator
	 * chains in addition to a standard Doctrine validation mechanism.
	 *
	 * @see Doctrine_Record::validate()
	 */
	protected function validate() {
		$errorStack = $this->getErrorStack();
		foreach ($this->getTable()->getColumns() as $field => $options) {
			$validators = $this->getColumnValidators($field);
			$value = $this->get($field);
			if ($value !== null && $validators instanceof Zend_Validate && $validators->isValid($value) === false) {
				foreach ($validators->getMessages() as $message) {
					$errorStack->add($field, $message);
				}
			}
		}
	}

	/**
	 * Setup validators, etc.
	 *
	 * @see Doctrine_Record::setUp()
	 */
	public function setUp() {
		$this->setUpValidators();
	}

	/**
	 * A template function for setting up validators.
	 *
	 * @example Validate e-mail address field:
	 * $validators = new Zend_Validate();
	 * $validators->addValidator(new Void_Validate_Email());
	 * $validators->addValidator(new Zend_Validate_...());
	 * $this->setColumnValidators('email', $validators);
	 *
	 */
	protected function setUpValidators() {
	}

}