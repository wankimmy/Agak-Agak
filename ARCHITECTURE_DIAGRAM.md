# System Architecture Diagram

## Text-Based Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         USER INTERFACE                           │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────┐ │
│  │   Landing    │  │   Dashboard  │  │   Excel Upload       │ │
│  │    Page      │  │              │  │   (Strict Contract)  │ │
│  └──────────────┘  └──────────────┘  └──────────────────────┘ │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────┐ │
│  │   Sales      │  │  Forecasts   │  │   CRUD Operations   │ │
│  │   DataTable  │  │  (7/30/90d)  │  │   (Products/Stores) │ │
│  └──────────────┘  └──────────────┘  └──────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    LARAVEL APPLICATION (PHP 8.2)                 │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │                    CONTROLLERS                            │  │
│  │  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐   │  │
│  │  │SaleController│  │ForecastCtrl  │  │ProductCtrl   │   │  │
│  │  │              │  │              │  │StoreCtrl     │   │  │
│  │  └──────────────┘  └──────────────┘  └──────────────┘   │  │
│  └──────────────────────────────────────────────────────────┘  │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │                      SERVICES                             │  │
│  │  ┌──────────────────┐  ┌──────────────────────────────┐ │  │
│  │  │ExcelUploadService │  │    ForecastService          │ │  │
│  │  │                   │  │                             │ │  │
│  │  │• Strict Validation│  │• Data Aggregation           │ │  │
│  │  │• Business Rules   │  │• Exogenous Variables        │ │  │
│  │  │• stock_available  │  │• API Communication         │ │  │
│  │  │  Enforcement      │  │                             │ │  │
│  │  └──────────────────┘  └──────────────────────────────┘ │  │
│  └──────────────────────────────────────────────────────────┘  │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │                      MODELS                              │  │
│  │  Product │ Sale │ Store │ Location │ Forecast │ User    │  │
│  └──────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
                              │
                ┌─────────────┴─────────────┐
                ▼                           ▼
┌──────────────────────────┐    ┌──────────────────────────┐
│      MYSQL DATABASE      │    │  PYTHON FASTAPI SERVICE  │
│                          │    │                          │
│  ┌────────────────────┐  │    │  ┌────────────────────┐ │
│  │   products         │  │    │  │  TimeGPT Client    │ │
│  │   • product_type   │  │    │  │                    │ │
│  │   • is_digital     │  │    │  │  • Forecast Engine  │ │
│  └────────────────────┘  │    │  │  • Exogenous Vars  │ │
│  ┌────────────────────┐  │    │  │  • 7/30/90 Days    │ │
│  │   sales            │  │    │  └────────────────────┘ │
│  │   • quantity_sold  │  │    │                          │
│  │   • revenue        │  │    │  Endpoints:             │
│  │   • stock_available│  │    │  • POST /forecast       │
│  │   • promo_flag     │  │    │  • GET /health          │
│  │   • holiday_flag   │  │    │                          │
│  │   • channel        │  │    │  Request Format:        │
│  │   • location       │  │    │  {                      │
│  └────────────────────┘  │    │    sales_data: [...],   │
│  ┌────────────────────┐  │    │    forecast_horizon: 30 │
│  │   stores           │  │    │  }                      │
│  │   • location       │  │    │                          │
│  └────────────────────┘  │    │  Response Format:       │
│  ┌────────────────────┐  │    │  {                      │
│  │   forecasts        │  │    │    forecast: [...],     │
│  │   • forecast_value │  │    │    success: true        │
│  │   • lower_bound    │  │    │  }                      │
│  │   • upper_bound    │  │    │                          │
│  └────────────────────┘  │    └──────────────────────────┘
└──────────────────────────┘
```

## Data Flow: Excel Upload

```
1. User uploads Excel file
   │
   ▼
2. ExcelUploadService validates:
   • Required columns exist
   • Data types correct
   • Business rules enforced
   • stock_available logic
   │
   ▼
3. Data processed:
   • Products created/updated
   • Stores created/updated
   • Sales records inserted
   │
   ▼
4. Database updated with:
   • Normalized product/store data
   • Sales with all fields
   • Historical location tracking
```

## Data Flow: Forecasting

```
1. User selects:
   • Product (optional)
   • Store (optional)
   • Forecast horizon (7/30/90 days)
   │
   ▼
2. ForecastService aggregates:
   • Historical sales by date
   • Exogenous variables (price, promo, stock, holiday)
   • Product type and channel
   │
   ▼
3. Data sent to Python API:
   POST /forecast
   {
     sales_data: [
       {date, value, price, promo_flag, stock_available, ...}
     ],
     forecast_horizon: 30
   }
   │
   ▼
4. Python TimeGPT processes:
   • Time series analysis
   • Exogenous variable integration
   • Confidence intervals
   │
   ▼
5. Results stored in database:
   • Forecast records
   • Lower/upper bounds
   │
   ▼
6. Displayed to user:
   • DataTables
   • Chart.js visualizations
```

## Business Rules Enforcement

```
┌─────────────────────────────────────────┐
│     stock_available Validation         │
├─────────────────────────────────────────┤
│                                         │
│  IF is_digital = 1                     │
│    THEN stock_available = 1 (forced)   │
│                                         │
│  IF stock_available = 0                │
│    THEN quantity_sold MUST = 0         │
│                                         │
│  stock_available = START of day value  │
│  (NOT end-of-day or after-sales)       │
│                                         │
└─────────────────────────────────────────┘
```

## Docker Architecture

```
┌─────────────────────────────────────────┐
│         Docker Compose Network          │
│                                         │
│  ┌──────────────┐                       │
│  │   Laravel    │  Port: 8000          │
│  │   (PHP 8.2)  │                       │
│  └──────┬───────┘                       │
│         │                                │
│    ┌────┴────┐    ┌──────────────┐     │
│    │         │    │              │     │
│    ▼         ▼    ▼              │     │
│  ┌──────┐  ┌──────────┐  ┌──────────┐ │
│  │MySQL │  │  Python  │  │  Shared  │ │
│  │:3306 │  │ FastAPI  │  │  .env    │ │
│  │      │  │  :8001   │  │  Config  │ │
│  └──────┘  └──────────┘  └──────────┘ │
│                                         │
└─────────────────────────────────────────┘
```

## Component Responsibilities

### Laravel Application
- User authentication (Laravel Breeze)
- Excel file upload and validation
- Business rule enforcement
- Data normalization
- UI rendering (Blade + Bootstrap 5)
- API client for Python service

### Python FastAPI Service
- TimeGPT integration
- Time series forecasting
- Exogenous variable processing
- Confidence interval calculation
- Multiple horizon support (7/30/90 days)

### MySQL Database
- Product catalog
- Store information
- Historical sales data
- Forecast results
- User sessions

## Security & Validation Layers

```
┌─────────────────────────────────────┐
│  1. Client-Side Validation          │
│     (HTML5, JavaScript)            │
└──────────────┬──────────────────────┘
               │
┌──────────────▼──────────────────────┐
│  2. Laravel Form Validation          │
│     (Request Validation Rules)       │
└──────────────┬──────────────────────┘
               │
┌──────────────▼──────────────────────┐
│  3. ExcelUploadService Validation    │
│     (Strict Data Contract)           │
│     (Business Rules)                 │
└──────────────┬──────────────────────┘
               │
┌──────────────▼──────────────────────┐
│  4. Database Constraints              │
│     (Foreign Keys, Data Types)        │
└──────────────────────────────────────┘
```
