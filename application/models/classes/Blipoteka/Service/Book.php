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
 * @package    Blipoteka_Service
 * @copyright  Copyright (c) 2010-2011 Jakub Argasiński (argasek@gmail.com)
 * @license    http://blipoteka.pl/license Simplified BSD License
 */

/**
 * Book related service class
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class Blipoteka_Service_Book extends Blipoteka_Service {
	/**
	 * Class of the record this service applies to
	 * @var string
	 */
	protected $_recordClass = 'Blipoteka_Book';

	/**
	 * The constructor
	 *
	 * @param Zend_Controller_Request_Abstract $request
	 * @param Void_Auth_Adapter_Interface $authAdapter
	 */
	public function __construct(Zend_Controller_Request_Abstract $request = null) {
		parent::__construct($request);
	}

	/**
	 * Get a book by a book identifier
	 *
	 * @see getBookBySlug
	 * @param string $book
	 * @return Blipoteka_Book
	 */
	public function getBook($book) {
		$item = $this->getBookBySlug($book);
		return $item;
	}

	/**
	 * Get a book by slug
	 *
	 * @param string $book
	 * @return Blipoteka_Book
	 */
	public function getBookBySlug($slug) {
		$query = $this->getBookQuery();
		$query->where('book.slug = ?', $slug);
		$item = $query->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
		return $item;
	}

	/**
	 * Get an author entity
	 *
	 * @param string $author
	 * @return Blipoteka_Author|false
	 */
	public function getAuthor($author) {
		$author = Doctrine::getTable('Blipoteka_Author')->findOneBySlug($author);
		return $author;
	}

	/**
	 * Get book list of given author
	 *
	 * @param string $author Slug of author
	 * @return array List of books by $author
	 */
	public function getBooksByAuthor($author) {
		$query = $this->getBookQuery();
		$query->addWhere('authors.slug = ?', $author);
		$result = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		return $result;
	}

	/**
	 * Return default query for selecting single books.
	 *
	 * @return Doctrine_Query
	 */
	protected function getBookQuery() {
		$query = Doctrine_Query::create();
		$query->select('book.*');
		$query->from($this->_recordClass . ' book');
		// User
		$query->leftJoin('book.user user');
		$query->addSelect($this->getUserFieldsQueryPart('user'));
		// Owner
		$query->leftJoin('book.owner owner');
		$query->addSelect($this->getUserFieldsQueryPart('owner'));
		// Holder
		$query->leftJoin('book.holder holder');
		$query->addSelect($this->getUserFieldsQueryPart('holder'));
		// Author
		$query->innerJoin('book.authors authors');
		$query->addSelect('authors.*');
		// Publisher
		$query->innerJoin('book.publisher publisher');
		$query->addSelect('publisher.*');
		// City
		$query->leftJoin('book.city city');
		$query->addSelect('city.*');

		return $query;
	}

	/**
	 * Get DQL query part with 'safe' list of user field (ie.
	 * usable and without sensible data like password).
	 *
	 * @param string $field Field being a Blipoteka_User
	 * @return string
	 */
	protected function getUserFieldsQueryPart($field) {
		$query = "$field.user_id, $field.blip";
		return $query;
	}

	/**
	 * Return collection of books owned by user
	 *
	 * @param Blipoteka_User $user
	 * @return Doctrine_Collection
	 */
	public function getOwnedBookList(Blipoteka_User $user) {
		return $user->books_owned;
	}

	/**
	 * Return default query for selecting collection of books.
	 *
	 * @return Doctrine_Query
	 */
	protected function getBookListQuery() {
		$query = Doctrine_Query::create();
		$query->select('book.type, book.status, book.title, book.slug');
		$query->from($this->_recordClass . ' book');
		// Owner
		$query->leftJoin('book.owner owner');
		$query->addSelect('owner.blip');
		// Holder
		$query->leftJoin('book.holder holder');
		$query->addSelect('holder.blip');
		// Holder's city name
		$query->leftJoin('holder.city holder_city');
		$query->addSelect('holder_city.name');
		// Author
		$query->innerJoin('book.authors authors');
		$query->addSelect('authors.name, authors.slug');
		// Publisher
		$query->innerJoin('book.publisher publisher');
		$query->addSelect('publisher.name');
		// Sorting
		$query->orderBy('authors.name');
		$query->addOrderBy('book.title');
		return $query;
	}

	/**
	 * Get collection of all books.
	 * @return Doctrine_Collection
	 */
	public function getBookList() {
		$query = $this->getBookListQuery();
		$result = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		return $result;
	}

	/**
	 * Get paginator entity for collection of all books.
	 * @return Zend_Paginator
	 */
	public function getBookListPaginator() {
		$pageNumber = 0;
		$itemCountPerPage = 20;

		// Get book list query
		$query = $this->getBookListQuery();

		// Create an appropriate adapter
		$adapter = new Zend_Paginator_Adapter_Doctrine($query);
		$adapter->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);

		// Create paginator
		$paginator = new Zend_Paginator($adapter);
		$paginator->setCurrentPageNumber($pageNumber);
		$paginator->setItemCountPerPage($itemCountPerPage);

		return $paginator;
	}

	/**
	 * Get book cover relative URL based on $book slug/id
	 * and $size parameter. If book has no cover, URL to
	 * or non-existant file.
	 *
	 * @param array $book A book array (with at least 'has_cover', 'book_id' and 'slug' keys)
	 * @param string $size Size of cover
	 * @return string
	 */
	public function getCoverUrl(array $book, $size = 'small') {
		$cover = new Blipoteka_Book_Cover();
		$relativeUrl = $cover->getUrl($book, $size);

		return $relativeUrl;
	}

	/**
	 * Set cover of a $book. If $path is not null, it must point to cover file location.
	 * Otherwise, we try to search for a file in a default original covers location.
	 * Return true if operation succeeded.
	 *
	 * @param Blipoteka_Book $book
	 * @param string $path A complete path to an original file
	 * @return bool
	 */
	public function setCover(Blipoteka_Book $book, $path = null) {
		$cover = new Blipoteka_Book_Cover();
		$result = $cover->set($book, $path);
		return $result;
	}

}
