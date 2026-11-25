DROP DATABASE IF EXISTS rosangela_bolos;

CREATE DATABASE IF NOT EXISTS rosangela_bolos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE rosangela_bolos;

CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    categoria VARCHAR(50),
    preco DECIMAL(10,2) NOT NULL,
    quantidade INT NOT NULL DEFAULT 0,
    quantidade_minima INT NOT NULL DEFAULT 5,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_produtos_preco CHECK (preco > 0)
);

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    login VARCHAR(50) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE vendas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL,
    valor_total DECIMAL(10,2) NOT NULL,
    data_venda TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE RESTRICT,
    CONSTRAINT chk_vendas_valor CHECK (valor_total > 0),
    CONSTRAINT chk_vendas_qtd CHECK (quantidade > 0)
);

INSERT INTO produtos (nome, categoria, preco, quantidade, quantidade_minima) VALUES
('Bolo de Chocolate', 'Bolos', 45.00, 10, 3),
('Bolo de Cenoura', 'Bolos', 40.00, 6, 3),
('Brigadeiro (cento)', 'Doces', 60.00, 2, 2),
('Torta de Limão', 'Tortas', 55.00, 4, 2);

