<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/AuthController.php';

AuthController::requireAdmin();

$db = Database::getInstance()->getConnection();

// Buscar todas as mensagens (de clientes e anônimas)
$sql = "SELECT c.*, cl.nome as cliente_nome, cl.email as cliente_email
        FROM contatos c
        LEFT JOIN clientes cl ON c.cliente_id = cl.id
        ORDER BY c.lido ASC, c.data_envio DESC";
$stmt = $db->query($sql);
$mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contar mensagens não lidas
$sqlNaoLidas = "SELECT COUNT(*) as total FROM contatos WHERE lido = 0";
$stmtNaoLidas = $db->query($sqlNaoLidas);
$totalNaoLidas = $stmtNaoLidas->fetch(PDO::FETCH_ASSOC)['total'];

// Contar mensagens lidas
$sqlLidas = "SELECT COUNT(*) as total FROM contatos WHERE lido = 1";
$stmtLidas = $db->query($sqlLidas);
$totalLidas = $stmtLidas->fetch(PDO::FETCH_ASSOC)['total'];

// Filtro de visualização
$filtro = $_GET['filtro'] ?? 'todas';

// Buscar mensagens com filtro
if ($filtro === 'nao_lidas') {
    $sql = "SELECT c.*, cl.nome as cliente_nome, cl.email as cliente_email
            FROM contatos c
            LEFT JOIN clientes cl ON c.cliente_id = cl.id
            WHERE c.lido = 0
            ORDER BY c.data_envio DESC";
} elseif ($filtro === 'lidas') {
    $sql = "SELECT c.*, cl.nome as cliente_nome, cl.email as cliente_email
            FROM contatos c
            LEFT JOIN clientes cl ON c.cliente_id = cl.id
            WHERE c.lido = 1
            ORDER BY c.data_envio DESC";
} else {
    $sql = "SELECT c.*, cl.nome as cliente_nome, cl.email as cliente_email
            FROM contatos c
            LEFT JOIN clientes cl ON c.cliente_id = cl.id
            ORDER BY c.lido ASC, c.data_envio DESC";
}
$stmt = $db->query($sql);
$mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Marcar mensagem como lida
if (isset($_POST['marcar_lida'])) {
    $id = $_POST['id'];
    $sqlUpdate = "UPDATE contatos SET lido = 1 WHERE id = ?";
    $stmtUpdate = $db->prepare($sqlUpdate);
    $stmtUpdate->execute([$id]);
    header('Location: mensagens.php?filtro=' . $filtro);
    exit;
}

// Marcar mensagem como não lida
if (isset($_POST['marcar_nao_lida'])) {
    $id = $_POST['id'];
    $sqlUpdate = "UPDATE contatos SET lido = 0 WHERE id = ?";
    $stmtUpdate = $db->prepare($sqlUpdate);
    $stmtUpdate->execute([$id]);
    header('Location: mensagens.php?filtro=' . $filtro);
    exit;
}

// Excluir mensagem
if (isset($_POST['excluir'])) {
    $id = $_POST['id'];
    $sqlDelete = "DELETE FROM contatos WHERE id = ?";
    $stmtDelete = $db->prepare($sqlDelete);
    $stmtDelete->execute([$id]);
    header('Location: mensagens.php?filtro=' . $filtro);
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensagens de Contato - Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        
        .container-admin {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, #4A90E2 0%, #2F5F94 100%);
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
            transition: all 0.3s;
        }
        
        .sidebar nav a:hover,
        .sidebar nav a.active {
            background: rgba(255, 255, 255, 0.2);
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
            font-size: 32px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .stat-card .numero {
            font-size: 32px;
            font-weight: bold;
            color: #4A90E2;
        }
        
        .filtros-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        
        .filtro-btn {
            padding: 10px 20px;
            border: 2px solid #4A90E2;
            background: white;
            color: #4A90E2;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .filtro-btn:hover {
            background: #f8f9fa;
        }
        
        .filtro-btn.active {
            background: #4A90E2;
            color: white;
        }
        
        .mensagens-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .mensagem-item {
            padding: 20px;
            border-bottom: 1px solid #eee;
            transition: background 0.3s;
        }
        
        .mensagem-item:hover {
            background: #f8f9fa;
        }
        
        .mensagem-item.nao-lida {
            background: #fff3cd;
            border-left: 4px solid #4A90E2;
        }
        
        .mensagem-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .mensagem-header strong {
            font-size: 16px;
            color: #333;
        }
        
        .mensagem-header .badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .badge-nova {
            background: #4A90E2;
            color: white;
        }
        
        .badge-lida {
            background: #28a745;
            color: white;
        }
        
        .mensagem-info {
            color: #666;
            font-size: 13px;
            margin-bottom: 10px;
        }
        
        .mensagem-texto {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            white-space: pre-wrap;
            color: #333;
        }
        
        .mensagem-acoes {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container-admin">
        <aside class="sidebar">
            <h2>🔥 Admin Panel</h2>
            <nav>
                <a href="index.php">📊 Dashboard</a>
                <a href="produtos.php">🥩 Produtos</a>
                <a href="categorias.php">📁 Categorias</a>
                <a href="pedidos.php">📦 Pedidos</a>
                <a href="clientes.php">👥 Clientes</a>
                <a href="mensagens.php" class="active">💬 Mensagens</a>
                <a href="../logout.php" style="margin-top: 20px; background: rgba(255,255,255,0.1);">🚪 Sair</a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="header-admin">
                <h1>💬 Mensagens de Contato</h1>
            </div>
            
            <div class="filtros-container">
                <a href="mensagens.php?filtro=todas" class="filtro-btn <?php echo $filtro === 'todas' ? 'active' : ''; ?>">
                    📋 Todas (<?php echo count($mensagens); ?>)
                </a>
                <a href="mensagens.php?filtro=nao_lidas" class="filtro-btn <?php echo $filtro === 'nao_lidas' ? 'active' : ''; ?>">
                    🔴 Não Lidas (<?php echo $totalNaoLidas; ?>)
                </a>
                <a href="mensagens.php?filtro=lidas" class="filtro-btn <?php echo $filtro === 'lidas' ? 'active' : ''; ?>">
                    ✅ Lidas (<?php echo $totalLidas; ?>)
                </a>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total de Mensagens</h3>
                    <div class="numero"><?php echo count($mensagens); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Não Lidas</h3>
                    <div class="numero" style="color: #4A90E2;"><?php echo $totalNaoLidas; ?></div>
                </div>
            </div>
            
            <div class="mensagens-container">
                <?php if (empty($mensagens)): ?>
                    <div style="padding: 40px; text-align: center; color: #666;">
                        <p>📭 Nenhuma mensagem recebida ainda.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($mensagens as $msg): ?>
                    <div class="mensagem-item <?php echo $msg['lido'] == 0 ? 'nao-lida' : ''; ?>">
                        <div class="mensagem-header">
                            <div>
                                <strong><?php echo htmlspecialchars($msg['assunto'] ?: 'Sem assunto'); ?></strong>
                                <?php if ($msg['lido'] == 0): ?>
                                    <span class="badge badge-nova">NOVA</span>
                                <?php else: ?>
                                    <span class="badge badge-lida">LIDA</span>
                                <?php endif; ?>
                            </div>
                            <span style="color: #999; font-size: 13px;">
                                📅 <?php echo date('d/m/Y H:i', strtotime($msg['data_envio'])); ?>
                            </span>
                        </div>
                        
                        <div class="mensagem-info">
                            <?php if ($msg['cliente_id']): ?>
                                <strong>👤 Cliente:</strong> <?php echo htmlspecialchars($msg['cliente_nome']); ?> 
                                (<?php echo htmlspecialchars($msg['cliente_email']); ?>)
                            <?php else: ?>
                                <strong>👤 Visitante:</strong> <?php echo htmlspecialchars($msg['nome']); ?>
                            <?php endif; ?>
                            <br>
                            <strong>📧 Email:</strong> <?php echo htmlspecialchars($msg['email']); ?>
                            <?php if ($msg['telefone']): ?>
                                | <strong>📱 Telefone:</strong> <?php echo htmlspecialchars($msg['telefone']); ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mensagem-texto">
                            <?php echo htmlspecialchars($msg['mensagem']); ?>
                        </div>
                        
                        <div class="mensagem-acoes">
                            <?php if ($msg['lido'] == 0): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                                <button type="submit" name="marcar_lida" class="btn btn-success">
                                    ✓ Marcar como Lida
                                </button>
                            </form>
                            <?php else: ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                                <button type="submit" name="marcar_nao_lida" class="btn" style="background: #ffc107; color: #000;">
                                    ↺ Marcar como Não Lida
                                </button>
                            </form>
                            <?php endif; ?>
                            
                            <?php if ($msg['email']): ?>
                            <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>" class="btn btn-secondary">
                                📧 Responder por E-mail
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($msg['telefone']): ?>
                            <a href="https://wa.me/55<?php echo preg_replace('/\D/', '', $msg['telefone']); ?>" 
                               target="_blank" class="btn btn-secondary" style="background: #25D366;">
                                💬 Responder no WhatsApp
                            </a>
                            <?php endif; ?>
                            
                            <form method="POST" style="display: inline;" onsubmit="return confirm('⚠️ Tem certeza que deseja excluir esta mensagem?');">
                                <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                                <button type="submit" name="excluir" class="btn btn-danger">
                                    🗑️ Excluir
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
