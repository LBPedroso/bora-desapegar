<?php
require_once 'config/config.php';
require_once 'controllers/AuthController.php';

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se o cliente está logado
AuthController::requireCliente();

$pedidoId = $_GET['id'] ?? null;

if (!$pedidoId) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva Confirmada - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="public/assets/css/style.css?v=20260316b">
    <style>
        .confirmacao-container {
            max-width: 700px;
            margin: 4rem auto;
            padding: 0 20px;
            text-align: center;
        }
        .confirmacao-card {
            background: #fff;
            padding: 3rem 2rem;
            border-radius: 20px;
            border: 1px solid rgba(74, 144, 226, 0.16);
            box-shadow: 0 12px 28px rgba(74, 144, 226, 0.14);
        }
        .check-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #4A90E2, #2f5f94);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            animation: scaleIn 0.5s ease-out;
        }
        @keyframes scaleIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        .pedido-numero {
            font-size: 2rem;
            font-weight: bold;
            color: #2f5f94;
            margin: 1rem 0;
        }
        .info-box {
            background: #f5fbff;
            padding: 1.5rem;
            border-radius: 10px;
            margin: 2rem 0;
            text-align: left;
        }
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0;
            border-bottom: 1px solid #dee2e6;
        }
        .info-item:last-child {
            border-bottom: none;
        }
        .btn-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        .btn-group a {
            flex: 1;
        }
    </style>
</head>
<body>
    <?php include 'views/partials/header.php'; ?>

    <div class="confirmacao-container">
        <div class="confirmacao-card">
            <div class="check-icon">
                <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>

            <h1 style="color: #2f5f94; margin-bottom: 1rem;">Reserva Confirmada!</h1>
            <p style="color: #666; font-size: 1.1rem;">Sua solicitação foi recebida com sucesso.</p>

            <div class="pedido-numero">
                Reserva #<?php echo str_pad($pedidoId, 6, '0', STR_PAD_LEFT); ?>
            </div>

            <div class="info-box">
                <h3 style="margin-bottom: 1rem; color: #333;">Informações da Reserva</h3>
                <div class="info-item">
                    <span><strong>Status:</strong></span>
                    <span style="color: #4A90E2; font-weight: 600;">⏳ Aguardando confirmação da equipe</span>
                </div>
                <div class="info-item">
                    <span><strong>Forma de Pagamento:</strong></span>
                    <span>Informado no checkout</span>
                </div>
                <div class="info-item">
                    <span><strong>Previsão de contato:</strong></span>
                    <span>Em breve via WhatsApp</span>
                </div>
            </div>

            <div style="background: #eaf6ff; padding: 1.5rem; border-radius: 10px; border-left: 4px solid #4A90E2; margin: 2rem 0; text-align: left;">
                <h4 style="margin-bottom: 0.5rem; color: #2f5f94;">📱 Próximos passos</h4>
                <ul style="margin: 0; padding-left: 1.5rem; color: #333;">
                    <li>Entraremos em contato para confirmar os detalhes da reserva</li>
                    <li>Você receberá atualizações sobre disponibilidade e retirada/entrega</li>
                    <li>Se preferir, pode chamar no WhatsApp para agilizar</li>
                </ul>
            </div>

            <div class="btn-group">
                <a href="minha-conta.php" class="btn btn-primary">
                    Ver Minhas Reservas
                </a>
                <a href="cardapio.php" class="btn btn-secondary">
                    Continuar Vendo Peças
                </a>
            </div>

            <p style="margin-top: 2rem; color: #999; font-size: 0.9rem;">
                Dúvidas? Entre em contato: 
                <a href="https://wa.me/5544998571669?text=Ol%C3%A1!%20Tenho%20d%C3%BAvidas%20sobre%20minha%20reserva." 
                   target="_blank" 
                   style="color: #25D366; text-decoration: none; font-weight: 600;">
                    <i class="bi bi-whatsapp brand-icon" aria-hidden="true"></i>(44) 99857-1669
                </a>
            </p>
        </div>
    </div>

    <?php include 'views/partials/footer.php'; ?>

    <script src="public/assets/js/carrinho.js"></script>
    <script>
        // Limpar carrinho após confirmação
        localStorage.removeItem('carrinho');
        atualizarContadorCarrinho();
    </script>
</body>
</html>
