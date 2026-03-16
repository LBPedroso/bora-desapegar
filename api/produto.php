<?php
/**
 * API: Produto
 * Retorna informações de um produto em JSON
 */

header('Content-Type: application/json');
require_once '../config/config.php';

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
    exit;
}

$produtoModel = new Produto();
$produto = $produtoModel->findById($_GET['id']);

if ($produto) {
    // Adicionar caminho completo da imagem
    if (isset($produto['imagem']) && $produto['imagem']) {
        $produto['imagem_url'] = 'public/assets/img/produtos/' . $produto['imagem'];
    } else {
        $produto['imagem_url'] = 'public/assets/img/produtos/default.jpg';
    }
    
    echo json_encode([
        'success' => true,
        'data' => $produto
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Produto não encontrado'
    ]);
}
