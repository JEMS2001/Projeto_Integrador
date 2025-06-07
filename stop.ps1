# Stop Laravel Sail + Frontend Environment
Write-Host "🛑 Stopping Laravel Sail + Frontend..." -ForegroundColor Red

# Stop Docker Compose
docker-compose down

Write-Host "✅ Environment stopped!" -ForegroundColor Green
