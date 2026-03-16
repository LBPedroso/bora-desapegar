<?php
session_start();
require_once __DIR__ . '/config/config.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/public/assets/css/style.css?v=20260316b">
    <style>
        .sobre-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 40px 20px 10px;
        }

        .sobre-hero {
            text-align: center;
            background: linear-gradient(180deg, #ffffff 0%, #eaf6ff 100%);
            border: 1px solid rgba(74, 144, 226, 0.18);
            border-radius: 18px;
            padding: 40px 24px;
            box-shadow: 0 14px 30px rgba(74, 144, 226, 0.12);
            margin-bottom: 28px;
        }

        .sobre-hero h1 {
            color: #214870;
            font-size: clamp(2rem, 4vw, 3rem);
            margin-bottom: 12px;
        }

        .sobre-hero p {
            color: #3f607f;
            max-width: 760px;
            margin: 0 auto;
            font-size: 1.08rem;
        }

        .sobre-card {
            background: #ffffff;
            border: 1px solid rgba(74, 144, 226, 0.14);
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 10px 24px rgba(74, 144, 226, 0.1);
            margin-bottom: 22px;
        }

        .sobre-card h2 {
            color: #2f5f94;
            margin-bottom: 14px;
        }

        .sobre-card p {
            color: #44596b;
            line-height: 1.8;
            margin-bottom: 12px;
        }

        .sobre-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 22px;
        }

        .sobre-item {
            background: #f5e9da;
            border: 1px solid rgba(74, 144, 226, 0.12);
            border-radius: 14px;
            padding: 20px;
            text-align: center;
        }

        .sobre-item h3 {
            color: #2f5f94;
            margin: 8px 0;
        }

        .sobre-item p {
            color: #4f6272;
            font-size: 0.95rem;
            margin: 0;
        }

        .sobre-compromisso {
            background: linear-gradient(150deg, #a8d8ff 0%, #4a90e2 100%);
            color: #fff;
            border-radius: 16px;
            padding: 28px;
            text-align: center;
            box-shadow: 0 14px 28px rgba(74, 144, 226, 0.25);
            margin-bottom: 26px;
        }

        .sobre-compromisso p {
            max-width: 760px;
            margin: 0 auto;
            line-height: 1.7;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/views/partials/header.php'; ?>

    <main class="sobre-container">
        <section class="sobre-hero">
            <h1>Sobre o Bora Desapegar</h1>
            <p>
                Um brechó infantil online criado para facilitar a rotina das famílias: peças bonitas,
                bem cuidadas e com preço justo para continuar circulando com carinho.
            </p>
        </section>

        <section>
            <div class="sobre-card">
                <h2>Nossa história</h2>
                <p>
                    O <strong>Bora Desapegar</strong> nasceu com um objetivo simples: transformar peças que já fizeram parte de uma fase
                    especial em novas oportunidades para outras crianças.
                </p>
                <p>
                    Sabemos que os pequenos crescem rápido e, muitas vezes, roupas e acessórios ficam em ótimo estado por pouco tempo de uso.
                    Por isso criamos um espaço confiável para compra e venda de itens infantis seminovos.
                </p>
                <p>
                    Atendemos famílias de <strong>Campo Mourão - PR</strong> e região com atendimento próximo, transparente e humano.
                </p>
            </div>

            <div class="sobre-grid">
                <article class="sobre-item">
                    <div style="font-size: 2rem;">🧸</div>
                    <h3>Curadoria Cuidadosa</h3>
                    <p>Peças infantis selecionadas por estado de conservação, qualidade e estilo.</p>
                </article>

                <article class="sobre-item">
                    <div style="font-size: 2rem;">💙</div>
                    <h3>Preço Justo</h3>
                    <p>Economia para quem compra e retorno para quem desapega.</p>
                </article>

                <article class="sobre-item">
                    <div style="font-size: 2rem;">♻️</div>
                    <h3>Consumo Consciente</h3>
                    <p>Mais reaproveitamento, menos desperdício e impacto positivo para todos.</p>
                </article>
            </div>

            <div class="sobre-compromisso">
                <h2 style="margin-bottom: 12px;">Nosso compromisso</h2>
                <p>
                    Oferecer uma experiência simples, acolhedora e segura para mães, pais e responsáveis,
                    conectando quem quer desapegar com quem procura peças infantis de qualidade.
                </p>
            </div>
        </section>
    </main>

    <?php include __DIR__ . '/views/partials/footer.php'; ?>
</body>
</html>
