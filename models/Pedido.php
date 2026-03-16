<?php
/**
 * Model: Pedido
 * Gerencia os pedidos do sistema
 */

require_once __DIR__ . '/Model.php';

class Pedido extends Model {
    protected $table = 'pedidos';
    
    /**
     * Buscar pedidos por cliente
     */
    public function findByCliente($cliente_id) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE cliente_id = ? 
                ORDER BY criado_em DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$cliente_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar pedidos por status
     */
    public function findByStatus($status) {
        $sql = "SELECT p.*, c.nome as cliente_nome, c.telefone as cliente_telefone
                FROM {$this->table} p
                INNER JOIN clientes c ON p.cliente_id = c.id
                WHERE p.status = ?
                ORDER BY p.data_entrega ASC, p.criado_em DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$status]);
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar todos os pedidos com dados do cliente
     */
    public function findAllWithCliente() {
        $sql = "SELECT p.*, c.nome as cliente_nome, c.telefone as cliente_telefone, c.email as cliente_email
                FROM {$this->table} p
                INNER JOIN clientes c ON p.cliente_id = c.id
                ORDER BY p.criado_em DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar pedido completo (com itens)
     */
    public function findByIdCompleto($id) {
        // Buscar pedido
        $pedido = $this->findById($id);
        
        if ($pedido) {
            // Buscar itens do pedido
            $sql = "SELECT * FROM pedidos_itens WHERE pedido_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $pedido['itens'] = $stmt->fetchAll();
        }
        
        return $pedido;
    }
    
    /**
     * Criar pedido com itens
     */
    public function criarPedido($cliente_id, $itens, $total, $taxa_entrega, $dados_entrega) {
        try {
            $this->db->beginTransaction();
            
            // Buscar endereço do cliente
            $stmt = $this->db->prepare("SELECT endereco_rua, endereco_numero, endereco_complemento, 
                                         endereco_bairro, endereco_cidade, endereco_estado, endereco_cep 
                                         FROM clientes WHERE id = ?");
            $stmt->execute([$cliente_id]);
            $cliente = $stmt->fetch();
            
            $endereco = sprintf(
                "%s, %s %s - %s, %s/%s - CEP: %s",
                $cliente['endereco_rua'] ?? '',
                $cliente['endereco_numero'] ?? '',
                $cliente['endereco_complemento'] ? '(' . $cliente['endereco_complemento'] . ')' : '',
                $cliente['endereco_bairro'] ?? '',
                $cliente['endereco_cidade'] ?? '',
                $cliente['endereco_estado'] ?? '',
                $cliente['endereco_cep'] ?? ''
            );
            
            $subtotal = $total - $taxa_entrega;
            
            // Preparar dados do pedido
            $dados_pedido = [
                'cliente_id' => $cliente_id,
                'subtotal' => $subtotal,
                'total' => $total,
                'taxa_entrega' => $taxa_entrega,
                'status' => 'pendente',
                'data_entrega' => $dados_entrega['data_entrega'],
                'forma_pagamento' => $dados_entrega['forma_pagamento'] ?? 'dinheiro',
                'observacoes' => $dados_entrega['observacoes'] ?? '',
                'endereco_entrega' => $endereco
            ];
            
            // Inserir pedido
            $pedido_id = $this->create($dados_pedido);
            
            // Inserir itens
            foreach ($itens as $item) {
                $sql = "INSERT INTO pedidos_itens (pedido_id, produto_id, produto_nome, quantidade, preco_unitario, subtotal)
                        VALUES (?, ?, ?, ?, ?, ?)";
                
                $subtotal = $item['preco'] * $item['quantidade'];
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $pedido_id,
                    $item['id'],
                    $item['nome'],
                    $item['quantidade'],
                    $item['preco'],
                    $subtotal
                ]);
                
                // Atualizar estoque
                $sql_estoque = "UPDATE produtos SET estoque = estoque - ? WHERE id = ?";
                $stmt_estoque = $this->db->prepare($sql_estoque);
                $stmt_estoque->execute([$item['quantidade'], $item['id']]);
            }
            
            $this->db->commit();
            return $pedido_id;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Atualizar status do pedido
     */
    public function atualizarStatus($id, $status) {
        return $this->update($id, ['status' => $status]);
    }
    
    /**
     * Total de vendas por período
     */
    public function totalVendasPeriodo($data_inicio, $data_fim) {
        $sql = "SELECT SUM(total) as total_vendas, COUNT(*) as total_pedidos
                FROM {$this->table}
                WHERE data_entrega BETWEEN ? AND ?
                AND status != 'cancelado'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$data_inicio, $data_fim]);
        return $stmt->fetch();
    }
    
    /**
     * Vendas por dia (para gráficos)
     */
    public function vendasPorDia($dias = 30) {
        $sql = "SELECT DATE(data_entrega) as data, 
                       SUM(total) as total_vendas,
                       COUNT(*) as total_pedidos
                FROM {$this->table}
                WHERE data_entrega >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                AND status != 'cancelado'
                GROUP BY DATE(data_entrega)
                ORDER BY data ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dias]);
        return $stmt->fetchAll();
    }
    
    /**
     * Pedidos por status (para dashboard)
     */
    public function contarPorStatus() {
        $sql = "SELECT status, COUNT(*) as total
                FROM {$this->table}
                GROUP BY status";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
