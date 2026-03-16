<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../models/Categoria.php';

AuthController::requireAdmin();

$categoriaModel = new Categoria();

// Processar ações
$mensagem = '';
$tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    
    if ($acao === 'criar') {
        $dados = [
            'nome' => $_POST['nome'],
            'descricao' => $_POST['descricao'],
            'ativo' => isset($_POST['ativo']) ? 1 : 0
        ];
        
        if ($categoriaModel->create($dados)) {
            $mensagem = 'Categoria criada com sucesso!';
            $tipo = 'sucesso';
        } else {
            $mensagem = 'Erro ao criar categoria.';
            $tipo = 'erro';
        }
    } elseif ($acao === 'editar') {
        $id = $_POST['id'];
        $dados = [
            'nome' => $_POST['nome'],
            'descricao' => $_POST['descricao'],
            'ativo' => isset($_POST['ativo']) ? 1 : 0
        ];
        
        if ($categoriaModel->update($id, $dados)) {
            $mensagem = 'Categoria atualizada com sucesso!';
            $tipo = 'sucesso';
        } else {
            $mensagem = 'Erro ao atualizar categoria.';
            $tipo = 'erro';
        }
    } elseif ($acao === 'excluir') {
        $id = $_POST['id'];
        if ($categoriaModel->delete($id)) {
            $mensagem = 'Categoria excluída com sucesso!';
            $tipo = 'sucesso';
        } else {
            $mensagem = 'Erro ao excluir categoria. Pode haver produtos vinculados.';
            $tipo = 'erro';
        }
    }
}

// Buscar todas as categorias
$categorias = $categoriaModel->findAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Categorias - Admin</title>
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
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-small {
            padding: 5px 10px;
            font-size: 12px;
            margin-right: 5px;
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
        
        .categorias-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .categoria-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            transition: transform 0.3s;
        }
        
        .categoria-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .categoria-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .categoria-header h3 {
            color: #4A90E2;
            font-size: 20px;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-ativo {
            background: #d4edda;
            color: #155724;
        }
        
        .status-inativo {
            background: #f8d7da;
            color: #721c24;
        }
        
        .categoria-descricao {
            color: #666;
            margin-bottom: 15px;
            min-height: 40px;
        }
        
        .categoria-acoes {
            display: flex;
            gap: 10px;
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
            margin: 10% auto;
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
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
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .form-group input[type="checkbox"] {
            width: auto;
            margin-right: 5px;
        }
        
        .checkbox-label {
            display: flex;
            align-items: center;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container-admin">
        <aside class="sidebar">
            <h2>🔥 Admin</h2>
            <nav>
                <a href="index.php">📊 Dashboard</a>
                <a href="produtos.php">🥩 Produtos</a>
                <a href="categorias.php" class="active">📁 Categorias</a>
                <a href="pedidos.php">📦 Pedidos</a>
                <a href="clientes.php">👥 Clientes</a>
                <a href="mensagens.php">💬 Mensagens</a>
                <a href="../logout.php">🚪 Sair</a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="header-admin">
                <h1>Gerenciar Categorias</h1>
                <button class="btn btn-primary" onclick="abrirModal('criar')">+ Nova Categoria</button>
            </div>
            
            <?php if ($mensagem): ?>
            <div class="mensagem <?php echo $tipo; ?>">
                <?php echo $mensagem; ?>
            </div>
            <?php endif; ?>
            
            <div class="categorias-grid">
                <?php foreach ($categorias as $categoria): ?>
                <div class="categoria-card">
                    <div class="categoria-header">
                        <h3><?php echo htmlspecialchars($categoria['nome']); ?></h3>
                        <span class="status-badge <?php echo $categoria['ativo'] ? 'status-ativo' : 'status-inativo'; ?>">
                            <?php echo $categoria['ativo'] ? 'Ativa' : 'Inativa'; ?>
                        </span>
                    </div>
                    <p class="categoria-descricao">
                        <?php echo htmlspecialchars($categoria['descricao'] ?? 'Sem descrição'); ?>
                    </p>
                    <div class="categoria-acoes">
                        <button class="btn btn-secondary btn-small" onclick='editarCategoria(<?php echo json_encode($categoria); ?>)'>Editar</button>
                        <button class="btn btn-danger btn-small" onclick="excluirCategoria(<?php echo $categoria['id']; ?>, '<?php echo addslashes($categoria['nome']); ?>')">Excluir</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
    
    <!-- Modal Criar/Editar -->
    <div id="modalCategoria" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitulo">Nova Categoria</h2>
                <span class="close" onclick="fecharModal()">&times;</span>
            </div>
            <form id="formCategoria" method="POST">
                <input type="hidden" name="acao" id="acao" value="criar">
                <input type="hidden" name="id" id="categoriaId">
                
                <div class="form-group">
                    <label for="nome">Nome da Categoria *</label>
                    <input type="text" name="nome" id="nome" required>
                </div>
                
                <div class="form-group">
                    <label for="descricao">Descrição</label>
                    <textarea name="descricao" id="descricao"></textarea>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="ativo" id="ativo" checked>
                        Categoria Ativa
                    </label>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Salvar Categoria</button>
                    <button type="button" class="btn btn-secondary" onclick="fecharModal()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Excluir -->
    <div id="modalExcluir" class="modal">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h2>Confirmar Exclusão</h2>
                <span class="close" onclick="fecharModalExcluir()">&times;</span>
            </div>
            <p id="mensagemExcluir" style="margin-bottom: 20px;"></p>
            <form method="POST">
                <input type="hidden" name="acao" value="excluir">
                <input type="hidden" name="id" id="excluirId">
                <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                <button type="button" class="btn btn-secondary" onclick="fecharModalExcluir()">Cancelar</button>
            </form>
        </div>
    </div>
    
    <script>
        function abrirModal(acao) {
            document.getElementById('modalCategoria').style.display = 'block';
            document.getElementById('formCategoria').reset();
            document.getElementById('acao').value = 'criar';
            document.getElementById('modalTitulo').textContent = 'Nova Categoria';
            document.getElementById('ativo').checked = true;
        }
        
        function editarCategoria(categoria) {
            document.getElementById('modalCategoria').style.display = 'block';
            document.getElementById('acao').value = 'editar';
            document.getElementById('modalTitulo').textContent = 'Editar Categoria';
            document.getElementById('categoriaId').value = categoria.id;
            document.getElementById('nome').value = categoria.nome;
            document.getElementById('descricao').value = categoria.descricao || '';
            document.getElementById('ativo').checked = categoria.ativo == 1;
        }
        
        function fecharModal() {
            document.getElementById('modalCategoria').style.display = 'none';
        }
        
        function excluirCategoria(id, nome) {
            document.getElementById('modalExcluir').style.display = 'block';
            document.getElementById('excluirId').value = id;
            document.getElementById('mensagemExcluir').textContent = `Tem certeza que deseja excluir a categoria "${nome}"? Todos os produtos desta categoria ficarão sem categoria.`;
        }
        
        function fecharModalExcluir() {
            document.getElementById('modalExcluir').style.display = 'none';
        }
        
        // Fechar modal ao clicar fora
        window.onclick = function(event) {
            const modalCategoria = document.getElementById('modalCategoria');
            const modalExcluir = document.getElementById('modalExcluir');
            if (event.target == modalCategoria) {
                fecharModal();
            }
            if (event.target == modalExcluir) {
                fecharModalExcluir();
            }
        }
    </script>
</body>
</html>
