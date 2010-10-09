#!/usr/bin/env php
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
 * @copyright  Copyright (c) 2010 Jakub Argasiński (argasek@gmail.com)
 * @license    http://blipoteka.pl/license Simplified BSD License
 */

define('APPLICATION_DOCTRINE_SCRIPT', true);

include dirname(__FILE__) . DIRECTORY_SEPARATOR . 'doctrine-console-common.php';

require_once 'Console/ProgressBar.php';
require_once 'Console/CommandLine.php';

class Import_GeoNames {
	const VERSION = '0.1';
	const DESCRIPTION = 'Import GeoNames CSV file into the Blipoteka database';

	private $_columns = array();

	public function __construct() {
		$this->_columns = array('city_id', 'name', 'asciiname', 'alternatenames', 'lat', 'lng', 'feature_class', 'feature_code', 'country_code', 'cc2', 'admin1_code', 'admin2_code', 'admin3_code', 'admin4_code', 'population', 'elevation', 'gtpo30', 'timezone', 'modified_at');
	}

	public function getCsvFile(Console_CommandLine_Result $cli) {
		// No path provided, let's assume a fixed directory
		if ($cli->options['path'] === null) {
			$geonamesPath = APPLICATION_PATH . DS . 'models' . DS . 'fixtures' . 'geonames' . DS;
		} else {
			$geonamesPath = rtrim($cli->options['path'], DS) . DS;
		}

		// Combine a path with a filename
		$geonamesFileName = $geonamesPath . mb_strtoupper($cli->args['language']) . '.txt';

		// Create file object
		try {
			$file = new SplFileObject($geonamesFileName);
			$file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);
			$file->setCsvControl("\t");
		} catch (RuntimeException $e) {
			printf("An error ocurred: %s\n" . $e->getMessage());
			exit($e->getCode());
		}

		return $file;
	}

	/**
	 * Get a number of lines in CSV file. Not very ellegant, but works.
	 *
	 * @param SplFileObject $file
	 * @return interger Number of lines
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
	 * @param Blipoteka_City $city
	 * @return array Ignored columns
	 */
	private function getIgnoredColumns(Blipoteka_City $city) {
		$ignoredColumns = array_diff($this->_columns, $city->getTable()->getColumnNames());
		return $ignoredColumns;
	}

	public function import(SplFileObject $file, Blipoteka_City $blipotekaCity, Console_CommandLine_Result $cli) {
		// We don't need all columns from imported CSV file, let's ignore them
		$ignoredColumns = $this->getIgnoredColumns($blipotekaCity);

		// Get numer of lines in file
		$lineCount = $this->getCsvFileLineCount($file);

		// Show a progress bar
		if ($cli->options['verbose']) {
			$progressBar = new Console_ProgressBar('Importing: [%bar%] %percent%', '=', '.', '60', $lineCount - 1);
			$line = 0;
		}

		$lineImported = 0;

		// Iterate file by line
		foreach ($file as $row) {
			// We need to increase progressbar here -- because we filter out some lines later
			if ($cli->options['verbose']) $progressBar->update($line);
			$line++;

			// Prepare associative array in format acceptable by fromArray() / synchronizeFromArray()
			$record = array_combine($this->_columns, $row);

			// We are interested in cities, villages etc. only
			if ($record['feature_class'] !== 'P') continue;

			// We want to skip ambadoned places, sections etc.
			if (preg_match('/^PPL[SRQX]$/s', $record['feature_code'])) continue;

			// Remove unnecessary columns
			foreach ($ignoredColumns as $ignoredColumn) unset($record[$ignoredColumn]);

			// Transform empty strings onto NULL values
			$record = array_map(function($item) { return ($item === '' ? null : $item); }, $record);

			// Save record to the database
			$city = Doctrine_Core::getTable('Blipoteka_City')->find($record['city_id']);
			$city = ($city instanceof Blipoteka_City ? $city : clone $blipotekaCity);
			$city->synchronizeWithArray($record);
			$city->free(true);

			// Increase number of imported records
			$lineImported++;
		}

		printf("\nSuccessfully imported %d out of %d records.\n", $lineImported, $lineCount);
	}

}

// Create the parser
$parser = new Console_CommandLine(array(
    'description' => Import_GeoNames::DESCRIPTION,
    'version'     => Import_GeoNames::VERSION
));

// Add an option to make the program verbose
$parser->addOption('verbose', array(
    'short_name'  => '-v',
    'long_name'   => '--verbose',
    'action'      => 'StoreTrue',
    'description' => 'turn on verbose output'
));

// Add an option to specify the path where GeoNames files reside
$parser->addOption('path', array(
    'short_name'  => '-p',
    'long_name'   => '--path',
    'action'      => 'StoreString',
    'description' => 'directory path to GeoNames files'
));

// Add an option to choose language file
$parser->addArgument('language', array(
    'action'      => 'StoreString',
    'description' => "language code of imported file ('pl', 'en', etc.)"
));

// Parse the command line arguments
try {
	$cli = $parser->parse();
} catch (Exception $e) {
	$parser->displayError($e->getMessage());
}

// Import the file
$importer = new Import_GeoNames();
$file = $importer->getCsvFile($cli);

$city = new Blipoteka_City();

$importer->import($file, $city, $cli);
