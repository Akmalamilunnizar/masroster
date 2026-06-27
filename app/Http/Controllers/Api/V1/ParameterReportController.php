<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ParameterReport;

class ParameterReportController extends Controller
{
    public function index()
    {
        $reports = (new ParameterReport)->getRecent();

        return view('admin.parameterreport', compact('reports'));
    }
}
