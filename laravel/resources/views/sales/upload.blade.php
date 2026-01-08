@extends('layouts.app')

@section('title', 'Upload Sales Excel')

@section('content')
<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Upload Sales Data (Excel)</h4>
                <a href="{{ route('sales.downloadTemplate') }}" class="btn btn-success">
                    <i class="fas fa-download me-2"></i>Download Excel Template
                </a>
            </div>
            <div class="card-body">
                <!-- Required Columns Info -->
                <div class="alert alert-primary">
                    <h5><i class="fas fa-info-circle me-2"></i>Required Columns</h5>
                    <p class="mb-2">Your Excel file must contain these columns in the first row:</p>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="mb-0">
                                <li><strong>date</strong> - Format: YYYY-MM-DD</li>
                                <li><strong>product_id</strong> - Unique product identifier</li>
                                <li><strong>product_name</strong> - Product name</li>
                                <li><strong>product_type</strong> - e.g., books, food, stationery, digital</li>
                                <li><strong>is_digital</strong> - 0 (physical) or 1 (digital)</li>
                                <li><strong>store_id</strong> - Store identifier</li>
                                <li><strong>location</strong> - Store location</li>
                                <li><strong>price</strong> - Unit price (numeric)</li>
                                <li><strong>quantity_sold</strong> - Quantity sold (integer, >= 0)</li>
                                <li><strong>revenue</strong> - Total revenue (auto-calculated if missing)</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Optional Forecast-Enhancing Columns:</h6>
                            <ul class="mb-0">
                                <li><strong>promo_flag</strong> - 0 or 1</li>
                                <li><strong>discount_pct</strong> - Discount percentage (0-100)</li>
                                <li><strong>stock_available</strong> - See important note below ⚠️</li>
                                <li><strong>holiday_flag</strong> - 0 or 1</li>
                                <li><strong>channel</strong> - online, retail, or marketplace</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Critical Business Rule -->
                <div class="alert alert-warning">
                    <h5><i class="fas fa-exclamation-triangle me-2"></i>CRITICAL: stock_available Definition</h5>
                    <p class="mb-2"><strong>stock_available represents whether the product was available for sale at the START of the day (before any sales occurred).</strong></p>
                    <ul class="mb-2">
                        <li><strong>1</strong> = Product was available for customers to buy at the start of the day</li>
                        <li><strong>0</strong> = Product was unavailable/out of stock at the start of the day</li>
                        <li><strong>❌ DO NOT</strong> use end-of-day or after-sales stock values</li>
                        <li><strong>✅ For digital products</strong> (is_digital = 1), stock_available is automatically set to 1</li>
                    </ul>
                    <p class="mb-0"><strong>Business Rule:</strong> If stock_available = 0, then quantity_sold MUST be 0 (you can't sell what's not available).</p>
                </div>

                <!-- Validation Rules -->
                <div class="alert alert-info">
                    <h5><i class="fas fa-check-circle me-2"></i>Validation Rules</h5>
                    <ul class="mb-0">
                        <li>All required columns must exist</li>
                        <li>Date must be valid and in YYYY-MM-DD format</li>
                        <li>Numeric columns must be numeric</li>
                        <li>quantity_sold must be >= 0</li>
                        <li>If stock_available = 0, quantity_sold must be 0</li>
                        <li>Revenue is auto-calculated (price × quantity_sold) if missing</li>
                        <li>Data leakage prevention: Future dates and unrealistic date ranges are rejected</li>
                    </ul>
                </div>

                @if(session('errors'))
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-times-circle me-2"></i>Import Errors</h5>
                        <ul class="mb-0 mt-2">
                            @foreach(session('errors') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('warnings'))
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-triangle me-2"></i>Import Warnings</h5>
                        <ul class="mb-0 mt-2">
                            @foreach(session('warnings') as $warning)
                                <li>{{ $warning }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('sales.processUpload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="excel_file" class="form-label">Excel File (.xlsx or .xls) *</label>
                        <input type="file" class="form-control @error('excel_file') is-invalid @enderror" 
                               id="excel_file" name="excel_file" accept=".xlsx,.xls" required>
                        @error('excel_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Maximum file size: 10MB</small>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('sales.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload me-2"></i>Upload Excel File
                        </button>
                    </div>
                </form>

                <!-- Example Data -->
                <div class="mt-4">
                    <h5>Example Data Row</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>date</th>
                                    <th>product_id</th>
                                    <th>product_name</th>
                                    <th>product_type</th>
                                    <th>is_digital</th>
                                    <th>store_id</th>
                                    <th>location</th>
                                    <th>price</th>
                                    <th>quantity_sold</th>
                                    <th>revenue</th>
                                    <th>stock_available</th>
                                    <th>promo_flag</th>
                                    <th>discount_pct</th>
                                    <th>holiday_flag</th>
                                    <th>channel</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>2024-01-15</td>
                                    <td>PROD-001</td>
                                    <td>Sample Book</td>
                                    <td>books</td>
                                    <td>0</td>
                                    <td>STORE-001</td>
                                    <td>New York</td>
                                    <td>29.99</td>
                                    <td>5</td>
                                    <td>149.95</td>
                                    <td>1</td>
                                    <td>0</td>
                                    <td></td>
                                    <td>0</td>
                                    <td>retail</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .alert ul {
        padding-left: 20px;
    }
    .alert li {
        margin-bottom: 5px;
    }
</style>
@endpush
