<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AI-Powered Sales Forecasting | Predict Your Future Revenue</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .hero-section {
            background: var(--gradient);
            color: white;
            padding: 100px 0;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
            opacity: 0.3;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
        }
        
        .feature-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
        }
        
        .demo-section {
            background: #f8f9fa;
            padding: 80px 0;
        }
        
        .upload-area {
            border: 3px dashed #0d6efd;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            background: white;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .upload-area:hover {
            border-color: #0a58ca;
            background: #f0f7ff;
        }
        
        .upload-area.dragover {
            border-color: #198754;
            background: #f0fff4;
        }
        
        .benefit-item {
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid var(--primary-color);
            background: white;
            border-radius: 5px;
        }
        
        .cta-button {
            padding: 15px 40px;
            font-size: 1.2rem;
            border-radius: 50px;
            font-weight: 600;
        }
        
        .stats-section {
            background: var(--primary-color);
            color: white;
            padding: 60px 0;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: bold;
            display: block;
        }
        
        .disclaimer {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 20px;
            border-radius: 5px;
            margin-top: 30px;
        }
        
        .chart-container {
            position: relative;
            height: 400px;
            margin-top: 30px;
        }
        
        .loading-spinner {
            display: none;
        }
        
        .loading-spinner.active {
            display: block;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="fas fa-chart-line me-2"></i>Sales Forecast AI
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#benefits">Benefits</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#demo">Try Demo</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary text-white ms-2 px-4" href="{{ route('register') }}">Get Started</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container hero-content">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Predict Your Sales with AI-Powered Forecasting</h1>
                    <p class="lead mb-4">Transform your business decisions with accurate, data-driven sales predictions. Our advanced AI technology analyzes your historical data to forecast future sales with precision.</p>
                    <div class="d-flex gap-3">
                        <a href="#demo" class="btn btn-light cta-button">
                            <i class="fas fa-play-circle me-2"></i>Try Free Demo
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-outline-light cta-button">
                            <i class="fas fa-rocket me-2"></i>Get Started
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <i class="fas fa-chart-line" style="font-size: 300px; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-3 stat-item">
                    <span class="stat-number">95%</span>
                    <p class="mb-0">Forecast Accuracy</p>
                </div>
                <div class="col-md-3 stat-item">
                    <span class="stat-number">30</span>
                    <p class="mb-0">Days Ahead</p>
                </div>
                <div class="col-md-3 stat-item">
                    <span class="stat-number">24/7</span>
                    <p class="mb-0">AI Processing</p>
                </div>
                <div class="col-md-3 stat-item">
                    <span class="stat-number">100%</span>
                    <p class="mb-0">Data Privacy</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features / USPs Section -->
    <section id="features" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Why Choose Our Sales Forecasting Platform?</h2>
                <p class="lead text-muted">Three powerful reasons that set us apart</p>
            </div>
            <div class="row g-4">
                <!-- USP 1 -->
                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon bg-primary text-white">
                                <i class="fas fa-brain"></i>
                            </div>
                            <h3 class="card-title fw-bold">AI-Powered by TimeGPT</h3>
                            <p class="card-text text-muted">
                                Leverage cutting-edge TimeGPT technology from Nixtla, one of the most advanced time series forecasting models. Our AI learns from your data patterns to deliver highly accurate predictions that adapt to your business trends.
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- USP 2 -->
                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon bg-success text-white">
                                <i class="fas fa-bolt"></i>
                            </div>
                            <h3 class="card-title fw-bold">Instant Insights</h3>
                            <p class="card-text text-muted">
                                Get comprehensive sales forecasts in seconds, not days. Upload your data and receive detailed predictions with confidence intervals, helping you make informed decisions quickly without waiting for lengthy analysis.
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- USP 3 -->
                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon bg-warning text-white">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h3 class="card-title fw-bold">Complete Data Privacy</h3>
                            <p class="card-text text-muted">
                                Your sales data is never stored. We process your files in real-time and immediately discard them after generating forecasts. Your business information remains completely private and secure.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section id="benefits" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">What You'll Get</h2>
                <p class="lead text-muted">Transform your business planning with these powerful benefits</p>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="benefit-item">
                        <h4><i class="fas fa-check-circle text-success me-2"></i>Accurate Revenue Predictions</h4>
                        <p class="mb-0">Plan your budget and resources with confidence using our highly accurate 30-day sales forecasts.</p>
                    </div>
                    <div class="benefit-item">
                        <h4><i class="fas fa-check-circle text-success me-2"></i>Inventory Optimization</h4>
                        <p class="mb-0">Avoid overstocking or stockouts by knowing exactly how much inventory you'll need in the coming weeks.</p>
                    </div>
                    <div class="benefit-item">
                        <h4><i class="fas fa-check-circle text-success me-2"></i>Strategic Planning</h4>
                        <p class="mb-0">Make data-driven decisions for marketing campaigns, staffing, and business expansion based on predicted demand.</p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="benefit-item">
                        <h4><i class="fas fa-check-circle text-success me-2"></i>Risk Management</h4>
                        <p class="mb-0">Identify potential sales downturns early and take proactive measures to protect your revenue.</p>
                    </div>
                    <div class="benefit-item">
                        <h4><i class="fas fa-check-circle text-success me-2"></i>Performance Tracking</h4>
                        <p class="mb-0">Compare actual sales against forecasts to continuously improve your business intelligence and accuracy.</p>
                    </div>
                    <div class="benefit-item">
                        <h4><i class="fas fa-check-circle text-success me-2"></i>Time Savings</h4>
                        <p class="mb-0">Eliminate hours of manual spreadsheet work. Get professional-grade forecasts in minutes, not days.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Demo Section -->
    <section id="demo" class="demo-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Try It Free - No Signup Required</h2>
                <p class="lead text-muted">Upload your sales data and see instant predictions</p>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow-lg">
                        <div class="card-body p-4">
                            <form id="demoForm" enctype="multipart/form-data">
                                @csrf
                                <div class="upload-area" id="uploadArea">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                    <h4>Drag & Drop Your File Here</h4>
                                    <p class="text-muted">or click to browse</p>
                                    <input type="file" id="demoFile" name="demo_file" accept=".csv,.xlsx,.xls" class="d-none" required>
                                    <button type="button" class="btn btn-primary mt-3" onclick="document.getElementById('demoFile').click()">
                                        <i class="fas fa-file-upload me-2"></i>Choose File
                                    </button>
                                    <p class="text-muted mt-3 small">Supported formats: CSV, Excel (.xlsx, .xls) | Max size: 5MB</p>
                                    <p class="text-muted small">Format: Date (YYYY-MM-DD), Value OR Date, SKU, Store, Price, Quantity</p>
                                    <p class="mt-2">
                                        <a href="/demo_sales_data.csv" download class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download me-2"></i>Download Sample CSV
                                        </a>
                                    </p>
                                </div>
                                
                                <div id="fileName" class="mt-3 text-center" style="display: none;">
                                    <p class="text-success"><i class="fas fa-check-circle me-2"></i><span id="fileNameText"></span></p>
                                </div>
                                
                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg px-5" id="submitBtn">
                                        <i class="fas fa-magic me-2"></i>Generate Forecast
                                    </button>
                                </div>
                                
                                <div class="loading-spinner text-center mt-4" id="loadingSpinner">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2">Processing your data and generating forecast...</p>
                                </div>
                            </form>
                            
                            <div id="results" style="display: none;" class="mt-4">
                                <div class="alert alert-success">
                                    <h5><i class="fas fa-check-circle me-2"></i>Forecast Generated Successfully!</h5>
                                    <p class="mb-0">Your 30-day sales forecast is ready. View the chart and table below.</p>
                                </div>
                                
                                <div class="chart-container">
                                    <canvas id="forecastChart"></canvas>
                                </div>
                                
                                <div class="table-responsive mt-4">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Forecast</th>
                                                <th>Lower Bound</th>
                                                <th>Upper Bound</th>
                                            </tr>
                                        </thead>
                                        <tbody id="forecastTableBody">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <div id="errorMessage" style="display: none;" class="mt-4">
                                <div class="alert alert-danger">
                                    <h5><i class="fas fa-exclamation-triangle me-2"></i>Error</h5>
                                    <p id="errorText" class="mb-0"></p>
                                </div>
                            </div>
                            
                            <div class="disclaimer">
                                <h6><i class="fas fa-shield-alt me-2"></i>Privacy & Security Disclaimer</h6>
                                <p class="mb-0 small">
                                    <strong>We do NOT store your sales data.</strong> Your uploaded file is processed in real-time for forecasting purposes only and is immediately discarded after processing. No information from your sales reports is saved, logged, or shared with any third parties. Your business data remains completely private and secure.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <h2 class="display-5 fw-bold mb-4">Ready to Transform Your Sales Planning?</h2>
            <p class="lead mb-4">Join thousands of businesses using AI-powered forecasting</p>
            <a href="{{ route('register') }}" class="btn btn-light btn-lg cta-button">
                <i class="fas fa-rocket me-2"></i>Get Started Free
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p class="mb-0">&copy; {{ date('Y') }} Sales Forecast AI. All rights reserved.</p>
            <p class="mb-0 small mt-2">
                <a href="{{ route('login') }}" class="text-white-50 text-decoration-none">Login</a> | 
                <a href="{{ route('register') }}" class="text-white-50 text-decoration-none">Register</a>
            </p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <script>
        let forecastChart = null;
        
        // File upload handling
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('demoFile');
        const fileName = document.getElementById('fileName');
        const fileNameText = document.getElementById('fileNameText');
        
        // Drag and drop
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                handleFileSelect();
            }
        });
        
        fileInput.addEventListener('change', handleFileSelect);
        
        function handleFileSelect() {
            if (fileInput.files.length > 0) {
                fileNameText.textContent = fileInput.files[0].name;
                fileName.style.display = 'block';
            }
        }
        
        // Form submission
        document.getElementById('demoForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('demo_file', fileInput.files[0]);
            formData.append('_token', '{{ csrf_token() }}');
            
            // Hide previous results/errors
            document.getElementById('results').style.display = 'none';
            document.getElementById('errorMessage').style.display = 'none';
            document.getElementById('loadingSpinner').classList.add('active');
            document.getElementById('submitBtn').disabled = true;
            
            try {
                const response = await fetch('{{ route("landing.demo") }}', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                document.getElementById('loadingSpinner').classList.remove('active');
                document.getElementById('submitBtn').disabled = false;
                
                if (data.success) {
                    displayResults(data);
                } else {
                    showError(data.message || 'An error occurred while generating the forecast.');
                }
            } catch (error) {
                document.getElementById('loadingSpinner').classList.remove('active');
                document.getElementById('submitBtn').disabled = false;
                showError('Network error. Please check your connection and try again.');
            }
        });
        
        function displayResults(data) {
            document.getElementById('results').style.display = 'block';
            
            // Prepare chart data
            const historicalData = data.historical_data || [];
            const forecastData = data.forecast || [];
            
            const allDates = [
                ...historicalData.map(d => d.date),
                ...forecastData.map(d => d.date)
            ];
            
            const historicalValues = historicalData.map(d => d.value);
            const forecastValues = forecastData.map(d => d.forecast);
            const lowerBounds = forecastData.map(d => d.lower_bound || d.forecast);
            const upperBounds = forecastData.map(d => d.upper_bound || d.forecast);
            
            // Destroy existing chart
            if (forecastChart) {
                forecastChart.destroy();
            }
            
            // Create new chart
            const ctx = document.getElementById('forecastChart').getContext('2d');
            forecastChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: allDates,
                    datasets: [
                        {
                            label: 'Historical Sales',
                            data: [...historicalValues, ...new Array(forecastValues.length).fill(null)],
                            borderColor: 'rgb(75, 192, 192)',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            tension: 0.1,
                            pointRadius: 4
                        },
                        {
                            label: 'Forecast',
                            data: [...new Array(historicalValues.length).fill(null), ...forecastValues],
                            borderColor: 'rgb(255, 99, 132)',
                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                            tension: 0.1,
                            pointRadius: 4,
                            borderDash: [5, 5]
                        },
                        {
                            label: 'Lower Bound',
                            data: [...new Array(historicalValues.length).fill(null), ...lowerBounds],
                            borderColor: 'rgba(255, 99, 132, 0.3)',
                            backgroundColor: 'rgba(255, 99, 132, 0.1)',
                            tension: 0.1,
                            pointRadius: 0,
                            borderDash: [2, 2],
                            fill: '+1'
                        },
                        {
                            label: 'Upper Bound',
                            data: [...new Array(historicalValues.length).fill(null), ...upperBounds],
                            borderColor: 'rgba(255, 99, 132, 0.3)',
                            backgroundColor: 'rgba(255, 99, 132, 0.1)',
                            tension: 0.1,
                            pointRadius: 0,
                            borderDash: [2, 2],
                            fill: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: '30-Day Sales Forecast'
                        },
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            title: {
                                display: true,
                                text: 'Sales Value ($)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        }
                    }
                }
            });
            
            // Populate table
            const tableBody = document.getElementById('forecastTableBody');
            tableBody.innerHTML = '';
            forecastData.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.date}</td>
                    <td>$${parseFloat(item.forecast).toFixed(2)}</td>
                    <td>$${parseFloat(item.lower_bound || item.forecast).toFixed(2)}</td>
                    <td>$${parseFloat(item.upper_bound || item.forecast).toFixed(2)}</td>
                `;
                tableBody.appendChild(row);
            });
        }
        
        function showError(message) {
            document.getElementById('errorMessage').style.display = 'block';
            document.getElementById('errorText').textContent = message;
        }
        
        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>

