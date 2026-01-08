from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from typing import List, Optional
import pandas as pd
from datetime import datetime, timedelta
from nixtlats import TimeGPT
import os

app = FastAPI(title="Sales Forecasting API")

# Initialize TimeGPT client
timegpt = TimeGPT(token=os.getenv("TIMEGPT_API_KEY", ""))


class SalesDataPoint(BaseModel):
    date: str
    value: float
    price: Optional[float] = None
    promo_flag: Optional[int] = None
    stock_available: Optional[int] = None
    holiday_flag: Optional[int] = None
    product_type: Optional[str] = None
    channel: Optional[str] = None


class ForecastRequest(BaseModel):
    sales_data: List[SalesDataPoint]
    forecast_horizon: int = 30
    product_id: Optional[str] = None
    store_id: Optional[str] = None


@app.get("/")
async def root():
    return {"message": "Sales Forecasting API", "status": "running"}


@app.get("/health")
async def health():
    return {"status": "healthy"}


@app.post("/forecast", response_model=ForecastResponse)
async def forecast_sales(request: ForecastRequest):
    try:
        if not request.sales_data or len(request.sales_data) < 2:
            raise HTTPException(
                status_code=400,
                detail="At least 2 data points are required for forecasting"
            )

        # Convert to DataFrame with exogenous variables
        data_rows = []
        for item in request.sales_data:
            row = {
                "ds": item.date,
                "y": item.value
            }
            # Add exogenous variables if available
            if item.price is not None:
                row["price"] = item.price
            if item.promo_flag is not None:
                row["promo_flag"] = item.promo_flag
            if item.stock_available is not None:
                row["stock_available"] = item.stock_available
            if item.holiday_flag is not None:
                row["holiday_flag"] = item.holiday_flag
            if item.product_type is not None:
                row["product_type"] = item.product_type
            if item.channel is not None:
                row["channel"] = item.channel
            data_rows.append(row)
        
        df = pd.DataFrame(data_rows)
        df['ds'] = pd.to_datetime(df['ds'])
        df = df.sort_values('ds')
        df = df.reset_index(drop=True)

        # Prepare exogenous variables for forecast
        # For future dates, we'll use the last known values or averages
        exogenous_vars = []
        if 'price' in df.columns:
            exogenous_vars.append('price')
        if 'promo_flag' in df.columns:
            exogenous_vars.append('promo_flag')
        if 'stock_available' in df.columns:
            exogenous_vars.append('stock_available')
        if 'holiday_flag' in df.columns:
            exogenous_vars.append('holiday_flag')

        # Generate forecast with exogenous variables if available
        forecast_horizon = request.forecast_horizon
        
        try:
            if exogenous_vars:
                # Use exogenous variables for forecasting
                forecast_df = timegpt.forecast(
                    df=df,
                    h=forecast_horizon,
                    freq='D',
                    X_df=df[['ds'] + exogenous_vars] if len(exogenous_vars) > 0 else None
                )
            else:
                # Simple forecast without exogenous variables
                forecast_df = timegpt.forecast(
                    df=df[['ds', 'y']],
                    h=forecast_horizon,
                    freq='D'
                )
        except Exception as e:
            # Fallback to simple forecast if exogenous variables cause issues
            forecast_df = timegpt.forecast(
                df=df[['ds', 'y']],
                h=forecast_horizon,
                freq='D'
            )

        # Format response
        forecast_results = []
        for _, row in forecast_df.iterrows():
            forecast_results.append({
                "date": row['ds'].strftime('%Y-%m-%d'),
                "forecast": float(row['TimeGPT']),
                "lower_bound": float(row.get('TimeGPT-lo-90', row['TimeGPT'] * 0.9)),
                "upper_bound": float(row.get('TimeGPT-hi-90', row['TimeGPT'] * 1.1))
            })

        return ForecastResponse(
            forecast=forecast_results,
            success=True,
            message=f"Forecast generated successfully for {forecast_horizon} days"
        )

    except Exception as e:
        raise HTTPException(
            status_code=500,
            detail=f"Forecasting error: {str(e)}"
        )

