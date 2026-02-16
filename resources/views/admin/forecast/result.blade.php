@extends('admin.layouts.template')
@section('page_title')
    Hasil Forecasting - CIME
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center py-3 mb-4">
        <div>
            <h4 class="fw-bold mb-0">Hasil Forecasting</h4>
            @if(isset($result->model))
                <small class="text-muted">Model: {{ $result->model }}</small>
            @endif
        </div>
        <a href="{{ route('forecast.form') }}" class="btn btn-outline-primary btn-sm">
            <i class="bx bx-chevron-left"></i> Kembali ke Form
        </a>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center bg-transparent border-bottom">
                    <h5 class="mb-0 text-primary"><i class="bx bx-analyse me-1"></i> Ringkasan Prediksi</h5>
                </div>
                <div class="card-body mt-4">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="card border-0 bg-light shadow-none mb-4">
                                <div class="card-body">
                                    <h5 class="card-title fw-bold text-dark mb-3"><i class="bx bx-calendar-star me-1 text-primary"></i> Estimasi Penjualan Mendatang</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover border-top">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Urutan</th>
                                                    <th class="text-end">Prediksi Unit</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($result->forecast as $index => $value)
                                                    <tr>
                                                        <td>Bulan ke-{{ $index + 1 }}</td>
                                                        <td class="text-end fw-bold text-primary">{{ number_format($value, 2) }} Unit</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="card border-primary mb-4">
                                <div class="card-body">
                                    <h5 class="card-title fw-bold text-primary mb-3"><i class="bx bx-check-shield me-1"></i> Akurasi Model</h5>
                                    <div class="mb-3">
                                        <label class="text-muted small d-block">MAE (Mean Absolute Error)</label>
                                        <span class="h4 fw-bold mb-0">{{ number_format($result->mae, 2) }}</span>
                                    </div>
                                    <div>
                                        <label class="text-muted small d-block">RMSE (Root Mean Square Error)</label>
                                        <span class="h4 fw-bold mb-0">{{ number_format($result->rmse, 2) }}</span>
                                    </div>
                                    <hr>
                                    <div class="bg-info bg-opacity-10 p-3 rounded">
                                        <p class="small text-info mb-0">
                                            <i class="bx bx-info-circle me-1"></i>
                                            Semakin rendah nilai MAE dan RMSE, semakin tinggi tingkat akurasi prediksi model.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning border-left-warning d-flex align-items-center" role="alert">
                        <i class="bx bx-bulb fs-4 me-2"></i>
                        <div>
                            Gunakan hasil ini sebagai referensi untuk perencanaan stok gudang di bulan-bulan berikutnya.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .border-left-warning {
        border-left: 5px solid #ffab00;
    }
</style>
@endsection 
