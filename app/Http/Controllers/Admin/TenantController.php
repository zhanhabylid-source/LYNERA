<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function index(): View
    {
        return view('admin.tenants.index', [
            'tenants' => User::query()
                ->with('subscription')
                ->withCount(['services', 'customers', 'bookings'])
                ->latest()
                ->paginate(20),
        ]);
    }
}
