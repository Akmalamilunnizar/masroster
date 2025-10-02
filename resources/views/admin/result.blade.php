@extends('admin.layouts.template')
@section('page_title')
    Daftar Transaksi - CIME
@endsection
@section('search')
    <div class="navbar-nav align-items-center">
        <div class="nav-item d-flex align-items-center">
            <i class="bx bx-search fs-4 lh-0"></i>
            {{-- <form method="GET" action={{ route('searchitem') }}> --}}
            <input type="text" name="search" class="form-control border-0 shadow-none ps-1 ps-sm-2 w-100"
                placeholder="Pencarian id atau nama..." value="{{ isset($search) ? $search : '' }}" aria-label="Pencarian..."
                style="600px" />
            </form>
        </div>
    </div>
@endsection
@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forecast Result</title>
</head>
<body>
    <h1>Forecast Result</h1>
    <p>Forecast: {{ implode(', ', $result->forecast) }}</p>
    <p>MAE: {{ $result->mae }}</p>
    <p>RMSE: {{ $result->rmse }}</p>
</body>
</html>

@endsection
