#!/bin/bash
# Post-pull script untuk rebuild assets

echo "🔄 Rebuilding assets after pull..."

# Check if node_modules exists
if [ ! -d "node_modules" ]; then
    echo "📦 Installing npm dependencies..."
    npm install
fi

# Rebuild Tailwind CSS
echo "🎨 Building Tailwind CSS..."
npm run build:css

# Check if composer vendor exists
if [ ! -d "vendor" ]; then
    echo "📦 Installing composer dependencies..."
    composer install --no-dev --ignore-platform-reqs
fi

echo "✅ Assets rebuilt successfully!"
echo "💡 Tip: Run this script after every git pull"
