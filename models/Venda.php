<?php
/**
 * Model: Venda
 * Gerencia as vendas e atualiza status da peca
 */

require_once __DIR__ . '/Model.php';

class Venda extends Model {
    protected $table = 'vendas';

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

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function estatisticasDashboard() {
        $result = [
            'total_pecas' => 0,
            'pecas_disponiveis' => 0,
            'pecas_vendidas' => 0,
            'valor_total_vendido' => 0.0
        ];

        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM pecas");
        $result['total_pecas'] = (int) ($stmt->fetch()['total'] ?? 0);

        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM pecas WHERE status = 'disponivel'");
        $result['pecas_disponiveis'] = (int) ($stmt->fetch()['total'] ?? 0);

        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM pecas WHERE status = 'vendido'");
        $result['pecas_vendidas'] = (int) ($stmt->fetch()['total'] ?? 0);

        $stmt = $this->db->query("SELECT COALESCE(SUM(valor), 0) AS total FROM {$this->table}");
        $result['valor_total_vendido'] = (float) ($stmt->fetch()['total'] ?? 0);

        return $result;
    }

    public function vendasPorCategoria() {
        $sql = "SELECT p.categoria, COUNT(*) AS total, COALESCE(SUM(v.valor), 0) AS valor_total
                FROM {$this->table} v
                INNER JOIN pecas p ON p.id = v.peca_id
                GROUP BY p.categoria
                ORDER BY total DESC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}
