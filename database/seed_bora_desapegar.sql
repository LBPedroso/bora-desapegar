-- ============================================
-- BORA DESAPEGAR - DADOS INICIAIS
-- ============================================

USE bora_desapegar;

-- Admin padrao
-- Email: admin@boradesapegar.com
-- Senha: admin123
INSERT INTO usuarios_admin (nome, email, senha, ativo)
VALUES ('Administrador', 'admin@boradesapegar.com', '$2y$12$Po38YUrjiLg0jAGm07VCXeut2rR6DUuFxD7/IKFTDXEUXhmG2EePi', 1)
ON DUPLICATE KEY UPDATE email = VALUES(email);

-- Pecas de exemplo
INSERT INTO pecas (nome, categoria, tamanho, preco, foto, observacao, status)
VALUES
('Body Algodao Rosa', 'Body', 'P', 25.00, 'default.jpg', 'Sem manchas, pouco uso', 'disponivel'),
('Conjunto Inverno Azul', 'Conjunto', 'M', 49.90, 'default.jpg', 'Tecido quentinho', 'disponivel')
ON DUPLICATE KEY UPDATE nome = VALUES(nome);
