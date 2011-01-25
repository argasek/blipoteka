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
 * @package    Void_Controller_Action_Helper
 * @copyright  Copyright (c) 2010-2011 Jakub Argasiński (argasek@gmail.com)
 * @license    http://tekla.art.pl/license/void-simplified-bsd-license.txt Simplified BSD License
 */

/**
 * Action helper for saving and restoring previous page user was in.
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class Void_Controller_Action_Helper_PreviousPage extends Zend_Controller_Action_Helper_Abstract {

	/**
	 * Save current page as previous page for a given namespace.
	 *
	 * @param string|null $namespace
	 * @param string|null $defaultPage
	 */
	public function setPreviousPage($namespace = null, $defaultPage = null) {
		$request = $this->getRequest();
		$defaultPage = $defaultPage ?: $request->getRequestUri();
		$history = $this->getSessionNamespace($namespace);
		$history->previous = $request->getRequestUri() ?: $defaultPage;
	}

	/**
	 * Get previous page visited by user for a given namespace
	 *
	 * @param string $namespace
	 * @return string
	 */
	public function getPreviousPage($namespace) {
		$history = $this->getSessionNamespace($namespace);
		return $history->previous;
	}

	/**
	 * Get session namespace entity by name
	 *
	 * @return Zend_Session_Namespace
	 */
	protected function getSessionNamespace($namespace = null) {
		$namespace = $namespace ?: $this->getDefaultNamespace();
		return new Zend_Session_Namespace('history' . '-' . $namespace);
	}

	/**
	 * Get default session namespace for current module, controller
	 * and action if none provided.
	 *
	 * @return string
	 */
	protected function getDefaultNamespace() {
		$request = $this->getRequest();
		$namespace = $request->getModuleName() . '-' . $request->getControllerName() . '-' . $request->getActionName();
		return $namespace;
	}

}
