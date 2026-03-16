-- ============================================
-- ALTERAÇÃO: Tornar email e telefone opcionais
-- Permitir que o cliente se cadastre com apenas um dos dois
-- ============================================

-- Remover a restrição NOT NULL do email
ALTER TABLE clientes 
MODIFY COLUMN email VARCHAR(100) UNIQUE NULL;

-- Tornar o telefone mais robusto (caso precise ser único no futuro)
ALTER TABLE clientes 
MODIFY COLUMN telefone VARCHAR(20) NULL;

-- Adicionar constraint para garantir que pelo menos email OU telefone seja fornecido
-- MySQL 8.0+ suporta CHECK constraints
ALTER TABLE clientes
ADD CONSTRAINT chk_contato 
CHECK (email IS NOT NULL OR telefone IS NOT NULL);
