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
 * @package    Blipoteka_Scripts
 * @copyright  Copyright (c) 2010-2011 Jakub Argasiński (argasek@gmail.com)
 * @license    http://blipoteka.pl/license Simplified BSD License
 */

require 'Console/Table.php';

/**
 * Book management tool script class
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class Blipoteka_Scripts_Book extends Void_Scripts {
	const VERSION = '0.2';
	const DESCRIPTION = 'Book management tool';

	public function __construct() {
		parent::__construct();
	}

	/**
	 * Run an action basing on a command issued
	 * @see Void_Scripts::run()
	 */
	public function run() {
		// Parse command line
		parent::run();
		// Run an action basing on a command issued
		switch ($this->cli->command_name) {
			case 'cover-import':
				$this->actionImportCover((bool) $this->cli->command->options['set'], (bool) $this->cli->command->options['force']);
				break;
			case 'cover-import-all':
				$this->actionImportAllCovers((bool) $this->cli->command->options['set'], (bool) $this->cli->command->options['force']);
				break;
			case 'cover-set':
				$this->actionSetCover();
				break;
			case 'list-all':
				$this->actionListAllBooks();
				break;
			default:
				$this->parser->displayUsage();
		}
	}

	/**
	 * Set book cover
	 */
	protected function actionSetCover() {
		$slug = $this->cli->command->args['slug'];
		$cover = $this->cli->command->args['cover'];
		// Display some additional information if verbosity was requested
		if ($this->cli->options['verbose'] === true) {
		}
		$service = new Blipoteka_Service_Book();
		$book = $service->getBookBySlug($slug, Doctrine_Core::HYDRATE_RECORD);
		$service->setCover($book, $cover);
	}

	/**
	 * Import book cover into orginal covers path with optional setting book cover.
	 *
	 * @param bool $setCover If true, set the imported cover as a book cover
	 * @param bool $forceImport Force import/set operation even if book already has a cover
	 */
	protected function actionImportCover($setCover = false, $forceImport = false) {
		$slug = $this->cli->command->args['slug'];
		$service = new Blipoteka_Service_Book();
		$book = $service->getBookBySlug($slug, Doctrine_Core::HYDRATE_RECORD);
		$this->importBookCover($book, $setCover, $forceImport);
	}

	/**
	 * Import covers for all books.
	 *
	 * @param bool $setCover If true, set the imported cover as a book cover
	 * @param bool $forceImport Force import/set operation even if book already has a cover
	 */
	protected function actionImportAllCovers($setCover = false, $forceImport = false) {
		$service = new Blipoteka_Service_Book();
		// Get all books
		$books = $service->getBookList(Doctrine_Core::HYDRATE_RECORD);
		if ($this->cli->options['verbose'] === true) {
			printf("Importing covers for %d book(s)...\n", $books->count());
		}
		// Import cover for each book
		foreach ($books as $book) {
			$this->importBookCover($book, $setCover, $forceImport);
		}
	}

	/**
	 * Import (and, optionally, set) a book cover.
	 *
	 * @todo Implement multiple importers mechanism
	 * @param Blipoteka_Book $book A book to import/set cover for.
	 * @param bool $setCover If true, set the imported cover as a book cover
	 * @param bool $forceImport Force import/set operation even if book already has a cover
	 * @return bool True, if import/set operation succeeded
	 */
	protected function importBookCover(Blipoteka_Book $book, $setCover = false, $forceImport = false) {
		// Display some additional information if verbosity was requested
		if ($this->cli->options['verbose'] === true) {
			printf("Importing cover for book '%s'\n", $book->title);
		}
		// If book has no cover, or we are forced to do so, import it
		if ($book->has_cover == false || $forceImport === true) {
			// Instantiate an importer
			$import = new Blipoteka_Import_LubimyCzytac($book);
			$coverUrl = $import->importCover();
			if ($coverUrl === false) {
				printf("Cover for the book '%s' not found or an error occured.\n", $book->title);
				return false;
			}
			// Save cover in original covers directory
			$cover = new Blipoteka_Book_Cover();
			$path = $cover->putOriginalFile($book, $coverUrl);
			// Set this cover as book cover
			if ($setCover === true) {
				$cover->set($book, $path);
			}
			printf("Successfully %s a cover for the book '%s'\n", ($setCover ? 'imported and set' : 'imported'), $book->title);
		} else {
			printf("The book '%s' already has a cover, skipping...\n", $book->title);
		}
		return true;
	}

	/**
	 * List all books
	 */
	protected function actionListAllBooks() {
		// Find all books
		$service = new Blipoteka_Service_Book();
		$books = $service->getBookList();
		if (count($books) == 0) {
			printf("No books have been found.\n");
			exit(0);
		}
		$headers = array("author", "title", "publisher");
		// List books
		$table = new Console_Table();
		$table->setHeaders($headers);
		foreach ($books as $book) {
			$row = array();
			$row[] = $book['authors'][0]['name'];
			$row[] = Void_Util_Text::truncate($book['title'], 30, true, '');
			$row[] = Void_Util_Text::truncate($book['publisher']['name'], 30, true, '');
			$table->addRow($row);
		}
		echo $table->getTable();
	}

	/**
	 * Set up additional command line options, arguments, commands etc.
	 */
	protected function setUpParser() {
		// Add an option to prevent performing of actual actions (those, which can modify a database)
		$this->parser->addOption('dryrun', array(
			'short_name'  => '-d',
			'long_name'   => '--dry-run',
			'action'      => 'StoreTrue',
			'description' => "don't perform actual changes"
		));

		$slug = array('description' => 'A slug of book (ie. title-of-book URL part)', 'action' => 'StoreString');

		// Add a command to list all books
		$command = $this->parser->addCommand('list-all', array('description' => 'list books'));

		// Add a command to set book cover
		$command = $this->parser->addCommand('cover-set', array('description' => 'set book cover from a given file'));
		$command->addArgument('slug', $slug);
		$command->addArgument('cover', array('description' => 'A cover file', 'action' => 'StoreString'));

		// Add a command to import book cover
		$command = $this->parser->addCommand('cover-import', array('description' => 'try to import book cover'));
		$command->addArgument('slug', $slug);
		// Add an option to set an imported cover as the book cover
		$command->addOption('set', array(
			'short_name'  => '-s',
			'long_name'   => '--set',
			'action'      => 'StoreTrue',
			'description' => "set an imported cover as the book cover"
		));
		// Add an option to force import/set operation even if book already has the cover
		$command->addOption('force', array(
			'short_name'  => '-f',
			'long_name'   => '--force',
			'action'      => 'StoreTrue',
			'description' => "force import/set operation"
		));

		// Add a command to import book cover
		$command = $this->parser->addCommand('cover-import-all', array('description' => 'try to import covers for all books'));
		// Add an option to set an imported cover as the book cover
		$command->addOption('set', array(
			'short_name'  => '-s',
			'long_name'   => '--set',
			'action'      => 'StoreTrue',
			'description' => "set an imported cover as the book cover"
		));
		// Add an option to force import/set operation even if book already has the cover
		$command->addOption('force', array(
			'short_name'  => '-f',
			'long_name'   => '--force',
			'action'      => 'StoreTrue',
			'description' => "force import/set operation"
		));
	}

}
