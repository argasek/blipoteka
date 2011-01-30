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
 * @package    Blipoteka_Tests
 * @copyright  Copyright (c) 2010-2011 Jakub Argasiński (argasek@gmail.com)
 * @license    http://blipoteka.pl/license Simplified BSD License
 */

/**
 * Author entity test case
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class Blipoteka_Service_BookTest extends PHPUnit_Framework_TestCase {
	/**
	 * Array of available book covers dimensions
	 *
	 * @var array
	 */
	protected $sizes;

	/**
	 * Mockup of book cast to array
	 *
	 * @var array
	 */
	protected $bookArrayMock;

	public function setUp() {
		$service = new Blipoteka_Service_Book();
		$this->sizes = $service->getAvailableCoverSizes();
		$this->bookArrayMock = array(
			'book_id' => '50',
			'type' => '0',
			'user_id' => '1',
			'owner_id' => '1',
			'holder_id' => NULL,
			'status' => '0',
			'title' => 'Blade Runner',
			'original_title' => 'Do Androids Dream of Electric Sheep?',
			'city_id' => NULL,
			'publisher_id' => '5',
			'year' => '2005',
			'pages' => '272',
			'isbn' => '8374692316',
			'description' => NULL,
			'auto_accept_requests' => false,
			'has_cover' => true,
			'created_at' => '2010-10-17 21:15:49',
			'slug' => 'blade-runner',
			'status_name' => 'dostępna',
			'type_name' => 'wróć',
		);
	}

	/**
	 * We expect a correct part of relative URL.
	 */
	public function testGetBookCoverDimensionsBySize() {
		$reflector = new ReflectionClass('Blipoteka_Service_Book');
		$method = $reflector->getMethod('getBookCoverDimensionsBySize');
		$method->setAccessible(true);
		$service = $reflector->newInstance();

		$dimensions = array_combine($this->sizes, array(
			'50x75',
			'120x180',
			'180x270',
			'original'
		));

		foreach ($dimensions as $size => $dimension) {
			$this->assertEquals($dimension, $method->invoke($service, $size));
		}
	}

	/**
	 * We expect a correct relative URL.
	 */
	public function testGetCoverUrl() {
		$service = new Blipoteka_Service_Book();
		$book = $this->bookArrayMock;

		$urls = array_combine($this->sizes, array(
			'img/cover/50x75/blade-runner-50.jpg',
			'img/cover/120x180/blade-runner-50.jpg',
			'img/cover/180x270/blade-runner-50.jpg',
			'img/cover/original/blade-runner-50.jpg'
		));

		foreach ($urls as $size => $url) {
			$this->assertEquals($url, $service->getCoverUrl($book, $size));
		}
	}
}
