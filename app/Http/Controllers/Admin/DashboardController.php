<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentUser;
use App\Models\FundingRequest;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Dashboard Admin
     */
    public function index(): View
    {
        $stats = [
            // Utilisateurs
            'total_users' => User::count(),
            'new_users_this_month' => User::whereMonth('created_at', now()->month)->count(),
            'pending_verification_users' => User::where('is_verified', false)->count(),

            // Demandes
            'total_requests' => FundingRequest::count(),
            'pending_review' => FundingRequest::whereIn('status', ['submitted', 'under_review'])->count(),
            'pending_committee' => FundingRequest::where('status', 'pending_committee')->count(),
            'approved_this_month' => FundingRequest::where('status', 'approved')
                ->whereMonth('approved_at', now()->month)
                ->count(),
            'total_funded_amount' => FundingRequest::where('status', 'funded')->sum('amount_approved'),

            // Documents
            'pending_documents' => DocumentUser::where('status', 'pending')->count(),

            // Transactions
            'today_transactions' => Transaction::whereDate('created_at', today())
                ->where('status', 'completed')
                ->sum('amount'),
        ];

        // Activité récente
        $recentRequests = FundingRequest::with(['user', 'typeFinancement'])
            ->latest()
            ->limit(10)
            ->get();

        $pendingDocuments = DocumentUser::with(['user', 'typeDoc'])
            ->where('status', 'pending')
            ->latest()
            ->limit(10)
            ->get();

        // Graphique: demandes par mois (6 derniers mois)
        $chartData = $this->getChartData();

        return view('admin.dashboard.index', compact(
            'stats',
            'recentRequests',
            'pendingDocuments',
            'chartData'
        ));
    }

    private function getChartData(): array
    {
        $months = collect(range(0, 5))->map(fn($i) => now()->subMonths($i)->format('Y-m'))->reverse();

        $requestsByMonth = $months->map(fn($month) => [
            'month' => \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y'),
            'created' => FundingRequest::whereYear('created_at', substr($month, 0, 4))
                ->whereMonth('created_at', substr($month, 5, 2))
                ->count(),
            'funded' => FundingRequest::where('status', 'funded')
                ->whereYear('funded_at', substr($month, 0, 4))
                ->whereMonth('funded_at', substr($month, 5, 2))
                ->sum('amount_approved'),
        ]);

        $statusDistribution = [
            'draft' => FundingRequest::where('status', 'draft')->count(),
            'submitted' => FundingRequest::where('status', 'submitted')->count(),
            'under_review' => FundingRequest::where('status', 'under_review')->count(),
            'pending_committee' => FundingRequest::where('status', 'pending_committee')->count(),
            'approved' => FundingRequest::where('status', 'approved')->count(),
            'funded' => FundingRequest::where('status', 'funded')->count(),
            'rejected' => FundingRequest::where('status', 'rejected')->count(),
        ];

        return [
            'by_month' => $requestsByMonth,
            'by_status' => $statusDistribution,
        ];
    }
}
