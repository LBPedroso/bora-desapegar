<?php
/**
 * Model: Venda
 * Gerencia as vendas e atualiza status da peca
 */

require_once __DIR__ . '/Model.php';

class Venda extends Model {
    protected $table = 'vendas';

    public function __construct() {
        parent::__construct();
        $this->garantirEstruturaVendas();
    }

    private function garantirEstruturaVendas() {
        $sql = "CREATE TABLE IF NOT EXISTS vendas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cliente VARCHAR(150) NOT NULL,
            peca_id INT NOT NULL,
            valor DECIMAL(10,2) NOT NULL,
            data_venda TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_venda_peca (peca_id),
            INDEX idx_data_venda (data_venda)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->db->exec($sql);
    }

    public function registrarVenda($cliente, $pecaId, $valor = null) {
        try {
            $this->db->beginTransaction();

            // Bloqueia a peca para evitar venda duplicada em concorrencia.
            $sqlPeca = "SELECT id, nome, preco, status FROM pecas WHERE id = ? FOR UPDATE";
            $stmtPeca = $this->db->prepare($sqlPeca);
            $stmtPeca->execute([$pecaId]);
            $peca = $stmtPeca->fetch();

            if (!$peca) {
                throw new Exception('Peca nao encontrada.');
            }

            if ($peca['status'] !== 'disponivel') {
                throw new Exception('Esta peca ja foi vendida.');
            }

            $valorFinal = $valor !== null ? (float) $valor : (float) $peca['preco'];

            $sqlVenda = "INSERT INTO {$this->table} (cliente, peca_id, valor) VALUES (?, ?, ?)";
            $stmtVenda = $this->db->prepare($sqlVenda);
            $stmtVenda->execute([$cliente, $pecaId, $valorFinal]);

            $sqlStatus = "UPDATE pecas SET status = 'vendido' WHERE id = ?";
            $stmtStatus = $this->db->prepare($sqlStatus);
            $stmtStatus->execute([$pecaId]);

            $vendaId = $this->db->lastInsertId();

            $this->db->commit();
            return $vendaId;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function listarComPeca($limite = 50) {
        $limite = max(1, (int) $limite);

        $sql = "SELECT v.*, p.nome AS peca_nome, p.tamanho, p.categoria, p.foto
                FROM {$this->table} v
                INNER JOIN pecas p ON p.id = v.peca_id
                ORDER BY v.id DESC
                LIMIT {$limite}";

        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function estatisticasDashboard() {
        $result = [
            'total_pecas' => 0,
            'pecas_disponiveis' => 0,
            'pecas_vendidas' => 0,
            'valor_total_vendido' => 0.0
        ];

        try {
            $stmt = $this->db->query("SELECT COUNT(*) AS total FROM pecas");
            $result['total_pecas'] = (int) ($stmt->fetch()['total'] ?? 0);

            $stmt = $this->db->query("SELECT COUNT(*) AS total FROM pecas WHERE status = 'disponivel'");
            $result['pecas_disponiveis'] = (int) ($stmt->fetch()['total'] ?? 0);

            $stmt = $this->db->query("SELECT COUNT(*) AS total FROM pecas WHERE status = 'vendido'");
            $result['pecas_vendidas'] = (int) ($stmt->fetch()['total'] ?? 0);

            $stmt = $this->db->query("SELECT COALESCE(SUM(valor), 0) AS total FROM {$this->table}");
            $result['valor_total_vendido'] = (float) ($stmt->fetch()['total'] ?? 0);
        } catch (Exception $e) {
            // Mantém valores zero se o banco ainda estiver incompleto.
        }

        return $result;
    }

    public function vendasPorCategoria() {
        $sql = "SELECT p.categoria, COUNT(*) AS total, COALESCE(SUM(v.valor), 0) AS valor_total
                FROM {$this->table} v
                INNER JOIN pecas p ON p.id = v.peca_id
                GROUP BY p.categoria
                ORDER BY total DESC";

        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
}
