<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboard)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->hasRole(UserRole::EMPLOYEE->value, UserRole::GUEST->value)) {
            return view('dashboard.my-tickets', $this->dashboard->myOverview($user));
        }

        return view('dashboard.index', $this->dashboard->overview($user));
    }
}
