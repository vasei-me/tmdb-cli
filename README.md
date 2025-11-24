## 🎬 TMDB CLI - Movie Database Command Line Tool

A professional PHP command-line interface for The Movie Database (TMDB) API.
![ghtrend demo](assets/demo.png)

````markdown
## 🚀 Quick Start

```bash
# Get popular movies
php tmdb-app --type popular
# Get now playing movies
php tmdb-app --type playing
# Get top rated movies
php tmdb-app --type top
# Get upcoming movies
php tmdb-app --type upcoming
```
````

## 📦 Installation

```bash
# Clone repository
git clone https://github.com/vasei-me/tmdb-cli.git
cd tmdb-cli
# Install dependencies
composer install
# Configure environment (add your TMDB API key)
echo "TMDB_API_KEY=your_api_key_here" > .env
```

## 🎯 Usage Examples

```bash
# Basic movie queries
php tmdb-app --type popular
php tmdb-app --type playing
php tmdb-app --type top
php tmdb-app --type upcoming
# Pagination support
php tmdb-app --type popular --page 2
# Development mode (mock data)
php tmdb-app --type popular --mock
```

## 🛠️ Development

```bash
# Run tests
php tmdb-app --type popular --mock
# Check logs
tail -f logs/app.log
# Clear cache
rm -rf cache/
```
