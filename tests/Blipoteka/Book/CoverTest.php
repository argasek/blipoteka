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
 * Book cover related test case
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class Blipoteka_Book_CoverTest extends PHPUnit_Framework_TestCase {
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
		$service = new Blipoteka_Book_Cover();
		$this->sizes = $service->getAvailableSizes();
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
	public function testGetDimensionsBySize() {
		$cover = new Blipoteka_Book_Cover();

		$dimensions = array_combine($this->sizes, array(
			'50x75',
			'120x180',
			'180x270',
			'original'
		));

		foreach ($dimensions as $size => $dimension) {
			$this->assertEquals($dimension, $cover->getDimensionsBySize($size));
		}
	}

	/**
	 * We expect a correct relative URL.
	 */
	public function testGetCoverUrl() {
		$cover = new Blipoteka_Book_Cover();
		$book = $this->bookArrayMock;

		$urls = array_combine($this->sizes, array(
			'img/cover/50x75/blade-runner-50.jpg',
			'img/cover/120x180/blade-runner-50.jpg',
			'img/cover/180x270/blade-runner-50.jpg',
			'img/cover/original/blade-runner-50.jpg'
		));

		foreach ($urls as $size => $url) {
			$this->assertEquals($url, $cover->getUrl($book, $size));
		}
	}

	/**
	 * Test setting of book cover
	 */
	public function testSet() {
		require_once('WideImage/WideImage.php');
		$cover = new Blipoteka_Book_Cover();
		$book = Doctrine_Core::getTable('Blipoteka_Book')->find(57);
		$cover->set($book);

		$paths = array_combine($this->sizes, array(
			'img' . DS . 'cover' . DS . '50x75' . DS . 'piknik-na-skraju-drogi-57.jpg',
			'img' . DS . 'cover' . DS . '120x180' . DS . 'piknik-na-skraju-drogi-57.jpg',
			'img' . DS . 'cover' . DS . '180x270' . DS . 'piknik-na-skraju-drogi-57.jpg',
			'img' . DS . 'cover' . DS . 'original' . DS . 'piknik-na-skraju-drogi-57.jpg'
		));
		// Check if files exists
		foreach ($paths as $size => $path) {
			$file = ROOT_PATH . DS . 'public' . DS . $path;
			$this->assertFileExists($file);
		}
		// Check if image dimensions are correct
		foreach ($paths as $size => $path) {
			$file = ROOT_PATH . DS . 'public' . DS . $path;
			$image = WideImage::load($file);
			$actualWidth = $image->getWidth();
			$actualHeight = $image->getHeight();
			$actualDimensions = $actualWidth . 'x' . $actualHeight;
			$dimensions = $cover->getDimensionsBySize($size);
			if ($dimensions !== 'original') {
				$this->assertEquals($dimensions, $actualDimensions);
			}
		}

	}

}
