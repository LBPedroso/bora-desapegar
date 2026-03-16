<?php
/**
 * Model: Produto
 * Gerencia os produtos do cardápio
 */

require_once __DIR__ . '/Model.php';

class Produto extends Model {
    protected $table = 'produtos';

    public function __construct() {
        parent::__construct();
        $this->garantirEstruturaProdutos();
    }

    /**
     * Garante estrutura mínima de categorias/produtos para o painel legado de produtos.
     */
    private function garantirEstruturaProdutos() {
        $sqlCategorias = "CREATE TABLE IF NOT EXISTS categorias (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            descricao TEXT NULL,
            ativo BOOLEAN DEFAULT TRUE,
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_nome (nome),
            INDEX idx_ativo (ativo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $sqlProdutos = "CREATE TABLE IF NOT EXISTS produtos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            codigo VARCHAR(20) NULL,
            nome VARCHAR(150) NOT NULL,
            descricao TEXT NULL,
            preco DECIMAL(10,2) NOT NULL,
            estoque INT DEFAULT 0,
            unidade VARCHAR(50) DEFAULT 'un',
            categoria_id INT NULL,
            imagem VARCHAR(255) DEFAULT 'default.jpg',
            ativo BOOLEAN DEFAULT TRUE,
            destaque BOOLEAN DEFAULT FALSE,
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_categoria (categoria_id),
            INDEX idx_ativo (ativo),
            INDEX idx_destaque (destaque)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->db->exec($sqlCategorias);
        $this->db->exec($sqlProdutos);

        // Compatibilidade com instalações antigas.
        $this->db->exec("ALTER TABLE produtos ADD COLUMN IF NOT EXISTS destaque BOOLEAN DEFAULT FALSE");
        $this->db->exec("ALTER TABLE produtos ADD COLUMN IF NOT EXISTS unidade VARCHAR(50) DEFAULT 'un'");
        $this->db->exec("ALTER TABLE produtos ADD COLUMN IF NOT EXISTS imagem VARCHAR(255) DEFAULT 'default.jpg'");

        $totalCategorias = (int) $this->db->query("SELECT COUNT(*) FROM categorias")->fetchColumn();
        if ($totalCategorias === 0) {
            $stmt = $this->db->prepare("INSERT INTO categorias (nome, descricao, ativo) VALUES (?, ?, 1)");
            $categoriasPadrao = [
                ['Roupas', 'Pecas de vestuario infantil'],
                ['Calcados', 'Sapatos e tenis infantis'],
                ['Acessorios', 'Acessorios em geral']
            ];

            foreach ($categoriasPadrao as $categoria) {
                $stmt->execute($categoria);
            }
        }
    }
    
    /**
     * Buscar produtos ativos
     */
    public function findAtivos() {
        return $this->findAll('ativo = ?', [1]);
    }
    
    /**
     * Buscar produtos por categoria
     */
    public function findByCategoria($categoria_id) {
        $sql = "SELECT p.*, c.nome as categoria_nome 
                FROM {$this->table} p
                INNER JOIN categorias c ON p.categoria_id = c.id
                WHERE p.categoria_id = ? AND p.ativo = 1
                ORDER BY p.nome";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$categoria_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar todos com categoria
     */
    public function findAllWithCategoria() {
        $sql = "SELECT p.*, c.nome as categoria_nome 
                FROM {$this->table} p
                INNER JOIN categorias c ON p.categoria_id = c.id
                ORDER BY c.nome, p.nome";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar por código
     */
    public function findByCodigo($codigo) {
        $sql = "SELECT * FROM {$this->table} WHERE codigo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$codigo]);
        return $stmt->fetch();
    }
    
    /**
     * Buscar produtos com estoque baixo
     */
    public function findEstoqueBaixo($limite = 10) {
        $sql = "SELECT p.*, c.nome as categoria_nome 
                FROM {$this->table} p
                INNER JOIN categorias c ON p.categoria_id = c.id
                WHERE p.estoque <= ? AND p.ativo = 1
                ORDER BY p.estoque ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limite]);
        return $stmt->fetchAll();
    }
    
    /**
     * Atualizar estoque
     */
    public function atualizarEstoque($id, $quantidade) {
        $sql = "UPDATE {$this->table} SET estoque = estoque + ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$quantidade, $id]);
    }
    
    /**
     * Verificar disponibilidade (usa a FUNCTION do MySQL)
     */
    public function verificarDisponibilidade($id, $quantidade) {
        $sql = "SELECT fn_verificar_estoque(?, ?) as disponivel";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id, $quantidade]);
        $result = $stmt->fetch();
        return (bool) $result['disponivel'];
    }
    
    /**
     * Buscar produtos mais vendidos
     */
    public function findMaisVendidos($limite = 10) {
        $sql = "SELECT p.*, c.nome as categoria_nome, 
                       SUM(pi.quantidade) as total_vendido,
                       SUM(pi.subtotal) as receita_total
                FROM {$this->table} p
                INNER JOIN categorias c ON p.categoria_id = c.id
                INNER JOIN pedidos_itens pi ON p.id = pi.produto_id
                INNER JOIN pedidos ped ON pi.pedido_id = ped.id
                WHERE ped.status != 'cancelado'
                GROUP BY p.id
                ORDER BY total_vendido DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limite]);
        return $stmt->fetchAll();
    }
}
