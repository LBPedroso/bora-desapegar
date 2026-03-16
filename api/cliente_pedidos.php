<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$clienteId = $_GET['id'] ?? null;

if (!$clienteId) {
    echo json_encode(['success' => false, 'message' => 'ID do cliente não fornecido']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    $sql = "SELECT * FROM pedidos WHERE cliente_id = ? ORDER BY criado_em DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute([$clienteId]);
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'pedidos' => $pedidos
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
