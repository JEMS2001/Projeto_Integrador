# Setup script for Projeto Integrador Docker Environment
Write-Host "🚀 Setting up Projeto Integrador Docker Environment..." -ForegroundColor Green

# Check if Docker and Docker Compose are installed
try {
    docker --version | Out-Null
} catch {
    Write-Host "❌ Docker is not installed. Please install Docker first." -ForegroundColor Red
    exit 1
}

try {
    docker-compose --version | Out-Null
} catch {
    Write-Host "❌ Docker Compose is not installed. Please install Docker Compose first." -ForegroundColor Red
    exit 1
}

# Stop any running containers
Write-Host "🛑 Stopping existing containers..." -ForegroundColor Yellow
docker-compose -f docker-compose-fullstack.yml down

# Remove existing volumes (optional - comment out if you want to keep data)
Write-Host "🗑️ Removing existing volumes..." -ForegroundColor Yellow
docker volume rm projeto_integrador_postgres_data projeto_integrador_backend_storage 2>$null

# Create .env file for backend if it doesn't exist
if (!(Test-Path "backend\.env")) {
    Write-Host "📝 Creating backend .env file..." -ForegroundColor Blue
    Copy-Item "backend\.env.example" "backend\.env"
    
    # Generate Laravel app key
    Write-Host "🔑 Generating Laravel application key..." -ForegroundColor Blue
    docker-compose -f docker-compose-fullstack.yml run --rm backend php artisan key:generate
}

# Build and start containers
Write-Host "🏗️ Building and starting containers..." -ForegroundColor Blue
docker-compose -f docker-compose-fullstack.yml up --build -d

# Wait for database to be ready
Write-Host "⏳ Waiting for database to be ready..." -ForegroundColor Yellow
Start-Sleep -Seconds 30

# Run Laravel migrations
Write-Host "🗃️ Running database migrations..." -ForegroundColor Blue
docker-compose -f docker-compose-fullstack.yml exec backend php artisan migrate --force

# Display container status
Write-Host "📊 Container status:" -ForegroundColor Green
docker-compose -f docker-compose-fullstack.yml ps

Write-Host "✅ Setup complete!" -ForegroundColor Green
Write-Host ""
Write-Host "🌐 Applications are now running at:" -ForegroundColor Cyan
Write-Host "   Frontend (Next.js): http://localhost:3000" -ForegroundColor White
Write-Host "   Backend (Laravel):  http://localhost:8000" -ForegroundColor White
Write-Host "   Database (PostgreSQL): localhost:5432" -ForegroundColor White
Write-Host ""
Write-Host "📝 To view logs: docker-compose -f docker-compose-fullstack.yml logs -f [service_name]" -ForegroundColor Yellow
Write-Host "🛑 To stop: docker-compose -f docker-compose-fullstack.yml down" -ForegroundColor Yellow
