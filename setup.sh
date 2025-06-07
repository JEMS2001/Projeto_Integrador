#!/bin/bash

# Setup script for Projeto Integrador Docker Environment
echo "🚀 Setting up Projeto Integrador Docker Environment..."

# Check if Docker and Docker Compose are installed
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed. Please install Docker first."
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

# Stop any running containers
echo "🛑 Stopping existing containers..."
docker-compose -f docker-compose-fullstack.yml down

# Remove existing volumes (optional - comment out if you want to keep data)
echo "🗑️ Removing existing volumes..."
docker volume rm projeto_integrador_postgres_data projeto_integrador_backend_storage 2>/dev/null || true

# Create .env file for backend if it doesn't exist
if [ ! -f backend/.env ]; then
    echo "📝 Creating backend .env file..."
    cp backend/.env.example backend/.env
    
    # Generate Laravel app key
    echo "🔑 Generating Laravel application key..."
    docker-compose -f docker-compose-fullstack.yml run --rm backend php artisan key:generate
fi

# Build and start containers
echo "🏗️ Building and starting containers..."
docker-compose -f docker-compose-fullstack.yml up --build -d

# Wait for database to be ready
echo "⏳ Waiting for database to be ready..."
sleep 30

# Run Laravel migrations
echo "🗃️ Running database migrations..."
docker-compose -f docker-compose-fullstack.yml exec backend php artisan migrate --force

# Display container status
echo "📊 Container status:"
docker-compose -f docker-compose-fullstack.yml ps

echo "✅ Setup complete!"
echo ""
echo "🌐 Applications are now running at:"
echo "   Frontend (Next.js): http://localhost:3000"
echo "   Backend (Laravel):  http://localhost:8000"
echo "   Database (PostgreSQL): localhost:5432"
echo ""
echo "📝 To view logs: docker-compose -f docker-compose-fullstack.yml logs -f [service_name]"
echo "🛑 To stop: docker-compose -f docker-compose-fullstack.yml down"
