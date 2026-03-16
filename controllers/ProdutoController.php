<?php
/**
 * Controller: Produto
 * Gerencia as operações relacionadas a produtos
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Produto.php';
require_once __DIR__ . '/../models/Categoria.php';

class ProdutoController {
    private $produtoModel;
    private $categoriaModel;
    
    public function __construct() {
        $this->produtoModel = new Produto();
        $this->categoriaModel = new Categoria();
    }
    
    /**
     * Listar produtos por categoria
     */
    public function listarPorCategoria($categoria_id = null) {
        if ($categoria_id) {
            return $this->produtoModel->findAll('categoria_id = ? AND ativo = ?', [$categoria_id, 1]);
        }
        return $this->produtoModel->findAll('ativo = ?', [1]);
    }
    
    /**
     * Buscar produto por ID
     */
    public function buscar($id) {
        return $this->produtoModel->findById($id);
    }
    
    /**
     * Buscar produtos em destaque
     */
    public function buscarDestaques($limite = 6) {
        $sql = "SELECT p.*, c.nome as categoria_nome 
                FROM produtos p 
                INNER JOIN categorias c ON p.categoria_id = c.id 
                WHERE p.ativo = 1 AND p.destaque = 1 
                ORDER BY p.criado_em DESC 
                LIMIT ?";
        
        $stmt = $this->produtoModel->db->prepare($sql);
        $stmt->execute([$limite]);
        return $stmt->fetchAll();
    }
    
    /**
     * Buscar produtos para o cardápio com filtros
     */
    public function buscarCardapio($categoria_id = null, $busca = null) {
        $sql = "SELECT p.*, c.nome as categoria_nome 
                FROM produtos p 
                INNER JOIN categorias c ON p.categoria_id = c.id 
                WHERE p.ativo = 1";
        
        $params = [];
        
        if ($categoria_id) {
            $sql .= " AND p.categoria_id = ?";
            $params[] = $categoria_id;
        }
        
        if ($busca) {
            $sql .= " AND (p.nome LIKE ? OR p.descricao LIKE ?)";
            $params[] = "%$busca%";
            $params[] = "%$busca%";
        }
        
        $sql .= " ORDER BY c.id, p.nome";
        
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Verificar disponibilidade do produto
     */
    public function verificarDisponibilidade($produto_id, $quantidade) {
        $produto = $this->buscar($produto_id);
        
        if (!$produto || !$produto['ativo']) {
            return false;
        }
        
        // Usar a function do banco para verificar estoque
        $sql = "SELECT fn_verificar_estoque(?, ?) as disponivel";
        $stmt = $this->produtoModel->db->prepare($sql);
        $stmt->execute([$produto_id, $quantidade]);
        $result = $stmt->fetch();
        
        return (bool)$result['disponivel'];
    }
}
