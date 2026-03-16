<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/PedidoController.php';
require_once __DIR__ . '/../models/Pedido.php';

AuthController::requireAdmin();

$pedidoController = new PedidoController();
$pedidoModel = new Pedido();

// Processar atualização de status
$mensagem = '';
$tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    if ($_POST['acao'] === 'atualizar_status') {
        $id = $_POST['id'];
        $status = $_POST['status'];
        
        if ($pedidoController->atualizarStatus($id, $status)) {
            $mensagem = 'Status do pedido atualizado com sucesso!';
            $tipo = 'sucesso';
        } else {
            $mensagem = 'Erro ao atualizar status do pedido.';
            $tipo = 'erro';
        }
    }
}

// Buscar todos os pedidos com informações do cliente
$db = Database::getInstance()->getConnection();
$sql = "SELECT p.*, c.nome as cliente_nome, c.email as cliente_email, c.telefone as cliente_telefone,
        (SELECT COUNT(*) FROM pedidos_itens WHERE pedido_id = p.id) as total_itens
        FROM pedidos p
        LEFT JOIN clientes c ON p.cliente_id = c.id
        ORDER BY p.criado_em DESC";
$stmt = $db->prepare($sql);
$stmt->execute();
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cores para cada status
$statusCores = [
    'pendente' => '#ffc107',
    'confirmado' => '#17a2b8',
    'preparando' => '#fd7e14',
    'pronto' => '#28a745',
    'entregue' => '#6c757d',
    'cancelado' => '#dc3545'
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Pedidos - Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f2ec;
        }
        
        .container-admin {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, #2b2d42, #1f2232);
            color: white;
            padding: 20px;
        }
        
        .sidebar h2 {
            margin-bottom: 30px;
            font-size: 24px;
        }
        
        .sidebar nav a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 12px 15px;
            margin-bottom: 5px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .sidebar nav a:hover {
            background: rgba(255,255,255,0.1);
        }
        
        .sidebar nav a.active {
            background: rgba(255,255,255,0.2);
            font-weight: bold;
        }
        
        .main-content {
            flex: 1;
            padding: 30px;
        }
        
        .header-admin {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .header-admin h1 {
            color: #333;
            font-size: 28px;
        }
        
        .filtros {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .filtros select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            margin-right: 10px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #4A90E2;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2F5F94;
        }
        
        .btn-secondary {
            background: #A8D8FF;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #D66D00;
        }
        
        .btn-small {
            padding: 5px 10px;
            font-size: 12px;
        }
        
        .mensagem {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .mensagem.sucesso {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .mensagem.erro {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .pedidos-lista {
            display: grid;
            gap: 20px;
        }
        
        .pedido-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            border-left: 5px solid #4A90E2;
        }
        
        .pedido-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .pedido-info {
            flex: 1;
        }
        
        .pedido-id {
            font-size: 18px;
            font-weight: bold;
            color: #4A90E2;
            margin-bottom: 5px;
        }
        
        .pedido-data {
            color: #666;
            font-size: 14px;
        }
        
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            color: white;
        }
        
        .pedido-body {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-bottom: 15px;
        }
        
        .pedido-section {
            padding: 10px;
        }
        
        .pedido-section h4 {
            color: #333;
            margin-bottom: 10px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .pedido-section p {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .pedido-acoes {
            display: flex;
            gap: 10px;
            align-items: center;
            padding-top: 15px;
            border-top: 2px solid #f0f0f0;
        }
        
        .pedido-acoes select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .modal-header h2 {
            color: #333;
        }
        
        .close {
            font-size: 28px;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
        }
        
        .close:hover {
            color: #000;
        }
        
        .detalhes-item {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .detalhes-total {
            display: flex;
            justify-content: space-between;
            padding: 15px 10px;
            font-weight: bold;
            font-size: 18px;
            background: #f8f9fa;
            margin-top: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container-admin">
        <aside class="sidebar">
            <h2>🧸 Bora Desapegar</h2>
            <nav>
                <a href="index.php">📊 Dashboard</a>
                <a href="pecas.php">🧸 Peças</a>
                <a href="vendas.php">💰 Vendas</a>
                <a href="pedidos.php" class="active">📦 Pedidos</a>
                <a href="clientes.php">👥 Clientes</a>
                <a href="mensagens.php">💬 Mensagens</a>
                <a href="../logout.php">🚪 Sair</a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="header-admin">
                <h1>Gerenciar Pedidos</h1>
            </div>
            
            <?php if ($mensagem): ?>
            <div class="mensagem <?php echo $tipo; ?>">
                <?php echo $mensagem; ?>
            </div>
            <?php endif; ?>
            
            <div class="filtros">
                <label>Filtrar por status:</label>
                <select id="filtroStatus" onchange="filtrarPedidos()">
                    <option value="">Todos</option>
                    <option value="pendente">Pendente</option>
                    <option value="confirmado">Confirmado</option>
                    <option value="preparando">Preparando</option>
                    <option value="pronto">Pronto</option>
                    <option value="entregue">Entregue</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </div>
            
            <div class="pedidos-lista" id="pedidosLista">
                <?php foreach ($pedidos as $pedido): ?>
                <div class="pedido-card" data-status="<?php echo $pedido['status']; ?>">
                    <div class="pedido-header">
                        <div class="pedido-info">
                            <div class="pedido-id">Pedido #<?php echo str_pad($pedido['id'], 4, '0', STR_PAD_LEFT); ?></div>
                            <div class="pedido-data">
                                📅 <?php echo date('d/m/Y H:i', strtotime($pedido['criado_em'])); ?>
                                | 🚚 Entrega: <?php echo date('d/m/Y', strtotime($pedido['data_entrega'])); ?> às <?php echo $pedido['horario_entrega']; ?>
                            </div>
                        </div>
                        <span class="status-badge" style="background: <?php echo $statusCores[$pedido['status']]; ?>">
                            <?php echo strtoupper($pedido['status']); ?>
                        </span>
                    </div>
                    
                    <div class="pedido-body">
                        <div class="pedido-section">
                            <h4>👤 Cliente</h4>
                            <p><strong><?php echo htmlspecialchars($pedido['cliente_nome'] ?? 'N/A'); ?></strong></p>
                            <p>📧 <?php echo htmlspecialchars($pedido['cliente_email'] ?? 'N/A'); ?></p>
                            <p>📱 <?php echo htmlspecialchars($pedido['cliente_telefone'] ?? 'N/A'); ?></p>
                        </div>
                        
                        <div class="pedido-section">
                            <h4>📍 Endereço de Entrega</h4>
                            <p><?php echo htmlspecialchars($pedido['endereco_entrega']); ?></p>
                        </div>
                        
                        <div class="pedido-section">
                            <h4>💰 Valores</h4>
                            <p>Subtotal: R$ <?php echo number_format($pedido['subtotal'], 2, ',', '.'); ?></p>
                            <p>Taxa de Entrega: R$ <?php echo number_format($pedido['taxa_entrega'], 2, ',', '.'); ?></p>
                            <p><strong>Total: R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?></strong></p>
                            <p>Itens: <?php echo $pedido['total_itens']; ?></p>
                        </div>
                    </div>
                    
                    <?php if ($pedido['observacoes']): ?>
                    <div class="pedido-section">
                        <h4>📝 Observações</h4>
                        <p><?php echo htmlspecialchars($pedido['observacoes']); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="pedido-acoes">
                        <form method="POST" style="display: flex; gap: 10px; align-items: center; flex: 1;">
                            <input type="hidden" name="acao" value="atualizar_status">
                            <input type="hidden" name="id" value="<?php echo $pedido['id']; ?>">
                            <label>Alterar Status:</label>
                            <select name="status" required>
                                <option value="pendente" <?php echo $pedido['status'] === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                                <option value="confirmado" <?php echo $pedido['status'] === 'confirmado' ? 'selected' : ''; ?>>Confirmado</option>
                                <option value="preparando" <?php echo $pedido['status'] === 'preparando' ? 'selected' : ''; ?>>Preparando</option>
                                <option value="pronto" <?php echo $pedido['status'] === 'pronto' ? 'selected' : ''; ?>>Pronto</option>
                                <option value="entregue" <?php echo $pedido['status'] === 'entregue' ? 'selected' : ''; ?>>Entregue</option>
                                <option value="cancelado" <?php echo $pedido['status'] === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                            </select>
                            <button type="submit" class="btn btn-primary btn-small">Atualizar</button>
                        </form>
                        <button class="btn btn-secondary btn-small" onclick="verDetalhes(<?php echo $pedido['id']; ?>)">Ver Itens</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
    
    <!-- Modal Detalhes -->
    <div id="modalDetalhes" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitulo">Detalhes do Pedido</h2>
                <span class="close" onclick="fecharModal()">&times;</span>
            </div>
            <div id="detalhesConteudo"></div>
        </div>
    </div>
    
    <script>
        function filtrarPedidos() {
            const filtro = document.getElementById('filtroStatus').value;
            const cards = document.querySelectorAll('.pedido-card');
            
            cards.forEach(card => {
                if (filtro === '' || card.dataset.status === filtro) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
        
        async function verDetalhes(pedidoId) {
            try {
                const response = await fetch(`../api/pedido_detalhes.php?id=${pedidoId}`);
                const data = await response.json();
                
                if (data.success) {
                    const pedido = data.pedido;
                    const itens = data.itens;
                    
                    let html = '<h3>Itens do Pedido #' + String(pedidoId).padStart(4, '0') + '</h3>';
                    
                    itens.forEach(item => {
                        html += `
                            <div class="detalhes-item">
                                <div>
                                    <strong>${item.produto_nome}</strong><br>
                                    <small>${item.quantidade}x R$ ${parseFloat(item.preco_unitario).toFixed(2).replace('.', ',')}</small>
                                </div>
                                <div style="text-align: right;">
                                    <strong>R$ ${parseFloat(item.subtotal).toFixed(2).replace('.', ',')}</strong>
                                </div>
                            </div>
                        `;
                    });
                    
                    html += `
                        <div class="detalhes-total">
                            <span>TOTAL</span>
                            <span>R$ ${parseFloat(pedido.total).toFixed(2).replace('.', ',')}</span>
                        </div>
                    `;
                    
                    document.getElementById('detalhesConteudo').innerHTML = html;
                    document.getElementById('modalDetalhes').style.display = 'block';
                } else {
                    alert('Erro ao carregar detalhes do pedido');
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao carregar detalhes do pedido');
            }
        }
        
        function fecharModal() {
            document.getElementById('modalDetalhes').style.display = 'none';
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('modalDetalhes');
            if (event.target == modal) {
                fecharModal();
            }
        }
    </script>
</body>
</html>
