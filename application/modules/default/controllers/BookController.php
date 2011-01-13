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
 * @package    Blipoteka_Book
 * @copyright  Copyright (c) 2010-2011 Jakub Argasiński (argasek@gmail.com)
 * @license    http://blipoteka.pl/license Simplified BSD License
 */

/**
 * Books' account controller.
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class BookController extends Blipoteka_Controller {

	/**
	 * Index action
	 *
	 * @return void
	 */
	public function indexAction() {
		$service = new Blipoteka_Service_Book();
		$paginator = $service->getBookListPaginator();
		$paginator->setCurrentPageNumber($this->_getParam('page'));
		$this->view->books = $paginator;
		$this->_helper->previousPage->setPreviousPage('book-index');
	}

}
