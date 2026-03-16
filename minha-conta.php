<?php
require_once 'config/config.php';
require_once 'config/helpers.php';
require_once 'controllers/AuthController.php';
require_once 'models/Cliente.php';
require_once 'models/Pedido.php';

// Verificar se está logado
if (!isset($_SESSION['cliente_id'])) {
    header('Location: login.php');
    exit;
}

$clienteModel = new Cliente();
$pedidoModel = new Pedido();

$cliente = $clienteModel->findById($_SESSION['cliente_id']);

// Se cliente não encontrado, fazer logout
if (!$cliente) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$pedidos = $pedidoModel->findByCliente($_SESSION['cliente_id']);

// Processar atualização de dados
$mensagem = '';
$tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar'])) {
    $dados = [
        'nome' => $_POST['nome'] ?? '',
        'email' => $_POST['email'] ?? '',
        'telefone' => $_POST['telefone'] ?? '',
        'cpf' => $_POST['cpf'] ?? '',
        'endereco_rua' => $_POST['endereco'] ?? '',
        'endereco_numero' => $_POST['numero'] ?? '',
        'endereco_complemento' => $_POST['complemento'] ?? '',
        'endereco_bairro' => $_POST['bairro'] ?? '',
        'endereco_cidade' => $_POST['cidade'] ?? '',
        'endereco_estado' => $_POST['estado'] ?? '',
        'endereco_cep' => $_POST['cep'] ?? ''
    ];
    
    // Se digitou nova senha, atualizar
    if (!empty($_POST['nova_senha'])) {
        if ($_POST['nova_senha'] === $_POST['confirmar_senha']) {
            $dados['senha'] = password_hash($_POST['nova_senha'], PASSWORD_DEFAULT);
        } else {
            $mensagem = 'As senhas não conferem';
            $tipo = 'erro';
        }
    }
    
    if (empty($mensagem)) {
        if ($clienteModel->update($_SESSION['cliente_id'], $dados)) {
            $mensagem = 'Dados atualizados com sucesso!';
            $tipo = 'sucesso';
            $cliente = $clienteModel->findById($_SESSION['cliente_id']);
            $_SESSION['cliente_nome'] = $cliente['nome'];
        } else {
            $mensagem = 'Erro ao atualizar dados';
            $tipo = 'erro';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Conta - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="public/assets/css/style.css?v=20260316b">
</head>
<body>
    <?php include 'views/partials/header.php'; ?>

    <section style="padding: 3rem 0; min-height: 70vh;">
        <div class="container">
            <h1 class="section-title">Minha Conta</h1>
            
            <?php if ($mensagem): ?>
            <div class="mensagem <?php echo $tipo; ?>" style="margin-bottom: 2rem;">
                <?php echo $mensagem; ?>
            </div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; margin-top: 2rem;">
                <!-- SIDEBAR -->
                <div>
                    <div style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                        <h3 style="margin-bottom: 1rem; color: var(--cor-secundaria);">👤 Olá, <?php echo htmlspecialchars(explode(' ', $cliente['nome'])[0]); ?>!</h3>
                        <div style="padding: 1rem 0; border-top: 1px solid #eee;">
                            <p><strong>Email:</strong> <?php echo !empty($cliente['email']) ? htmlspecialchars($cliente['email']) : 'Não informado'; ?></p>
                            <p style="margin-top: 0.5rem;"><strong>Telefone:</strong> <?php echo !empty($cliente['telefone']) ? htmlspecialchars($cliente['telefone']) : 'Não informado'; ?></p>
                            <p style="margin-top: 0.5rem;"><strong>CPF:</strong> <?php echo !empty($cliente['cpf']) ? htmlspecialchars($cliente['cpf']) : 'Não informado'; ?></p>
                        </div>
                    </div>
                </div>

                <!-- CONTEÚDO PRINCIPAL -->
                <div>
                    <!-- TABS -->
                    <div style="margin-bottom: 2rem; border-bottom: 2px solid #eee;">
                        <button class="tab-btn active" onclick="mudarTab('dados')">📝 Meus Dados</button>
                        <button class="tab-btn" onclick="mudarTab('pedidos')">📦 Minhas Reservas</button>
                    </div>

                    <!-- TAB DADOS -->
                    <div id="tab-dados" class="tab-content active">
                        <form method="POST" style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                            <h3 style="margin-bottom: 1.5rem; color: var(--cor-secundaria);">Atualizar Dados</h3>
                            
                            <div class="form-group">
                                <label>Nome Completo *</label>
                                <input type="text" name="nome" value="<?php echo htmlspecialchars($cliente['nome']); ?>" required>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="form-group">
                                    <label>Email *</label>
                                    <input type="email" name="email" value="<?php echo htmlspecialchars($cliente['email']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Telefone *</label>
                                    <input type="tel" name="telefone" value="<?php echo htmlspecialchars($cliente['telefone'] ?? ''); ?>" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>CPF</label>
                                <input type="text" name="cpf" value="<?php echo htmlspecialchars($cliente['cpf'] ?? ''); ?>" maxlength="14">
                            </div>

                            <h4 style="margin: 2rem 0 1rem;">📍 Endereço Principal</h4>

                            <div class="form-group">
                                <label>Rua *</label>
                                <input type="text" name="endereco" value="<?php echo htmlspecialchars($cliente['endereco_rua'] ?? ''); ?>" required>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1rem;">
                                <div class="form-group">
                                    <label>Número *</label>
                                    <input type="text" name="numero" value="<?php echo htmlspecialchars($cliente['endereco_numero'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Complemento</label>
                                    <input type="text" name="complemento" value="<?php echo htmlspecialchars($cliente['endereco_complemento'] ?? ''); ?>">
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="form-group">
                                    <label>Bairro *</label>
                                    <input type="text" name="bairro" value="<?php echo htmlspecialchars($cliente['endereco_bairro'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>CEP</label>
                                    <input type="text" name="cep" value="<?php echo htmlspecialchars($cliente['endereco_cep'] ?? ''); ?>" maxlength="9">
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
                                <div class="form-group">
                                    <label>Cidade *</label>
                                    <input type="text" name="cidade" value="<?php echo htmlspecialchars($cliente['endereco_cidade'] ?? 'Campo Mourão'); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Estado *</label>
                                    <input type="text" name="estado" value="<?php echo htmlspecialchars($cliente['endereco_estado'] ?? 'PR'); ?>" maxlength="2" required>
                                </div>
                            </div>

                            <h4 style="margin: 2rem 0 1rem;">🔒 Alterar Senha (opcional)</h4>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="form-group">
                                    <label>Nova Senha</label>
                                    <input type="password" name="nova_senha" minlength="6">
                                </div>
                                <div class="form-group">
                                    <label>Confirmar Senha</label>
                                    <input type="password" name="confirmar_senha" minlength="6">
                                </div>
                            </div>

                            <button type="submit" name="atualizar" class="btn btn-primary" style="margin-top: 1rem;">
                                Salvar Alterações
                            </button>
                        </form>
                    </div>

                    <!-- TAB PEDIDOS -->
                    <div id="tab-pedidos" class="tab-content" style="display: none;">
                        <?php if (empty($pedidos)): ?>
                            <div style="text-align: center; padding: 3rem; background: white; border-radius: 10px;">
                                <p style="font-size: 1.2rem; color: #666;">📦 Você ainda não possui reservas registradas</p>
                                <a href="cardapio.php" class="btn btn-primary" style="margin-top: 1rem;">Ver Peças</a>
                            </div>
                        <?php else: ?>
                            <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                                <h3 style="margin-bottom: 1.5rem;">Histórico de Reservas</h3>
                                <?php foreach ($pedidos as $pedido): ?>
                                    <div style="padding: 1rem; border: 1px solid #eee; border-radius: 5px; margin-bottom: 1rem;">
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <div>
                                                <strong>Reserva #<?php echo $pedido['id']; ?></strong>
                                                <p style="color: #666; margin-top: 0.3rem;">
                                                    <?php echo date('d/m/Y H:i', strtotime($pedido['criado_em'])); ?>
                                                </p>
                                            </div>
                                            <div style="text-align: right;">
                                                <div class="status-badge status-<?php echo $pedido['status']; ?>">
                                                    <?php echo ucfirst($pedido['status']); ?>
                                                </div>
                                                <p style="font-weight: bold; margin-top: 0.5rem; color: var(--cor-primaria);">
                                                    R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'views/partials/footer.php'; ?>

    <script>
        function mudarTab(tab) {
            // Esconder todas as tabs
            document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            
            // Mostrar tab selecionada
            document.getElementById('tab-' + tab).style.display = 'block';
            event.target.classList.add('active');
        }
    </script>

    <style>
        .tab-btn {
            background: none;
            border: none;
            padding: 1rem 2rem;
            cursor: pointer;
            font-size: 1rem;
            color: #666;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        .tab-btn:hover {
            color: var(--cor-secundaria);
        }
        .tab-btn.active {
            color: var(--cor-secundaria);
            border-bottom-color: var(--cor-secundaria);
        }
        .status-pendente { background: #ffc107; color: #000; }
        .status-confirmado { background: #28a745; color: #fff; }
        .status-preparando { background: #17a2b8; color: #fff; }
        .status-saiu-entrega { background: #007bff; color: #fff; }
        .status-entregue { background: #6c757d; color: #fff; }
        .status-cancelado { background: #dc3545; color: #fff; }
    </style>

    <script>
        // Máscaras de formatação
        document.addEventListener('DOMContentLoaded', function() {
            // Máscara para telefone (44) 99999-9999
            const telefoneInput = document.querySelector('input[name="telefone"]');
            if (telefoneInput) {
                telefoneInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length <= 11) {
                        value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
                        value = value.replace(/(\d)(\d{4})$/, '$1-$2');
                    }
                    e.target.value = value;
                });
            }

            // Máscara para CPF 000.000.000-00
            const cpfInput = document.querySelector('input[name="cpf"]');
            if (cpfInput) {
                cpfInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length <= 11) {
                        value = value.replace(/(\d{3})(\d)/, '$1.$2');
                        value = value.replace(/(\d{3})(\d)/, '$1.$2');
                        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                    }
                    e.target.value = value;
                });
            }

            // Máscara para CEP 00000-000
            const cepInput = document.querySelector('input[name="cep"]');
            if (cepInput) {
                cepInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length <= 8) {
                        value = value.replace(/(\d{5})(\d)/, '$1-$2');
                    }
                    e.target.value = value;
                });
            }
        });
    </script>
</body>
</html>
