<?php
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$mensagem = '';
$tipo = '';

// Processar envio do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $assunto = $_POST['assunto'] ?? '';
    $mensagemTexto = $_POST['mensagem'] ?? '';
    
    // Validação simples
    if (empty($nome) || empty($mensagemTexto)) {
        $mensagem = 'Por favor, preencha nome e mensagem.';
        $tipo = 'erro';
    } elseif (empty($email) && empty($telefone)) {
        $mensagem = 'Por favor, informe pelo menos um e-mail ou telefone para contato.';
        $tipo = 'erro';
    } else {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Verificar se o cliente está logado
            $cliente_id = $_SESSION['cliente_id'] ?? null;
            
            // Salvar mensagem no banco
            $sql = "INSERT INTO contatos (cliente_id, nome, email, telefone, assunto, mensagem) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$cliente_id, $nome, $email, $telefone, $assunto, $mensagemTexto]);
            
            $mensagem = 'Mensagem enviada com sucesso! Entraremos em contato em breve.';
            $tipo = 'sucesso';
        } catch (Exception $e) {
            $mensagem = 'Erro ao enviar mensagem. Tente novamente.';
            $tipo = 'erro';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contato - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/public/assets/css/style.css?v=20260316b">
    <style>
        .contato-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .contato-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 30px;
        }
        
        .contato-info {
            background: #fff;
            padding: 30px;
            border-radius: 14px;
            border: 1px solid rgba(74, 144, 226, 0.16);
            box-shadow: 0 12px 24px rgba(74, 144, 226, 0.12);
        }
        
        .contato-info h2 {
            color: #2f5f94;
            margin-bottom: 20px;
            font-size: 28px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-item .icon {
            font-size: 24px;
            margin-right: 15px;
            color: #4A90E2;
        }
        
        .info-item .content h3 {
            color: #333;
            margin-bottom: 5px;
            font-size: 16px;
        }
        
        .info-item .content p {
            color: #666;
            font-size: 14px;
        }
        
        .whatsapp-link {
            transition: all 0.3s ease;
        }
        
        .whatsapp-link:hover {
            color: #128C7E !important;
            transform: scale(1.05);
        }
        
        .instagram-link:hover {
            color: #e1306c !important;
            text-decoration: underline !important;
        }
        
        .contato-form {
            background: #fff;
            padding: 30px;
            border-radius: 14px;
            border: 1px solid rgba(74, 144, 226, 0.16);
            box-shadow: 0 12px 24px rgba(74, 144, 226, 0.12);
        }
        
        .contato-form h2 {
            color: #2f5f94;
            margin-bottom: 20px;
            font-size: 28px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #c7dcf0;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4A90E2;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.15);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .mensagem-feedback {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .mensagem-feedback.sucesso {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .mensagem-feedback.erro {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .btn-enviar {
            background: linear-gradient(135deg, #4A90E2 0%, #2f5f94 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s;
        }
        
        .btn-enviar:hover {
            transform: translateY(-2px);
        }
        
        .mapa-container {
            margin-top: 40px;
            background: #fff;
            padding: 30px;
            border-radius: 14px;
            border: 1px solid rgba(74, 144, 226, 0.16);
            box-shadow: 0 12px 24px rgba(74, 144, 226, 0.12);
        }
        
        .mapa-container h2 {
            color: #2f5f94;
            margin-bottom: 20px;
            font-size: 28px;
        }
        
        @media (max-width: 768px) {
            .contato-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/views/partials/header.php'; ?>
    
    <main class="contato-container">
        <h1 style="font-size: 48px; color: #214870; text-align: center; margin-bottom: 10px;">📞 Fale com o Bora Desapegar</h1>
        <p style="text-align: center; color: #4f6272; font-size: 18px; margin-bottom: 30px;">
            Tire dúvidas, envie sugestões e fale com a gente sobre peças infantis, compra e desapego.
        </p>
        
        <div class="contato-grid">
            <div class="contato-info">
                <h2>Informações de Contato</h2>
                
                <div class="info-item">
                    <div class="icon"><i class="bi bi-whatsapp brand-whatsapp" aria-hidden="true"></i></div>
                    <div class="content">
                        <h3>WhatsApp / Telefone</h3>
                        <p>
                            <a href="https://wa.me/5544998571669?text=Ol%C3%A1!%20Quero%20saber%20mais%20sobre%20as%20pe%C3%A7as%20do%20Bora%20Desapegar." 
                               target="_blank" 
                               class="whatsapp-link"
                               style="color: #25D366; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                                <i class="bi bi-whatsapp brand-icon" aria-hidden="true"></i>
                                (44) 99857-1669
                            </a>
                        </p>
                        <small style="color: #999; display: block; margin-top: 5px;">Clique para conversar no WhatsApp</small>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="icon"><i class="bi bi-instagram brand-instagram" aria-hidden="true"></i></div>
                    <div class="content">
                        <h3>Instagram</h3>
                        <p>
                            <a href="https://www.instagram.com/desapegoinfantil.menino/" 
                               target="_blank"
                               rel="noopener noreferrer"
                               class="instagram-link"
                               style="color: #2f5f94; text-decoration: none; font-weight: 600;">
                                @desapegoinfantil.menino
                            </a>
                        </p>
                        <small style="color: #999; display: block; margin-top: 5px;">Clique para abrir nosso Instagram</small>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="icon">📍</div>
                    <div class="content">
                        <h3>Localização</h3>
                        <p>Campo Mourão - PR</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="icon">🕐</div>
                    <div class="content">
                        <h3>Horário de Atendimento</h3>
                        <p><strong>Segunda a Sexta:</strong> Atendimento online</p>
                        <p><strong>Sábado e Domingo:</strong> Organização de entregas e retiradas</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="icon">🚚</div>
                    <div class="content">
                        <h3>Como Funciona</h3>
                        <p>📦 <strong>Escolha as peças:</strong> Veja disponibilidade no site</p>
                        <p>💬 <strong>Confirme por WhatsApp:</strong> Tire dúvidas e combine detalhes</p>
                        <p style="font-size: 13px; color: #666; margin-top: 8px;">Atendimento humanizado para mães e famílias.</p>
                    </div>
                </div>
            </div>
            
            <div class="contato-form">
                <h2>Envie sua Mensagem</h2>
                
                <?php if ($mensagem): ?>
                <div class="mensagem-feedback <?php echo $tipo; ?>">
                    <?php echo $mensagem; ?>
                </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="nome">Nome Completo *</label>
                        <input type="text" id="nome" name="nome" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email">
                        <small style="color: #666;">* Informe pelo menos e-mail ou telefone</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="telefone">Telefone</label>
                        <input type="tel" id="telefone" name="telefone" placeholder="(44) 99999-9999">
                        <small style="color: #666;">* Informe pelo menos e-mail ou telefone</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="assunto">Assunto</label>
                        <select id="assunto" name="assunto">
                            <option value="">Selecione...</option>
                            <option value="duvida">Dúvida sobre peças</option>
                            <option value="venda">Quero desapegar peças</option>
                            <option value="entrega">Informações de entrega/retirada</option>
                            <option value="sugestao">Sugestão</option>
                            <option value="reclamacao">Reclamação</option>
                            <option value="outro">Outro</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="mensagem">Mensagem *</label>
                        <textarea id="mensagem" name="mensagem" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn-enviar">Enviar Mensagem</button>
                </form>
            </div>
        </div>
        
        <div class="mapa-container">
            <h2>Nossa Localização</h2>
            <p style="color: #666; margin-bottom: 20px;">
                📍 Campo Mourão - PR | Atendemos a região com opções de entrega e retirada mediante combinação.
            </p>
            <div style="border-radius: 10px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d58042.35848799934!2d-52.40888073261718!3d-24.045887999999998!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x94ecf573fe068e31%3A0x5d238bf3c59c51a0!2sCampo%20Mour%C3%A3o%2C%20PR!5e0!3m2!1spt-BR!2sbr!4v1700000000000!5m2!1spt-BR!2sbr" 
                    width="100%" 
                    height="400" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>
    </main>
    
    <?php include __DIR__ . '/views/partials/footer.php'; ?>
    
    <script>
        // Máscara para telefone (44) 99999-9999
        document.getElementById('telefone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não é número
            
            if (value.length <= 11) {
                if (value.length > 2) {
                    value = value.replace(/(\d{2})(\d)/, '($1) $2');
                }
                if (value.length > 7) {
                    value = value.replace(/(\d{2})\) (\d{5})(\d)/, '$1) $2-$3');
                }
            }
            
            e.target.value = value;
        });
    </script>
</body>
</html>
