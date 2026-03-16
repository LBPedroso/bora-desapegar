<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/config.php';
require_once '../controllers/AuthController.php';

$authController = new AuthController();

// Se já estiver logado, redirecionar
if ($authController->isAdmin()) {
    header('Location: index.php');
    exit;
}

$erro = '';

// Processar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    
    $resultado = $authController->loginAdmin($email, $senha);
    
    if ($resultado['success']) {
        header('Location: index.php');
        exit;
    } else {
        $erro = $resultado['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #4A90E2, #A8D8FF);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .login-box {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 400px;
            width: 100%;
        }
        .login-box h1 {
            text-align: center;
            color: var(--cor-primaria);
            margin-bottom: 0.5rem;
        }
        .login-box p {
            text-align: center;
            color: #666;
            margin-bottom: 2rem;
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
        .form-group input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--cor-primaria);
        }
        .alert-error {
            background: #fee;
            color: #c33;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #c33;
        }
        .back-link {
            text-align: center;
            margin-top: 1.5rem;
        }
        .back-link a {
            color: var(--cor-secundaria);
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>🔐 Admin</h1>
        <p><?php echo SITE_NAME; ?></p>

        <?php if ($erro): ?>
            <div class="alert-error"><?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required autofocus>
            </div>

            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;">
                Entrar
            </button>
        </form>

        <div class="back-link">
            <a href="../index.php">← Voltar ao Site</a>
        </div>
    </div>
</body>
</html>
