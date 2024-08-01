# Sistema Integrado de Gestão Empresarial e Projetos

Este é um sistema web completo desenvolvido com PHP e MariaDB, oferecendo funcionalidades abrangentes para cadastro de empresas, gerenciamento de membros, projetos, tarefas e reuniões. O sistema inclui recursos avançados de autenticação, controle de sessão e notificações.

## Funcionalidades Principais

1. **Gestão de Empresas e Membros**:
   - Cadastro detalhado de empresas com validação de CNPJ.
   - Registro de membros com informações pessoais e profissionais.
   - Associação de membros a empresas.

2. **Gerenciamento de Projetos e Tarefas**:
   - Criação e acompanhamento de projetos com datas e status.
   - Sistema de tarefas com níveis de dificuldade e estimativas de tempo.
   - Acompanhamento de progresso das tarefas.

3. **Calendário e Eventos**:
   - Agendamento de reuniões e eventos.
   - Visualização em calendário integrado.

4. **Sistema de Autenticação e Segurança**:
   - Login seguro para empresas e membros.
   - Recuperação de senha via e-mail.
   - Controle de sessões de usuários.

5. **Notificações e Atualizações**:
   - Sistema de notificações para empresas e membros.
   - Acompanhamento de alterações em tarefas e projetos.

## Tecnologias Utilizadas

- **Frontend**:
  - HTML5 e CSS3
  - JavaScript (ES6+)
  - Bootstrap 5
- **Backend**:
  - PHP 8.0+
  - MariaDB 10.5+
- **Ferramentas Adicionais**:
  - Composer para gerenciamento de dependências
  - PHPMailer para envio de e-mails

## Instalação e Configuração

### Pré-requisitos
- Servidor Web (Apache 2.4+ ou Nginx)
- PHP 8.0+
- MariaDB 10.5+

### Passos para Instalação

1. Clone o repositório:
   ```
   git clone https://github.com/seu-usuario/nome-do-projeto.git
   ```

2. Configure o banco de dados:
   - Crie um banco de dados no MariaDB.
   - Importe o schema do SQL fornecido abaixo.
     
3. Acesse o sistema pelo navegador e siga as instruções de configuração inicial.

## SQL para Criação das Tabelas

```sql
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
    FOREIGN KEY (id_empresa) REFERENCES empresa(id_empresa),
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
    FOREIGN KEY (id_empresa) REFERENCES empresa(id_empresa)
);

CREATE TABLE membro_projeto (
    id_membro INT,
    id_projeto INT,
    PRIMARY KEY (id_membro, id_projeto),
    FOREIGN KEY (id_membro) REFERENCES membro(id_membro),
    FOREIGN KEY (id_projeto) REFERENCES projeto(id_projeto)
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
    FOREIGN KEY (id_membro) REFERENCES membro(id_membro),
    FOREIGN KEY (id_projeto) REFERENCES projeto(id_projeto)
);

CREATE TABLE progresso_tarefa (
    id_progresso INT AUTO_INCREMENT PRIMARY KEY,
    id_tarefa INT,
    id_membro INT,
    status_anterior VARCHAR(100) NOT NULL,
    status_novo VARCHAR(100) NOT NULL,
    tempo_gasto INT COMMENT 'Tempo gasto em dias',
    data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_tarefa) REFERENCES tarefa(id_tarefa),
    FOREIGN KEY (id_membro) REFERENCES membro(id_membro)
);

CREATE TABLE sessao_usuario (
    id_sessao INT AUTO_INCREMENT PRIMARY KEY,
    id_membro INT,
    data_inicio DATETIME NOT NULL,
    data_fim DATETIME,
    duracao_segundos INT,
    FOREIGN KEY (id_membro) REFERENCES membro(id_membro)
);

CREATE TABLE calendar_event_master (
  event_id int NOT NULL,
  event_name varchar(255) NOT NULL,
  event_start_time time NOT NULL,
  event_end_time time NOT NULL,
  event_platform varchar(255) NOT NULL,
  event_date date NOT NULL
);

CREATE TABLE notificacao(
    id_notificacao INT AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT,
    nome VARCHAR(100),
    cpf_membro VARCHAR(14),
    FOREIGN KEY (id_empresa) REFERENCES empresa(id_empresa),
    FOREIGN KEY (cpf_membro) REFERENCES membro(cpf)
);

-- Índices para melhorar o desempenho
CREATE INDEX idx_membro_data ON sessao_usuario (id_membro, data_inicio);
CREATE INDEX idx_tarefa_status ON tarefa (status);
CREATE INDEX idx_progresso_tarefa ON progresso_tarefa (id_tarefa, data_atualizacao);

-- Constraint adicional
ALTER TABLE membro
ADD CONSTRAINT unique_cpf UNIQUE (cpf);
```

## Licença

Este projeto está licenciado sob a [MIT License](LICENSE).
