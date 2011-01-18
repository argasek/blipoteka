<?php

/**
 * Blipoteka.pl
 *
 * LICENSE
 *
 * This source file is subject to the Simplified BSD License that is
 * bundled with this package in the file docs/LICENSE.txt.
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

// A minimum PHP interpreter version to run the application.
define('MINIMUM_PHP_VERSION', '5.3.0');

// Basic PHP version check. We need to do it now, because later instructions would cause simple parser Fatal Error.
if (version_compare(phpversion(), MINIMUM_PHP_VERSION, '<')) {
	// Display an error in a more pleasant way, when script's being run from a web browser
	if (PHP_SAPI !== 'cli') header('Content-Type: text/plain; charset=UTF-8');
	exit('This application requires PHP version ' . MINIMUM_PHP_VERSION . ' or newer, but your installed version is currently ' . phpversion() . ". It makes me a sad panda." . PHP_EOL);
}

// A handful shortcuts for filesystem directory separator and system variable PATH separator
defined('DS') || define('DS', DIRECTORY_SEPARATOR);
defined('PS') || define('PS', PATH_SEPARATOR);
// Define the root directory path and the path to an application directory, if it's not defined already
defined('ROOT_PATH') || define('ROOT_PATH', realpath(__DIR__ . DS . '..'));
defined('APPLICATION_PATH') || define('APPLICATION_PATH', ROOT_PATH . DS . 'application');
// Define the application environment (context), if it's not defined already
defined('APPLICATION_ENV') || define('APPLICATION_ENV', getenv('APPLICATION_ENV') ?: 'production');

// Here we add include paths to files which are an absolute must
set_include_path(
	ROOT_PATH . DS. 'library' . DS . 'vendor' . PS .
	get_include_path()
);

require_once 'Zend/Application.php';
require_once 'Zend/Config/Ini.php';
require_once 'Zend/Cache.php';

// We load main application configuration file, all sections (null) and we allow to further modify configuration values (true)
try {
	$configPath = APPLICATION_PATH . DS . 'configs' . DS;
	$ini = array(
		'application' => $configPath . 'application.ini',
		'routing' => $configPath . 'routing.ini',
		'custom' => $configPath . 'custom.ini',
	);
	// Prepare configuration cache frontend
	$frontendOptions = array(
		'lifetime' => 7200,
		'automatic_serialization' => true,
		'master_files' => $ini,
		'ignore_missing_master_files' => true
	);
	// Prepare configuration cache backend
	$backendOptions = array(
		'cache_dir' => APPLICATION_PATH . DS . 'cache'
	);
	// Instantiate configration cache object
	$configCache = Zend_Cache::factory('File', 'Apc', $frontendOptions, $backendOptions);
	// Try to load configuration from cache. In case of cache miss, parse files.
	if (($config = $configCache->load('config')) === false) {
		$config = new Zend_Config_Ini($ini['application'], null, true);
		$config->merge(new Zend_Config_Ini($ini['routing']));
		if (is_file($ini['custom'])) {
			$config->merge(new Zend_Config_Ini($ini['custom']));
		}
		$configCache->save($config, 'config');
	}
} catch (Zend_Config_Exception $e) {
	if (PHP_SAPI !== 'cli') header('Content-Type: text/plain; charset=UTF-8');
	exit('The application was unable to read one of the main configuration file (application.ini or routing.ini). It makes me a sad panda as well.' . PHP_EOL);
}

try {
	// Instantiate the application, bootstrap, and run
	$application = new Zend_Application(APPLICATION_ENV, $config->{APPLICATION_ENV});

	$autoloader = $application->getAutoloader();

	// We want the autoloader to load any namespace
	$autoloader->setFallbackAutoloader(false);
	// We want to be informed about missing classes in a clear way
	// There's a conflict with Doctrine trying to load non-existing (created at run-time) classes
	$autoloader->suppressNotFoundWarnings(true);

// Catch any uncaught exceptions
} catch (Exception $e) {
	if (PHP_SAPI !== 'cli') header('Content-Type: text/plain; charset=UTF-8');
	exit('Application error: ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL);
}
