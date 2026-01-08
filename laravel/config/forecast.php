<?php

return [
    'api_url' => env('FORECAST_API_URL', 'http://localhost:8001'),
    'timegpt_api_key' => env('TIMEGPT_API_KEY', ''),
    'forecast_horizon' => env('FORECAST_HORIZON', 30),
];

