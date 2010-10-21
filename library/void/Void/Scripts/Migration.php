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
 * @package    Void_Scripts
 * @copyright  Copyright (c) 2010 Jakub Argasiński (argasek@gmail.com)
 * @license    http://tekla.art.pl/license/void-simplified-bsd-license.txt Simplified BSD License
 */

/**
 * Migration tool script class
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class Void_Scripts_Migration extends Void_Scripts {
	const VERSION = '0.2';
	const DESCRIPTION = 'Doctrine migrations helper tool';

	/**
	 * A migration object
	 * @var Doctrine_Migration
	 */
	protected $migration;

	public function __construct(Doctrine_Cli $cli) {
		parent::__construct();

		// Set up a migration object
		$this->setUpMigration($cli);
	}

	/**
	 * Run an action basing on a command issued
	 * @see Void_Scripts::run()
	 */
	public function run() {
		// Parse command line
		parent::run();
		// Run an action basing on a command issued
		switch ($this->cli->command_name) {
			case 'get-version':
				$this->actionShowMigrationVersion();
				break;
			case 'set-version':
				$this->actionSetMigrationVersion();
				break;
			case 'migrate':
				$this->actionMigrate();
				break;
			default:
				$this->parser->displayUsage();
		}
	}

	/**
	 * Get a current database migration version
	 * @return integer
	 */
	private function getCurrentVersion() {
		return (integer) $this->getMigration()->getCurrentVersion();
	}

	/**
	 * Get a latest database migration version available
	 * @return integer
	 */
	private function getLatestVersion() {
		return (integer) $this->getMigration()->getLatestVersion();
	}

	/**
	 * Show information about current and latest available version
	 * @return array
	 */
	private function showMigrationVersions() {
		$current = $this->getCurrentVersion();
		$latest = $this->getLatestVersion();
		printf("Current migration version: %d\n", $current);
		printf("Latest available migration version: %d\n", $latest);
		return array($current, $latest);
	}

	/**
	 * Show informations about migrations versions
	 */
	private function actionShowMigrationVersion() {
		// Show information about current and latest available version
		list($current, $latest) = $this->showMigrationVersions();

		// Display some additional information if verbosity was requested
		if ($this->cli->options['verbose'] === true) {
			if ($current === $latest && $current === 0) {
				printf("The database is in it's initial state and no migrations exist yet. A virgin!\n");
			}
			if ($current === $latest && $current > 0) {
				printf("The database is in the latest version, no migration required.\n");
			}
			if ($current < $latest) {
				printf("The database is not in the latest version, migration required!\n");
			}
			if ($current > $latest) {
				printf("Warning: the current database version is greater than the latest migration version available!\n");
			}
		}
	}

	/**
	 * Get destination version specified on the command line
	 * @return integer
	 */
	private function getDestinationVersion() {
		$version = $this->cli->command->args['version'];
		return ($version === null ? $this->getLatestVersion() : (integer) $version);
	}

	/**
	 * Set a current migration version, without doing an actual migration.
	 * Useful when fixing bugs, testing etc.
	 */
	private function actionSetMigrationVersion() {
		$version = $this->getDestinationVersion();
		// Show information about current and latest available version
		$this->showMigrationVersions();
		printf("Setting migration version: %s\n", ($this->cli->command->args['version'] === null ? 'latest' : $version));
		if ($version > $this->getLatestVersion()) {
			printf("Warning: version provided (%d) greater than latest available migration (%d), Cthulhu will eat you!\n", $version, $this->getLatestVersion());
		}
		// Dry run? Stop here. If not, process.
		if ($this->cli->options['dryrun'] === true) return;
		$this->migration->setCurrentVersion($version);
	}

	/**
	 * Perform a migration
	 */
	private function actionMigrate() {
		$version = $this->getDestinationVersion();
		// Show information about current and latest available version
		$this->showMigrationVersions();
		printf("Migrating to version: %s\n", ($this->cli->command->args['version'] === null ? 'latest' : $version));
		if ($version > $this->getLatestVersion()) {
			printf("Warning: version provided (%d) greater than latest version (%d), migrating to the latter one...\n", $version, $this->getLatestVersion());
			$version = $this->getLatestVersion();
		}
		// Skip, if already at latest version
		if ($version === $this->getCurrentVersion()) {
			printf("Already at the latest version, skipping...\n");
		} else {
			// Migrate, optionally with a dry run.
			try {
				$this->getMigration()->migrate($version, !!$this->cli->options['dryrun']);
			} catch (Doctrine_Migration_Exception $e) {
				printf("Error: %s\n", $e->getMessage());
				exit($e->getCode());
			}
		}
	}

	/**
	 * Set up additional command line options, arguments, commands etc.
	 */
	protected function setUpParser() {
		// Add an option to prevent performing of actual actions (those, which can modify a database)
		$this->parser->addOption('dryrun', array(
		    'short_name'  => '-d',
			'long_name'   => '--dry-run',
		    'action'      => 'StoreTrue',
		    'description' => "don't perform actual changes"
		));

		// Add a command to get current migration version
		$this->parser->addCommand('get-version', array('description' => 'get a current migration version'));

		// Add a command to forcibly set a current migration version, without
		// doing an actual migration. Useful when fixing bugs, testing etc.
		$command = $this->parser->addCommand('set-version', array('description' => 'set a current migration version (if none set, assume latest; be careful!)'));
		$command->addArgument('version', array('description' => 'migration version', 'action' => 'StoreInteger', 'optional' => true));

		// Add a command to run migration to a latest (or provided) version
		$command = $this->parser->addCommand('migrate', array('description' => 'migrate to a version provided (if none set, assume latest)'));
		$command->addArgument('version', array('description' => 'migration version', 'action' => 'StoreInteger', 'optional' => true));
	}

	/**
	 * Set up migration object
	 */
	protected function setUpMigration(Doctrine_Cli $cli) {
		$config = $cli->getConfig();
		$this->migration = new Doctrine_Migration($config['migrations_path']);
	}

	/**
	 * Get migration object
	 * @return Doctrine_Migration
	 */
	public function getMigration() {
		return $this->migration;
	}

}
