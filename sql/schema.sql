-- charset utf8mb4 / InnoDB
SET NAMES utf8mb4;
SET time_zone = '-03:00';

CREATE DATABASE IF NOT EXISTS impressao3d
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE impressao3d;

CREATE TABLE admins (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  senha_hash VARCHAR(255) NOT NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE clientes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome_completo VARCHAR(160) NOT NULL,
  nome_normalizado VARCHAR(160) NOT NULL UNIQUE,
  telefone_opcional VARCHAR(30) NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE pedidos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(20) NOT NULL UNIQUE,
  cliente_id INT UNSIGNED NOT NULL,
  descricao TEXT NOT NULL,
  valor_total DECIMAL(10,2) NOT NULL,
  chave_pix VARCHAR(255) NOT NULL,
  status ENUM(
    'aguardando_pagamento','comprovante_enviado','em_revisao',
    'pago','em_producao','finalizado','publicado','cancelado'
  ) NOT NULL DEFAULT 'aguardando_pagamento',
  pago_em DATETIME NULL,
  finalizado_em DATETIME NULL,
  publicado_em DATETIME NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE RESTRICT,
  INDEX idx_status (status),
  INDEX idx_cliente (cliente_id)
) ENGINE=InnoDB;

CREATE TABLE comprovantes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pedido_id INT UNSIGNED NOT NULL,
  arquivo VARCHAR(255) NOT NULL,
  enviado_por ENUM('cliente','admin') NOT NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
  INDEX idx_pedido (pedido_id)
) ENGINE=InnoDB;

CREATE TABLE pagamentos_extrato (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pedido_id INT UNSIGNED NOT NULL,
  valor DECIMAL(10,2) NOT NULL,
  confirmado_por_admin_id INT UNSIGNED NOT NULL,
  confirmado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
  FOREIGN KEY (confirmado_por_admin_id) REFERENCES admins(id),
  UNIQUE KEY uq_pedido (pedido_id),
  INDEX idx_confirmado_em (confirmado_em)
) ENGINE=InnoDB;

CREATE TABLE projetos_publicados (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pedido_id INT UNSIGNED NOT NULL UNIQUE,
  descricao TEXT NOT NULL,
  publicado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE projeto_midias (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  projeto_id INT UNSIGNED NOT NULL,
  tipo ENUM('foto','video') NOT NULL,
  arquivo VARCHAR(255) NOT NULL,
  ordem INT NOT NULL DEFAULT 0,
  FOREIGN KEY (projeto_id) REFERENCES projetos_publicados(id) ON DELETE CASCADE,
  INDEX idx_projeto (projeto_id)
) ENGINE=InnoDB;

CREATE TABLE contatos_whatsapp (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT UNSIGNED NOT NULL,
  dono_escolhido VARCHAR(80) NOT NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
) ENGINE=InnoDB;