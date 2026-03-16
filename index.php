<?php
require_once __DIR__ . '/config/config.php';

$pecaModel = new Peca();
$categorias = [];
$pecasDestaque = [];

try {
    $categorias = $pecaModel->listarCategorias();
    $pecasDestaque = array_slice($pecaModel->findComFiltros(['status' => 'disponivel']), 0, 8);
} catch (Exception $e) {
    $categorias = [];
    $pecasDestaque = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo SITE_SLOGAN; ?></title>
    <link rel="stylesheet" href="public/assets/css/style.css?v=20260316b">
</head>
<body>
    <?php include __DIR__ . '/views/partials/header.php'; ?>

    <section class="hero">
        <div class="hero-content">
            <h1>Bora Desapegar</h1>
            <p>Peças infantis seminovas, selecionadas com carinho e preço justo.</p>
            <a href="cardapio.php" class="btn btn-primary">Ver peças disponíveis</a>
            <a href="admin/" class="btn btn-secondary">Painel administrativo</a>
        </div>
    </section>

    <section class="bg-light">
        <div class="container">
            <h2 class="section-title">Categorias</h2>
            <div class="categorias-grid">
                <?php if (empty($categorias)): ?>
                    <div class="categoria-card">
                        <div class="categoria-icon">i</div>
                        <h3>Sem categorias ainda</h3>
                        <p>Cadastre peças no painel para ver as categorias aqui.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($categorias as $categoria): ?>
                        <a href="cardapio.php?categoria=<?php echo urlencode($categoria['categoria']); ?>" class="categoria-card">
                            <div class="categoria-icon">+</div>
                            <h3><?php echo htmlspecialchars($categoria['categoria']); ?></h3>
                            <p>Ver peças desta categoria</p>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section>
        <div class="container">
            <h2 class="section-title">Destaques</h2>

            <?php if (empty($pecasDestaque)): ?>
                <p style="text-align:center; color:#666;">Nenhuma peça disponível no momento.</p>
            <?php else: ?>
                <div class="produtos-grid">
                    <?php foreach ($pecasDestaque as $peca): ?>
                        <div class="produto-card">
                            <img src="public/assets/img/pecas/<?php echo htmlspecialchars($peca['foto'] ?: 'default.jpg'); ?>" alt="<?php echo htmlspecialchars($peca['nome']); ?>" class="produto-img js-zoomable-image" loading="lazy" title="Clique para ampliar">
                            <div class="produto-info">
                                <div class="produto-categoria"><?php echo htmlspecialchars($peca['categoria']); ?></div>
                                <h3 class="produto-nome"><?php echo htmlspecialchars($peca['nome']); ?></h3>
                                <p class="produto-descricao">Tamanho: <?php echo htmlspecialchars($peca['tamanho']); ?></p>
                                <div class="produto-footer">
                                    <span class="produto-preco">R$ <?php echo number_format($peca['preco'], 2, ',', '.'); ?></span>
                                    <a href="https://wa.me/5544998571669?text=Ola!%20Tenho%20interesse%20na%20peca%20<?php echo rawurlencode($peca['nome']); ?>" target="_blank" class="btn-adicionar">WhatsApp</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include __DIR__ . '/views/partials/footer.php'; ?>
</body>
</html>
