CREATE DATABASE IF NOT EXISTS logistica CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE logistica;

CREATE TABLE produtos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  descricao VARCHAR(255),
  peso DECIMAL(10,2) NOT NULL DEFAULT 0,
  valor DECIMAL(10,2) NOT NULL DEFAULT 0
);

CREATE TABLE enderecos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  apelido VARCHAR(60) NOT NULL,
  cep VARCHAR(9),
  logradouro VARCHAR(120) NOT NULL,
  numero VARCHAR(10),
  bairro VARCHAR(80),
  cidade VARCHAR(80) NOT NULL,
  uf CHAR(2) NOT NULL
);

CREATE TABLE transportadores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  documento VARCHAR(18),
  telefone VARCHAR(20),
  veiculo VARCHAR(60)
);

CREATE TABLE fretes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  transportador_id INT NOT NULL,
  origem_id INT NOT NULL,
  destino_id INT NOT NULL,
  data_frete DATE NOT NULL,
  valor_frete DECIMAL(10,2) NOT NULL DEFAULT 0,
  observacao VARCHAR(255),
  FOREIGN KEY (transportador_id) REFERENCES transportadores(id),
  FOREIGN KEY (origem_id) REFERENCES enderecos(id),
  FOREIGN KEY (destino_id) REFERENCES enderecos(id)
);

-- Um frete tem um transportador e vários produtos (1 para muitos)
CREATE TABLE frete_produtos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  frete_id INT NOT NULL,
  produto_id INT NOT NULL,
  quantidade INT NOT NULL DEFAULT 1,
  FOREIGN KEY (frete_id) REFERENCES fretes(id) ON DELETE CASCADE,
  FOREIGN KEY (produto_id) REFERENCES produtos(id)
);
