<footer>
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Sobre o Bora Desapegar</h3>
                <p>Brecho infantil online focado em pecas seminovas, consumo consciente e economia para familias.</p>
            </div>
            
            <div class="footer-section">
                <h3>Links Rápidos</h3>
                <ul>
                    <li><a href="<?php echo SITE_URL; ?>/cardapio.php">Peças</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/sobre.php">Sobre Nós</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/contato.php">Contato</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/admin/">Área Administrativa</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Atendimento</h3>
                <p><strong>Online:</strong><br>
                Segunda a Domingo, das 9h as 19h</p>
                <p><strong>Canal principal:</strong><br>
                WhatsApp</p>
            </div>
            
            <div class="footer-section">
                <h3>Contato</h3>
                <p><i class="bi bi-whatsapp brand-icon brand-whatsapp" aria-hidden="true"></i>
                <a href="https://wa.me/5544998571669?text=Ola!%20Quero%20ver%20as%20pecas%20disponiveis." 
                   target="_blank" 
                   style="color: #25D366; text-decoration: none; font-weight: 600;">
                    (44) 99857-1669
                </a><br>
                <i class="bi bi-instagram brand-icon brand-instagram" aria-hidden="true"></i>
                <a href="https://www.instagram.com/desapegoinfantil.menino/" 
                   target="_blank"
                   rel="noopener noreferrer"
                   style="color: #e1306c; text-decoration: none; font-weight: 700;">
                    @desapegoinfantil.menino
                </a><br>
                📍 Campo Mourão, PR</p>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Bora Desapegar - Campo Mourão, PR | Sistema desenvolvido por LBPStartWeb</p>
        </div>
    </div>
</footer>

<div id="image-lightbox" class="image-lightbox" aria-hidden="true">
    <button type="button" class="image-lightbox-close" aria-label="Fechar visualizacao">×</button>
    <img id="image-lightbox-content" src="" alt="Visualizacao ampliada">
</div>

<script>
    (function () {
        if (window.__boraImageLightboxInit) {
            return;
        }
        window.__boraImageLightboxInit = true;

        const lightbox = document.getElementById('image-lightbox');
        const lightboxImg = document.getElementById('image-lightbox-content');
        const closeBtn = lightbox ? lightbox.querySelector('.image-lightbox-close') : null;

        if (!lightbox || !lightboxImg || !closeBtn) {
            return;
        }

        function abrir(src, alt) {
            if (!src) {
                return;
            }
            lightboxImg.src = src;
            lightboxImg.alt = alt || 'Visualizacao ampliada';
            lightbox.classList.add('is-open');
            lightbox.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function fechar() {
            lightbox.classList.remove('is-open');
            lightbox.setAttribute('aria-hidden', 'true');
            lightboxImg.src = '';
            document.body.style.overflow = '';
        }

        document.addEventListener('click', function (event) {
            const img = event.target.closest('.js-zoomable-image');
            if (img) {
                abrir(img.getAttribute('data-full') || img.getAttribute('src'), img.getAttribute('alt'));
                return;
            }

            if (event.target === lightbox || event.target === closeBtn) {
                fechar();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && lightbox.classList.contains('is-open')) {
                fechar();
            }
        });
    })();
</script>
