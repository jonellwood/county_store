#!/bin/bash

# SanMar Product Scraper - Local Development Server
# This script sets up a local PHP development server for testing

echo "🚀 Starting SanMar Product Scraper Local Development Server..."
echo ""

# Get the current directory
CURRENT_DIR=$(pwd)

# Check if we're in the store directory
if [[ ! -f "admin/pages/tools/product-scraper.php" ]]; then
    echo "❌ Error: Please run this script from the store root directory"
    echo "   Expected: /Users/jonathanellwood/Documents/GitHub/store"
    echo "   Current:  $CURRENT_DIR"
    exit 1
fi

# Create symlink for downloads to be web accessible
echo "📁 Setting up downloads directory..."
DOWNLOADS_DIR="admin/pages/tools/downloads"
if [[ ! -d "$DOWNLOADS_DIR" ]]; then
    mkdir -p "$DOWNLOADS_DIR"
    echo "   ✅ Created downloads directory"
else
    echo "   ✅ Downloads directory exists"
fi

# Set permissions
chmod 755 "$DOWNLOADS_DIR"
echo "   ✅ Set directory permissions"

# Start PHP development server
echo ""
echo "🌐 Starting PHP development server..."
echo "   📍 Local URL: http://localhost:8080"
echo "   📂 Document Root: $CURRENT_DIR"
echo "   🛠️ Product Scraper: http://localhost:8080/admin/pages/tools/product-scraper.php"
echo ""
echo "💡 Tips:"
echo "   • Use Ctrl+C to stop the server"
echo "   • Images will be accessible at http://localhost:8080/admin/pages/tools/downloads/"
echo "   • Server logs will appear below"
echo ""
echo "🎯 Starting server..."

# Start the PHP built-in server
php -S localhost:8080 -t .