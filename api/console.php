#!/usr/bin/php -q
<?php

// Check the existence of vendor/autoload.php
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    echo "The file 'vendor/autoload.php' does not exist. Please run 'composer install' first.\n";
    exit(1);
}

require_once __DIR__ . '/../vendor/autoload.php';

use splitbrain\phpcli\CLI;
use splitbrain\phpcli\Options;
use League\CLImate\CLImate;
use FtpEirb\Services\Database;

class FtpEirbCli extends CLI
{
    // register options and arguments
    protected function setup(Options $options)
    {
        $options->setHelp('This script allows you to create the database schema and the first user of the application.');
        $options->registerCommand('db:create', 'Create database schema (tables and procedures)');
        $options->registerCommand('db:drop', 'Drop database schema (tables and procedures)');
        $options->registerCommand('db:query', 'Run a custom SQL query and display the result');
    }

    // implement your code
    protected function main(Options $options)
    {
        $climate = new CLImate;

        // Check the existence of .env
        $this->checkEnvFile($climate);

        if ($options->getCmd() === 'db:create') {
            $this->createDatabaseSchema($climate);
        } elseif ($options->getCmd() === 'db:drop') {
            $this->dropDatabaseSchema();
        } elseif ($options->getCmd() === 'db:query') {
            $this->runSqlQuery($climate);
        } elseif ($options->getOpt('version')) {
            $this->info("FTP'eirb v.1.0.0");
        } else {
            echo $options->help();
        }
    }

    /**
     * Create the database schema (tables and procedures)
     *
     * @param CLImate $climate
     *
     * @return void
     */
    private function checkEnvFile($climate)
    {
        if (!file_exists(__DIR__ . '/../.env')) {
            $this->error("The file '.env' does not exist. Please create it first.");
            /** @var \League\CLImate\TerminalObject\Dynamic\Confirm $input */
            $input = $climate->bold()->confirm('Do you want to create it now?');

            if ($input->confirmed()) {
                /** @var \League\CLImate\TerminalObject\Dynamic\Input $input */
                $input = $climate->input('- Enter the database name (default: ftp):');
                $input->defaultTo('ftp');
                $dbName = $input->prompt();

                /** @var \League\CLImate\TerminalObject\Dynamic\Input $input */
                $input = $climate->input('- Enter the database user (default: ftp):');
                $input->defaultTo('ftp');
                $dbUser = $input->prompt();

                /** @var \League\CLImate\TerminalObject\Dynamic\Input $input */
                $input = $climate->password('- Enter the database password:');
                $dbPassword = $input->prompt();

                /** @var \League\CLImate\TerminalObject\Dynamic\Input $input */
                $input = $climate->input('- Enter the database host (default: 127.0.0.1):');
                $input->defaultTo('127.0.0.1');
                $dbHost = $input->prompt();

                /** @var \League\CLImate\TerminalObject\Dynamic\Input $input */
                $input = $climate->input('- Enter the database port (default: 3306):');
                $input->defaultTo('3306');
                $dbPort = $input->prompt();

                /** @var \League\CLImate\TerminalObject\Dynamic\Input $input */
                $input = $climate->input('- Enter the FTP session duration in minutes (default: 10):');
                $input->defaultTo('10');
                $accessDuration = $input->prompt();

                $this->info("Creating .env file...");
                $envFile = fopen(__DIR__ . '/../.env', 'w');
                if (!$envFile) {
                    $this->error("Unable to create .env file.");
                    exit(1);
                }
                fwrite($envFile, "MYSQL_DB_NAME=$dbName\nMYSQL_DB_USERNAME=$dbUser\nMYSQL_DB_PASSWORD=$dbPassword\nMYSQL_DB_HOST=$dbHost\nMYSQL_DB_PORT=$dbPort\nACCESS_DURATION=$accessDuration");
                fclose($envFile);
                $this->success("Done!");
            } else {
                exit(1);
            }
        }
    }

    /**
     * Create the database schema (tables and procedures)
     *
     * @param CLImate $climate
     *
     * @return void
     */
    private function createDatabaseSchema($climate)
    {
        $db = Database::getInstance();

        $this->info('Creating database schema...');
        $sql = file_get_contents(__DIR__ . '/Sql/create.sql');
        if (!$sql) {
            $this->error('Unable to read the file ' . __DIR__ . '/Sql/create.sql');
            exit(1);
        }
        $db->exec($sql);
        $this->success('Database schema created successfully !');

        $options  = ['Create default admin user', 'Exit'];
        /** @var \League\CLImate\TerminalObject\Dynamic\Radio $input */
        $input    = $climate->radio('What do you want to do next?', $options);
        $response = $input->prompt();

        if ($response === $options[0]) {
            $this->info('User creation form');
            /** @var \League\CLImate\TerminalObject\Dynamic\Input $input */
            $input = $climate->input('- Enter the id (CAS login):');
            $id = $input->prompt();

            /** @var \League\CLImate\TerminalObject\Dynamic\Input $input */
            $input = $climate->input('- Enter the first name:');
            $firstName = $input->prompt();

            /** @var \League\CLImate\TerminalObject\Dynamic\Input $input */
            $input = $climate->input('- Enter the last name:');
            $lastName = $input->prompt();

            /** @var \League\CLImate\TerminalObject\Dynamic\Input $input */
            $input = $climate->input('- Enter the details about the user (role, etc.):');
            $details = $input->prompt();

            $user = new FtpEirb\Models\User();
            $user->id = $id;
            $user->first_name = $firstName;
            $user->last_name = $lastName;
            $user->details = $details;
            $user->admin = true;
            $user->save();
            if (FtpEirb\Models\User::getById($id)) {
                $this->success('User created successfully !');
            } else {
                $this->error('An error occured while creating the user !');
            }
        }
        exit;
    }

    /**
     * Drop the database schema (tables and procedures)
     *
     * @return void
     */
    private function dropDatabaseSchema()
    {
        $db = Database::getInstance();

        $this->info('Dropping database schema...');
        $sql = file_get_contents(__DIR__ . '/Sql/drop.sql');
        if (!$sql) {
            $this->error('Unable to read the file ' . __DIR__ . '/Sql/drop.sql');
            exit(1);
        }
        $db->exec($sql);
        $this->success('Database schema dropped successfully !');
        exit;
    }

    /**
     * Run custom SQL queries and display the result
     * 
     * @param CLImate $climate
     * 
     * @return void
     */
    private function runSqlQuery($climate) {
        $db = Database::getInstance();

        $this->info('SQL query');
        /** @var \League\CLImate\TerminalObject\Dynamic\Input $input */
        $input = $climate->input('- Enter the SQL query to execute:');
        $sql = $input->prompt();

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($result) {
            $climate->table($result);
        } else {
            $this->error('No result.');
        }

        exit;
    }
}

// execute it
$cli = new FtpEirbCli();
$cli->run();
