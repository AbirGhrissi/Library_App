#!/bin/bash

echo "🚀 Setting up Library Management System..."

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}Setting up Backend...${NC}"
cd backend

# Check if vendor exists
if [ ! -d "vendor" ]; then
    echo "Installing Composer dependencies..."
    composer install
fi

# Check if database exists and create if not
echo "Setting up database..."
php bin/console doctrine:database:create --if-not-exists 2>/dev/null || echo "Database already exists"

# Run migrations
echo "Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

echo -e "${GREEN}✓ Backend setup complete!${NC}"
echo ""

cd ..

echo -e "${YELLOW}Setting up Frontend...${NC}"
cd frontend

# Check if node_modules exists
if [ ! -d "node_modules" ]; then
    echo "Installing NPM dependencies..."
    npm install
fi

echo -e "${GREEN}✓ Frontend setup complete!${NC}"
echo ""

cd ..

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Setup Complete! 🎉${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo "To start the application:"
echo ""
echo "1. Start the backend (in backend directory):"
echo "   cd backend"
echo "   symfony server:start"
echo "   OR"
echo "   php -S localhost:8000 -t public"
echo ""
echo "2. Start the frontend (in frontend directory):"
echo "   cd frontend"
echo "   npm run dev"
echo ""
echo "Then visit:"
echo "  - Frontend: http://localhost:3000"
echo "  - Backend API: http://localhost:8000/api"
echo "  - Admin Panel: http://localhost:8000/admin"
echo "  - API Docs: http://localhost:8000/api/docs"
