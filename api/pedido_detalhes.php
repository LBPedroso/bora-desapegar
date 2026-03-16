<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID do pedido não fornecido']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Buscar dados do pedido
    $sqlPedido = "SELECT p.*, c.nome as cliente_nome, c.telefone as cliente_telefone, 
                  c.email as cliente_email
                  FROM pedidos p
                  LEFT JOIN clientes c ON p.cliente_id = c.id
                  WHERE p.id = ?";
    $stmtPedido = $db->prepare($sqlPedido);
    $stmtPedido->execute([$id]);
    $pedido = $stmtPedido->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedido) {
        echo json_encode(['success' => false, 'message' => 'Pedido não encontrado']);
        exit;
    }
    
    // Buscar itens do pedido
    $sqlItens = "SELECT * FROM pedidos_itens WHERE pedido_id = ?";
    $stmtItens = $db->prepare($sqlItens);
    $stmtItens->execute([$id]);
    $itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'pedido' => $pedido,
        'itens' => $itens
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
