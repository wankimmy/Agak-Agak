# System Architecture

## Overview

The Sales Forecasting Web Application is a full-stack system built with Laravel 10 and a Python microservice for AI-powered forecasting.

## Components

### 1. Laravel Application (PHP 8.2)

**Location**: `laravel/`

**Responsibilities**:
- User authentication and authorization
- CRUD operations for business entities
- CSV data import
- REST API client for forecasting service
- Web UI with Blade templates

**Key Technologies**:
- Laravel 10 Framework
- Laravel Breeze (Authentication)
- Bootstrap 5 (UI Framework)
- DataTables jQuery Plugin
- MySQL Database

### 2. Python Forecasting Service (FastAPI)

**Location**: `python-forecast/`

**Responsibilities**:
- Receive sales data from Laravel
- Generate forecasts using Nixtla TimeGPT
- Return forecast results as JSON

**Key Technologies**:
- FastAPI
- Nixtla TimeGPT SDK
- Pandas (Data processing)

### 3. MySQL Database

**Responsibilities**:
- Store application data
- User sessions
- Cache data

## Data Flow

### Sales Data Entry
```
User Input → Laravel Controller → Validation → Database
```

### CSV Import
```
CSV File → Laravel Controller → Parse CSV → Validate → Database
```

### Forecast Generation
```
User Request → ForecastController → ForecastService → Python API
                                                          ↓
Database ← ForecastService ← JSON Response ← TimeGPT Forecast
```

## Database Schema

### Core Entities

1. **users**: Authentication and user management
2. **locations**: Physical store locations
3. **stores**: Individual store entities (linked to locations)
4. **products**: Product catalog with SKU, pricing
5. **sales**: Daily sales transactions
6. **forecasts**: Generated forecast predictions

### Relationships

```
Location (1) ──→ (N) Store
Store (1) ──→ (N) Sale
Product (1) ──→ (N) Sale
Store (1) ──→ (N) Forecast
Product (1) ──→ (N) Forecast
```

## API Integration

### Laravel → Python Service

**Endpoint**: `POST /forecast`

**Request Format**:
```json
{
  "sales_data": [
    {"date": "2024-01-01", "value": 150.50},
    {"date": "2024-01-02", "value": 200.75}
  ],
  "forecast_horizon": 30
}
```

**Response Format**:
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

## Security

- Authentication: Laravel Breeze (Session-based)
- Password Hashing: bcrypt
- CSRF Protection: Laravel built-in
- Input Validation: Laravel Form Requests
- SQL Injection: Eloquent ORM (Parameterized queries)

## Deployment Architecture

### Docker Compose Services

1. **mysql**: MySQL 8.0 database
2. **laravel**: PHP 8.2 + Laravel application
3. **python-forecast**: Python 3.11 + FastAPI service

### Network

All services communicate via Docker bridge network: `sales_forecast_network`

### Ports

- Laravel: 8000
- Python API: 8001
- MySQL: 3306

## File Structure

```
Sales Prediction System/
├── docker-compose.yml
├── laravel/
│   ├── app/
│   │   ├── Http/Controllers/     # Request handlers
│   │   ├── Models/                # Eloquent models
│   │   └── Services/              # Business logic
│   ├── database/migrations/       # Schema definitions
│   ├── resources/views/           # Blade templates
│   └── routes/                    # Route definitions
├── python-forecast/
│   ├── main.py                    # FastAPI app
│   └── requirements.txt           # Dependencies
└── README.md
```

## Environment Variables

### Laravel (.env)
- `DB_*`: Database connection
- `FORECAST_API_URL`: Python service URL
- `TIMEGPT_API_KEY`: Nixtla API key

### Python Service
- `TIMEGPT_API_KEY`: Nixtla API key

## Best Practices Implemented

1. **MVC Architecture**: Clear separation of concerns
2. **Service Layer**: Business logic in services
3. **RESTful Routes**: Standard HTTP methods
4. **Database Migrations**: Version-controlled schema
5. **Environment Configuration**: Secure credential management
6. **Docker Containerization**: Isolated, reproducible environments
7. **Error Handling**: Try-catch blocks and validation
8. **Code Organization**: PSR-4 autoloading standards

