#!/bin/bash

# Zyra Video Conferencing - Development Script
# This script helps with development and testing

echo "🎥 Zyra Video Conferencing - Development Setup"
echo "=============================================="

# Check if we're in the right directory
if [ ! -f "index.php" ]; then
    echo "❌ Error: Please run this script from the Zyra project root directory"
    exit 1
fi

# Create necessary directories
echo "📁 Creating necessary directories..."
mkdir -p logs
mkdir -p assets/css
mkdir -p assets/js
mkdir -p api

# Set permissions
echo "🔐 Setting permissions..."
chmod 755 logs
chmod 644 *.php
chmod 644 assets/css/*.css
chmod 644 assets/js/*.js
chmod 644 api/*.php

# Check PHP version
echo "🐘 Checking PHP version..."
php_version=$(php -v | head -n 1 | cut -d " " -f 2 | cut -d "." -f 1,2)
echo "PHP Version: $php_version"

if (( $(echo "$php_version < 7.4" | bc -l) )); then
    echo "⚠️  Warning: PHP 7.4+ is recommended for best performance"
else
    echo "✅ PHP version is compatible"
fi

# Check if web server is running
echo "🌐 Checking web server..."
if curl -s http://localhost/Zyra/ > /dev/null 2>&1; then
    echo "✅ Web server is running"
    echo "🔗 Application URL: http://localhost/Zyra/"
elif curl -s http://localhost:8080/Zyra/ > /dev/null 2>&1; then
    echo "✅ Web server is running on port 8080"
    echo "🔗 Application URL: http://localhost:8080/Zyra/"
else
    echo "⚠️  Web server not detected. Please start your web server (Apache, Nginx, or XAMPP)"
    echo "   Then access: http://localhost/Zyra/"
fi

# Start Tailwind CSS watcher if available
echo "🎨 Starting Tailwind CSS watcher..."
if command -v npx &> /dev/null; then
    echo "Starting Tailwind CSS watcher in background..."
    npx tailwindcss -i assets/css/style.css -o assets/css/output.css --watch &
    echo "✅ Tailwind CSS watcher started"
else
    echo "⚠️  npx not found. Install Node.js to use Tailwind CSS watcher"
fi

echo ""
echo "🚀 Development setup complete!"
echo ""
echo "Next steps:"
echo "1. Open your browser and go to http://localhost/Zyra/"
echo "2. Test creating and joining meetings"
echo "3. Check browser console for any errors"
echo "4. Modify files as needed - changes will be reflected immediately"
echo ""
echo "Press Ctrl+C to stop the Tailwind watcher when done"
