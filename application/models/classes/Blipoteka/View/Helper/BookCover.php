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
 * @package    Blipoteka_View_Helper
 * @copyright  Copyright (c) 2010-2011 Jakub Argasiński (argasek@gmail.com)
 * @license    http://blipoteka.pl/license Simplified BSD License
 */

/**
 * Book cover URL view helper
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class Blipoteka_View_Helper_BookCover extends Zend_View_Helper_Abstract {
	/**
	 * Action helper
	 * @var Void_Controller_Action_Helper_PreviousPage
	 */
	protected $_helper;

	/**
	 * Get book cover URL for a book array.
	 *
	 * @param array $book A book array (with at least 'book_id' and 'isbn' keys)
	 * @param string $size Size of cover ('tiny', 'small', 'medium', 'original')
	 * @return string
	 */
	public function bookCover(array $book, $size = 'small') {
		$service = new Blipoteka_Service_Book();
		$relativeUrl = $service->getCoverUrl($book, $size);
		$url = $this->view->baseUrl($relativeUrl);
		return $url;
	}

}
