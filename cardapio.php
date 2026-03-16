<?php
require_once __DIR__ . '/config/config.php';

$pecaModel = new Peca();

$categoria = isset($_GET['categoria']) ? trim($_GET['categoria']) : '';
$busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';

$filtros = [
    'status' => 'disponivel',
    'categoria' => $categoria,
    'busca' => $busca
];

try {
    $pecas = $pecaModel->findComFiltros($filtros);
    $categorias = $pecaModel->listarCategorias();
} catch (Exception $e) {
    $pecas = [];
    $categorias = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peças - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="public/assets/css/style.css?v=20260316b">
</head>
<body>
    <!-- HEADER -->
    <?php include __DIR__ . '/views/partials/header.php'; ?>

    <!-- BREADCRUMB -->
    <section style="background: var(--cor-clara); padding: 1rem 0;">
        <div class="container">
            <p>
                <a href="index.php" style="color: var(--cor-secundaria); text-decoration: none;">Início</a> 
                » Peças
                <?php if ($categoria !== ''): ?>
                    » <?php echo htmlspecialchars($categoria); ?>
                <?php endif; ?>
            </p>
        </div>
    </section>

    <!-- PECAS -->
    <section>
        <div class="container">
            <h1 class="section-title">
                <?php echo $categoria !== '' ? htmlspecialchars($categoria) : 'Peças Disponíveis'; ?>
            </h1>

            <!-- FILTROS -->
            <div style="margin-bottom: 2rem;">
                <form method="GET" style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <select name="categoria" style="padding: 0.8rem; border-radius: 5px; border: 2px solid #ddd;">
                        <option value="">Todas as categorias</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['categoria']); ?>" <?php echo $categoria === $cat['categoria'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['categoria']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <input type="text" name="busca" placeholder="Buscar peça..." 
                           value="<?php echo htmlspecialchars($busca); ?>"
                           style="padding: 0.8rem; border-radius: 5px; border: 2px solid #ddd; min-width: 300px;">
                    
                    <button type="submit" class="btn btn-primary" style="padding: 0.8rem 2rem;">
                        🔍 Buscar
                    </button>
                    
                    <?php if ($categoria !== '' || $busca !== ''): ?>
                        <a href="cardapio.php" class="btn btn-secondary" style="padding: 0.8rem 2rem;">
                            ✖ Limpar Filtros
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- PECAS -->
            <?php if (empty($pecas)): ?>
                <div style="text-align: center; padding: 4rem 0;">
                    <p style="font-size: 1.2rem; color: #666;">
                        Nenhuma peça encontrada
                    </p>
                    <a href="cardapio.php" class="btn btn-primary" style="margin-top: 1rem;">
                        Ver todas as peças
                    </a>
                </div>
            <?php else: ?>
                <div class="produtos-grid">
                    <?php foreach ($pecas as $peca): ?>
                        <div class="produto-card">
                            <img src="public/assets/img/pecas/<?php echo htmlspecialchars($peca['foto'] ?: 'default.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($peca['nome']); ?>"
                                 class="produto-img js-zoomable-image"
                                 loading="lazy"
                                 title="Clique para ampliar">
                            
                            <div class="produto-info">
                                <div class="produto-categoria">
                                    <?php echo htmlspecialchars($peca['categoria']); ?>
                                </div>
                                
                                <h3 class="produto-nome">
                                    <?php echo htmlspecialchars($peca['nome']); ?>
                                </h3>
                                
                                <p class="produto-descricao">
                                    Tamanho: <?php echo htmlspecialchars($peca['tamanho']); ?>
                                    <?php if (!empty($peca['observacao'])): ?>
                                        <br><?php echo htmlspecialchars($peca['observacao']); ?>
                                    <?php endif; ?>
                                </p>
                                
                                <div class="produto-footer">
                                    <span class="produto-preco">
                                        R$ <?php echo number_format($peca['preco'], 2, ',', '.'); ?>
                                    </span>

                                    <a href="https://wa.me/5544998571669?text=Ola!%20Tenho%20interesse%20na%20peca%20<?php echo rawurlencode($peca['nome']); ?>"
                                       target="_blank"
                                       class="btn-adicionar">
                                        WhatsApp
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- FOOTER -->
    <?php include __DIR__ . '/views/partials/footer.php'; ?>
</body>
</html>
