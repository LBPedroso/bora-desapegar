<?php
/**
 * Model: Peca
 * Gerencia as pecas do brecho
 */

require_once __DIR__ . '/Model.php';

class Peca extends Model {
    protected $table = 'pecas';

    public function findDisponiveis() {
        return $this->findAll('status = ?', ['disponivel']);
    }

    public function findVendidas() {
        return $this->findAll('status = ?', ['vendido']);
    }

    public function atualizarStatus($id, $status) {
        return $this->update($id, ['status' => $status]);
    }

    public function listarCategorias() {
        $sql = "SELECT DISTINCT categoria FROM {$this->table} WHERE categoria <> '' ORDER BY categoria ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function findComFiltros($filtros = []) {
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
    }
}
