CREATE DATABASE IF NOT EXISTS projeto;
USE projeto;

CREATE USER IF NOT EXISTS 'jfhk'@'%' IDENTIFIED BY 'jfhk123';
GRANT ALL PRIVILEGES ON meu_banco.* TO 'jfhk'@'%';

FLUSH PRIVILEGES;
CREATE TABLE empresa (
    id_empresa INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cnpj VARCHAR(20) NOT NULL UNIQUE,
    endereco VARCHAR(255),
    email VARCHAR(100) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    imagem VARCHAR(400),
    CONSTRAINT chk_cnpj_empresa CHECK (cnpj REGEXP '^[0-9]{2}\\.[0-9]{3}\\.[0-9]{3}/[0-9]{4}-[0-9]{2}$'),
    CONSTRAINT chk_email_empresa CHECK (email LIKE '%_@__%.__%')
);

CREATE TABLE membro (
    id_membro INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    data_nascimento DATE NOT NULL,
    telefone VARCHAR(20),
    cpf VARCHAR(14) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    id_empresa INT,
    imagem VARCHAR(400),
    esta_logado BOOLEAN DEFAULT FALSE,
    ultimo_login DATETIME,
    FOREIGN KEY (id_empresa) REFERENCES empresa(id_empresa) ON DELETE SET NULL,
    CONSTRAINT chk_cpf_membro CHECK (cpf REGEXP '^[0-9]{3}\\.[0-9]{3}\\.[0-9]{3}-[0-9]{2}$'),
    CONSTRAINT chk_email_membro CHECK (email LIKE '%_@__%.__%')
);

CREATE TABLE projeto (
    id_projeto INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    data_inicio DATE NOT NULL,
    data_fim DATE,
    id_empresa INT,
    banner_path VARCHAR(255),
    status VARCHAR(20) DEFAULT 'no prazo',
    FOREIGN KEY (id_empresa) REFERENCES empresa(id_empresa) ON DELETE CASCADE
);

CREATE TABLE membro_projeto (
    id_membro INT,
    id_projeto INT,
    PRIMARY KEY (id_membro, id_projeto),
    FOREIGN KEY (id_membro) REFERENCES membro(id_membro) ON DELETE CASCADE,
    FOREIGN KEY (id_projeto) REFERENCES projeto(id_projeto) ON DELETE CASCADE
);

CREATE TABLE tarefa (
    id_tarefa INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao VARCHAR(255),
    status VARCHAR(100) NOT NULL,
    nivel_dificuldade ENUM('Fácil', 'Médio', 'Difícil') NOT NULL,
    tempo_estimado INT NOT NULL COMMENT 'Tempo estimado em dias',
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_conclusao DATETIME,
    id_membro INT,
    id_projeto INT,
    FOREIGN KEY (id_membro) REFERENCES membro(id_membro) ON DELETE SET NULL,
    FOREIGN KEY (id_projeto) REFERENCES projeto(id_projeto) ON DELETE CASCADE
);

CREATE TABLE progresso_tarefa (
    id_progresso INT AUTO_INCREMENT PRIMARY KEY,
    id_tarefa INT,
    id_membro INT,
    status_anterior VARCHAR(100) NOT NULL,
    status_novo VARCHAR(100) NOT NULL,
    tempo_gasto INT COMMENT 'Tempo gasto em dias',
    data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_tarefa) REFERENCES tarefa(id_tarefa) ON DELETE CASCADE,
    FOREIGN KEY (id_membro) REFERENCES membro(id_membro) ON DELETE SET NULL
);

CREATE TABLE sessao_usuario (
    id_sessao INT AUTO_INCREMENT PRIMARY KEY,
    id_membro INT,
    data_inicio DATETIME NOT NULL,
    data_fim DATETIME,
    duracao_segundos INT,
    FOREIGN KEY (id_membro) REFERENCES membro(id_membro) ON DELETE CASCADE
);

CREATE INDEX idx_membro_data ON sessao_usuario (id_membro, data_inicio);
CREATE INDEX idx_tarefa_status ON tarefa (status);
CREATE INDEX idx_progresso_tarefa ON progresso_tarefa (id_tarefa, data_atualizacao);

CREATE TABLE calendar_event_master (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(255) NOT NULL,
    event_start_time TIME NOT NULL,
    event_end_time TIME NOT NULL,
    event_platform VARCHAR(255) NOT NULL,
    event_date DATE NOT NULL
);

CREATE TABLE notificacao (
    id_notificacao INT AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT,
    nome VARCHAR(100),
    cpf_membro VARCHAR(14),
    FOREIGN KEY (id_empresa) REFERENCES empresa(id_empresa) ON DELETE CASCADE,
    FOREIGN KEY (cpf_membro) REFERENCES membro(cpf) ON DELETE SET NULL
);
