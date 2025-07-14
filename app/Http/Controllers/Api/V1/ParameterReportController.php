<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ParameterReport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ParameterReportController extends Controller
{
    public function index()
    {
        $reports = (new ParameterReport)->getRecent();
        return view('admin.parameterreport', compact('reports'));
    }
}
