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
 * Book cover related class
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class Blipoteka_Book_Cover {
	const DIMENSIONS_ORIGINAL = 'original';

	/**
	 * Get book cover dimensions array.
	 *
	 * @return array
	 */
	protected function getDimensions() {
		$dimensions = array(
			'tiny' => '50x75',
			'small' => '120x180',
			'medium' => '180x270',
			'original' => self::DIMENSIONS_ORIGINAL
		);
		return $dimensions;
	}

	/**
	 * Get available cover sizes as array of strings.
	 *
	 * @return array
	 */
	public function getAvailableSizes() {
		$dimensions = $this->getDimensions();
		$sizes = array_keys($dimensions);
		return $sizes;
	}

	/**
	 * Get path where original covers' files are stored.
	 *
	 * @return string
	 */
	protected function getOriginalPath() {
		return ROOT_PATH . DS . 'public' . DS . 'img' . DS . 'cover' . DS . 'original' . DS;
	}

	/**
	 * Get book cover file basename
	 *
	 * @param array $book A book array (with at least 'book_id' and 'slug' keys)
	 * @return string
	 */
	protected function getFileBasename(array $book) {
		$basename = $book['slug'] . '-' . $book['book_id'];
		return $basename;
	}

	/**
	 * Get book cover filename
	 *
	 * @param array $book A book array (with at least 'book_id' and 'slug' keys)
	 * @return string
	 */
	public function getFilename(array $book) {
		$filename = $this->getFileBasename($book);
		$filename .= '.jpg';
		return $filename;
	}

	/**
	 * Get book cover relative URL based on $book slug/id
	 * and $size parameter. If book has no cover, URL to
	 * or non-existant file.
	 *
	 * @param array $book A book array (with at least 'has_cover', 'book_id' and 'slug' keys)
	 * @param string $size Size of cover
	 * @return string
	 */
	public function getUrl(array $book, $size = 'small') {
		// Map $size to $dimensions
		$dimensions = $this->getDimensionsBySize($size);

		// If book has cover, generate cover filename, otherwise use cover placeholder
		if ($book['has_cover']) {
			$filename = $this->getFilename($book);
		} else {
			$filename = 'missing.png';
		}

		// Build relative URL
		$relativeUrl = 'img/cover/' . $dimensions . '/' . $filename;

		return $relativeUrl;
	}

	/**
	 * Get book cover absolute path based on $book slug/id
	 * and $size parameter. If book has no cover, URL to
	 * or non-existant file.
	 *
	 * @param array $book A book array (with at least 'has_cover', 'book_id' and 'slug' keys)
	 * @param string $size Size of cover
	 * @return string
	 */
	protected function getPath(array $book, $size = 'small') {
		$filename = $this->getFilename($book);
		$dimensions = $this->getDimensionsBySize($size);
		return ROOT_PATH . DS . 'public' . DS . 'img' . DS . 'cover' . DS . $dimensions . DS . $filename;
	}

	/**
	 * Get book cover dimensions for $size given.
	 *
	 * @param string $size ('tiny', 'small', 'medium', 'original')
	 * @return string ('50x80', 'original', etc.)
	 */
	public function getDimensionsBySize($size) {
		$dimensions = $this->getDimensions();
		return $dimensions[$size];
	}

	/**
	 * Set cover of a $book. If $path is not null, it must point to cover file location.
	 * Otherwise, we try to search for a file in a default original covers location.
	 *
	 * @param Blipoteka_Book $book
	 * @param string $path A complete path to an original file
	 */
	public function set(Blipoteka_Book $book, $path = null) {
		// Mark the book as having the cover
		$book->has_cover = true;
		$bookArray = $book->toArray();

		// If path to a file not provided, use default one
		if ($path === null) {
			$path = $this->getOriginalPath() . $this->getFilename($bookArray);
		}
		// (Re-)Generate book cover thumbnails for all available sizes
		foreach ($this->getAvailableSizes() as $size) {
			$this->createThumbnail($bookArray, $path, $size);
		}

		// Update book record
		$book->save();

		return true;
	}

	/**
	 * Generate book cover thumbnail with $size from given $path.
	 *
	 * @param string $path A complete path to an original file
	 * @param string $size A size of thumbnail ('tiny', 'small', etc.)
	 * @return string A file path to just created file
	 */
	protected function createThumbnail(array $book, $path, $size) {
		$dimensions = $this->getDimensionsBySize($size);
		// If dimensions are 'original', don't scale, simply copy
		if ($dimensions == self::DIMENSIONS_ORIGINAL) {
			$originalPath = $this->getOriginalPath() . $this->getFilename($book);
			// If source path is different than directory with original covers,
			// simply copy the file. Otherwise do nothing.
			if ($path != $originalPath) {
				copy($path, $originalPath);
			}
			return $originalPath;
		}
		list($width, $height) = explode('x', $dimensions);
		require_once('WideImage/WideImage.php');
		$destination = $this->getPath($book, $size);
		$image = WideImage::load($path);
		$image->resize($width, $height, 'outside', 'any')->crop('center', 'middle', $width, $height)->saveToFile($destination, 95);
		$image->destroy();

		return $destination;
	}

	/**
	 * Copy and rename file pointed by $path (URL allowed) to original
	 * cover files directory.
	 *
	 * @param Blipoteka_Book $book
	 * @param string $path
	 * @return A file path to cover file
	 */
	public function putOriginalFile(Blipoteka_Book $book, $path) {
		return $this->createThumbnail($book->toArray(), $path, 'original');
	}

}