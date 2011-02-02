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
 * Generic importer interface.
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
interface Blipoteka_Import_Interface {

	/**
	 * The constructor takes the $book entity, which serves as
	 * source of data used to search for an imported book.
	 *
	 * @param Blipoteka_Book $book This book data is used for search
	 */
	public function __construct(Blipoteka_Book $book);

	/**
	 * Import book data.
	 *
	 * The function uses all useful data provided by the $book
	 * to search for it. Then, if book is found, if fills as much
	 * much missing fields as it can (if missing field information
	 * is available).
	 *
	 * If $force argument is set to true, all information gathered
	 * during import is used and will overwrite existing $book fields.
	 *
	 * Returned object is a clone of $book with fields modified,
	 * or false, if import failed (i.e. server timeout, etc.)
	 *
	 * @param bool $force Force overwriting of already filled in fields of book
	 * @return Blipoteka_Book|false
	 */
	public function importBook($force = false);

	/**
	 * Import a book cover and (optional) copy it into $path.
	 *
	 * @param string $path A path where to place a file. If null, no copying
	 * @return string|false An URL to the book cover, false otherwise.
	 */
	public function importCover($path = null);

}
