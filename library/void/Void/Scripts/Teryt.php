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
 * @package    Void_Scripts
 * @copyright  Copyright (c) 2010-2011 Jakub Argasiński (argasek@gmail.com)
 * @license    http://tekla.art.pl/license/void-simplified-bsd-license.txt Simplified BSD License
 */

require_once 'Console/ProgressBar.php';
require_once 'mbfunctions.php';

/**
 * Teryt database import tool script class
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class Void_Scripts_Teryt extends Void_Scripts {
	const VERSION = '0.1';
	const DESCRIPTION = 'Import Teryt CSV files into the database';

	/**
	 * City object
	 * @var Doctrine_Record
	 */
	private $_city;

	/**
	 * City object class name
	 * @var Doctrine_Record
	 */
	private $_cityComponentName;

	/**
	 * Terc object
	 * @var Doctrine_Record
	 */
	private $_terc;

	/**
	 * Terc object class name
	 * @var Doctrine_Record
	 */
	private $_tercComponentName;

	/**
	 * A path where importer should look for Teryt files,
	 * when no path is specified
	 * @var string
	 */
	private $_defaultTerytPath;

	/**
	 * The constructor
	 *
	 * @param Doctrine_Record $city A city template entity
	 * @param Doctrine_Record $terc A territory template entity
	 * @param string $defaultTerytPath A default path to TERYT CSV files
	 */
	public function __construct(Doctrine_Record $city, Doctrine_Record $terc, $defaultTerytPath = '') {
		parent::__construct();
		$this->_defaultTerytPath = $defaultTerytPath;
		$this->_city = $city;
		$this->_cityComponentName = $city->getTable()->getComponentName();
		$this->_terc = $terc;
		$this->_tercComponentName = $terc->getTable()->getComponentName();
	}

	public function run() {
		parent::run();

		// Import administrative units
		$this->importTerc($this->getCsvFile('TERC'));
		// Import cities, towns, villages, etc.
		$this->importSimc($this->getCsvFile('SIMC'));
	}

	/**
	 * Get CSV file as SplFileObject.
	 *
	 * @return SplFileObject
	 */
	public function getCsvFile($name) {
		// No path provided, let's assume a fixed directory
		if ($this->cli->options['path'] === null) {
			$terytPath = $this->getDefaultTerytPath();
		} else {
			$terytPath = rtrim($this->cli->options['path'], DS) . DS;
		}

		// Combine a path with a filename
		$terytFileName = $terytPath . mb_strtoupper($name) . '.txt';

		// Create file object
		try {
			$file = new SplFileObject($terytFileName);
			$file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);
			$file->setCsvControl(";");
		} catch (RuntimeException $e) {
			printf("An error ocurred: %s\n" , $e->getMessage());
			exit($e->getCode());
		}

		return $file;
	}

	/**
	 * Get default location of TERYT CSV files.
	 *
	 * @return string
	 */
	protected function getDefaultTerytPath() {
		return $this->_defaultTerytPath;
	}

	/**
	 * Get a number of lines in CSV file. Not very ellegant, but works.
	 *
	 * @param SplFileObject $file
	 * @return integer Number of lines
	 */
	private function getCsvFileLineCount(SplFileObject $file) {
		$file->seek(PHP_INT_MAX);
		$count = $file->key();
		$file->rewind();
		return $count;
	}

	/**
	 * Get list of columns from CSV file we should skip when inserting
	 * record into database table.
	 *
	 * @param Doctrine_Record $city
	 * @return array Ignored columns
	 */
	private function getIgnoredColumns(Doctrine_Record $city) {
		$ignoredColumns = array_diff($this->_columns, $city->getTable()->getColumnNames());
		return $ignoredColumns;
	}

	/**
	 * Import TERC CSV into database.
	 *
	 * @param SplFileObject $file TERC CSV file object
	 */
	public function importTerc(SplFileObject $file) {
		// Get numer of lines in file
		$lineCount = $this->getCsvFileLineCount($file);
		// We skip first line (it contains column names)
		$lineCount = ($lineCount > 0 ? $lineCount-- : $lineCount);

		// Show a progress bar
		if ($this->cli->options['verbose']) {
			$progressBar = new Console_ProgressBar('Importing: [%bar%] %percent%', '=', '.', '60', $lineCount - 1);
			$line = 0;
		}

		$lineImported = 1;

		// Iterate file by line
		while ($row = $file->fgetcsv()) {
			// We need to increase progressbar here -- because we filter out some lines later
			if ($this->cli->options['verbose']) $progressBar->update($line++);

			list($province, $district, $borough, $type, $name, , $modified_at, ) = $row;

			// Prepare TERC ID
			$terc_id = $province . $district . $borough . $type;

			// Create or update database record
			$terc = Doctrine_Core::getTable($this->_tercComponentName)->find($terc_id);
			$terc = ($terc instanceof $this->_tercComponentName ? $terc : clone $this->_terc);

			$asciiname = mb_strip_accents($name);

			$terc->terc_id = $terc_id;
			$terc->province = $province;
			$terc->district = $district;
			$terc->borough = $borough;
			$terc->type = $type;
			$terc->modified_at = $modified_at;
			$terc->name = $name;
			$terc->asciiname = $asciiname;

			// Save record
			$terc->save();
			$terc->free();

			// Increase number of imported records
			$lineImported++;
		}

		printf("\nSuccessfully imported %d out of %d TERC records.\n", $lineImported, $lineCount);
	}

	/**
	 * Import SIMC CSV into database.
	 *
	 * @param SplFileObject $file TERC CSV file object
	 */
	public function importSimc(SplFileObject $file) {

	}

	protected function setUpParser() {
		// Add an option to specify the path where Teryt files reside
		$this->parser->addOption('path', array(
			'short_name'  => '-p',
			'long_name'   => '--path',
			'action'      => 'StoreString',
			'description' => 'directory path to Teryt files'
	    ));

	}

}
