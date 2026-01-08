# Quick Start Guide

## Prerequisites Checklist

- [ ] Docker Desktop installed and running
- [ ] TimeGPT API key from Nixtla (https://nixtla.io)

## 5-Minute Setup

### Step 1: Set Environment Variables

#### Root Directory `.env`

Create a `.env` file in the root directory:

```env
TIMEGPT_API_KEY=your_api_key_here
```

#### Laravel `.env`

Create `laravel/.env` file with minimal configuration:

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
TIMEGPT_API_KEY=your_api_key_here
FORECAST_HORIZON=30
```

**Tip**: See `ENV_CONFIGURATION.md` for complete configuration options.

### Step 2: Start Services

```bash
docker-compose up -d
```

Wait 30 seconds for MySQL to initialize.

### Step 3: Install Laravel Dependencies

```bash
docker exec -it sales_forecast_laravel composer install
```

### Step 4: Configure Laravel

```bash
# Generate app key
docker exec -it sales_forecast_laravel php artisan key:generate

# Run migrations
docker exec -it sales_forecast_laravel php artisan migrate
```

### Step 5: Access Application

Open your browser: **http://localhost:8000**

Register a new account and start using the system!

## First Steps After Login

1. **Create a Location**
   - Go to Locations → Add New Location
   - Enter location details

2. **Create a Store**
   - Go to Stores → Add New Store
   - Link it to a location

3. **Add Products**
   - Go to Products → Add New Product
   - Enter product details with SKU

4. **Record Sales**
   - Go to Sales → Add New Sale
   - Or upload CSV file

5. **Generate Forecast**
   - Go to Forecasts → Generate Forecast
   - Select options and generate

## CSV Upload Format

Save your sales data as CSV with this format:

```csv
sale_date,product_sku,store_name,price,quantity
2024-01-01,PROD-001,Store A,29.99,5
2024-01-02,PROD-002,Store B,49.99,3
```

**Important**: 
- Product SKU must exist in Products
- Store Name must match exactly
- Date format: YYYY-MM-DD

## Troubleshooting

### Can't connect to database?
```bash
docker-compose restart mysql
# Wait 10 seconds, then try again
```

### Python service not working?
```bash
# Check if API key is set
docker exec -it sales_forecast_python env | grep TIMEGPT

# View logs
docker logs sales_forecast_python
```

### Laravel errors?
```bash
# Clear cache
docker exec -it sales_forecast_laravel php artisan cache:clear
docker exec -it sales_forecast_laravel php artisan config:clear
```

## Stopping the Application

```bash
docker-compose down
```

## Restarting Everything

```bash
docker-compose down
docker-compose up -d
docker exec -it sales_forecast_laravel php artisan migrate:fresh
```

## Need Help?

- Check `README.md` for detailed documentation
- Check `ARCHITECTURE.md` for system design
- View logs: `docker logs <container_name>`

