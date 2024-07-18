# Projeto Integrador de Sistema de Cadastro de Empresa e Controle de Reuniões

Este é um projeto de sistema integrador desenvolvido com PHP e MariaDB, que inclui funcionalidades de cadastro de empresa, gerenciamento de membros, projetos e tarefas, bem como um calendário para marcação de reuniões. O sistema também possui funcionalidades de login e recuperação de senha.

## Funcionalidades

1. **Cadastro de Empresa**:
   - Sistema de cadastro de empresas com verificação de CNPJ único.

2. **Gerenciamento de Membros e Projetos**:
   - Cadastro de membros.
   - Criação de projetos.
   - Associação de membros aos projetos.
   - Gerenciamento de tarefas dentro dos projetos.

3. **Sistema de Login e Recuperação de Senha**:
   - Login para empresas e membros.
   - Sistema de recuperação de senha via e-mail.

4. **Calendário de Reuniões**:
   - Marcação de reuniões.
   - Destaque de reuniões no calendário.
   - Notificação por e-mail 24 horas antes das reuniões.

## Tecnologias Utilizadas

- **Frontend**:
  - HTML/CSS
  - JavaScript
  - Bootstrap

- **Backend**:
  - PHP
  - PDO para conexão com o banco de dados

## Estrutura do Projeto

- `config.php`: Arquivo de configuração de conexão com o banco de dados.
- `login.php`: Página de login com recuperação de senha.
- `register.php`: Página de cadastro de empresa e membros.
- `dashboard.php`: Página inicial após o login, com acesso ao calendário de reuniões..
- `assets/`: Diretório para arquivos CSS, JS e imagens.
- `sql/`: Diretório com o script SQL para criação das tabelas no banco de dados.

## Instruções de Instalação

### Pré-requisitos

- Servidor Web (Apache, Nginx, etc.)
- PHP 7.4+
- MariaDB
- Composer (para gerenciamento de dependências PHP)

### Passo a Passo

 **Configurar o Banco de Dados**:
   - Crie um banco de dados no MariaDB.
   - Importe o script `sql/database.sql` para criar as tabelas.
   - Configure as credenciais do banco de dados em `config.php`.

 **Configurar o Servidor Web**:
   - Aponte o diretório raiz do servidor web para o diretório do projeto.
   - Certifique-se de que o servidor web tenha permissão para acessar os arquivos do projeto.

 **Executar o Projeto**:
   - Acesse o sistema através do navegador.

## Scripts SQL

### Criação das Tabelas

```sql
CREATE TABLE empresa (
    id_empresa INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    cnpj VARCHAR(18) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL,
    senha VARCHAR(255) NOT NULL
);

CREATE TABLE membro (
    id_membro INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    senha VARCHAR(255) NOT NULL
);

CREATE TABLE projeto (
    id_projeto INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    id_empresa INT,
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
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    status VARCHAR(50),
    id_projeto INT,
    FOREIGN KEY (id_projeto) REFERENCES projeto(id_projeto)
);

CREATE TABLE reuniao (
    id_reuniao INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    data_hora DATETIME NOT NULL,
    id_empresa INT,
    FOREIGN KEY (id_empresa) REFERENCES empresa(id_empresa)
);
```

Este README fornece uma visão geral abrangente do projeto, incluindo suas funcionalidades, tecnologias utilizadas, estrutura, instruções de instalação, scripts SQL, e diretrizes de contribuição.
