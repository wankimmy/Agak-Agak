# Sales Forecasting Web Application

A comprehensive sales forecasting system built with Laravel 10, Bootstrap 5, and Python FastAPI microservice using Nixtla TimeGPT for forecasting.

## System Architecture

```
┌─────────────────┐
│   Laravel App   │
│  (PHP 8.2)      │
│  Port: 8000     │
└────────┬────────┘
         │
         ├─────────────────┐
         │                 │
┌────────▼────────┐  ┌─────▼──────────┐
│   MySQL 8.0     │  │  Python API    │
│  Port: 3306     │  │  (FastAPI)     │
│                 │  │  Port: 8001    │
└─────────────────┘  └────────────────┘
```

## Features

- **Landing Page**: Beautiful marketing page with free demo (no signup required)
- **User Authentication**: Laravel Breeze authentication system
- **CRUD Operations**: Full CRUD for Products, Stores, Locations, and Sales
- **CSV Upload**: Bulk import sales data via CSV files
- **DataTables Integration**: Searchable, paginated tables for all data
- **Sales Forecasting**: AI-powered forecasting using Nixtla TimeGPT
- **Dashboard**: Overview statistics and recent sales
- **Bootstrap 5 UI**: Modern, responsive interface
- **Privacy-First Demo**: Try forecasting without storing your data

## Prerequisites

- Docker and Docker Compose
- TimeGPT API Key from Nixtla (get it from https://nixtla.io)

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd "Sales Prediction System"
```

### 2. Set Up Environment Variables

#### Root Directory `.env`

Create a `.env` file in the root directory:

```env
TIMEGPT_API_KEY=your_timegpt_api_key_here
```

#### Laravel `.env`

Create `laravel/.env` file (copy from `laravel/.env.example` if available, or use the template below):

```env
APP_NAME="Sales Forecast"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=sales_forecast
DB_USERNAME=laravel
DB_PASSWORD=laravel

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

FORECAST_API_URL=http://python-forecast:8001
TIMEGPT_API_KEY=your_timegpt_api_key_here
FORECAST_HORIZON=30
```

**Note**: See `ENV_CONFIGURATION.md` for complete environment variable reference.

### 3. Start Docker Containers

```bash
docker-compose up -d
```

This will start:
- MySQL database container
- Laravel application container
- Python forecasting service container

### 4. Install Laravel Dependencies

```bash
docker exec -it sales_forecast_laravel composer install
```

### 5. Generate Application Key

```bash
docker exec -it sales_forecast_laravel php artisan key:generate
```

This will automatically update the `APP_KEY` in your `laravel/.env` file.

### 6. Run Migrations

```bash
docker exec -it sales_forecast_laravel php artisan migrate
```

### 7. Create Storage Link (if needed)

```bash
docker exec -it sales_forecast_laravel php artisan storage:link
```

## Usage

### Access the Application

- **Landing Page**: http://localhost:8000 (homepage with demo)
- **Laravel Application**: http://localhost:8000/dashboard (after login)
- **Python API**: http://localhost:8001
- **MySQL**: localhost:3306

### Try the Free Demo

Visit the homepage to try the forecasting demo without signing up:
1. Upload a CSV or Excel file with your sales data
2. Get instant 30-day forecasts with visual charts
3. Your data is processed in real-time and never stored

### Default Credentials

After running migrations, you can register a new user through the registration page.

### CSV Upload Format

When uploading sales data via CSV, use the following format:

```csv
sale_date,product_sku,store_name,price,quantity
2024-01-01,PROD-001,Store A,29.99,5
2024-01-02,PROD-002,Store B,49.99,3
```

**Column Order:**
1. Sale Date (YYYY-MM-DD)
2. Product SKU
3. Store Name
4. Price (decimal)
5. Quantity (integer)

### Generating Forecasts

1. Navigate to **Forecasts** → **Generate Forecast**
2. Optionally select a specific Product or Store
3. Set the forecast horizon (default: 30 days)
4. Click **Generate Forecast**

The system will:
- Collect historical sales data
- Send it to the Python forecasting service
- Use TimeGPT to generate predictions
- Store results in the database
- Display forecasts in tables

## API Integration Example

### Request from Laravel to Python Service

**Endpoint**: `POST http://python-forecast:8001/forecast`

**Request Body**:
```json
{
  "sales_data": [
    {
      "date": "2024-01-01",
      "value": 150.50
    },
    {
      "date": "2024-01-02",
      "value": 200.75
    }
  ],
  "forecast_horizon": 30
}
```

**Response**:
```json
{
  "success": true,
  "message": "Forecast generated successfully",
  "forecast": [
    {
      "date": "2024-01-31",
      "forecast": 175.25,
      "lower_bound": 150.00,
      "upper_bound": 200.50
    }
  ]
}
```

## Project Structure

```
Sales Prediction System/
├── docker-compose.yml          # Docker orchestration
├── laravel/                     # Laravel application
│   ├── app/
│   │   ├── Http/Controllers/   # Controllers
│   │   ├── Models/              # Eloquent models
│   │   └── Services/            # Business logic services
│   ├── database/migrations/     # Database migrations
│   ├── resources/views/          # Blade templates
│   └── routes/                  # Route definitions
├── python-forecast/             # Python FastAPI service
│   ├── main.py                  # FastAPI application
│   ├── requirements.txt         # Python dependencies
│   └── Dockerfile               # Python container config
└── README.md                    # This file
```

## Database Schema

### Tables

- **users**: User authentication
- **locations**: Store locations
- **stores**: Store information
- **products**: Product catalog
- **sales**: Daily sales records
- **forecasts**: Generated forecast data

### Relationships

- Location → Stores (one-to-many)
- Store → Sales (one-to-many)
- Product → Sales (one-to-many)
- Store → Forecasts (one-to-many)
- Product → Forecasts (one-to-many)

## Development

### Running Commands

All Laravel artisan commands should be run inside the container:

```bash
docker exec -it sales_forecast_laravel php artisan <command>
```

### Viewing Logs

```bash
# Laravel logs
docker logs sales_forecast_laravel

# Python service logs
docker logs sales_forecast_python

# MySQL logs
docker logs sales_forecast_mysql
```

### Stopping Containers

```bash
docker-compose down
```

### Rebuilding Containers

```bash
docker-compose up -d --build
```

## Troubleshooting

### Issue: Cannot connect to database

**Solution**: Ensure MySQL container is running and wait a few seconds for it to initialize:
```bash
docker-compose ps
docker-compose restart mysql
```

### Issue: Python API not responding

**Solution**: Check if TimeGPT API key is set correctly:
```bash
docker exec -it sales_forecast_python env | grep TIMEGPT
```

### Issue: Permission errors

**Solution**: Fix Laravel storage permissions:
```bash
docker exec -it sales_forecast_laravel chmod -R 775 storage bootstrap/cache
```

## Technologies Used

- **Backend**: Laravel 10, PHP 8.2
- **Frontend**: Bootstrap 5, jQuery, DataTables
- **Database**: MySQL 8.0
- **Forecasting**: Python 3.11, FastAPI, Nixtla TimeGPT
- **Containerization**: Docker, Docker Compose

## License

MIT License

## Support

For issues or questions, please open an issue in the repository.

