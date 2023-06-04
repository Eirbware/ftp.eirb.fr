<?php

namespace FtpEirb\Services;

use PDO;
use PDOException;
use Dotenv;
use Exception;

class Database
{
    /**
     * Indicates if the database has been initialized
     * @var bool
     */
    private static $isInitialized = false;

    /**
     * The PDO instance
     * @var PDO
     */
    private static $pdo;

    private static function init(): void
    {
        if (self::$isInitialized) {
            return;
        } else {
            self::$isInitialized = true;
        }

        try {
            // We load the environment variables
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../../");
            $dotenv->load();
            $dotenv->required("MYSQL_DB_HOST")->notEmpty();
            $dotenv->required("MYSQL_DB_PORT")->isInteger();
            $dotenv->required("MYSQL_DB_USERNAME")->notEmpty();
            $dotenv->required("MYSQL_DB_PASSWORD")->notEmpty();
            $dotenv->required("MYSQL_DB_NAME")->notEmpty();
            $dotenv->required("ACCESS_DURATION")->isInteger();
        } catch (Exception $e) {
            if (php_sapi_name() === "cli") {
                logError($e->getMessage());
            } else {
                error("Fichier .env manquant !", "DATABASE/ENVIRONMENT_VARIABLES", 400);
            }
            exit(1);
        }

        $host = $_ENV["MYSQL_DB_HOST"];
        $port = $_ENV["MYSQL_DB_PORT"];
        $username = $_ENV["MYSQL_DB_USERNAME"];
        $password = $_ENV["MYSQL_DB_PASSWORD"];
        $databaseName = $_ENV["MYSQL_DB_NAME"];

        if (
            empty($host) ||
            empty($port) ||
            empty($username) ||
            empty($password) ||
            empty($databaseName)
        ) {
            if (php_sapi_name() === "cli") {
                logError("Please set the environment variables in the .env file.");
            } else {
                error("Variables d'environnement manquantes !", "DATABASE/ENVIRONMENT_VARIABLES", 400);
            }
            exit(1);
        }

        // Try connecting to the database
        try {
            $pdo = new PDO(
                "mysql:host=$host;port=$port;dbname=$databaseName",
                $username,
                $password,
                [
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                ]
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
            self::$pdo = $pdo;
        } catch (PDOException $e) {
            if (php_sapi_name() === "cli") {
                logError("Unable to connect to the database : " . $e->getMessage());
            } else {
                error("Impossible de se connecter à la base de données !", "DATABASE/CONNECTION", 400);
            }
            exit(1);
        }
    }

    public static function getInstance(): PDO
    {
        if (!self::$isInitialized) {
            self::init();
        }

        return self::$pdo;
    }
}
