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
 * @package    Blipoteka_Import
 * @copyright  Copyright (c) 2010-2011 Jakub Argasiński (argasek@gmail.com)
 * @license    http://blipoteka.pl/license Simplified BSD License
 */

require_once 'phpQuery.php';
require_once 'mbfunctions.php';

/**
 * Import book data from LubimyCzytać.pl
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
final class Blipoteka_Import_LubimyCzytac extends Blipoteka_Import {
	const BOOK_SEARCH_URL = 'http://lubimyczytac.pl/szukaj/ksiazki?phrase=';
	const BOOK_URL = 'http://lubimyczytac.pl/';

	/**
	 * Import book data.
	 *
	 * @see Blipoteka_Import_Interface::importBook()
	 */
	public function importBook($force = false) {
		return $this->book;
	}

	/**
	 * Import book cover and (optional) copy it into $path.
	 *
	 * @see Blipoteka_Import_Interface::importCover()
	 */
	public function importCover($path = null) {
		$bookUrl = $this->getBookUrl();
		// If could not get book URL, we cannot import cover
		if ($bookUrl === false) return false;
		// Sleep for a second, then load the second document
		sleep(1);
		$pq = phpQuery::newDocumentFileHTML($bookUrl);
		// Find first search result
		$anchor = $pq->find('#bookDetails > a:first-child');
		// If DOM element is not a HTML anchor, either no results were found,
		// or returned document structure has changed and we need to update algorithm.
		if ($anchor->is('a') === false) {
			return false;
		}
		// The anchor's href attribute points to the cover image file.
		$imageUrl = $anchor->attr('href');
		// Optional copying
		if ($path !== null) {
			copy($imageUrl, $path);
		}
		// Return the image URL
		return $imageUrl;
	}

	protected function getBookSearchUrl($phrase) {
		return self::BOOK_SEARCH_URL . urlencode($phrase);
	}

	/**
	 * Get URL of book page, or false, if none found / error occured.
	 *
	 * @return string|false
	 */
	protected function getBookUrl() {
		$phrase = $this->book->title;
		$url = $this->getBookSearchUrl($phrase);
		// Sleep for a second, then load a first document
		sleep(1);
		// Load the HTML document with search results
		$pq = phpQuery::newDocumentFileHTML($url);
		// Find a first search result
		$anchors = $pq->find('#left > .container > .book-list-container > ul > li > a:first-child');
		// No results
		if ($anchors->count() == 0) return false;
		$matches = array();
		$dom = array();
		// Get book authors as array
		foreach ($this->book->authors as $author) $authors[] = $author['name'];
		// Iterate trough all results until matching one found
		foreach ($anchors as $anchor) {
			// Find DOM element containing book title
			$dom['title'] = pq($anchor);
			// If DOM element is not a HTML anchor, either no results were found,
			// or returned document structure has changed and we need to update algorithm.
			if ($dom['title']->is('a') === false) return false;
			// Find DOM element containing book author
			$dom['author'] = $dom['title']->siblings('div.book-item-data')->find('div.book-item-title > a.bookAuthorS');
			// If DOM element is not a HTML anchor, either no results were found,
			// or returned document structure has changed and we need to update algorithm.
			if ($dom['author']->is('a') === false) return false;
			// Get book author
			$author = trim($dom['author']->text());
			// Change all whitespace characters to single spaces
			$author = preg_replace('/\s+/u', ' ', $author);
			// Split author name parts
			$author = explode(' ', $author);
			// Get last name of author
			$lastname = array_pop($author);
			// Prepend last name of author before first name(s)
			array_unshift($author, $lastname);
			// Join author name parts
			$author = implode(' ', $author);
			// If title match, try to find matching author
			if ($dom['title']->attr('title') == $this->book->title) {
				// Don't search anymore if matching author found
				if (in_array($author, $authors)) {
					// Reset matches array
					$matches = array();
					break;
				}
			}
			$levenshtein = 0;
			// Calculate Levenshtein difference between title found and expected
			$levenshtein += levenshtein($dom['title']->attr('title'), $this->book->title);
			// Calculate Levenshtein differences between author found and all book authors and choose smallest one
			$l = array();
			foreach ($authors as $bookAuthor) $l[] = levenshtein($author, $bookAuthor);
			$levenshtein += min($l);
			// Add match to the $matches array
			$matches[$levenshtein] = $dom['title']->attr('href');
		}
		// If matches array is not empty, choose the best match
		if (count($matches)) {
			// Choose lowest Levensthein difference
			$href = $matches[min(array_keys($matches))];
		} else {
			// There was an exact match
			$href = $dom['title']->attr('href');
		}
		// The anchor's href attribute points to the real book page.
		$bookUrl = self::BOOK_URL . $href;
		return $bookUrl;
	}
}
