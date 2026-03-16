<?php
// Iniciar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header>
    <div class="header-top">
        <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <i class="bi bi-whatsapp brand-icon" aria-hidden="true" style="color: #ffffff;"></i>
                <a href="https://wa.me/5544998571669?text=Ola!%20Quero%20ver%20as%20pecas%20do%20Bora%20Desapegar." 
                   target="_blank" 
                   style="color: white; text-decoration: none; font-weight: 600;">
                    (44) 99857-1669
                </a>
                 | <i class="bi bi-instagram brand-icon" aria-hidden="true" style="color: #ffffff; margin-left: 6px;"></i>
                <a href="https://www.instagram.com/desapegoinfantil.menino/" 
                   target="_blank"
                   rel="noopener noreferrer"
                   style="color: white; text-decoration: none;">
                    @desapegoinfantil.menino
                </a>
            </div>
            <div>
                📲 Atendimento online para maes e familias
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="header-content">
            <div>
                <a href="<?php echo SITE_URL; ?>/index.php" class="logo">
                    <span class="logo-icon">🧸</span>
                    <div>
                        <div><?php echo SITE_NAME; ?></div>
                        <div class="slogan"><?php echo SITE_SLOGAN; ?></div>
                    </div>
                </a>
            </div>
            
            <nav>
                <ul>
                    <li><a href="<?php echo SITE_URL; ?>/index.php">Início</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/cardapio.php">Peças</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/sobre.php">Sobre</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/contato.php">Contato</a></li>
                    <?php if (isset($_SESSION['cliente_id'])): ?>
                        <li><a href="<?php echo SITE_URL; ?>/minha-conta.php">Minha Conta</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/logout.php">Sair</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo SITE_URL; ?>/login.php">Entrar</a></li>
                    <?php endif; ?>
                    <li>
                        <a href="<?php echo SITE_URL; ?>/carrinho.php" class="btn-carrinho">
                            🛒 Sacola
                            <span class="carrinho-count" id="carrinho-count">0</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</header>
