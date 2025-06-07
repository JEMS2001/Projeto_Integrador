# Start Laravel Sail + Frontend Environment
Write-Host "🚀 Starting Laravel Sail + Frontend..." -ForegroundColor Green

# Check if .env exists in backend
if (-not (Test-Path "backend\.env")) {
    Write-Host "⚠️  Creating .env file for Laravel..." -ForegroundColor Yellow
    Copy-Item "backend\.env.example" "backend\.env"
}

# Start Docker Compose
Write-Host "🐳 Starting Docker containers..." -ForegroundColor Blue
docker-compose up -d

# Wait a bit for containers to start
Start-Sleep -Seconds 5

Write-Host "✅ Environment started!" -ForegroundColor Green
Write-Host "📝 Laravel Backend: http://localhost:8000" -ForegroundColor Cyan
Write-Host "⚛️  Next.js Frontend: http://localhost:3000" -ForegroundColor Cyan
Write-Host "💾 MySQL: localhost:3306" -ForegroundColor Cyan

Write-Host "`n🔧 Next steps:" -ForegroundColor Yellow
Write-Host "1. Run migrations: docker-compose exec laravel.test php artisan migrate" -ForegroundColor White
Write-Host "2. Generate app key: docker-compose exec laravel.test php artisan key:generate" -ForegroundColor White
