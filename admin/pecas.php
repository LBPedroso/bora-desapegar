<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/PecaController.php';

AuthController::requireAdmin();

$pecaController = new PecaController();
$mensagem = '';
$tipoMensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'salvar') {
        $id = (int) ($_POST['id'] ?? 0);
        $dados = [
            'nome' => $_POST['nome'] ?? '',
            'categoria' => $_POST['categoria'] ?? '',
            'tamanho' => $_POST['tamanho'] ?? '',
            'preco' => $_POST['preco'] ?? '',
            'observacao' => $_POST['observacao'] ?? '',
            'status' => $_POST['status'] ?? 'disponivel'
        ];

        if ($id > 0) {
            $resultado = $pecaController->atualizar($id, $dados, $_FILES['foto'] ?? null);
        } else {
            $resultado = $pecaController->criar($dados, $_FILES['foto'] ?? null);
        }

        $mensagem = $resultado['message'];
        $tipoMensagem = $resultado['success'] ? 'sucesso' : 'erro';
    }

    if ($acao === 'excluir') {
        $id = (int) ($_POST['id'] ?? 0);
        $resultado = $pecaController->excluir($id);
        $mensagem = $resultado['message'];
        $tipoMensagem = $resultado['success'] ? 'sucesso' : 'erro';
    }
}

$filtros = [
    'status' => $_GET['status'] ?? '',
    'categoria' => $_GET['categoria'] ?? '',
    'busca' => $_GET['busca'] ?? ''
];

$pecas = $pecaController->listar($filtros);
$categorias = $pecaController->categorias();

$pecaEdicao = null;
if (!empty($_GET['editar'])) {
    $pecaEdicao = $pecaController->buscar((int) $_GET['editar']);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pecas - <?php echo SITE_NAME; ?></title>
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

        .content {
            padding: 20px;
        }

        .panel {
            background: var(--painel);
            border: 1px solid var(--borda);
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 14px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.04);
        }

        h1, h2 {
            margin-top: 0;
        }

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

        .grid-form {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .field.full {
            grid-column: 1 / -1;
        }

        label {
            font-size: 13px;
            color: #555;
        }

        input, select, textarea {
            width: 100%;
            padding: 10px 11px;
            border: 1px solid #d8d8d8;
            border-radius: 9px;
            font-size: 14px;
            background: #fff;
        }

        textarea {
            min-height: 80px;
            resize: vertical;
        }

        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 8px;
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
        .btn-neutral { background: #f1f1f1; color: #444; }
        .btn-danger { background: #cc4b5a; color: #fff; }

        .filters {
            display: grid;
            grid-template-columns: 1.2fr 1fr 1fr auto;
            gap: 8px;
            align-items: end;
        }

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

        .thumb {
            width: 48px;
            height: 48px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid var(--borda);
            cursor: zoom-in;
        }

        .admin-lightbox {
            position: fixed;
            inset: 0;
            background: rgba(20, 30, 42, 0.82);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 18px;
        }

        .admin-lightbox.open {
            display: flex;
        }

        .admin-lightbox img {
            max-width: min(92vw, 1100px);
            max-height: 88vh;
            object-fit: contain;
            border-radius: 12px;
            box-shadow: 0 20px 38px rgba(0, 0, 0, 0.36);
            background: #fff;
        }

        .admin-lightbox-close {
            position: absolute;
            top: 14px;
            right: 14px;
            width: 40px;
            height: 40px;
            border: 0;
            border-radius: 50%;
            background: #fff;
            color: #1f2232;
            font-size: 1.6rem;
            line-height: 1;
            cursor: pointer;
        }

        .status {
            font-size: 12px;
            font-weight: 600;
            border-radius: 999px;
            padding: 4px 10px;
            display: inline-block;
        }

        .status.disponivel {
            color: #1d6f47;
            background: #d8f3dc;
        }

        .status.vendido {
            color: #8b1e2e;
            background: #f9d7dd;
        }

        .table-actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .footer {
            text-align: center;
            color: #666;
            font-size: 13px;
            margin-top: 10px;
        }

        @media (max-width: 980px) {
            .layout { grid-template-columns: 1fr; }
            .filters { grid-template-columns: 1fr; }
            .grid-form { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <h2>Bora Desapegar</h2>
            <a class="nav-link" href="index.php">📊 Dashboard</a>
            <a class="nav-link active" href="pecas.php">🧸 Peças</a>
            <a class="nav-link" href="vendas.php">💰 Vendas</a>
            <a class="nav-link" href="../index.php" target="_blank">🌐 Ver site</a>
            <a class="nav-link" href="../logout.php">🚪 Sair</a>
        </aside>

        <main class="content">
            <h1>Cadastro e estoque de pecas</h1>

            <?php if ($mensagem): ?>
                <div class="message <?php echo $tipoMensagem; ?>"><?php echo htmlspecialchars($mensagem); ?></div>
            <?php endif; ?>

            <section class="panel">
                <h2><?php echo $pecaEdicao ? 'Editar peca' : 'Nova peca'; ?></h2>
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="acao" value="salvar">
                    <input type="hidden" name="id" value="<?php echo (int) ($pecaEdicao['id'] ?? 0); ?>">

                    <div class="grid-form">
                        <div class="field">
                            <label for="nome">Nome da peca</label>
                            <input id="nome" type="text" name="nome" required value="<?php echo htmlspecialchars($pecaEdicao['nome'] ?? ''); ?>">
                        </div>

                        <div class="field">
                            <label for="categoria">Categoria</label>
                            <input id="categoria" type="text" name="categoria" required value="<?php echo htmlspecialchars($pecaEdicao['categoria'] ?? ''); ?>" placeholder="Ex: Body, Vestido, Conjunto">
                        </div>

                        <div class="field">
                            <label for="tamanho">Tamanho</label>
                            <input id="tamanho" type="text" name="tamanho" required value="<?php echo htmlspecialchars($pecaEdicao['tamanho'] ?? ''); ?>" placeholder="Ex: P, M, G, 1 ano">
                        </div>

                        <div class="field">
                            <label for="preco">Preco (R$)</label>
                            <input id="preco" type="number" step="0.01" min="0" name="preco" required value="<?php echo htmlspecialchars($pecaEdicao['preco'] ?? ''); ?>">
                        </div>

                        <div class="field">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="disponivel" <?php echo (($pecaEdicao['status'] ?? 'disponivel') === 'disponivel') ? 'selected' : ''; ?>>Disponivel</option>
                                <option value="vendido" <?php echo (($pecaEdicao['status'] ?? '') === 'vendido') ? 'selected' : ''; ?>>Vendido</option>
                            </select>
                        </div>

                        <div class="field">
                            <label for="foto">Foto</label>
                            <input id="foto" type="file" name="foto" accept=".jpg,.jpeg,.png,.gif,.webp">
                        </div>

                        <div class="field full">
                            <label for="observacao">Observacao</label>
                            <textarea id="observacao" name="observacao"><?php echo htmlspecialchars($pecaEdicao['observacao'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div class="actions">
                        <button class="btn btn-primary" type="submit"><?php echo $pecaEdicao ? 'Salvar alteracoes' : 'Cadastrar peca'; ?></button>
                        <?php if ($pecaEdicao): ?>
                            <a class="btn btn-neutral" href="pecas.php">Cancelar edicao</a>
                        <?php endif; ?>
                    </div>
                </form>
            </section>

            <section class="panel">
                <h2>Filtro de estoque</h2>
                <form method="get" class="filters">
                    <div class="field">
                        <label for="busca">Buscar por nome</label>
                        <input id="busca" type="text" name="busca" value="<?php echo htmlspecialchars($filtros['busca']); ?>" placeholder="Nome da peca">
                    </div>

                    <div class="field">
                        <label for="f_status">Status</label>
                        <select id="f_status" name="status">
                            <option value="">Todos</option>
                            <option value="disponivel" <?php echo $filtros['status'] === 'disponivel' ? 'selected' : ''; ?>>Disponivel</option>
                            <option value="vendido" <?php echo $filtros['status'] === 'vendido' ? 'selected' : ''; ?>>Vendido</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="f_categoria">Categoria</label>
                        <select id="f_categoria" name="categoria">
                            <option value="">Todas</option>
                            <?php foreach ($categorias as $cat): ?>
                                <?php $nomeCat = $cat['categoria']; ?>
                                <option value="<?php echo htmlspecialchars($nomeCat); ?>" <?php echo $filtros['categoria'] === $nomeCat ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($nomeCat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="actions">
                        <button class="btn btn-primary" type="submit">Filtrar</button>
                        <a class="btn btn-neutral" href="pecas.php">Limpar</a>
                    </div>
                </form>
            </section>

            <section class="panel">
                <h2>Lista de pecas</h2>
                <div style="overflow-x:auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Nome</th>
                                <th>Categoria</th>
                                <th>Tamanho</th>
                                <th>Preco</th>
                                <th>Status</th>
                                <th>Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pecas)): ?>
                                <tr>
                                    <td colspan="7">Nenhuma peca encontrada.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pecas as $peca): ?>
                                    <tr>
                                        <td>
                                            <img class="thumb js-admin-zoom" src="../public/assets/img/pecas/<?php echo htmlspecialchars($peca['foto'] ?: 'default.jpg'); ?>" alt="Foto da peca" title="Clique para ampliar">
                                        </td>
                                        <td><?php echo htmlspecialchars($peca['nome']); ?></td>
                                        <td><?php echo htmlspecialchars($peca['categoria']); ?></td>
                                        <td><?php echo htmlspecialchars($peca['tamanho']); ?></td>
                                        <td>R$ <?php echo number_format($peca['preco'], 2, ',', '.'); ?></td>
                                        <td>
                                            <span class="status <?php echo $peca['status'] === 'vendido' ? 'vendido' : 'disponivel'; ?>">
                                                <?php echo htmlspecialchars($peca['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="table-actions">
                                                <a class="btn btn-neutral" href="pecas.php?editar=<?php echo (int) $peca['id']; ?>">Editar</a>
                                                <form method="post" onsubmit="return confirm('Deseja excluir esta peca?');">
                                                    <input type="hidden" name="acao" value="excluir">
                                                    <input type="hidden" name="id" value="<?php echo (int) $peca['id']; ?>">
                                                    <button type="submit" class="btn btn-danger">Excluir</button>
                                                </form>
                                            </div>
                                        </td>
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

    <div id="admin-lightbox" class="admin-lightbox" aria-hidden="true">
        <button type="button" class="admin-lightbox-close" aria-label="Fechar visualizacao">×</button>
        <img id="admin-lightbox-image" src="" alt="Foto ampliada">
    </div>

    <script>
        (function () {
            const lightbox = document.getElementById('admin-lightbox');
            const lightboxImg = document.getElementById('admin-lightbox-image');
            const closeBtn = lightbox ? lightbox.querySelector('.admin-lightbox-close') : null;

            if (!lightbox || !lightboxImg || !closeBtn) {
                return;
            }

            function abrir(src, alt) {
                if (!src) {
                    return;
                }
                lightboxImg.src = src;
                lightboxImg.alt = alt || 'Foto ampliada';
                lightbox.classList.add('open');
                lightbox.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }

            function fechar() {
                lightbox.classList.remove('open');
                lightbox.setAttribute('aria-hidden', 'true');
                lightboxImg.src = '';
                document.body.style.overflow = '';
            }

            document.addEventListener('click', function (event) {
                const img = event.target.closest('.js-admin-zoom');
                if (img) {
                    abrir(img.getAttribute('src'), img.getAttribute('alt'));
                    return;
                }

                if (event.target === lightbox || event.target === closeBtn) {
                    fechar();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && lightbox.classList.contains('open')) {
                    fechar();
                }
            });
        })();
    </script>
</body>
</html>
