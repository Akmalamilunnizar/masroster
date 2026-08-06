<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        // Check user role and redirect accordingly
        if (Auth::user()?->isAdmin()) {
            return redirect('/admin/dashboard');
        } else {
            return redirect('/tokodashboard');
        }
    }
}
