<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/VendaController.php';

AuthController::requireAdmin();

$stats = [
    'total_pecas' => 0,
    'pecas_disponiveis' => 0,
    'pecas_vendidas' => 0,
    'valor_total_vendido' => 0
];
$vendasRecentes = [];
$vendasPorCategoria = [];

try {
    $vendaController = new VendaController();
    $stats = $vendaController->estatisticas();
    $vendasRecentes = $vendaController->listarRecentes(10);
    $vendasPorCategoria = $vendaController->vendasPorCategoria();
} catch (Throwable $e) {
    error_log('Falha ao carregar dashboard admin: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
    <style>
        :root {
            --bg: #f5f2ec;
            --painel: #ffffff;
            --primaria: #4A90E2;
            --secundaria: #A8D8FF;
            --escura: #2b2d42;
            --sucesso: #2a9d8f;
            --borda: #ece7df;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg);
            color: var(--escura);
        }

        .admin-layout {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
        }

        .sidebar {
            background: linear-gradient(180deg, var(--escura), #1f2232);
            color: #fff;
            padding: 24px 16px;
        }

        .brand {
            margin: 0 0 24px;
            font-size: 22px;
            color: #fff;
        }

        .subtitle {
            margin: 0 0 28px;
            color: #d9dbef;
            font-size: 14px;
        }

        .nav-link {
            display: block;
            color: #fff;
            text-decoration: none;
            padding: 11px 12px;
            border-radius: 10px;
            margin-bottom: 8px;
            transition: background 0.2s;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(255,255,255,0.14);
        }

        .content {
            padding: 24px;
        }

        .content h1 {
            margin-top: 0;
            margin-bottom: 20px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .card {
            background: var(--painel);
            border: 1px solid var(--borda);
            border-radius: 14px;
            padding: 16px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.04);
        }

        .card h3 {
            margin: 0;
            font-size: 13px;
            color: #555;
        }

        .card .value {
            margin-top: 8px;
            font-size: 32px;
            font-weight: 700;
            line-height: 1;
        }

        .value.primary { color: var(--primaria); }
        .value.secondary { color: var(--secundaria); }
        .value.success { color: var(--sucesso); }
        .value.dark { color: var(--escura); }

        .grid {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 14px;
        }

        .panel {
            background: var(--painel);
            border: 1px solid var(--borda);
            border-radius: 14px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        .panel-header {
            padding: 14px 16px;
            border-bottom: 1px solid var(--borda);
            background: #fff;
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th, td {
            padding: 10px 12px;
            border-bottom: 1px solid var(--borda);
            text-align: left;
        }

        th {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #676767;
        }

        .status {
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }

        .status.disponivel {
            color: #1d6f47;
            background: #d8f3dc;
        }

        .status.vendido {
            color: #8b1e2e;
            background: #f9d7dd;
        }

        .categoria-list {
            padding: 12px 16px;
        }

        .categoria-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px dashed var(--borda);
            font-size: 14px;
        }

        .categoria-item:last-child { border-bottom: 0; }

        .footer {
            margin-top: 16px;
            color: #666;
            font-size: 13px;
            text-align: center;
        }

        @media (max-width: 900px) {
            .admin-layout {
                grid-template-columns: 1fr;
            }

            .sidebar {
                padding: 14px;
            }

            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="sidebar">
            <h2 class="brand">Bora Desapegar</h2>
            <p class="subtitle">Painel administrativo</p>

            <a class="nav-link active" href="index.php">📊 Dashboard</a>
            <a class="nav-link" href="pecas.php">🧸 Peças</a>
            <a class="nav-link" href="vendas.php">💰 Vendas</a>
            <a class="nav-link" href="pedidos.php">📦 Pedidos</a>
            <a class="nav-link" href="clientes.php">👥 Clientes</a>
            <a class="nav-link" href="mensagens.php">💬 Mensagens</a>
            <a class="nav-link" href="../index.php" target="_blank">🌐 Ver site</a>
            <a class="nav-link" href="../logout.php">🚪 Sair</a>
        </aside>

        <main class="content">
            <h1>Resumo do brecho</h1>

            <section class="cards">
                <article class="card">
                    <h3>Total de pecas cadastradas</h3>
                    <div class="value dark"><?php echo (int) $stats['total_pecas']; ?></div>
                </article>

                <article class="card">
                    <h3>Pecas disponiveis</h3>
                    <div class="value success"><?php echo (int) $stats['pecas_disponiveis']; ?></div>
                </article>

                <article class="card">
                    <h3>Pecas vendidas</h3>
                    <div class="value primary"><?php echo (int) $stats['pecas_vendidas']; ?></div>
                </article>

                <article class="card">
                    <h3>Valor total vendido</h3>
                    <div class="value secondary">R$ <?php echo number_format($stats['valor_total_vendido'], 2, ',', '.'); ?></div>
                </article>
            </section>

            <section class="grid">
                <article class="panel">
                    <div class="panel-header">Ultimas vendas</div>
                    <div style="overflow-x:auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Cliente</th>
                                    <th>Peca</th>
                                    <th>Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($vendasRecentes)): ?>
                                    <tr>
                                        <td colspan="4">Nenhuma venda registrada ainda.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($vendasRecentes as $venda): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y H:i', strtotime($venda['data_venda'])); ?></td>
                                            <td><?php echo htmlspecialchars($venda['cliente']); ?></td>
                                            <td><?php echo htmlspecialchars($venda['peca_nome']); ?></td>
                                            <td>R$ <?php echo number_format($venda['valor'], 2, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </article>

                <article class="panel">
                    <div class="panel-header">Vendas por categoria</div>
                    <div class="categoria-list">
                        <?php if (empty($vendasPorCategoria)): ?>
                            <p style="margin: 0;">Sem dados de categoria no momento.</p>
                        <?php else: ?>
                            <?php foreach ($vendasPorCategoria as $item): ?>
                                <div class="categoria-item">
                                    <div>
                                        <strong><?php echo htmlspecialchars($item['categoria']); ?></strong><br>
                                        <small><?php echo (int) $item['total']; ?> venda(s)</small>
                                    </div>
                                    <strong>R$ <?php echo number_format($item['valor_total'], 2, ',', '.'); ?></strong>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </article>
            </section>

            <div class="footer">Sistema desenvolvido por LBPStartWeb</div>
        </main>
    </div>
</body>
</html>
