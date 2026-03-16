<?php
/**
 * Model: Cliente
 * Gerencia os clientes do sistema
 */

require_once __DIR__ . '/Model.php';

class Cliente extends Model {
    protected $table = 'clientes';

    public function __construct() {
        parent::__construct();
        $this->garantirEstruturaMinima();
    }

    /**
     * Garante a estrutura mínima para cadastro/login de clientes.
     */
    private function garantirEstruturaMinima() {
        $sql = "CREATE TABLE IF NOT EXISTS clientes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NULL,
            senha VARCHAR(255) NOT NULL,
            telefone VARCHAR(20) UNIQUE NULL,
            cpf VARCHAR(14) UNIQUE NULL,
            endereco_rua VARCHAR(200) NULL,
            endereco_numero VARCHAR(10) NULL,
            endereco_complemento VARCHAR(100) NULL,
            endereco_bairro VARCHAR(100) NULL,
            endereco_cidade VARCHAR(100) NULL,
            endereco_estado VARCHAR(2) NULL,
            endereco_cep VARCHAR(9) NULL,
            ativo BOOLEAN DEFAULT TRUE,
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_cpf (cpf),
            INDEX idx_telefone (telefone)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->db->exec($sql);
    }

    private function somenteNumeros($valor) {
        return preg_replace('/\\D/', '', (string) $valor);
    }

    /**
     * Buscar cliente por email
     */
    public function findByEmail($email) {
        if (empty($email)) {
            return null;
        }

        $sql = "SELECT * FROM {$this->table} WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /**
     * Buscar cliente por telefone (ignora formatação)
     */
    public function findByTelefone($telefone) {
        $numeros = $this->somenteNumeros($telefone);
        if ($numeros === '') {
            return null;
        }

        $sql = "SELECT * FROM {$this->table}
                WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(telefone, '(', ''), ')', ''), ' ', ''), '-', ''), '.', '') = ?
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$numeros]);
        return $stmt->fetch();
    }

    /**
     * Buscar cliente por CPF (ignora formatação)
     */
    public function findByCPF($cpf) {
        $numeros = $this->somenteNumeros($cpf);
        if ($numeros === '') {
            return null;
        }

        $sql = "SELECT * FROM {$this->table}
                WHERE REPLACE(REPLACE(cpf, '.', ''), '-', '') = ?
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$numeros]);
        return $stmt->fetch();
    }

    /**
     * Buscar cliente para login por CPF ou telefone.
     */
    public function findByCpfOrTelefone($identificador) {
        $valor = trim((string) $identificador);
        if ($valor === '') {
            return null;
        }

        $numeros = $this->somenteNumeros($valor);

        if ($numeros === '') {
            return null;
        }

        $sql = "SELECT * FROM {$this->table}
                WHERE REPLACE(REPLACE(cpf, '.', ''), '-', '') = ?
                   OR REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(telefone, '(', ''), ')', ''), ' ', ''), '-', ''), '.', '') = ?
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$numeros, $numeros]);
        return $stmt->fetch();
    }

    /**
     * Compatibilidade com código legado.
     */
    public function findByEmailOrTelefone($emailOrTelefone) {
        if (filter_var((string) $emailOrTelefone, FILTER_VALIDATE_EMAIL)) {
            return $this->findByEmail($emailOrTelefone);
        }

        return $this->findByCpfOrTelefone($emailOrTelefone);
    }

    /**
     * Verificar login
     */
    public function verificarLogin($identificador, $senha) {
        $cliente = $this->findByCpfOrTelefone($identificador);

        if ($cliente && password_verify($senha, $cliente['senha'])) {
            return $cliente;
        }

        return false;
    }

    /**
     * Registrar novo cliente
     */
    public function registrar($dados) {
        $dados['senha'] = password_hash($dados['senha'], PASSWORD_DEFAULT);
        return $this->create($dados);
    }

    /**
     * Buscar clientes recentes
     */
    public function findRecentes($limite = 10) {
        $sql = "SELECT * FROM {$this->table}
                WHERE ativo = 1
                ORDER BY criado_em DESC
                LIMIT ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limite]);
        return $stmt->fetchAll();
    }

    /**
     * Contar pedidos do cliente
     */
    public function contarPedidos($cliente_id) {
        $sql = "SELECT COUNT(*) as total FROM pedidos WHERE cliente_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$cliente_id]);
        $result = $stmt->fetch();
        return $result['total'];
    }

    /**
     * Total gasto pelo cliente
     */
    public function totalGasto($cliente_id) {
        $sql = "SELECT SUM(total) as total_gasto
                FROM pedidos
                WHERE cliente_id = ? AND status != 'cancelado'";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$cliente_id]);
        $result = $stmt->fetch();
        return $result['total_gasto'] ?? 0;
    }
}
