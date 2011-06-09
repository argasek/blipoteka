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
 * @package    Blipoteka_Book
 * @copyright  Copyright (c) 2010-2011 Jakub Argasiński (argasek@gmail.com)
 * @license    http://blipoteka.pl/license Simplified BSD License
 */

/**
 * City controller.
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class CityController extends Blipoteka_Controller {

	/**
	 * Index action (get city list by prefix)
	 *
	 * @return void
	 */
	public function indexAction() {
		$prefix = $this->getRequest()->getParam('city');
		$service = new Blipoteka_Service_City();
		$cities = $service->getCitiesByPrefix($prefix, Doctrine_Core::HYDRATE_SCALAR);
		$cities = ($cities === null ? array() : $cities);
		$this->_helper->json($cities);
	}

}
