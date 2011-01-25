<?php
/**
 * Void
 *
 * LICENSE
 *
 * This source file is subject to the Simplified BSD License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://tekla.art.pl/license/void-simplified-bsd-license.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to argasek@gmail.com so I can send you a copy immediately.
 *
 * @category   Void
 * @package    Void_Filter
 * @copyright  Copyright (c) 2010-2011 Jakub Argasiński (argasek@gmail.com)
 * @license    http://tekla.art.pl/license/void-simplified-bsd-license.txt Simplified BSD License
 */

/**
 * @see Zend_Filter_Interface
 */
require_once 'Zend/Filter/Interface.php';

/**
 * Multibyte ucfirst().
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class Void_Filter_Ucfirst implements Zend_Filter_Interface {
    /**
     * Encoding for the input string
     *
     * @var string
     */
    protected $_encoding = null;

    /**
     * Constructor
     *
     * @param string|array $options OPTIONAL
     */
    public function __construct($options = null) {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } else if (!is_array($options)) {
            $options = func_get_args();
            $temp    = array();
            if (!empty($options)) {
                $temp['encoding'] = array_shift($options);
            }
            $options = $temp;
        }

        if (!array_key_exists('encoding', $options) && function_exists('mb_internal_encoding')) {
            $options['encoding'] = mb_internal_encoding();
        }

        if (array_key_exists('encoding', $options)) {
            $this->setEncoding($options['encoding']);
        }
    }

    /**
     * Returns the set encoding
     *
     * @return string
     */
    public function getEncoding() {
        return $this->_encoding;
    }

    /**
     * Set the input encoding for the given string
     *
     * @param  string $encoding
     * @return Zend_Filter_StringToUpper Provides a fluent interface
     * @throws Zend_Filter_Exception
     */
    public function setEncoding($encoding = null) {
        if ($encoding !== null) {
            if (!function_exists('mb_strtoupper')) {
                require_once 'Zend/Filter/Exception.php';
                throw new Zend_Filter_Exception('mbstring is required for this feature');
            }

            $encoding = (string) $encoding;
            if (!in_array(strtolower($encoding), array_map('strtolower', mb_list_encodings()))) {
                require_once 'Zend/Filter/Exception.php';
                throw new Zend_Filter_Exception("The given encoding '$encoding' is not supported by mbstring");
            }
        }

        $this->_encoding = $encoding;
        return $this;
    }

    /**
     * Defined by Zend_Filter_Interface
     *
     * Returns the string $value, converting first character to uppercase
     *
     * @param  string $value
     * @return string
     */
    public function filter($value) {
    	$value = (string) $value;
        if ($this->_encoding) {
        	$first = mb_substr($value, 0, 1, $this->_encoding);
			$first = mb_strtoupper($first, $this->_encoding);
			$rest = mb_substr($value, 1, mb_strlen($value) - 1, $this->_encoding);
            return $first . $rest;
        }

        return ucfirst($value);
    }
}
