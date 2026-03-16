<?php
/**
 * Configuração de Conexão com Banco de Dados
 * Bora Desapegar
 */

// Configurações do banco de dados
// Detecta automaticamente o ambiente (local ou produção)
$isLocal = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', 'localhost:8080', '127.0.0.1', '127.0.0.1:8080']);

if ($isLocal) {
    // LOCAL (XAMPP)
    define('DB_HOST', '127.0.0.1');
    define('DB_NAME', 'bora_desapegar');
    define('DB_USER', 'root');
    define('DB_PASS', '');
} else {
    // PRODUÇÃO (InfinityFree)
    define('DB_HOST', 'sql300.infinityfree.com');
    define('DB_NAME', 'if0_41401599_if0_41401599_boradesapegar');
    define('DB_USER', 'if0_41401599');
    define('DB_PASS', 'UbOzFoUXa9JbB5');
}
define('DB_CHARSET', 'utf8mb4');

/**
 * Classe Database - Singleton Pattern
 * Gerencia a conexão com o banco de dados usando PDO
 */
class Database {
    private static $instance = null;
    private $connection;

    /**
     * Construtor privado para implementar Singleton
     */
    private function __construct() {
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];

        // Em produção, alguns painéis adicionam prefixo duplo no nome do banco.
        $dbNames = [DB_NAME];
        if (!$GLOBALS['isLocal']) {
            $fallbackNames = [
                DB_USER . '_' . DB_NAME,
                preg_replace('/^' . preg_quote(DB_USER . '_', '/') . '/', '', DB_NAME)
            ];

            foreach ($fallbackNames as $candidate) {
                if (!empty($candidate) && !in_array($candidate, $dbNames, true)) {
                    $dbNames[] = $candidate;
                }
            }
        }

        $lastException = null;
        foreach ($dbNames as $dbName) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . $dbName . ";charset=" . DB_CHARSET;
                $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
                return;
            } catch (PDOException $e) {
                $lastException = $e;
            }
        }

        die("Erro na conexão com o banco de dados: " . ($lastException ? $lastException->getMessage() : 'Falha desconhecida'));
    }

    /**
     * Retorna a instância única da classe (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Retorna a conexão PDO
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Previne clonagem da instância
     */
    private function __clone() {}

    /**
     * Previne deserialização da instância
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
