<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
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

    /**
     * Bonus Feature: Dashboard Real-time.
     * Endpoint ringan (tanpa cache 60 detik seperti overview()) yang di-poll
     * dari browser tiap beberapa detik lewat JS (lihat dashboard/index.blade.php).
     * Sengaja hanya mengembalikan angka-angka yang murah dihitung (bukan seluruh
     * grafik) supaya polling tidak membebani database.
     */
    public function live(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasRole(UserRole::EMPLOYEE->value, UserRole::GUEST->value)) {
            return response()->json($this->dashboard->myOverview($user));
        }

        return response()->json([
            'statusCounts' => $this->dashboard->statusCounts(),
            'periodStats' => $this->dashboard->periodStats(),
            'latestActivities' => $this->dashboard->latestActivities()->map(fn ($activity) => [
                'actor' => $activity->user?->name ?? 'Sistem',
                'description' => $activity->description,
                'ticket_number' => $activity->ticket?->ticket_number,
                'when' => $activity->created_at->diffForHumans(),
            ]),
            'overdueCount' => $this->dashboard->slaPerformance()['currently_overdue'],
            'generated_at' => now()->toIso8601String(),
        ]);
    }
}
