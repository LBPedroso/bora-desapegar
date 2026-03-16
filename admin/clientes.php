<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../models/Cliente.php';

AuthController::requireAdmin();

$clienteModel = new Cliente();

// Função para formatar telefone
function formatarTelefone($telefone) {
    $telefone = preg_replace('/\D/', '', $telefone); // Remove tudo que não é número
    if (strlen($telefone) == 11) {
        return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 5) . '-' . substr($telefone, 7);
    } elseif (strlen($telefone) == 10) {
        return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 4) . '-' . substr($telefone, 6);
    }
    return $telefone;
}

// Função para formatar CPF
function formatarCPF($cpf) {
    $cpf = preg_replace('/\D/', '', $cpf); // Remove tudo que não é número
    if (strlen($cpf) == 11) {
        return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
    }
    return $cpf;
}

// Função para formatar CEP
function formatarCEP($cep) {
    $cep = preg_replace('/\D/', '', $cep); // Remove tudo que não é número
    if (strlen($cep) == 8) {
        return substr($cep, 0, 5) . '-' . substr($cep, 5);
    }
    return $cep;
}

// Buscar estatísticas dos clientes
$db = Database::getInstance()->getConnection();

// Garante tabelas usadas no relatório de clientes em ambiente novo.
$db->exec("CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_entrega DATE NULL,
    horario_entrega VARCHAR(20) NULL,
    status ENUM('pendente','confirmado','preparando','em_preparo','saiu-entrega','entregue','cancelado') DEFAULT 'pendente',
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
    taxa_entrega DECIMAL(10,2) NOT NULL DEFAULT 0,
    total DECIMAL(10,2) NOT NULL DEFAULT 0,
    forma_pagamento VARCHAR(50) DEFAULT 'dinheiro',
    observacoes TEXT NULL,
    endereco_entrega TEXT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cliente (cliente_id),
    INDEX idx_status (status),
    INDEX idx_data_entrega (data_entrega)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$db->exec("CREATE TABLE IF NOT EXISTS contatos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NULL,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(120) NULL,
    telefone VARCHAR(20) NULL,
    assunto VARCHAR(150) NULL,
    mensagem TEXT NOT NULL,
    lido BOOLEAN DEFAULT FALSE,
    data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cliente (cliente_id),
    INDEX idx_lido (lido),
    INDEX idx_data_envio (data_envio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Total de clientes
$sqlTotal = "SELECT COUNT(*) as total FROM clientes";
$stmtTotal = $db->query($sqlTotal);
$totalClientes = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];

// Buscar todos os clientes com estatísticas de pedidos e mensagens
$sql = "SELECT c.*, 
        COUNT(DISTINCT p.id) as total_pedidos,
        COALESCE(SUM(p.total), 0) as total_gasto,
        MAX(p.criado_em) as ultimo_pedido,
        COUNT(DISTINCT ct.id) as total_mensagens,
        SUM(CASE WHEN ct.lido = 0 THEN 1 ELSE 0 END) as mensagens_nao_lidas
        FROM clientes c
        LEFT JOIN pedidos p ON c.id = p.cliente_id
        LEFT JOIN contatos ct ON c.id = ct.cliente_id
        GROUP BY c.id
        ORDER BY total_pedidos DESC, c.nome ASC";
$stmt = $db->query($sql);
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Clientes - Admin</title>
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
            margin-bottom: 30px;
        }
        
        .header-admin h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .stats-resumo {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card-pequeno {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stat-card-pequeno h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .stat-card-pequeno .valor {
            color: #4A90E2;
            font-size: 32px;
            font-weight: bold;
        }
        
        .busca-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .busca-container input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .clientes-grid {
            display: grid;
            gap: 20px;
        }
        
        .cliente-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 20px;
            transition: transform 0.3s;
        }
        
        .cliente-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .cliente-info h3 {
            color: #4A90E2;
            font-size: 20px;
            margin-bottom: 10px;
        }
        
        .cliente-info p {
            color: #666;
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .cliente-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        
        .stat-item {
            text-align: center;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .stat-item .numero {
            font-size: 20px;
            font-weight: bold;
            color: #4A90E2;
        }
        
        .stat-item .label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
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
        
        .btn-secondary {
            background: #A8D8FF;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #D66D00;
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
        
        .pedido-item {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .pedido-item:last-child {
            border-bottom: none;
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
                <a href="pedidos.php">📦 Pedidos</a>
                <a href="clientes.php" class="active">👥 Clientes</a>
                <a href="mensagens.php">💬 Mensagens</a>
                <a href="../logout.php">🚪 Sair</a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="header-admin">
                <h1>Gerenciar Clientes</h1>
                <p style="color: #666;">Visualize e acompanhe seus clientes</p>
            </div>
            
            <div class="stats-resumo">
                <div class="stat-card-pequeno">
                    <h3>Total de Clientes</h3>
                    <div class="valor"><?php echo $totalClientes; ?></div>
                </div>
                <div class="stat-card-pequeno">
                    <h3>Clientes Ativos</h3>
                    <div class="valor"><?php echo count(array_filter($clientes, fn($c) => $c['total_pedidos'] > 0)); ?></div>
                </div>
                <div class="stat-card-pequeno">
                    <h3>Novos Este Mês</h3>
                    <div class="valor">
                        <?php 
                        $novos = array_filter($clientes, function($c) {
                            return date('Y-m', strtotime($c['criado_em'])) === date('Y-m');
                        });
                        echo count($novos);
                        ?>
                    </div>
                </div>
            </div>
            
            <div class="busca-container">
                <input type="text" id="buscaCliente" placeholder="🔍 Buscar cliente por nome, email ou telefone..." onkeyup="buscarClientes()">
            </div>
            
            <div class="clientes-grid" id="clientesGrid">
                <?php foreach ($clientes as $cliente): ?>
                <div class="cliente-card" data-nome="<?php echo strtolower($cliente['nome']); ?>" data-email="<?php echo strtolower($cliente['email'] ?? ''); ?>" data-telefone="<?php echo $cliente['telefone'] ?? ''; ?>">
                    <div class="cliente-info">
                        <h3><?php echo htmlspecialchars($cliente['nome']); ?></h3>
                        <p>📧 <?php echo !empty($cliente['email']) ? htmlspecialchars($cliente['email']) : 'Não informado'; ?></p>
                        <p>📱 <?php echo !empty($cliente['telefone']) ? formatarTelefone($cliente['telefone']) : 'Não informado'; ?></p>
                        <?php if (!empty($cliente['endereco_rua'])): ?>
                        <p>📍 <?php echo htmlspecialchars($cliente['endereco_rua']); ?>, <?php echo htmlspecialchars($cliente['endereco_numero'] ?? 's/n'); ?> - <?php echo htmlspecialchars($cliente['endereco_bairro'] ?? ''); ?></p>
                        <p style="margin-left: 1.5rem; color: #666;">
                            <?php echo htmlspecialchars($cliente['endereco_cidade'] ?? ''); ?>/<?php echo htmlspecialchars($cliente['endereco_estado'] ?? ''); ?>
                            <?php if (!empty($cliente['endereco_cep'])): ?>
                                - CEP: <?php echo formatarCEP($cliente['endereco_cep']); ?>
                            <?php endif; ?>
                        </p>
                        <?php else: ?>
                        <p>📍 Endereço não cadastrado</p>
                        <?php endif; ?>
                        <?php if (!empty($cliente['cpf'])): ?>
                        <p>🆔 CPF: <?php echo formatarCPF($cliente['cpf']); ?></p>
                        <?php endif; ?>
                        <p>📅 Cliente desde <?php echo date('d/m/Y', strtotime($cliente['criado_em'])); ?></p>
                        <?php if ($cliente['ultimo_pedido']): ?>
                        <p>🕒 Último pedido: <?php echo date('d/m/Y', strtotime($cliente['ultimo_pedido'])); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <div class="cliente-stats">
                            <div class="stat-item">
                                <div class="numero"><?php echo $cliente['total_pedidos']; ?></div>
                                <div class="label">Pedidos</div>
                            </div>
                            <div class="stat-item">
                                <div class="numero">R$ <?php echo number_format($cliente['total_gasto'], 0); ?></div>
                                <div class="label">Total Gasto</div>
                            </div>
                            <div class="stat-item">
                                <div class="numero">
                                    <?php echo $cliente['total_mensagens']; ?>
                                    <?php if ($cliente['mensagens_nao_lidas'] > 0): ?>
                                        <span style="color: #4A90E2; font-size: 12px;">●</span>
                                    <?php endif; ?>
                                </div>
                                <div class="label">Mensagens</div>
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px;">
                            <?php if ($cliente['total_pedidos'] > 0): ?>
                            <button class="btn btn-secondary" onclick="verPedidosCliente(<?php echo $cliente['id']; ?>, '<?php echo addslashes($cliente['nome']); ?>')">
                                Ver Pedidos
                            </button>
                            <?php endif; ?>
                            <?php if ($cliente['total_mensagens'] > 0): ?>
                            <button class="btn btn-secondary" onclick="verMensagensCliente(<?php echo $cliente['id']; ?>, '<?php echo addslashes($cliente['nome']); ?>')">
                                💬 Mensagens
                                <?php if ($cliente['mensagens_nao_lidas'] > 0): ?>
                                    <span style="background: #4A90E2; padding: 2px 6px; border-radius: 10px; font-size: 11px; margin-left: 5px;"><?php echo $cliente['mensagens_nao_lidas']; ?></span>
                                <?php endif; ?>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
    
    <!-- Modal Pedidos do Cliente -->
    <div id="modalPedidos" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitulo">Pedidos do Cliente</h2>
                <span class="close" onclick="fecharModal()">&times;</span>
            </div>
            <div id="pedidosConteudo"></div>
        </div>
    </div>
    
    <script>
        function buscarClientes() {
            const busca = document.getElementById('buscaCliente').value.toLowerCase();
            const cards = document.querySelectorAll('.cliente-card');
            
            cards.forEach(card => {
                const nome = card.dataset.nome;
                const email = card.dataset.email;
                const telefone = card.dataset.telefone;
                
                if (nome.includes(busca) || email.includes(busca) || telefone.includes(busca)) {
                    card.style.display = 'grid';
                } else {
                    card.style.display = 'none';
                }
            });
        }
        
        async function verPedidosCliente(clienteId, clienteNome) {
            try {
                const response = await fetch(`../api/cliente_pedidos.php?id=${clienteId}`);
                const data = await response.json();
                
                if (data.success) {
                    const pedidos = data.pedidos;
                    
                    document.getElementById('modalTitulo').textContent = `Pedidos de ${clienteNome}`;
                    
                    let html = '';
                    if (pedidos.length === 0) {
                        html = '<p style="text-align: center; padding: 20px; color: #666;">Nenhum pedido encontrado.</p>';
                    } else {
                        pedidos.forEach(pedido => {
                            const statusCores = {
                                'pendente': '#ffc107',
                                'confirmado': '#17a2b8',
                                'preparando': '#fd7e14',
                                'pronto': '#28a745',
                                'entregue': '#6c757d',
                                'cancelado': '#dc3545'
                            };
                            
                            html += `
                                <div class="pedido-item">
                                    <div>
                                        <strong>Pedido #${String(pedido.id).padStart(4, '0')}</strong><br>
                                        <small>📅 ${new Date(pedido.criado_em).toLocaleDateString('pt-BR')}</small><br>
                                        <span style="display: inline-block; margin-top: 5px; padding: 4px 8px; border-radius: 12px; font-size: 12px; color: white; background: ${statusCores[pedido.status]}">
                                            ${pedido.status.toUpperCase()}
                                        </span>
                                    </div>
                                    <div style="text-align: right;">
                                        <strong style="font-size: 18px; color: #4A90E2;">R$ ${parseFloat(pedido.total).toFixed(2).replace('.', ',')}</strong>
                                    </div>
                                </div>
                            `;
                        });
                    }
                    
                    document.getElementById('pedidosConteudo').innerHTML = html;
                    document.getElementById('modalPedidos').style.display = 'block';
                } else {
                    alert('Erro ao carregar pedidos do cliente');
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao carregar pedidos do cliente');
            }
        }
        
        async function verMensagensCliente(clienteId, clienteNome) {
            try {
                const response = await fetch(`../api/cliente_mensagens.php?id=${clienteId}`);
                const data = await response.json();
                
                if (data.success) {
                    const mensagens = data.mensagens;
                    
                    document.getElementById('modalTitulo').textContent = `💬 Mensagens de ${clienteNome}`;
                    
                    let html = '';
                    if (mensagens.length === 0) {
                        html = '<p style="text-align: center; padding: 20px; color: #666;">Nenhuma mensagem encontrada.</p>';
                    } else {
                        mensagens.forEach(msg => {
                            html += `
                                <div class="pedido-item" style="flex-direction: column; align-items: flex-start;">
                                    <div style="width: 100%; display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                        <div>
                                            <strong>${msg.assunto || 'Sem assunto'}</strong>
                                            ${msg.lido == 0 ? '<span style="background: #4A90E2; color: white; padding: 2px 6px; border-radius: 10px; font-size: 11px; margin-left: 5px;">NOVA</span>' : ''}
                                        </div>
                                        <small style="color: #666;">📅 ${new Date(msg.data_envio).toLocaleDateString('pt-BR')} ${new Date(msg.data_envio).toLocaleTimeString('pt-BR')}</small>
                                    </div>
                                    <div style="width: 100%; padding: 10px; background: #f8f9fa; border-radius: 5px; margin-bottom: 8px;">
                                        <p style="margin: 0; white-space: pre-wrap;">${msg.mensagem}</p>
                                    </div>
                                    <div style="width: 100%; display: flex; gap: 10px; font-size: 13px; color: #666;">
                                        <span>📧 ${msg.email}</span>
                                        ${msg.telefone ? `<span>📱 ${msg.telefone}</span>` : ''}
                                    </div>
                                </div>
                            `;
                        });
                    }
                    
                    document.getElementById('pedidosConteudo').innerHTML = html;
                    document.getElementById('modalPedidos').style.display = 'block';
                } else {
                    alert('Erro ao carregar mensagens do cliente');
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao carregar mensagens do cliente');
            }
        }
        
        function fecharModal() {
            document.getElementById('modalPedidos').style.display = 'none';
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('modalPedidos');
            if (event.target == modal) {
                fecharModal();
            }
        }
    </script>
</body>
</html>
