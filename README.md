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
- **Excel Upload with Strict Data Contract**: Bulk import sales data via Excel (.xlsx) files with comprehensive validation
- **Business Rules Enforcement**: Automatic enforcement of critical business rules (stock_available logic)
- **DataTables Integration**: Server-side searchable, paginated tables for all data
- **Advanced Sales Forecasting**: AI-powered forecasting using Nixtla TimeGPT with exogenous variables
- **Multiple Forecast Horizons**: Generate forecasts for 7, 30, or 90 days
- **Dashboard**: Overview statistics and recent sales
- **Bootstrap 5 UI**: Modern, responsive interface with sidebar navigation
- **Privacy-First Demo**: Try forecasting without storing your data
- **Excel Template Download**: Pre-formatted Excel template for easy data entry

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

This will install:
- Laravel framework and dependencies
- PhpSpreadsheet (for Excel file processing)
- All other required packages

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

### Excel Template Download

Before uploading your data, download the Excel template:
1. Navigate to **Sales** → **Upload Excel**
2. Click **Download Excel Template**
3. The template includes:
   - All required columns as headers
   - Sample data row
   - Pre-formatted structure
   - Column explanations

### Sample Data File

A complete sample CSV file is provided: `sample_sales_data.csv`

**Features of the sample:**
- 31 days of sample sales data
- Multiple products (books, food, stationery, digital)
- Multiple stores and locations
- Demonstrates all business rules:
  - Out of stock scenario (stock_available = 0, quantity_sold = 0)
  - Digital products (auto stock_available = 1)
  - Promotions (promo_flag, discount_pct)
  - Holidays (holiday_flag)
  - Different channels (retail, online)

**To use:**
1. Open `sample_sales_data.csv` in Excel
2. Save as `.xlsx` format
3. Upload to test the system

See `SAMPLE_EXCEL_GUIDE.md` for detailed examples and explanations.

### Default Credentials

After running migrations, you can register a new user through the registration page.

### Excel Upload Format (STRICT DATA CONTRACT)

The system uses a strict data contract for Excel uploads. Download the template from the upload page for the correct format.

#### Required Columns

Your Excel file must contain these columns in the first row:

1. **date** - Format: YYYY-MM-DD
2. **product_id** - Unique product identifier
3. **product_name** - Product name
4. **product_type** - e.g., books, food, stationery, digital
5. **is_digital** - 0 (physical) or 1 (digital)
6. **store_id** - Store identifier
7. **location** - Store location
8. **price** - Unit price (numeric)
9. **quantity_sold** - Quantity sold (integer, >= 0)
10. **revenue** - Total revenue (auto-calculated if missing)

#### Optional Forecast-Enhancing Columns

- **promo_flag** - 0 or 1
- **discount_pct** - Discount percentage (0-100)
- **stock_available** - See critical business rule below ⚠️
- **holiday_flag** - 0 or 1
- **channel** - online, retail, or marketplace

#### ⚠️ CRITICAL: stock_available Business Rule

**stock_available represents whether the product was available for sale at the START of the day (before any sales occurred).**

- **1** = Product was available for customers to buy at the start of the day
- **0** = Product was unavailable/out of stock at the start of the day
- **❌ DO NOT** use end-of-day or after-sales stock values
- **✅ For digital products** (is_digital = 1), stock_available is automatically set to 1
- **Business Rule**: If stock_available = 0, then quantity_sold MUST be 0

#### Validation Rules

- All required columns must exist
- Date must be valid and in YYYY-MM-DD format
- Numeric columns must be numeric
- quantity_sold must be >= 0
- If stock_available = 0, quantity_sold must be 0
- Revenue is auto-calculated (price × quantity_sold) if missing
- Data leakage prevention: Future dates and unrealistic date ranges are rejected

#### Example Excel Row

| date | product_id | product_name | product_type | is_digital | store_id | location | price | quantity_sold | revenue | stock_available | promo_flag | discount_pct | holiday_flag | channel |
|------|------------|--------------|--------------|------------|----------|----------|-------|---------------|---------|-----------------|------------|--------------|--------------|---------|
| 2024-01-15 | PROD-001 | Sample Book | books | 0 | STORE-001 | New York | 29.99 | 5 | 149.95 | 1 | 0 | | 0 | retail |

### Generating Forecasts

1. Navigate to **Forecasts** → **Generate Forecast**
2. Optionally select a specific Product or Store
3. Select forecast horizon:
   - **7 Days**: Short-term planning, inventory management
   - **30 Days**: Medium-term planning, monthly forecasts (default)
   - **90 Days**: Long-term planning, quarterly forecasts
4. Click **Generate Forecast**

The system will:
- Collect historical sales data with exogenous variables (price, promotions, stock availability, holidays)
- Aggregate data by date (grouping product/store combinations)
- Send enriched data to the Python forecasting service
- Use TimeGPT with exogenous variables to generate accurate predictions
- Calculate confidence intervals (lower and upper bounds)
- Store results in the database
- Display forecasts in DataTables and Chart.js visualizations

## API Integration Example

### Request from Laravel to Python Service

**Endpoint**: `POST http://python-forecast:8001/forecast`

**Request Body** (with exogenous variables):
```json
{
  "sales_data": [
    {
      "date": "2024-01-01",
      "value": 1500.50,
      "price": 29.99,
      "promo_flag": 0,
      "stock_available": 1,
      "holiday_flag": 0,
      "product_type": "books",
      "channel": "retail"
    },
    {
      "date": "2024-01-02",
      "value": 2000.75,
      "price": 29.99,
      "promo_flag": 1,
      "stock_available": 1,
      "holiday_flag": 0,
      "product_type": "books",
      "channel": "retail"
    }
  ],
  "forecast_horizon": 30,
  "product_id": "PROD-001",
  "store_id": "STORE-001"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Forecast generated successfully for 30 days",
  "forecast": [
    {
      "date": "2024-01-31",
      "forecast": 1750.25,
      "lower_bound": 1500.00,
      "upper_bound": 2000.50
    },
    {
      "date": "2024-02-01",
      "forecast": 1800.00,
      "lower_bound": 1550.00,
      "upper_bound": 2050.00
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
- **stores**: Store information (includes location field)
- **products**: Product catalog (includes product_type, is_digital)
- **sales**: Daily sales records with enhanced fields
- **forecasts**: Generated forecast data with confidence intervals

### Enhanced Sales Table Fields

- `quantity_sold` - Quantity sold (renamed from quantity)
- `revenue` - Total revenue (renamed from total_amount)
- `stock_available` - Stock availability at START of day (critical business rule)
- `promo_flag` - Promotion indicator (0/1)
- `discount_pct` - Discount percentage (0-100)
- `holiday_flag` - Holiday indicator (0/1)
- `channel` - Sales channel (online, retail, marketplace)
- `location` - Store location (stored for historical integrity)

### Enhanced Products Table Fields

- `product_type` - Product category (books, food, stationery, digital, etc.)
- `is_digital` - Digital product flag (0 = physical, 1 = digital)

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

### Issue: Excel upload validation errors

**Solution**: 
- Ensure all required columns are present in the first row
- Check that dates are in YYYY-MM-DD format
- Verify stock_available business rule: if 0, quantity_sold must be 0
- For digital products, stock_available is automatically set to 1
- Download and use the Excel template for correct format

### Issue: PhpSpreadsheet not found

**Solution**: Install the package:
```bash
docker exec -it sales_forecast_laravel composer require phpoffice/phpspreadsheet
```

## Technologies Used

- **Backend**: Laravel 10, PHP 8.2
- **Frontend**: Bootstrap 5, jQuery, DataTables, Chart.js
- **Database**: MySQL 8.0
- **Forecasting**: Python 3.11, FastAPI, Nixtla TimeGPT
- **Excel Processing**: PhpSpreadsheet 1.29
- **Containerization**: Docker, Docker Compose

## Key Business Rules

### stock_available Definition

This is a **critical business rule** that must be understood:

- **stock_available** represents whether the product was available for sale at the **START of the day** (before any sales occurred)
- **1** = Product was available for customers to buy at the start of the day
- **0** = Product was unavailable/out of stock at the start of the day
- **❌ DO NOT** use end-of-day or after-sales stock values
- **✅ For digital products** (is_digital = 1), stock_available is automatically set to 1
- **Enforcement**: If stock_available = 0, then quantity_sold MUST be 0 (you can't sell what's not available)

### Data Validation

The system enforces strict validation:
- All required columns must exist in Excel uploads
- Date format must be YYYY-MM-DD
- Numeric fields must be valid numbers
- Business rules are enforced server-side
- Data leakage prevention (no future dates, realistic ranges)

## Forecast Horizons

The system supports three forecast horizons:

- **7 Days**: Short-term planning, inventory management, weekly operations
- **30 Days**: Medium-term planning, monthly forecasts, resource allocation
- **90 Days**: Long-term planning, quarterly forecasts, strategic planning

Each forecast includes:
- Mean forecast value
- Lower bound (90% confidence interval)
- Upper bound (90% confidence interval)
- Visual charts and DataTables display

## License

MIT License

## Important Notes

### Data Contract Compliance

This system uses a **strict data contract** for Excel uploads. Please ensure:

1. **Download the template** before creating your Excel file
2. **Follow column order** exactly as specified
3. **Understand stock_available** - it represents START of day availability, not end-of-day
4. **Validate your data** before uploading to avoid errors
5. **Check error messages** - they provide specific row-level feedback

### Forecasting Best Practices

- **More historical data = Better forecasts**: Provide at least 30 days of data for accurate predictions
- **Include exogenous variables**: Promotions, holidays, and stock availability improve accuracy
- **Choose appropriate horizon**: 
  - 7 days for operational decisions
  - 30 days for monthly planning
  - 90 days for strategic planning
- **Review confidence intervals**: Upper and lower bounds indicate forecast uncertainty

## Support

For issues or questions, please open an issue in the repository.

## Additional Documentation

- See `ARCHITECTURE_DIAGRAM.md` for detailed system architecture
- See `QUICKSTART.md` for quick setup guide
- See `SAMPLE_EXCEL_GUIDE.md` for sample data examples and usage