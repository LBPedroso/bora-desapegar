<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/AuthController.php';

// Verificar se é admin
if (!AuthController::isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

$cliente_id = $_GET['id'] ?? 0;

try {
    $db = Database::getInstance()->getConnection();
    
    $sql = "SELECT * FROM contatos 
            WHERE cliente_id = ? 
            ORDER BY data_envio DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute([$cliente_id]);
    $mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Marcar mensagens como lidas
    $sqlUpdate = "UPDATE contatos SET lido = 1 WHERE cliente_id = ?";
    $stmtUpdate = $db->prepare($sqlUpdate);
    $stmtUpdate->execute([$cliente_id]);
    
    echo json_encode([
        'success' => true,
        'mensagens' => $mensagens
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar mensagens: ' . $e->getMessage()
    ]);
}
