# 🚀 Laravel Sail + Next.js Environment

Ambiente Docker completo com Laravel (backend), Next.js (frontend) e MySQL.

## 📋 Pré-requisitos

- Docker e Docker Compose instalados
- PowerShell (Windows) ou Bash (Linux/Mac)

## 🏃‍♂️ Como usar

### Iniciar o ambiente
```powershell
# Windows
.\start.ps1

# Ou manualmente
docker-compose up -d
```

### Parar o ambiente
```powershell
# Windows
.\stop.ps1

# Ou manualmente
docker-compose down
```

## 🔗 URLs dos serviços

- **Laravel Backend**: http://localhost:8000
- **Next.js Frontend**: http://localhost:3000
- **MySQL Database**: localhost:3306

## ⚙️ Configurações

### Variáveis de ambiente (.env)
As principais variáveis estão configuradas no arquivo `.env` na raiz:

```env
APP_PORT=8000
FORWARD_DB_PORT=3306
DB_DATABASE=projeto_integrador_dev
DB_USERNAME=app_user
DB_PASSWORD=password
```

### Comandos úteis do Laravel

```bash
# Executar migrations
docker-compose exec laravel.test php artisan migrate

# Gerar chave da aplicação
docker-compose exec laravel.test php artisan key:generate

# Acessar shell do container Laravel
docker-compose exec laravel.test bash

# Rodar testes
docker-compose exec laravel.test php artisan test
```

### Comandos úteis do Frontend

```bash
# Acessar shell do container frontend
docker-compose exec frontend bash

# Instalar dependências
docker-compose exec frontend bun install

# Build de produção
docker-compose exec frontend bun run build
```

## 🗄️ Banco de dados

- **Host**: mysql (interno) ou localhost (externo)
- **Porta**: 3306
- **Database**: projeto_integrador_dev
- **Usuário**: app_user
- **Senha**: password

## 🐛 Troubleshooting

### Container não inicia
```bash
# Ver logs
docker-compose logs laravel.test
docker-compose logs frontend
docker-compose logs mysql

# Rebuild containers
docker-compose build --no-cache
```

### Problemas de permissão (Linux/Mac)
```bash
# Ajustar permissões
sudo chown -R $USER:$USER backend/
sudo chown -R $USER:$USER frontend/
```

### Reset completo
```bash
# Parar tudo e limpar
docker-compose down -v
docker system prune -f
docker-compose up -d --build
```

## 📁 Estrutura

```
├── docker-compose.yml        # Configuração principal do Docker
├── .env                     # Variáveis de ambiente
├── start.ps1               # Script para iniciar (Windows)
├── stop.ps1                # Script para parar (Windows)
├── backend/                # Laravel application
│   ├── .env                # Configurações do Laravel
│   └── ...
└── frontend/               # Next.js application
    ├── Dockerfile          # Build do frontend
    └── ...
```

## 🔧 Desenvolvimento

1. **Backend**: Código em `backend/`, usar comandos Artisan via `docker-compose exec`
2. **Frontend**: Código em `frontend/`, hot-reload ativo na porta 3000
3. **Database**: Dados persistidos em volume Docker `sail-mysql`
