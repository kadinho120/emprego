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
                // Execute schema.sql (contains multiple statements)
                $this->conn->exec($sql);
            } catch (PDOException $e) {
                // If it fails (e.g. part of it already exists), we continue to migrations
            }

            try {
                // Specific migrations for existing databases
                $this->conn->exec("ALTER TABLE resumes ADD COLUMN IF NOT EXISTS city VARCHAR(100)");
                $this->conn->exec("ALTER TABLE resumes ADD COLUMN IF NOT EXISTS state VARCHAR(50)");
                $this->conn->exec("ALTER TABLE resumes ADD COLUMN IF NOT EXISTS photo_path VARCHAR(255)");

                // Ensure users table exists before adding FK
                $this->conn->exec("CREATE TABLE IF NOT EXISTS users (id SERIAL PRIMARY KEY, email VARCHAR(255) UNIQUE NOT NULL, password_hash VARCHAR(255), role VARCHAR(20) DEFAULT 'user', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");

                $this->conn->exec("ALTER TABLE resumes ADD COLUMN IF NOT EXISTS user_id INTEGER REFERENCES users(id) ON DELETE CASCADE");
                $this->conn->exec("ALTER TABLE resumes ADD COLUMN IF NOT EXISTS slug VARCHAR(255) UNIQUE");
                $this->conn->exec("ALTER TABLE resumes ADD COLUMN IF NOT EXISTS views INTEGER DEFAULT 0");
                $this->conn->exec("ALTER TABLE resumes ADD COLUMN IF NOT EXISTS primary_color VARCHAR(20) DEFAULT '#6366f1'");
                $this->conn->exec("ALTER TABLE resumes ADD COLUMN IF NOT EXISTS font_family VARCHAR(50) DEFAULT 'jakarta'");
                $this->conn->exec("ALTER TABLE experiences ADD COLUMN IF NOT EXISTS sort_order INTEGER DEFAULT 0");
                $this->conn->exec("ALTER TABLE education ADD COLUMN IF NOT EXISTS sort_order INTEGER DEFAULT 0");
                $this->conn->exec("ALTER TABLE education ADD COLUMN IF NOT EXISTS field_of_study VARCHAR(255)");
                $this->conn->exec("ALTER TABLE skills ADD COLUMN IF NOT EXISTS category VARCHAR(100)");
            } catch (PDOException $e) {
                // Ignore migration errors (usually means columns already exist)
            }
        }
    }

    public function getConnection()
    {
        return $this->conn;
    }
}
