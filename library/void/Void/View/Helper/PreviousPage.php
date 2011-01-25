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
 * @package    Void_View_Helper
 * @copyright  Copyright (c) 2010-2011 Jakub Argasiński (argasek@gmail.com)
 * @license    http://tekla.art.pl/license/void-simplified-bsd-license.txt Simplified BSD License
 */

/**
 * Previous page view helper
 * @see Void_Controller_Plugin_PreviousPage
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class Void_View_Helper_PreviousPage extends Zend_View_Helper_Abstract {
	/**
	 * Action helper
	 * @var Void_Controller_Action_Helper_PreviousPage
	 */
	protected $_helper;

	/**
	 * Get previous page URL for a given $namespace.
	 *
	 * @param string $namespace
	 * @return string
	 */
	public function previousPage($namespace) {
		if ($this->_helper === null) {
			$this->_helper = Zend_Controller_Action_HelperBroker::getStaticHelper('PreviousPage');
		}
		return $this->_helper->getPreviousPage($namespace);
	}

}
