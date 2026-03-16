<?php
/**
 * Model: Peca
 * Gerencia as pecas do brecho
 */

require_once __DIR__ . '/Model.php';

class Peca extends Model {
    protected $table = 'pecas';

    public function __construct() {
        parent::__construct();
        $this->garantirEstruturaPecas();
    }

    private function garantirEstruturaPecas() {
        $sql = "CREATE TABLE IF NOT EXISTS pecas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(150) NOT NULL,
            categoria VARCHAR(100) NOT NULL,
            tamanho VARCHAR(20) NOT NULL,
            preco DECIMAL(10,2) NOT NULL,
            foto VARCHAR(255) DEFAULT 'default.jpg',
            observacao TEXT NULL,
            status VARCHAR(20) DEFAULT 'disponivel',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_status (status),
            INDEX idx_categoria (categoria),
            INDEX idx_tamanho (tamanho)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->db->exec($sql);
    }

    public function findDisponiveis() {
        try {
            return $this->findAll('status = ?', ['disponivel']);
        } catch (Exception $e) {
            return [];
        }
    }

    public function findVendidas() {
        return $this->findAll('status = ?', ['vendido']);
    }

    public function atualizarStatus($id, $status) {
        return $this->update($id, ['status' => $status]);
    }

    public function listarCategorias() {
        try {
            $sql = "SELECT DISTINCT categoria FROM {$this->table} WHERE categoria <> '' ORDER BY categoria ASC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function findComFiltros($filtros = []) {
        try {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        if (!empty($filtros['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filtros['status'];
        }

        if (!empty($filtros['categoria'])) {
            $sql .= " AND categoria = ?";
            $params[] = $filtros['categoria'];
        }

        if (!empty($filtros['busca'])) {
            $sql .= " AND nome LIKE ?";
            $params[] = '%' . $filtros['busca'] . '%';
        }

        $sql .= " ORDER BY id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
}
