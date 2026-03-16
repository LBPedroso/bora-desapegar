<?php
/**
 * Configuração de Conexão com Banco de Dados
 * Bora Desapegar
 */

// Configurações do banco de dados
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'bora_desapegar');
define('DB_USER', 'root');
define('DB_PASS', '');
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
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];

            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            die("Erro na conexão com o banco de dados: " . $e->getMessage());
        }
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
