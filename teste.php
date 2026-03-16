<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Funcionalidades - Bora Desapegar</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: Arial, sans-serif; 
            padding: 20px;
            background: #f5f5f5;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { 
            color: #4A90E2; 
            margin-bottom: 20px;
            text-align: center;
        }
        .test-section {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .test-section h2 {
            color: #2B2D42;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #A8D8FF;
        }
        .status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            margin-left: 10px;
        }
        .status.ok {
            background: #d4edda;
            color: #155724;
        }
        .status.error {
            background: #f8d7da;
            color: #721c24;
        }
        .status.warning {
            background: #fff3cd;
            color: #856404;
        }
        .test-item {
            padding: 10px;
            margin: 10px 0;
            border-left: 4px solid #ddd;
            padding-left: 15px;
        }
        .test-item.ok { border-color: #28a745; }
        .test-item.error { border-color: #dc3545; }
        .test-item.warning { border-color: #ffc107; }
        a.test-link {
            display: inline-block;
            padding: 8px 15px;
            background: #4A90E2;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
        }
        a.test-link:hover {
            background: #d62839;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Teste de Funcionalidades - Bora Desapegar</h1>

        <?php
        require_once 'config/config.php';
        
        $testes = [];
        
        // Teste 1: Conexão com Banco
        try {
            $db = Database::getInstance()->getConnection();
            $testes[] = ['nome' => 'Conexão com Banco de Dados', 'status' => 'ok', 'msg' => 'Conectado com sucesso'];
        } catch (Exception $e) {
            $testes[] = ['nome' => 'Conexão com Banco de Dados', 'status' => 'error', 'msg' => $e->getMessage()];
        }
        
        // Teste 2: Models
        try {
            $categoriaModel = new Categoria();
            $categorias = $categoriaModel->findAtivas();
            $testes[] = ['nome' => 'Model Categoria', 'status' => 'ok', 'msg' => count($categorias) . ' categorias ativas'];
        } catch (Exception $e) {
            $testes[] = ['nome' => 'Model Categoria', 'status' => 'error', 'msg' => $e->getMessage()];
        }
        
        try {
            $produtoModel = new Produto();
            $produtos = $produtoModel->findAll('ativo = ?', [1]);
            $testes[] = ['nome' => 'Model Produto', 'status' => 'ok', 'msg' => count($produtos) . ' produtos ativos'];
        } catch (Exception $e) {
            $testes[] = ['nome' => 'Model Produto', 'status' => 'error', 'msg' => $e->getMessage()];
        }
        
        // Teste 3: Controllers
        try {
            require_once 'controllers/ProdutoController.php';
            $produtoController = new ProdutoController();
            $testes[] = ['nome' => 'Controller Produto', 'status' => 'ok', 'msg' => 'Instanciado com sucesso'];
        } catch (Exception $e) {
            $testes[] = ['nome' => 'Controller Produto', 'status' => 'error', 'msg' => $e->getMessage()];
        }
        
        try {
            require_once 'controllers/AuthController.php';
            $authController = new AuthController();
            $testes[] = ['nome' => 'Controller Auth', 'status' => 'ok', 'msg' => 'Instanciado com sucesso'];
        } catch (Exception $e) {
            $testes[] = ['nome' => 'Controller Auth', 'status' => 'error', 'msg' => $e->getMessage()];
        }
        
        // Teste 4: Arquivos essenciais
        $arquivos = [
            'index.php' => 'Página Inicial',
            'cardapio.php' => 'Cardápio',
            'login.php' => 'Login/Registro',
            'logout.php' => 'Logout',
            'views/partials/header.php' => 'Header',
            'views/partials/footer.php' => 'Footer',
            'public/assets/css/style.css' => 'CSS Principal',
            'public/assets/js/carrinho.js' => 'JavaScript Carrinho'
        ];
        
        foreach ($arquivos as $arquivo => $nome) {
            if (file_exists($arquivo)) {
                $testes[] = ['nome' => "Arquivo: $nome", 'status' => 'ok', 'msg' => 'Existe'];
            } else {
                $testes[] = ['nome' => "Arquivo: $nome", 'status' => 'error', 'msg' => 'Não encontrado'];
            }
        }
        
        // Teste 5: Banco de Dados - Recursos Avançados
        try {
            // Testar TRIGGER
            $stmt = $db->query("SHOW TRIGGERS WHERE `Trigger` = 'trg_auditoria_preco_update'");
            $trigger = $stmt->fetch();
            if ($trigger) {
                $testes[] = ['nome' => 'TRIGGER (Auditoria Preço)', 'status' => 'ok', 'msg' => 'Configurado'];
            } else {
                $testes[] = ['nome' => 'TRIGGER (Auditoria Preço)', 'status' => 'warning', 'msg' => 'Não encontrado'];
            }
            
            // Testar PROCEDURE
            $stmt = $db->query("SHOW PROCEDURE STATUS WHERE Db = 'bora_desapegar' AND Name = 'sp_inserir_produtos_massivo'");
            $procedure = $stmt->fetch();
            if ($procedure) {
                $testes[] = ['nome' => 'PROCEDURE (Inserção Massiva)', 'status' => 'ok', 'msg' => 'Configurado'];
            } else {
                $testes[] = ['nome' => 'PROCEDURE (Inserção Massiva)', 'status' => 'warning', 'msg' => 'Não encontrado'];
            }
            
            // Testar FUNCTION
            $stmt = $db->query("SHOW FUNCTION STATUS WHERE Db = 'bora_desapegar' AND Name = 'fn_verificar_estoque'");
            $function = $stmt->fetch();
            if ($function) {
                $testes[] = ['nome' => 'FUNCTION (Verificar Estoque)', 'status' => 'ok', 'msg' => 'Configurado'];
            } else {
                $testes[] = ['nome' => 'FUNCTION (Verificar Estoque)', 'status' => 'warning', 'msg' => 'Não encontrado'];
            }
            
        } catch (Exception $e) {
            $testes[] = ['nome' => 'Recursos Avançados BD', 'status' => 'error', 'msg' => $e->getMessage()];
        }
        
        // Contar status
        $ok = count(array_filter($testes, fn($t) => $t['status'] === 'ok'));
        $error = count(array_filter($testes, fn($t) => $t['status'] === 'error'));
        $warning = count(array_filter($testes, fn($t) => $t['status'] === 'warning'));
        $total = count($testes);
        ?>

        <!-- Resumo -->
        <div class="test-section">
            <h2>📊 Resumo Geral</h2>
            <div class="grid">
                <div style="text-align: center; padding: 20px; background: #d4edda; border-radius: 10px;">
                    <div style="font-size: 2rem; color: #155724; font-weight: bold;"><?php echo $ok; ?></div>
                    <div style="color: #155724;">Testes OK</div>
                </div>
                <div style="text-align: center; padding: 20px; background: #fff3cd; border-radius: 10px;">
                    <div style="font-size: 2rem; color: #856404; font-weight: bold;"><?php echo $warning; ?></div>
                    <div style="color: #856404;">Avisos</div>
                </div>
                <div style="text-align: center; padding: 20px; background: #f8d7da; border-radius: 10px;">
                    <div style="font-size: 2rem; color: #721c24; font-weight: bold;"><?php echo $error; ?></div>
                    <div style="color: #721c24;">Erros</div>
                </div>
                <div style="text-align: center; padding: 20px; background: #e7f3ff; border-radius: 10px;">
                    <div style="font-size: 2rem; color: #004085; font-weight: bold;"><?php echo $total; ?></div>
                    <div style="color: #004085;">Total de Testes</div>
                </div>
            </div>
        </div>

        <!-- Detalhes dos Testes -->
        <div class="test-section">
            <h2>🔍 Detalhes dos Testes</h2>
            <?php foreach ($testes as $teste): ?>
                <div class="test-item <?php echo $teste['status']; ?>">
                    <strong><?php echo $teste['nome']; ?></strong>
                    <span class="status <?php echo $teste['status']; ?>">
                        <?php 
                        echo $teste['status'] === 'ok' ? '✓ OK' : 
                             ($teste['status'] === 'error' ? '✗ ERRO' : '⚠ AVISO'); 
                        ?>
                    </span>
                    <div style="color: #666; margin-top: 5px;"><?php echo $teste['msg']; ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Links de Teste -->
        <div class="test-section">
            <h2>🔗 Testar Páginas</h2>
            <div class="grid">
                <a href="index.php" class="test-link" target="_blank">🏠 Página Inicial</a>
                <a href="cardapio.php" class="test-link" target="_blank">📋 Cardápio</a>
                <a href="login.php" class="test-link" target="_blank">🔐 Login</a>
                <a href="carrinho.php" class="test-link" target="_blank">🛒 Carrinho</a>
            </div>
        </div>

        <!-- Próximos Passos -->
        <div class="test-section">
            <h2>📝 Próximos Passos</h2>
            <ul style="list-style: none; padding: 0;">
                <li style="padding: 8px 0;">✅ Fase 1: Controllers e Login - <strong>CONCLUÍDO</strong></li>
                <li style="padding: 8px 0;">🔄 Fase 2: Carrinho e Checkout - <strong>EM ANDAMENTO</strong></li>
                <li style="padding: 8px 0;">⏳ Fase 3: Painel Admin e Dashboard - <strong>PENDENTE</strong></li>
            </ul>
        </div>

    </div>
</body>
</html>
