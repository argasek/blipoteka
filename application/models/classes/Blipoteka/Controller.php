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
 * @package    Blipoteka
 * @copyright  Copyright (c) 2010-2011 Jakub Argasiński (argasek@gmail.com)
 * @license    http://blipoteka.pl/license Simplified BSD License
 */

/**
 * Generic Blipoteka controller class
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
abstract class Blipoteka_Controller extends Zend_Controller_Action {

	public function init() {
		$this->view->hasIdentity = Zend_Auth::getInstance()->hasIdentity();
		if (Zend_Auth::getInstance()->hasIdentity() && $this->view->identity === null) {
			$service = new Blipoteka_Service_User();
			$this->view->identity = $service->getAuthenticatedUser();
		}
	}

	/**
	 * Forward to 404 page (with optional custom $message).
	 * @param string $message
	 */
	public function notfound($message = null) {
		$this->_forward('not-found', 'error', null, array('message' => $message));
	}

}
