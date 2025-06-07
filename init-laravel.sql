-- Initialize database for Laravel refactored project
CREATE DATABASE IF NOT EXISTS projeto_integrador_dev;

-- Create user with proper permissions
CREATE USER IF NOT EXISTS 'app_user'@'%' IDENTIFIED BY 'app_password';
GRANT ALL PRIVILEGES ON projeto_integrador_dev.* TO 'app_user'@'%';

-- Also grant permissions to root user from any host
GRANT ALL PRIVILEGES ON projeto_integrador_dev.* TO 'root'@'%';

FLUSH PRIVILEGES;

-- Switch to the new database
USE projeto_integrador_dev;

-- Basic tables that Laravel will need for authentication and basic functionality
-- Laravel will create additional tables through migrations
