@extends('admin.layouts.template')
@section('page_title')
    Hasil Forecasting - CIME
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">Hasil Forecasting</h4>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Hasil Prediksi</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Hasil Prediksi</h5>
                                    <p class="card-text">
                                        @foreach($result->forecast as $index => $value)
                                            Bulan ke-{{ $index + 1 }}: {{ number_format($value, 2) }} unit<br>
                                        @endforeach
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Metrik Evaluasi</h5>
                                    <p class="card-text">
                                        MAE (Mean Absolute Error): {{ number_format($result->mae, 2) }}<br>
                                        RMSE (Root Mean Square Error): {{ number_format($result->rmse, 2) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <a href="{{ route('forecast.form') }}" class="btn btn-primary">Kembali ke Form</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 