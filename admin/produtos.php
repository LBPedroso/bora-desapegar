<?php
header('Location: pecas.php');
exit;

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../models/Produto.php';
require_once __DIR__ . '/../models/Categoria.php';

AuthController::requireAdmin();

$produtoModel = new Produto();
$categoriaModel = new Categoria();

// Processar ações
$mensagem = '';
$tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    
    if ($acao === 'criar') {
        // Processar upload de imagem
        $nomeImagem = 'default.jpg';
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $nomeArquivo = $_FILES['imagem']['name'];
            $extensao = strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION));
            
            if (in_array($extensao, $extensoesPermitidas)) {
                $nomeImagem = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $nomeArquivo);
                $caminhoDestino = __DIR__ . '/../public/assets/img/produtos/' . $nomeImagem;
                
                if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoDestino)) {
                    $nomeImagem = 'default.jpg';
                }
            }
        }
        
        $dados = [
            'nome' => $_POST['nome'],
            'descricao' => $_POST['descricao'],
            'preco' => $_POST['preco'],
            'unidade' => $_POST['unidade'],
            'categoria_id' => $_POST['categoria_id'],
            'estoque' => $_POST['estoque'],
            'ativo' => isset($_POST['ativo']) ? 1 : 0,
            'destaque' => isset($_POST['destaque']) ? 1 : 0,
            'imagem' => $nomeImagem
        ];
        
        if ($produtoModel->create($dados)) {
            $mensagem = 'Produto criado com sucesso!';
            $tipo = 'sucesso';
        } else {
            $mensagem = 'Erro ao criar produto.';
            $tipo = 'erro';
        }
    } elseif ($acao === 'editar') {
        $id = $_POST['id'];
        
        // Buscar produto atual para manter imagem se não houver upload
        $produtoAtual = $produtoModel->findById($id);
        $nomeImagem = $produtoAtual['imagem'] ?? 'default.jpg';
        
        // Processar upload de nova imagem
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $nomeArquivo = $_FILES['imagem']['name'];
            $extensao = strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION));
            
            if (in_array($extensao, $extensoesPermitidas)) {
                $nomeImagemNova = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $nomeArquivo);
                $caminhoDestino = __DIR__ . '/../public/assets/img/produtos/' . $nomeImagemNova;
                
                if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoDestino)) {
                    // Deletar imagem antiga se não for default
                    if ($nomeImagem !== 'default.jpg' && $nomeImagem !== 'placeholder.jpg') {
                        $caminhoAntigo = __DIR__ . '/../public/assets/img/produtos/' . $nomeImagem;
                        if (file_exists($caminhoAntigo)) {
                            unlink($caminhoAntigo);
                        }
                    }
                    $nomeImagem = $nomeImagemNova;
                }
            }
        }
        
        $dados = [
            'nome' => $_POST['nome'],
            'descricao' => $_POST['descricao'],
            'preco' => $_POST['preco'],
            'unidade' => $_POST['unidade'],
            'categoria_id' => $_POST['categoria_id'],
            'estoque' => $_POST['estoque'],
            'ativo' => isset($_POST['ativo']) ? 1 : 0,
            'destaque' => isset($_POST['destaque']) ? 1 : 0,
            'imagem' => $nomeImagem
        ];
        
        if ($produtoModel->update($id, $dados)) {
            $mensagem = 'Produto atualizado com sucesso!';
            $tipo = 'sucesso';
        } else {
            $mensagem = 'Erro ao atualizar produto.';
            $tipo = 'erro';
        }
    } elseif ($acao === 'excluir') {
        $id = $_POST['id'];
        if ($produtoModel->delete($id)) {
            $mensagem = 'Produto excluído com sucesso!';
            $tipo = 'sucesso';
        } else {
            $mensagem = 'Erro ao excluir produto.';
            $tipo = 'erro';
        }
    }
}

// Buscar todos os produtos com categorias (JOIN)
$db = Database::getInstance()->getConnection();
$stmt = $db->query("
    SELECT p.*, c.nome as categoria_nome 
    FROM produtos p 
    LEFT JOIN categorias c ON p.categoria_id = c.id 
    ORDER BY p.id DESC
");
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categorias = $categoriaModel->findAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Produtos - Admin</title>
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
        
        .tabela-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: #f8f9fa;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        
        th {
            font-weight: 600;
            color: #495057;
            user-select: none;
        }
        
        th[onclick] {
            transition: background 0.2s;
        }
        
        th[onclick]:hover {
            background: #e9ecef;
        }
        
        th span {
            opacity: 0.3;
            font-size: 12px;
            margin-left: 5px;
        }
        
        tr:hover {
            background: #f8f9fa;
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
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
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
        .form-group select,
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
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 15px;
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
            <h2>🧸 Bora Desapegar</h2>
            <nav>
                <a href="index.php">📊 Dashboard</a>
                <a href="produtos.php" class="active">👕 Produtos</a>
                <a href="categorias.php">🏷️ Categorias</a>
                <a href="pedidos.php">📦 Pedidos</a>
                <a href="clientes.php">👥 Clientes</a>
                <a href="mensagens.php">💬 Mensagens</a>
                <a href="../logout.php">🚪 Sair</a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="header-admin">
                <h1>Gerenciar Produtos</h1>
                <button class="btn btn-primary" onclick="abrirModal('criar')">+ Novo Produto</button>
            </div>
            
            <?php if ($mensagem): ?>
            <div class="mensagem <?php echo $tipo; ?>">
                <?php echo $mensagem; ?>
            </div>
            <?php endif; ?>
            
            <!-- Filtros -->
            <div class="filtros-container" style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 500;">🔍 Buscar</label>
                        <input type="text" id="filtroNome" placeholder="Nome do produto..." style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;" onkeyup="filtrarProdutos()">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 500;">📁 Categoria</label>
                        <select id="filtroCategoria" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;" onchange="filtrarProdutos()">
                            <option value="">Todas</option>
                            <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['nome']); ?>"><?php echo htmlspecialchars($cat['nome']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 500;">📊 Status</label>
                        <select id="filtroStatus" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;" onchange="filtrarProdutos()">
                            <option value="">Todos</option>
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 500;">⚖️ Unidade</label>
                        <select id="filtroUnidade" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;" onchange="filtrarProdutos()">
                            <option value="">Todas</option>
                            <option value="un">un (Unidade)</option>
                            <option value="kg">kg (Quilograma)</option>
                            <option value="pct">pct (Pacote)</option>
                            <option value="bandeja">bandeja</option>
                            <option value="porção">porção</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 500;">⭐ Destaque</label>
                        <select id="filtroDestaque" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;" onchange="filtrarProdutos()">
                            <option value="">Todos</option>
                            <option value="sim">Sim</option>
                            <option value="nao">Não</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="tabela-container">
                <table>
                    <thead>
                        <tr>
                            <th onclick="ordenarTabela('id')" style="cursor: pointer;" title="Clique para ordenar">
                                ID <span id="sort-id">⇅</span>
                            </th>
                            <th>Imagem</th>
                            <th onclick="ordenarTabela('nome')" style="cursor: pointer;" title="Clique para ordenar">
                                Nome <span id="sort-nome">⇅</span>
                            </th>
                            <th onclick="ordenarTabela('categoria')" style="cursor: pointer;" title="Clique para ordenar">
                                Categoria <span id="sort-categoria">⇅</span>
                            </th>
                            <th onclick="ordenarTabela('preco')" style="cursor: pointer;" title="Clique para ordenar">
                                Preço <span id="sort-preco">⇅</span>
                            </th>
                            <th onclick="ordenarTabela('unidade')" style="cursor: pointer;" title="Clique para ordenar">
                                Unidade <span id="sort-unidade">⇅</span>
                            </th>
                            <th onclick="ordenarTabela('estoque')" style="cursor: pointer;" title="Clique para ordenar">
                                Estoque <span id="sort-estoque">⇅</span>
                            </th>
                            <th onclick="ordenarTabela('status')" style="cursor: pointer;" title="Clique para ordenar">
                                Status <span id="sort-status">⇅</span>
                            </th>
                            <th>Destaque</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaProdutos">
                        <?php foreach ($produtos as $produto): ?>
                        <tr class="linha-produto" 
                            data-id="<?php echo $produto['id']; ?>"
                            data-nome="<?php echo strtolower(htmlspecialchars($produto['nome'])); ?>"
                            data-categoria="<?php echo strtolower(htmlspecialchars($produto['categoria_nome'] ?? '')); ?>"
                            data-preco="<?php echo $produto['preco']; ?>"
                            data-unidade="<?php echo strtolower(htmlspecialchars($produto['unidade'] ?? 'un')); ?>"
                            data-estoque="<?php echo $produto['estoque']; ?>"
                            data-status="<?php echo $produto['ativo'] ? 'ativo' : 'inativo'; ?>"
                            data-destaque="<?php echo $produto['destaque'] ? 'sim' : 'nao'; ?>">
                            <td><?php echo $produto['id']; ?></td>
                            <td>
                                <img src="<?php echo SITE_URL; ?>/public/assets/img/produtos/<?php echo $produto['imagem'] ?? 'default.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($produto['nome']); ?>"
                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px; border: 1px solid #ddd;">
                            </td>
                            <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                            <td><?php echo htmlspecialchars($produto['categoria_nome'] ?? 'N/A'); ?></td>
                            <td>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($produto['unidade'] ?? 'un'); ?></td>
                            <td><?php echo $produto['estoque']; ?></td>
                            <td>
                                <span class="status-badge <?php echo $produto['ativo'] ? 'status-ativo' : 'status-inativo'; ?>">
                                    <?php echo $produto['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                </span>
                            </td>
                            <td><?php echo $produto['destaque'] ? '⭐' : ''; ?></td>
                            <td>
                                <button class="btn btn-secondary btn-small" onclick='editarProduto(<?php echo json_encode($produto); ?>)'>Editar</button>
                                <button class="btn btn-danger btn-small" onclick="excluirProduto(<?php echo $produto['id']; ?>, '<?php echo addslashes($produto['nome']); ?>')">Excluir</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <!-- Modal Criar/Editar -->
    <div id="modalProduto" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitulo">Novo Produto</h2>
                <span class="close" onclick="fecharModal()">&times;</span>
            </div>
            <form id="formProduto" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="acao" id="acao" value="criar">
                <input type="hidden" name="id" id="produtoId">
                
                <div class="form-group">
                    <label for="nome">Nome do Produto *</label>
                    <input type="text" name="nome" id="nome" required>
                </div>
                
                <div class="form-group">
                    <label for="descricao">Descrição</label>
                    <textarea name="descricao" id="descricao"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="imagem">Imagem do Produto</label>
                    <input type="file" name="imagem" id="imagem" accept="image/*">
                    <small style="display: block; margin-top: 5px; color: #666;">
                        Formatos aceitos: JPG, JPEG, PNG, GIF, WEBP
                    </small>
                    <div id="previewImagem" style="margin-top: 10px;"></div>
                </div>
                
                <div class="form-group">
                    <label for="categoria_id">Categoria *</label>
                    <select name="categoria_id" id="categoria_id" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nome']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="preco">Preço (R$) *</label>
                    <input type="number" name="preco" id="preco" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="unidade">Unidade de Medida *</label>
                    <select name="unidade" id="unidade" required>
                        <option value="un">un (Unidade)</option>
                        <option value="kg">kg (Quilograma)</option>
                        <option value="pct">pct (Pacote)</option>
                        <option value="bandeja">bandeja</option>
                        <option value="porção">porção</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="estoque">Estoque *</label>
                    <input type="number" name="estoque" id="estoque" min="0" required>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="ativo" id="ativo" checked>
                            Produto Ativo
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="destaque" id="destaque">
                            Produto em Destaque
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Salvar Produto</button>
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
        // Preview de imagem
        document.getElementById('imagem').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('previewImagem');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" style="max-width: 200px; max-height: 200px; border-radius: 5px; border: 2px solid #ddd;">`;
                }
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '';
            }
        });
        
        function abrirModal(acao) {
            document.getElementById('modalProduto').style.display = 'block';
            document.getElementById('formProduto').reset();
            document.getElementById('acao').value = 'criar';
            document.getElementById('modalTitulo').textContent = 'Novo Produto';
            document.getElementById('ativo').checked = true;
            document.getElementById('previewImagem').innerHTML = '';
        }
        
        function editarProduto(produto) {
            document.getElementById('modalProduto').style.display = 'block';
            document.getElementById('acao').value = 'editar';
            document.getElementById('modalTitulo').textContent = 'Editar Produto';
            document.getElementById('produtoId').value = produto.id;
            document.getElementById('nome').value = produto.nome;
            document.getElementById('descricao').value = produto.descricao || '';
            document.getElementById('categoria_id').value = produto.categoria_id;
            document.getElementById('preco').value = produto.preco;
            document.getElementById('unidade').value = produto.unidade || 'un';
            document.getElementById('estoque').value = produto.estoque;
            document.getElementById('ativo').checked = produto.ativo == 1;
            document.getElementById('destaque').checked = produto.destaque == 1;
            
            // Mostrar imagem atual
            const preview = document.getElementById('previewImagem');
            if (produto.imagem && produto.imagem !== 'default.jpg') {
                preview.innerHTML = `
                    <div style="margin-top: 10px;">
                        <p style="margin-bottom: 5px; font-weight: 500;">Imagem atual:</p>
                        <img src="<?php echo SITE_URL; ?>/public/assets/img/produtos/${produto.imagem}" 
                             style="max-width: 200px; max-height: 200px; border-radius: 5px; border: 2px solid #ddd;">
                        <p style="margin-top: 5px; font-size: 0.9em; color: #666;">Selecione uma nova imagem para substituir</p>
                    </div>
                `;
            } else {
                preview.innerHTML = '<p style="color: #666; margin-top: 10px;">Nenhuma imagem cadastrada</p>';
            }
        }
        
        function fecharModal() {
            document.getElementById('modalProduto').style.display = 'none';
        }
        
        function excluirProduto(id, nome) {
            document.getElementById('modalExcluir').style.display = 'block';
            document.getElementById('excluirId').value = id;
            document.getElementById('mensagemExcluir').textContent = `Tem certeza que deseja excluir o produto "${nome}"?`;
        }
        
        function fecharModalExcluir() {
            document.getElementById('modalExcluir').style.display = 'none';
        }
        
        // Filtrar produtos
        function filtrarProdutos() {
            const filtroNome = document.getElementById('filtroNome').value.toLowerCase();
            const filtroCategoria = document.getElementById('filtroCategoria').value.toLowerCase();
            const filtroStatus = document.getElementById('filtroStatus').value;
            const filtroUnidade = document.getElementById('filtroUnidade').value.toLowerCase();
            const filtroDestaque = document.getElementById('filtroDestaque').value;
            
            const linhas = document.querySelectorAll('.linha-produto');
            
            linhas.forEach(linha => {
                const nome = linha.dataset.nome;
                const categoria = linha.dataset.categoria;
                const status = linha.dataset.status;
                const unidade = linha.dataset.unidade;
                const destaque = linha.dataset.destaque;
                
                let mostrar = true;
                
                // Filtro por nome
                if (filtroNome && !nome.includes(filtroNome)) {
                    mostrar = false;
                }
                
                // Filtro por categoria
                if (filtroCategoria && categoria !== filtroCategoria) {
                    mostrar = false;
                }
                
                // Filtro por status
                if (filtroStatus && status !== filtroStatus) {
                    mostrar = false;
                }
                
                // Filtro por unidade
                if (filtroUnidade && unidade !== filtroUnidade) {
                    mostrar = false;
                }
                
                // Filtro por destaque
                if (filtroDestaque && destaque !== filtroDestaque) {
                    mostrar = false;
                }
                
                linha.style.display = mostrar ? '' : 'none';
            });
        }
        
        // Ordenação da tabela
        let ordemAtual = {};
        
        function ordenarTabela(coluna) {
            const tbody = document.getElementById('tabelaProdutos');
            const linhas = Array.from(tbody.querySelectorAll('.linha-produto'));
            
            // Alternar ordem (crescente/decrescente)
            ordemAtual[coluna] = ordemAtual[coluna] === 'asc' ? 'desc' : 'asc';
            const ordem = ordemAtual[coluna];
            
            // Resetar todos os ícones
            document.querySelectorAll('th span').forEach(span => {
                if (span.id.startsWith('sort-')) {
                    span.textContent = '⇅';
                    span.style.opacity = '0.3';
                }
            });
            
            // Atualizar ícone da coluna atual
            const icone = document.getElementById('sort-' + coluna);
            if (icone) {
                icone.textContent = ordem === 'asc' ? '↑' : '↓';
                icone.style.opacity = '1';
            }
            
            // Ordenar linhas
            linhas.sort((a, b) => {
                let valorA = a.dataset[coluna];
                let valorB = b.dataset[coluna];
                
                // Converter para número se for preço, estoque ou id
                if (coluna === 'preco' || coluna === 'estoque' || coluna === 'id') {
                    valorA = parseFloat(valorA) || 0;
                    valorB = parseFloat(valorB) || 0;
                }
                
                if (valorA < valorB) return ordem === 'asc' ? -1 : 1;
                if (valorA > valorB) return ordem === 'asc' ? 1 : -1;
                return 0;
            });
            
            // Reorganizar DOM
            linhas.forEach(linha => tbody.appendChild(linha));
        }
        
        // Fechar modal ao clicar fora
        window.onclick = function(event) {
            const modalProduto = document.getElementById('modalProduto');
            const modalExcluir = document.getElementById('modalExcluir');
            if (event.target == modalProduto) {
                fecharModal();
            }
            if (event.target == modalExcluir) {
                fecharModalExcluir();
            }
        }
    </script>
</body>
</html>
