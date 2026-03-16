-- =====================================================
-- MIGRACAO: fluxo de clientes no banco bora_desapegar
-- Objetivo:
-- 1) garantir tabela clientes (sem obrigatoriedade de endereco)
-- 2) habilitar login por CPF ou telefone
-- 3) garantir tabelas de apoio usadas no fluxo de cliente
-- =====================================================

USE bora_desapegar;

CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NULL,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20) UNIQUE NULL,
    cpf VARCHAR(14) UNIQUE NULL,
    endereco_rua VARCHAR(200) NULL,
    endereco_numero VARCHAR(10) NULL,
    endereco_complemento VARCHAR(100) NULL,
    endereco_bairro VARCHAR(100) NULL,
    endereco_cidade VARCHAR(100) NULL,
    endereco_estado VARCHAR(2) NULL,
    endereco_cep VARCHAR(9) NULL,
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_cpf (cpf),
    INDEX idx_telefone (telefone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ajustes de compatibilidade para bases antigas
ALTER TABLE clientes MODIFY COLUMN email VARCHAR(100) NULL;
ALTER TABLE clientes MODIFY COLUMN telefone VARCHAR(20) NULL;
ALTER TABLE clientes MODIFY COLUMN cpf VARCHAR(14) NULL;
ALTER TABLE clientes MODIFY COLUMN endereco_rua VARCHAR(200) NULL;
ALTER TABLE clientes MODIFY COLUMN endereco_numero VARCHAR(10) NULL;
ALTER TABLE clientes MODIFY COLUMN endereco_bairro VARCHAR(100) NULL;
ALTER TABLE clientes MODIFY COLUMN endereco_cidade VARCHAR(100) NULL;
ALTER TABLE clientes MODIFY COLUMN endereco_estado VARCHAR(2) NULL;

CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_entrega DATE NULL,
    horario_entrega VARCHAR(20) NULL,
    status ENUM('pendente','confirmado','preparando','em_preparo','saiu-entrega','entregue','cancelado') DEFAULT 'pendente',
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
    taxa_entrega DECIMAL(10,2) NOT NULL DEFAULT 0,
    total DECIMAL(10,2) NOT NULL DEFAULT 0,
    forma_pagamento VARCHAR(50) DEFAULT 'dinheiro',
    observacoes TEXT NULL,
    endereco_entrega TEXT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE RESTRICT,
    INDEX idx_cliente (cliente_id),
    INDEX idx_status (status),
    INDEX idx_data_entrega (data_entrega)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pedidos_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    produto_id INT NULL,
    produto_nome VARCHAR(150) NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    INDEX idx_pedido (pedido_id),
    INDEX idx_produto (produto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS contatos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NULL,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(120) NULL,
    telefone VARCHAR(20) NULL,
    assunto VARCHAR(150) NULL,
    mensagem TEXT NOT NULL,
    lido BOOLEAN DEFAULT FALSE,
    data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    INDEX idx_cliente (cliente_id),
    INDEX idx_lido (lido),
    INDEX idx_data_envio (data_envio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
