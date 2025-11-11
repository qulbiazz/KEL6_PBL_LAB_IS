#!/bin/bash

# Build script for deployment platforms
echo "Starting build process..."

# Create necessary directories
mkdir -p uploads/banners
mkdir -p uploads/products
mkdir -p uploads/partners
mkdir -p uploads/team
mkdir -p uploads/news
mkdir -p uploads/gallery
mkdir -p uploads/research

# Set permissions
chmod -R 755 uploads

# Copy .env.example to .env if it doesn't exist
if [ ! -f .env ]; then
    echo "Creating .env file from .env.example..."
    cp .env.example .env
fi

echo "Build completed successfully!"
