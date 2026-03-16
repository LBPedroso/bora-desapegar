<?php
require_once 'config/config.php';
require_once 'config/helpers.php';
require_once 'controllers/AuthController.php';

$authController = new AuthController();

// Se já estiver logado, redirecionar
if ($authController->isCliente()) {
    header('Location: minha-conta.php');
    exit;
}

$erro = '';
$sucesso = '';

// Processar login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'login') {
    $identificador = trim($_POST['identificador'] ?? '');
    $senha = $_POST['senha'] ?? '';

    $resultado = $authController->loginCliente($identificador, $senha);

    if ($resultado['success']) {
        header('Location: minha-conta.php');
        exit;
    } else {
        $erro = $resultado['message'];
    }
}

// Processar cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'cadastro') {
    $dados = [
        'nome' => trim($_POST['nome'] ?? ''),
        'email' => null,
        'telefone' => trim($_POST['telefone'] ?? ''),
        'cpf' => trim($_POST['cpf'] ?? ''),
        'senha' => $_POST['senha'] ?? '',
        'endereco_rua' => trim($_POST['endereco'] ?? ''),
        'endereco_numero' => trim($_POST['numero'] ?? ''),
        'endereco_complemento' => trim($_POST['complemento'] ?? ''),
        'endereco_bairro' => trim($_POST['bairro'] ?? ''),
        'endereco_cidade' => trim($_POST['cidade'] ?? ''),
        'endereco_estado' => trim($_POST['estado'] ?? ''),
        'endereco_cep' => trim($_POST['cep'] ?? '')
    ];

    // Validações
    $erros = [];

    // CPF ou telefone obrigatório
    if (empty($dados['cpf']) && empty($dados['telefone'])) {
        $erros[] = 'Informe CPF ou telefone para criar a conta';
    }

    // Validar CPF
    if (!empty($dados['cpf']) && !validarCPF($dados['cpf'])) {
        $erros[] = 'CPF inválido';
    }

    // Validar telefone (apenas se informado)
    if (!empty($dados['telefone']) && !validarTelefone($dados['telefone'])) {
        $erros[] = 'Telefone inválido';
    }

    // Validar CEP (apenas se informado)
    if (!empty($dados['endereco_cep']) && !validarCEP($dados['endereco_cep'])) {
        $erros[] = 'CEP inválido';
    }

    // Confirmar senha
    if ($dados['senha'] !== ($_POST['confirmar_senha'] ?? '')) {
        $erros[] = 'As senhas não conferem';
    }

    if (!empty($erros)) {
        $erro = implode('<br>', $erros);
    } else {
        $resultado = $authController->registrarCliente($dados);

        if ($resultado['success']) {
            header('Location: minha-conta.php');
            exit;
        } else {
            $erro = $resultado['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="public/assets/css/style.css?v=20260316b">
    <style>
        .login-container {
            max-width: 1000px;
            margin: 3rem auto;
            padding: 0 20px;
        }
        .login-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
        }
        .login-box {
            background: #fff;
            padding: 2rem;
            border-radius: 15px;
            border: 1px solid rgba(74, 144, 226, 0.15);
            box-shadow: 0 10px 22px rgba(74, 144, 226, 0.12);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--cor-escura);
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #d1e3f2;
            border-radius: 8px;
            font-size: 1rem;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--cor-secundaria);
        }
        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #c33;
        }
        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #3c3;
        }
        .password-wrap {
            position: relative;
        }
        .password-wrap input {
            padding-right: 48px;
        }
        .btn-show-password {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: #4A90E2;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
        }
        .btn-buscar-cep {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #4A90E2;
            border-radius: 8px;
            background: #eaf6ff;
            color: #2f5f94;
            font-weight: 700;
            cursor: pointer;
        }
        @media (max-width: 768px) {
            .login-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'views/partials/header.php'; ?>

    <div class="login-container">
        <h1 class="section-title">Acesse sua Conta</h1>

        <?php if ($erro): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($sucesso); ?></div>
        <?php endif; ?>

        <div class="login-grid">
            <!-- LOGIN -->
            <div class="login-box">
                <h2 style="margin-bottom: 1.5rem; color: var(--cor-primaria);">Já sou Cliente</h2>

                <form method="POST" id="form-login">
                    <input type="hidden" name="acao" value="login">

                    <div class="form-group">
                        <label for="login-identificador">CPF ou Telefone</label>
                        <input type="text" id="login-identificador" name="identificador" required placeholder="000.000.000-00 ou (44) 99999-9999">
                    </div>

                    <div class="form-group">
                        <label for="login-senha">Senha</label>
                        <div class="password-wrap">
                            <input type="password" id="login-senha" name="senha" required>
                            <button type="button" class="btn-show-password" data-toggle-password="login-senha">Mostrar</button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;">
                        Entrar
                    </button>
                </form>
            </div>

            <!-- CADASTRO -->
            <div class="login-box">
                <h2 style="margin-bottom: 1.5rem; color: var(--cor-secundaria);">Primeiro Acesso</h2>

                <form method="POST" id="form-cadastro">
                    <input type="hidden" name="acao" value="cadastro">

                    <div class="form-group">
                        <label for="cad-nome">Nome Completo *</label>
                        <input type="text" id="cad-nome" name="nome" required>
                    </div>

                    <div class="form-group">
                        <label for="cad-telefone">Telefone</label>
                        <input type="tel" id="cad-telefone" name="telefone" placeholder="(44) 99999-9999">
                    </div>

                    <div class="form-group">
                        <label for="cad-cpf">CPF</label>
                        <input type="text" id="cad-cpf" name="cpf" placeholder="000.000.000-00" maxlength="14">
                        <small style="color: #666;">* Informe CPF ou telefone para cadastrar e acessar</small>
                    </div>

                    <div class="form-group">
                        <label for="cad-senha">Senha *</label>
                        <div class="password-wrap">
                            <input type="password" id="cad-senha" name="senha" minlength="6" required>
                            <button type="button" class="btn-show-password" data-toggle-password="cad-senha">Mostrar</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="cad-confirmar">Confirmar Senha *</label>
                        <div class="password-wrap">
                            <input type="password" id="cad-confirmar" name="confirmar_senha" minlength="6" required>
                            <button type="button" class="btn-show-password" data-toggle-password="cad-confirmar">Mostrar</button>
                        </div>
                    </div>

                    <h3 style="margin: 1.5rem 0 1rem; font-size: 1.1rem;">Endereço Principal (opcional)</h3>

                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 0.8rem;">
                        <div class="form-group">
                            <label for="cad-cep">CEP</label>
                            <input type="text" id="cad-cep" name="cep" placeholder="87300-000">
                        </div>
                        <div class="form-group" style="display: flex; align-items: flex-end;">
                            <button type="button" id="btn-buscar-cep" class="btn-buscar-cep">Buscar CEP</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="cad-endereco">Rua</label>
                        <input type="text" id="cad-endereco" name="endereco">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="cad-numero">Número</label>
                            <input type="text" id="cad-numero" name="numero">
                        </div>

                        <div class="form-group">
                            <label for="cad-complemento">Complemento</label>
                            <input type="text" id="cad-complemento" name="complemento">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="cad-bairro">Bairro</label>
                        <input type="text" id="cad-bairro" name="bairro">
                    </div>

                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="cad-cidade">Cidade</label>
                            <input type="text" id="cad-cidade" name="cidade" placeholder="Campo Mourão">
                        </div>
                        <div class="form-group">
                            <label for="cad-estado">Estado</label>
                            <input type="text" id="cad-estado" name="estado" maxlength="2" placeholder="PR">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-secondary" style="width: 100%; padding: 1rem;">
                        Criar Conta
                    </button>
                </form>
            </div>
        </div>
    </div>

    <?php include 'views/partials/footer.php'; ?>

    <script>
        function validarCPF(cpf) {
            cpf = cpf.replace(/\D/g, '');
            if (cpf.length !== 11) return false;
            if (/^(\d)\1{10}$/.test(cpf)) return false;

            let soma = 0;
            for (let i = 0; i < 9; i++) soma += parseInt(cpf.charAt(i), 10) * (10 - i);
            let resto = 11 - (soma % 11);
            let digito1 = resto >= 10 ? 0 : resto;
            if (digito1 !== parseInt(cpf.charAt(9), 10)) return false;

            soma = 0;
            for (let i = 0; i < 10; i++) soma += parseInt(cpf.charAt(i), 10) * (11 - i);
            resto = 11 - (soma % 11);
            let digito2 = resto >= 10 ? 0 : resto;
            return digito2 === parseInt(cpf.charAt(10), 10);
        }

        function formatarTelefone(valor) {
            let numeros = valor.replace(/\D/g, '').slice(0, 11);
            if (numeros.length <= 2) return numeros;
            if (numeros.length <= 6) return `(${numeros.slice(0, 2)}) ${numeros.slice(2)}`;
            if (numeros.length <= 10) return `(${numeros.slice(0, 2)}) ${numeros.slice(2, 6)}-${numeros.slice(6)}`;
            return `(${numeros.slice(0, 2)}) ${numeros.slice(2, 7)}-${numeros.slice(7)}`;
        }

        function formatarCPF(valor) {
            let numeros = valor.replace(/\D/g, '').slice(0, 11);
            numeros = numeros.replace(/(\d{3})(\d)/, '$1.$2');
            numeros = numeros.replace(/(\d{3})(\d)/, '$1.$2');
            numeros = numeros.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            return numeros;
        }

        function formatarCEP(valor) {
            let numeros = valor.replace(/\D/g, '').slice(0, 8);
            return numeros.replace(/(\d{5})(\d)/, '$1-$2');
        }

        document.querySelectorAll('[data-toggle-password]').forEach(function (botao) {
            botao.addEventListener('click', function () {
                const inputId = botao.getAttribute('data-toggle-password');
                const input = document.getElementById(inputId);
                if (!input) return;

                const visivel = input.type === 'text';
                input.type = visivel ? 'password' : 'text';
                botao.textContent = visivel ? 'Mostrar' : 'Ocultar';
            });
        });

        const telefoneInput = document.getElementById('cad-telefone');
        const cpfInput = document.getElementById('cad-cpf');
        const cepInput = document.getElementById('cad-cep');
        const cadastroForm = document.getElementById('form-cadastro');
        const btnBuscarCep = document.getElementById('btn-buscar-cep');

        if (telefoneInput) {
            telefoneInput.addEventListener('input', function (e) {
                e.target.value = formatarTelefone(e.target.value);
            });
        }

        if (cpfInput) {
            cpfInput.addEventListener('input', function (e) {
                e.target.value = formatarCPF(e.target.value);
            });

            cpfInput.addEventListener('blur', function (e) {
                const cpf = e.target.value.replace(/\D/g, '');
                const mensagemAnterior = e.target.parentElement.querySelector('.cpf-erro');
                if (mensagemAnterior) mensagemAnterior.remove();

                if (cpf.length === 0) {
                    e.target.style.borderColor = '';
                    e.target.style.backgroundColor = '';
                    return;
                }

                const mensagem = document.createElement('small');
                mensagem.className = 'cpf-erro';
                mensagem.style.display = 'block';
                mensagem.style.marginTop = '5px';

                if (!validarCPF(cpf)) {
                    e.target.style.borderColor = '#dc3545';
                    e.target.style.backgroundColor = '#ffe6e6';
                    mensagem.style.color = '#dc3545';
                    mensagem.textContent = 'CPF inválido';
                } else {
                    e.target.style.borderColor = '#28a745';
                    e.target.style.backgroundColor = '#e6ffe6';
                    mensagem.style.color = '#28a745';
                    mensagem.textContent = 'CPF válido';
                }

                e.target.parentElement.appendChild(mensagem);
            });

            cpfInput.addEventListener('focus', function (e) {
                e.target.style.borderColor = '';
                e.target.style.backgroundColor = '';
                const msg = e.target.parentElement.querySelector('.cpf-erro');
                if (msg) msg.remove();
            });
        }

        if (cepInput) {
            cepInput.addEventListener('input', function (e) {
                e.target.value = formatarCEP(e.target.value);
            });
        }

        async function buscarCep() {
            const cep = cepInput.value.replace(/\D/g, '');
            if (cep.length !== 8) {
                alert('Informe um CEP válido com 8 dígitos.');
                return;
            }

            btnBuscarCep.disabled = true;
            btnBuscarCep.textContent = 'Buscando...';

            try {
                const resposta = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                const dados = await resposta.json();

                if (dados.erro) {
                    alert('CEP não encontrado.');
                    return;
                }

                document.getElementById('cad-endereco').value = dados.logradouro || '';
                document.getElementById('cad-bairro').value = dados.bairro || '';
                document.getElementById('cad-cidade').value = dados.localidade || '';
                document.getElementById('cad-estado').value = dados.uf || '';
            } catch (erro) {
                alert('Não foi possível consultar o CEP agora. Tente novamente.');
            } finally {
                btnBuscarCep.disabled = false;
                btnBuscarCep.textContent = 'Buscar CEP';
            }
        }

        if (btnBuscarCep) {
            btnBuscarCep.addEventListener('click', buscarCep);
        }

        if (cepInput) {
            cepInput.addEventListener('blur', function () {
                if (cepInput.value.replace(/\D/g, '').length === 8) {
                    buscarCep();
                }
            });
        }

        if (cadastroForm) {
            cadastroForm.addEventListener('submit', function (e) {
                const cpf = (cpfInput ? cpfInput.value : '').replace(/\D/g, '');
                const telefone = (telefoneInput ? telefoneInput.value : '').replace(/\D/g, '');

                if (!cpf && !telefone) {
                    e.preventDefault();
                    alert('Informe CPF ou telefone para cadastrar.');
                    if (cpfInput) cpfInput.focus();
                    return;
                }

                if (cpf && !validarCPF(cpf)) {
                    e.preventDefault();
                    alert('Por favor, insira um CPF válido antes de continuar.');
                    if (cpfInput) cpfInput.focus();
                }
            });
        }
    </script>
</body>
</html>
