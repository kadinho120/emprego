<?php

namespace App\Config;

use PDO;
use PDOException;
use Exception;

class Database
{
    private static $instance = null;
    private $conn;

    private function __construct()
    {
        $dbUrl = getenv('DB_URL');

        if (!$dbUrl) {
            throw new Exception("Environment variable DB_URL is not set.");
        }

        // Parse DB_URL: postgresql://user:pass@host:port/dbname
        $parsedUrl = parse_url($dbUrl);

        if ($parsedUrl === false) {
            throw new Exception("Invalid DB_URL format.");
        }

        $host = $parsedUrl['host'] ?? 'localhost';
        $port = $parsedUrl['port'] ?? '5432';
        $user = $parsedUrl['user'] ?? '';
        $pass = $parsedUrl['pass'] ?? '';
        $path = $parsedUrl['path'] ?? '/postgres';
        $dbname = ltrim($path, '/');

        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

        try {
            $this->conn = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new Exception("Connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
            self::$instance->initializeSchema();
        }
        return self::$instance->getConnection();
    }

    private function initializeSchema()
    {
        $schemaPath = __DIR__ . '/../../database/schema.sql';
        if (file_exists($schemaPath)) {
            $sql = file_get_contents($schemaPath);
            try {
                $this->conn->exec($sql);
            } catch (PDOException $e) {
                // Ignore if tables already exist or other schema-related issues
                // but log if necessary or handle gracefully.
            }
        }
    }

    public function getConnection()
    {
        return $this->conn;
    }
}
