<?php

namespace FtpEirb\Services;

class Database
{

    private static \PDO $pdo;

    public static function getInstance(): \PDO
    {
        if (!isset(self::$pdo)) {
            // We load the environment variables
            $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . "/../../");
            try {
                $dotenv->load();
            } catch (\Exception $e) {
                if (php_sapi_name() === "cli") {
                    logError($e->getMessage());
                } else {
                    error("Fichier .env manquant !", "DATABASE/ENVIRONMENT_VARIABLES", 400);
                }
                exit(1);
            }

            $MYSQL_DB_HOST = $_ENV["MYSQL_DB_HOST"];
            $MYSQL_DB_PORT = $_ENV["MYSQL_DB_PORT"];
            $MYSQL_DB_USERNAME = $_ENV["MYSQL_DB_USERNAME"];
            $MYSQL_DB_PASSWORD = $_ENV["MYSQL_DB_PASSWORD"];
            $MYSQL_DB_NAME = $_ENV["MYSQL_DB_NAME"];
            $ACCESS_DURATION = $_ENV["ACCESS_DURATION"];

            if (empty($MYSQL_DB_HOST) || empty($MYSQL_DB_PORT) || empty($MYSQL_DB_USERNAME) || empty($MYSQL_DB_PASSWORD) || empty($MYSQL_DB_NAME) || empty($ACCESS_DURATION)) {
                if (php_sapi_name() === "cli") {
                    logError("Please set the environment variables in the .env file.");
                } else {
                    error("Variables d'environnement manquantes !", "DATABASE/ENVIRONMENT_VARIABLES", 400);
                }
                exit(1);
            }


            // Try connecting to the database
            try {
                $pdo = new \PDO("mysql:host=$MYSQL_DB_HOST;port=$MYSQL_DB_PORT;dbname=$MYSQL_DB_NAME", $MYSQL_DB_USERNAME, $MYSQL_DB_PASSWORD);
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                date_default_timezone_set("UTC");
                self::$pdo = $pdo;
            } catch (\PDOException $e) {
                if (php_sapi_name() === "cli") {
                    logError("Unable to connect to the database : " . $e->getMessage());
                } else {
                    error("Impossible de se connecter à la base de données !", "DATABASE/CONNECTION", 400);
                }
                exit(1);
            }
        }
        return self::$pdo;
    }
}