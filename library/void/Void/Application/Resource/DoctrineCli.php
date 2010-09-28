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
 * @package    Void_Application_Resource
 * @copyright  Copyright (c) 2010 Jakub Argasiński (argasek@gmail.com)
 * @license    http://tekla.art.pl/license/void-simplified-bsd-license.txt Simplified BSD License
 */

/**
 * Doctrine CLI resource class.
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class Void_Application_Resource_DoctrineCli extends Zend_Application_Resource_ResourceAbstract {
	
	public function init() {
		return $this->getDoctrineCli();
	}

	public function getDoctrineCli() {
		$this->getBootstrap()->bootstrap('doctrine');
		
		$config = array(
			'data_fixtures_path'  =>  $this->_options['fixturesPath'],
			'models_path'         =>  $this->_options['modelsPath'],
			'migrations_path'     =>  $this->_options['migrationsPath'],
			'sql_path'            =>  $this->_options['sqlPath'],
			'yaml_schema_path'    =>  $this->_options['yamlSchemaPath']
		);
		
		return new Doctrine_Cli($config);
	}
}
