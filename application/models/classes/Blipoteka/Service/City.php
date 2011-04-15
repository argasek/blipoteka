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
 * City related service class
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class Blipoteka_Service_City extends Blipoteka_Service {
	/**
	 * Class of the record this service applies to
	 * @var string
	 */
	protected $_recordClass = 'Blipoteka_City';

	/**
	 * Get cities which name begin with $prefix along with
	 * borough, district and province information.
	 *
	 * @return Doctrine_Collection|array
	 */
	public function getCitiesByPrefix($prefix, $hydrationMode = Doctrine_Core::HYDRATE_ARRAY) {
		$query = $this->getCityBoroughDistrictProvinceQuery();
		$query->select('c.city_id, c.lat, c.lng, c.name, b.name, d.name, p.name');
		$query->where('c.name LIKE ?%', $prefix);
		$cites = $query->execute(array(), $hydrationMode);
		return $cities;
	}

	/**
	 * Get city query with borough, district and province information.
	 *
	 * @return Doctrine_Query
	 */
	public function getCityBoroughDistrictProvinceQuery() {
		$query = Doctrine_Query::create();
		$query->from('Blipoteka_City c');
		$query->innerJoin('c.borough b');
		$query->innerJoin('c.district d');
		$query->innerJoin('c.province p');
		return $query;
	}

}
