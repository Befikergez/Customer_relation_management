<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardRedirectController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        switch ($user->getRoleNames()->first()) {
            case 'superadmin':
            case 'salesperson':
            case 'sales_manager':
            case 'customer':
                return redirect()->route('dashboard');
            default:
                return redirect()->route('login');
        }
    }
}