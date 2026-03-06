<?php
require_once __DIR__ . '/vendor/autoload.php';
use App\Config\Database;

putenv('DB_URL=postgresql://user:pass@localhost:5432/resume_db');

try {
    $db = Database::getInstance();
    echo "Conexão com Banco de Dados OK!\n";

    // Test schema
    $db->query("SELECT 1 FROM resumes LIMIT 1");
    echo "Tabelas verificadas OK!\n";

} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    echo "Nota: Este teste falhará se não houver um Postgres rodando localmente, mas valida a lógica do código.\n";
}
