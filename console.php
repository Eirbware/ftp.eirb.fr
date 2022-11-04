#!/usr/bin/php -q
<?php

// Check the existence of vendor/autoload.php
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "The file 'vendor/autoload.php' does not exist. Please run 'composer install' first.\n";
    exit(1);
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/services/Database.php';
require_once __DIR__ . '/models/UserModel.php';

use splitbrain\phpcli\CLI;
use splitbrain\phpcli\Options;

class FtpEirbCli extends CLI
{
    // register options and arguments
    protected function setup(Options $options)
    {
        $options->setHelp('This script allows you to create the database schema and the first user of the application.');
        $options->registerCommand('db:create', 'Create database schema (tables and procedures)');
        $options->registerCommand('db:drop', 'Drop database schema (tables and procedures)');
        //$options->registerOption('version', 'Display version information', 'v');
    }

    // implement your code
    protected function main(Options $options)
    {
        $climate = new \League\CLImate\CLImate;

        // Check the existence of .env
        if (!file_exists(__DIR__ . '/.env')) {
            $this->error("The file '.env' does not exist. Please create it first.");
            $input = $climate->bold()->confirm('Do you want to create it now?');

            if ($input->confirmed()) {
                $input = $climate->input('- Enter the database name (default: ftp):');
                $input->defaultTo('ftp');
                $dbName = $input->prompt();

                $input = $climate->input('- Enter the database user (default: ftp):');
                $input->defaultTo('ftp');
                $dbUser = $input->prompt();

                $input = $climate->password('- Enter the database password:');
                $dbPassword = $input->prompt();

                $input = $climate->input('- Enter the database host (default: 127.0.0.1):');
                $input->defaultTo('127.0.0.1');
                $dbHost = $input->prompt();

                $input = $climate->input('- Enter the database port (default: 3306):');
                $input->defaultTo('3306');
                $dbPort = $input->prompt();

                $input = $climate->input('- Enter the FTP session duration in minutes (default: 30):');
                $input->defaultTo('30');
                $accessDuration = $input->prompt();

                $this->info("Creating .env file...");
                $envFile = fopen(__DIR__ . '/.env', 'w');
                fwrite($envFile, "MYSQL_DB_NAME=$dbName\nMYSQL_DB_USERNAME=$dbUser\nMYSQL_DB_PASSWORD=$dbPassword\nMYSQL_DB_HOST=$dbHost\nMYSQL_DB_PORT=$dbPort\nACCESS_DURATION=$accessDuration");
                fclose($envFile);
                $this->success("Done!");
            } else {
                exit(1);
            }
        }

        $db = \Services\Database::getInstance();

        if ($options->getCmd() === 'db:create') {
            $this->info('Creating database schema...');
            $sql = file_get_contents(__DIR__ . '/sql/create.sql');
            $db->exec($sql);
            $this->success('Database schema created successfully !');

            $options  = ['Create default admin user', 'Seed database with test data', 'Exit'];
            $input    = $climate->radio('What do you want to do next?', $options);
            $response = $input->prompt();

            if ($response === $options[0]) {
                $this->info('User creation form');
                $input = $climate->input('- Enter the id:');
                $id = $input->prompt();

                $input = $climate->input('- Enter the first name:');
                $firstName = $input->prompt();

                $input = $climate->input('- Enter the last name:');
                $lastName = $input->prompt();

                $user = new \Models\User();
                $user->id = $id;
                $user->first_name = $firstName;
                $user->last_name = $lastName;
                $user->admin = true;
                if ($user->persist()) {
                    $this->success('User created successfully !');
                } else {
                    $this->error('An error occured while creating the user !');
                }
            } elseif ($response === $options[1]) {
                $this->info('Seeding database with test data...');
                $sql = file_get_contents(__DIR__ . '/sql/seed.sql');
                $db->exec($sql);
                $this->success('Database seeded successfully !');
            }
            exit;
        } else if ($options->getCmd() === 'db:drop') {
            $this->info('Dropping database schema...');
            $sql = file_get_contents(__DIR__ . '/sql/drop.sql');
            $db->exec($sql);
            $this->success('Database schema dropped successfully !');
            exit;
        } else if ($options->getCmd() === 'user:add') {
        } else if ($options->getOpt('version')) {
            $this->info("FTP'eirb v.1.0.0");
        } else {
            echo $options->help();
        }
    }
}
// execute it
$cli = new FtpEirbCli();
$cli->run();
