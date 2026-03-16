<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/VendaController.php';

AuthController::requireAdmin();

$vendaController = new VendaController();
$mensagem = '';
$tipoMensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'registrar_venda') {
    $resultado = $vendaController->registrar([
        'cliente' => $_POST['cliente'] ?? '',
        'peca_id' => $_POST['peca_id'] ?? '',
        'valor' => $_POST['valor'] ?? ''
    ]);

    $mensagem = $resultado['message'];
    $tipoMensagem = $resultado['success'] ? 'sucesso' : 'erro';
}

$pecasDisponiveis = $vendaController->pecasDisponiveis();
$vendas = $vendaController->listarRecentes(100);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendas - <?php echo SITE_NAME; ?></title>
    <style>
        :root {
            --bg: #f5f2ec;
            --painel: #ffffff;
            --primaria: #4A90E2;
            --escura: #2b2d42;
            --sucesso: #2a9d8f;
            --erro: #c1121f;
            --borda: #ece7df;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg);
            color: var(--escura);
        }

        .layout {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
        }

        .sidebar {
            background: linear-gradient(180deg, var(--escura), #1f2232);
            color: #fff;
            padding: 24px 16px;
        }

        .sidebar h2 { margin: 0 0 24px; }

        .nav-link {
            display: block;
            color: #fff;
            text-decoration: none;
            padding: 11px 12px;
            border-radius: 10px;
            margin-bottom: 8px;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(255,255,255,0.14);
        }

        .content { padding: 20px; }

        .panel {
            background: var(--painel);
            border: 1px solid var(--borda);
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 14px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.04);
        }

        h1, h2 { margin-top: 0; }

        .message {
            border-radius: 10px;
            padding: 10px 12px;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .message.sucesso {
            background: #d8f3dc;
            color: #1d6f47;
        }

        .message.erro {
            background: #ffe2e6;
            color: var(--erro);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 180px auto;
            gap: 8px;
            align-items: end;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        label {
            font-size: 13px;
            color: #555;
        }

        input, select {
            width: 100%;
            padding: 10px 11px;
            border: 1px solid #d8d8d8;
            border-radius: 9px;
            font-size: 14px;
            background: #fff;
        }

        .btn {
            border: 0;
            border-radius: 9px;
            padding: 10px 13px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary { background: var(--primaria); color: #fff; }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th, td {
            text-align: left;
            padding: 9px 8px;
            border-bottom: 1px solid var(--borda);
            vertical-align: middle;
        }

        th {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .footer {
            text-align: center;
            color: #666;
            font-size: 13px;
            margin-top: 10px;
        }

        @media (max-width: 980px) {
            .layout { grid-template-columns: 1fr; }
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <h2>Bora Desapegar</h2>
            <a class="nav-link" href="index.php">📊 Dashboard</a>
            <a class="nav-link" href="pecas.php">🧸 Peças</a>
            <a class="nav-link active" href="vendas.php">💰 Vendas</a>
            <a class="nav-link" href="../index.php" target="_blank">🌐 Ver site</a>
            <a class="nav-link" href="../logout.php">🚪 Sair</a>
        </aside>

        <main class="content">
            <h1>Registro de vendas</h1>

            <?php if ($mensagem): ?>
                <div class="message <?php echo $tipoMensagem; ?>"><?php echo htmlspecialchars($mensagem); ?></div>
            <?php endif; ?>

            <section class="panel">
                <h2>Nova venda</h2>
                <form method="post">
                    <input type="hidden" name="acao" value="registrar_venda">
                    <div class="form-grid">
                        <div class="field">
                            <label for="cliente">Compradora</label>
                            <input id="cliente" name="cliente" required placeholder="Nome da cliente">
                        </div>

                        <div class="field">
                            <label for="peca_id">Peca vendida</label>
                            <select id="peca_id" name="peca_id" required>
                                <option value="">Selecione uma peca</option>
                                <?php foreach ($pecasDisponiveis as $peca): ?>
                                    <option value="<?php echo (int) $peca['id']; ?>">
                                        <?php echo htmlspecialchars($peca['nome']); ?>
                                        (<?php echo htmlspecialchars($peca['tamanho']); ?>)
                                        - R$ <?php echo number_format($peca['preco'], 2, ',', '.'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="field">
                            <label for="valor">Valor (opcional)</label>
                            <input id="valor" name="valor" type="number" step="0.01" min="0" placeholder="Usa preco da peca se vazio">
                        </div>

                        <button class="btn btn-primary" type="submit">Registrar</button>
                    </div>
                </form>
            </section>

            <section class="panel">
                <h2>Historico de vendas</h2>
                <div style="overflow-x:auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Cliente</th>
                                <th>Peca</th>
                                <th>Categoria</th>
                                <th>Tamanho</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($vendas)): ?>
                                <tr>
                                    <td colspan="6">Nenhuma venda registrada.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($vendas as $venda): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y H:i', strtotime($venda['data_venda'])); ?></td>
                                        <td><?php echo htmlspecialchars($venda['cliente']); ?></td>
                                        <td><?php echo htmlspecialchars($venda['peca_nome']); ?></td>
                                        <td><?php echo htmlspecialchars($venda['categoria']); ?></td>
                                        <td><?php echo htmlspecialchars($venda['tamanho']); ?></td>
                                        <td>R$ <?php echo number_format($venda['valor'], 2, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="footer">Sistema desenvolvido por LBPStartWeb</div>
        </main>
    </div>
</body>
</html>
