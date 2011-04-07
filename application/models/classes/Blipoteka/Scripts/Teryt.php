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

/**
 * Teryt database import tool script class
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class Blipoteka_Scripts_Teryt extends Void_Scripts_Teryt {
	const VERSION = '0.1';
	const DESCRIPTION = 'Import TERYT CSV files into the Blipoteka database';

	public function __construct(Blipoteka_City $city, Blipoteka_terc $terc, $defaultTerytPath = '') {
		parent::__construct($city, $terc, $defaultTerytPath);
	}

}
