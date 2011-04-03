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
 * @package    Blipoteka_View_Helper
 * @copyright  Copyright (c) 2010-2011 Jakub Argasiński (argasek@gmail.com)
 * @license    http://blipoteka.pl/license Simplified BSD License
 */

require_once('Blip/blipapi.php');

/**
 * User avatar URL view helper
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class Blipoteka_View_Helper_UserAvatar extends Zend_View_Helper_Abstract {

	/**
	 * Get URL of user's avatar in a specified size:
	 *
	 * femto - 15x15 px
	 * nano - 30x30 px
	 * pico - 50x50 px
	 * standard - 90x90 px
	 * large - 120x120 px
	 * 
	 * If operation fails for some reason, return false.
	 * 
	 * @todo This is mostly a placeholder; this requires better error handling
	 * (especially in case of network errors), caching etc.
	 *
	 * @param array $login A user login on Blip
	 * @param string $size Size of avatar ('tiny', 'small', 'medium', 'original')
	 * @return string|false
	 */
	public function userAvatar($blip, $size = 'nano') {
		$api = new BlipApi();
		$avatar = new BlipApi_Avatar();
		$avatar->size = $size;
		$avatar->user = $blip;
		try {
			$response = $api->read($avatar);
		} catch (InvalidArgumentException $e) {
			return false;
		}
		if ($response['status_code'] == 200) {
			$body = $response['body'];
			$url = 'http://blip.pl';
			$url .= $body->{$this->getSizeUrlPart($size)};
		} else {
			return false;
		}

		return $url;
	}
	
	/**
	 * Get size URL part.
	 * 
	 * @param string $size
	 * @return string
	 */
	protected function getSizeUrlPart($size) {
		$sizes = array(
			'femto' => 'url_15',
			'nano' => 'url_30',
			'pico' => 'url_50',
			'standard' => 'url_90',
			'large' => 'url_120',
		);
		return $sizes[$size];
	}

}
