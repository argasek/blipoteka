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

require 'Console/Table.php';

/**
 * User management tool script class
 *
 * @author Jakub Argasiński <argasek@gmail.com>
 *
 */
class Blipoteka_Scripts_User extends Void_Scripts {
	const VERSION = '0.1';
	const DESCRIPTION = 'User management tool';

	public function __construct() {
		parent::__construct();
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
			case 'create':
				$this->actionCreateUser();
				break;
			case 'remove':
				$this->actionRemoveUser();
				break;
			case 'passwd':
				$this->actionSetUserPassword();
				break;
			case 'list':
				$this->actionListAccounts();
				break;
			default:
				$this->parser->displayUsage();
		}
	}

	/**
	 * Set user password
	 */
	protected function actionSetUserPassword() {
		$identity = $this->cli->command->args['identity'];
		$credential = $this->cli->command->args['credential'];
		// Display some additional information if verbosity was requested
		if ($this->cli->options['verbose'] === true) {
			printf("Setting password for identity: %s\n", $identity);
		}
		// Find user
		$service = new Blipoteka_Service_User();
		$user = $service->getUserByIdentity($identity);
		if ($user instanceof Blipoteka_User) {
			if ($this->cli->options['dryrun'] !== true) {
				// Update password
				$service->updatePassword($credential, $user);
			}
			printf("Password updated successfully.\n", $identity);
		} else {
			printf("User %s not found.\n", $identity);
		}
	}

	/**
	 * Create new user
	 */
	protected function actionCreateUser() {
		$identity = $this->cli->command->args['identity'];
		$credential = $this->cli->command->args['credential'];
		$name = $this->cli->command->args['name'];
		$blip = $this->cli->command->args['blip'];
		$city_id = null;
		$notify = false;
		// $role = $this->cli->command->args['role'];
		// Display some additional information if verbosity was requested
		if ($this->cli->options['verbose'] === true) {
			printf("Creating a new user account: %s\n", $identity);
		}
		// Find user
		$service = new Blipoteka_Service_User();
		if ($service->getUserByIdentity($identity) === false) {
			if ($this->cli->options['dryrun'] !== true) {
				$user = new Blipoteka_User();
				$result = $service->createUser($user, $identity, $blip, $credential, $name, $city_id);
				if ($result === true) {
					printf("User %s created successfully.\n", $identity);
				} else {
					$error = $user->getErrorStackAsString();
					printf("Error creating user account: %s\n", $error);
				}
			}
		} else {
			printf("Account %s already exists, skipping...\n", $identity);
		}
	}

	/**
	 * Remove user
	 */
	protected function actionRemoveUser() {
		$identity = $this->cli->command->args['identity'];
		// Display some additional information if verbosity was requested
		if ($this->cli->options['verbose'] === true) {
			printf("Removing the user account: %s\n", $identity);
		}
		// Find user
		$service = new Blipoteka_Service_User();
		$user = $service->getUserByIdentity($identity);
		if ($user instanceof Blipoteka_User) {
		// 	Dry run? Stop here. If not, process.
			if ($this->cli->options['dryrun'] !== true) {
				$user->delete();
			}
			printf("User %s removed successfully.\n", $identity);
		} else {
			printf("Account doesn't exist, skipping...\n", $identity);
		}
	}

	/**
	 * List user accounts
	 */
	protected function actionListAccounts() {
		// Find all users
		$users = Doctrine_Core::getTable('Blipoteka_User')->findAll();
		if ($users->count() == 0) {
			printf("No accounts have been found.\n");
			exit(0);
		}
		$headers = array("email", "blip", "name", "active", "created_at");
		// List users
		$table = new Console_Table();
		$table->setHeaders($headers);
		foreach ($users as $user) {
			$row = array();
			$row[] = $user->email;
			$row[] = $user->blip;
			$row[] = $user->name;
			$row[] = $user->is_active;
			$row[] = $user->created_at;
			$table->addRow($row);
		}
		echo $table->getTable();
	}

	/**
	 * Get user object by identity
	 *
	 * @param string $identity
	 * @return Blipoteka_User
	 */
	protected function getUserByIdentity($identity) {
		$service = new Blipoteka_Service_User();
		$user = $service->getUserByIdentity($identity);
		if ($user instanceof Blipoteka_User) {
			return $user;
		}
		printf("User with identity '%s' not found.\n", $identity);
		exit(-2);
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

		$login = array('description' => 'An identity (email)', 'action' => 'StoreString');

		// Add a command to create a user based on specified arguments
		// doing an actual migration. Useful when fixing bugs, testing etc.
		$command = $this->parser->addCommand('create', array('description' => 'create a new user account'));
		$command->addArgument('identity', $login);
		$command->addArgument('blip', array('description' => 'A blip account', 'action' => 'StoreString'));
		$command->addArgument('name', array('description' => 'A real name', 'action' => 'StoreString'));
		$command->addArgument('credential', array('description' => 'A password as plain text', 'action' => 'StoreString'));

		// Add a command to list user accounts
		$command = $this->parser->addCommand('list', array('description' => 'list user accounts'));

		// Add a command to set user password
		$command = $this->parser->addCommand('passwd', array('description' => 'set password for a given identity'));
		$command->addArgument('identity', $login);
		$command->addArgument('credential', array('description' => 'A password as plain text', 'action' => 'StoreString'));

		// Add a command to remove user account
		$command = $this->parser->addCommand('remove', array('description' => 'remove user account (be careful!)'));
		$command->addArgument('identity', $login);

	}

}
