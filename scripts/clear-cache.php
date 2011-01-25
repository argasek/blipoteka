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
 * @copyright  Copyright (c) 2010-2011 Jakub Argasiński (argasek@gmail.com)
 * @license    http://blipoteka.pl/license Simplified BSD License
 */

define('APPLICATION_ENV', getenv('APPLICATION_ENV') ?: 'cli');
defined('DS') || define('DS', DIRECTORY_SEPARATOR);

include __DIR__ . DS . '..' . DS . 'public' . DS . 'initenv.php';

// Clear configuration cache
echo "Clearing configuration cache... ";
$configCache->clean(Zend_Cache::CLEANING_MODE_ALL);
echo "OK\n";

// Bootstrap and get Zend Cache Manager instance
$bootstrap = $application->bootstrap('cachemanager')->getBootstrap();
$manager = $bootstrap->getResource('cachemanager');

// Clear all caches
$caches = $manager->getCaches();
foreach ($caches as $name => $cache) {
	echo "Clearing $name cache... ";
	$cache->clean(Zend_Cache::CLEANING_MODE_ALL);
	echo "OK\n";
}
