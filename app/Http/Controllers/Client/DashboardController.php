<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard professionnel du client
     */
    public function index(): View
    {
        $user = auth()->user();
        $now = Carbon::now();

        // Statistiques principales avec tendances
        $stats = $this->getMainStats($user, $now);

        // Graphique d'activité (12 derniers mois)
        $activityChart = $this->getActivityChart($user);

        // État des demandes en cours
        $activeRequests = $this->getActiveRequests($user);

        // Résumé financier
        $financialSummary = $this->getFinancialSummary($user);

        // Alertes et notifications importantes
        $alerts = $this->getAlerts($user);

        // Actions prioritaires
        $priorityActions = $this->getPriorityActions($user);

        // Dernières activités
        $recentActivities = $this->getRecentActivities($user);

        // Performance du portefeuille
        $walletStats = $this->getWalletStats($user);

        // NOUVEAU: Informations entreprise principale
        $primaryCompany = $user->primaryCompany;

        return view('client.dashboard.index', compact(
            'stats',
            'activityChart',
            'activeRequests',
            'financialSummary',
            'alerts',
            'priorityActions',
            'recentActivities',
            'walletStats',
            'user',
            'primaryCompany' // AJOUTÉ
        ));
    }

    /**
     * Statistiques principales
     */
    private function getMainStats($user, $now): array
    {
        $currentMonth = $now->copy()->startOfMonth();
        $lastMonth = $now->copy()->subMonth()->startOfMonth();

        // Utilisation de la relation fundingRequests() au lieu du modèle direct
        $requestsThisMonth = $user->fundingRequests()
            ->whereMonth('created_at', $currentMonth->month)
            ->whereYear('created_at', $currentMonth->year)
            ->count();

        $requestsLastMonth = $user->fundingRequests()
            ->whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->count();

        $requestsTrend = $requestsLastMonth > 0
            ? round((($requestsThisMonth - $requestsLastMonth) / $requestsLastMonth) * 100, 1)
            : ($requestsThisMonth > 0 ? 100 : 0);

        // Montant total financé
        $totalFunded = $user->fundingRequests()
            ->where('status', 'funded')
            ->sum('amount_approved');

        // Taux de succès
        $totalRequests = $user->fundingRequests()
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->count();

        $approvedRequests = $user->fundingRequests()
            ->whereIn('status', ['approved', 'funded'])
            ->count();

        $successRate = $totalRequests > 0 ? round(($approvedRequests / $totalRequests) * 100, 1) : 0;

        return [
            'total_requests' => [
                'value' => $user->fundingRequests()->count(),
                'trend' => $requestsTrend,
                'this_month' => $requestsThisMonth,
            ],
            'total_funded' => [
                'value' => $totalFunded,
                'formatted' => $this->formatAmount($totalFunded),
            ],
            'success_rate' => [
                'value' => $successRate,
                'label' => $successRate >= 70 ? 'Excellent' : ($successRate >= 40 ? 'Bon' : 'À améliorer'),
            ],
            'active_requests' => $user->fundingRequests()
                ->whereIn('status', ['submitted', 'under_review', 'pending_committee'])
                ->count(),
            // NOUVEAU: Statistiques entreprise
            'companies_count' => $user->companies()->count(),
            'has_primary_company' => $user->hasPrimaryCompany(),
        ];
    }

    /**
     * Données pour le graphique d'activité
     */
    private function getActivityChart($user): array
    {
        $months = [];
        $requestsData = [];
        $fundedData = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');

            // Utilisation de la relation fundingRequests()
            $requestsData[] = $user->fundingRequests()
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();

            $fundedData[] = $user->fundingRequests()
                ->where('status', 'funded')
                ->whereMonth('funded_at', $date->month)
                ->whereYear('funded_at', $date->year)
                ->sum('amount_approved');
        }

        return [
            'labels' => $months,
            'requests' => $requestsData,
            'funded' => $fundedData,
        ];
    }

    /**
     * Demandes actives avec progression
     */
    private function getActiveRequests($user)
    {
        return $user->fundingRequests()
            ->with(['typeFinancement', 'documentUsers'])
            ->whereIn('status', ['draft', 'submitted', 'under_review', 'pending_committee', 'approved'])
            ->orderByRaw("FIELD(status, 'approved', 'pending_committee', 'under_review', 'submitted', 'draft')")
            ->limit(5)
            ->get()
            ->map(function ($request) {
                $request->progress = $this->calculateProgress($request);
                $request->progress_label = $this->getProgressLabel($request);
                $request->next_action = $this->getNextAction($request);

                return $request;
            });
    }

    /**
     * Résumé financier
     */
    private function getFinancialSummary($user): array
    {
        $wallet = $user->wallet;

        // CORRECTION: Préfixer les colonnes avec le nom de la table
        $monthlyIncome = $wallet ? $user->transactions()
            ->where('transactions.type', 'credit')
            ->where('transactions.status', 'completed')
            ->whereMonth('transactions.created_at', Carbon::now()->month)
            ->whereYear('transactions.created_at', Carbon::now()->year)
            ->sum('amount') : 0;

        $monthlyExpenses = $wallet ? $user->transactions()
            ->where('transactions.type', 'debit')
            ->where('transactions.status', 'completed')
            ->whereMonth('transactions.created_at', Carbon::now()->month)
            ->whereYear('transactions.created_at', Carbon::now()->year)
            ->sum('amount') : 0;

        // Montant en attente via relation fundingRequests
        $pendingAmount = $user->fundingRequests()
            ->whereIn('status', ['approved', 'funded'])
            ->whereNull('funded_at')
            ->sum('amount_approved');

        $transactionsCount = $wallet ? $user->transactions()->count() : 0;
        $lastTransaction = $wallet ? $user->transactions()->latest()->first() : null;

        return [
            'has_wallet' => $wallet !== null,
            'wallet_balance' => $wallet?->balance ?? 0,
            'formatted_balance' => $this->formatAmount($wallet?->balance ?? 0),
            'monthly_income' => $monthlyIncome,
            'monthly_expenses' => $monthlyExpenses,
            'pending_amount' => $pendingAmount,
            'formatted_pending' => $this->formatAmount($pendingAmount),
            'currency' => $wallet?->currency ?? 'XOF',
            'transactions_count' => $transactionsCount,
            'last_transaction' => $lastTransaction ? [
                'type' => $lastTransaction->type,
                'amount' => $lastTransaction->amount,
                'formatted_amount' => $this->formatAmount($lastTransaction->amount),
                'date' => $lastTransaction->created_at->diffForHumans(),
                'status' => $lastTransaction->status,
            ] : null,
        ];
    }

    /**
     * Alertes importantes
     */
    private function getAlerts($user): array
    {
        $alerts = [];

        // Documents rejetés via la relation documentUsers()
        $rejectedDocs = $user->documentUsers()
            ->where('status', 'rejected')
            ->count();

        if ($rejectedDocs > 0) {
            $alerts[] = [
                'type' => 'error',
                'icon' => 'document',
                'title' => 'Documents rejetés',
                'message' => "{$rejectedDocs} document(s) nécessitent votre attention",
                'action_url' => route('client.documents.index'),
                'action_text' => 'Corriger',
            ];
        }

        // Brouillons non finalisés via la relation
        $drafts = $user->fundingRequests()
            ->where('status', 'draft')
            ->where('created_at', '<', Carbon::now()->subDays(7))
            ->get();

        if ($drafts->count() > 0) {
            $firstDraft = $drafts->first();
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'draft',
                'title' => 'Brouillons en attente',
                'message' => $drafts->count().' demande(s) non finalisée(s) depuis plus de 7 jours',
                'action_url' => route('client.requests.payment', $firstDraft),
                'action_text' => 'Compléter le paiement',
            ];
        }

        // Notifications non lues via la relation notifications()
        $unreadImportant = $user->notifications()
            ->where('is_read', false)
            ->whereIn('type', ['request_approved', 'request_rejected', 'document_rejected'])
            ->count();

        if ($unreadImportant > 0) {
            $alerts[] = [
                'type' => 'info',
                'icon' => 'notification',
                'title' => 'Nouvelles notifications',
                'message' => "{$unreadImportant} notification(s) importante(s) non lue(s)",
                'action_url' => route('client.notifications.index'),
                'action_text' => 'Voir',
            ];
        }

        // Profil incomplet - Utilisation de hasPrimaryCompany()
        $requiredFields = ['phone', 'address', 'city'];
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (empty($user->$field)) {
                $missingFields[] = $field;
            }
        }

        // CORRECTION: Utilisation correcte de isEntreprise() et primaryCompany()
        if ($user->isEntreprise() || $user->hasPrimaryCompany()) {
            $companyRequired = ['company_name', 'company_type', 'sector'];
            $company = $user->primaryCompany;

            foreach ($companyRequired as $field) {
                if (! $company || empty($company->$field)) {
                    $missingFields[] = 'company_'.$field;
                }
            }
        }

        if (! empty($missingFields)) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'profile',
                'title' => 'Profil incomplet',
                'message' => 'Complétez votre profil pour accéder à tous les financements',
                'action_url' => route('client.profile'),
                'action_text' => 'Compléter',
            ];
        }

        return $alerts;
    }

    /**
     * Actions prioritaires
     */
    private function getPriorityActions($user): array
    {
        $actions = [];

        // Vérifier documents manquants via la relation fundingRequests()
        $requestsWithMissingDocs = $user->fundingRequests()
            ->with(['typeFinancement.requiredTypeDocs', 'documentUsers'])
            ->where('status', 'submitted')
            ->get()
            ->filter(function ($request) {
                return ! $request->hasAllRequiredDocuments();
            });

        foreach ($requestsWithMissingDocs as $request) {
            $missingCount = $request->missingDocuments()->count();
            $totalCount = $request->totalRequiredDocumentsCount();

            $actions[] = [
                'priority' => 'high',
                'title' => 'Documents manquants',
                'description' => "Ajoutez {$missingCount}/{$totalCount} document(s) pour finaliser \"{$request->title}\"",
                'url' => route('client.documents.required', $request),
                'deadline' => $request->created_at->addDays(14)->diffForHumans(),
                'progress' => $request->documentsCompletionPercentage(),
            ];
        }

        // Suggestion de nouvelle demande
        if ($user->fundingRequests()->count() === 0) {
            $actions[] = [
                'priority' => 'medium',
                'title' => 'Première demande',
                'description' => 'Découvrez les financements disponibles et faites votre première demande',
                'url' => route('client.financements.index'),
                'deadline' => null,
            ];
        }

        // NOUVEAU: Action si pas d'entreprise principale
        if (! $user->hasPrimaryCompany()) {
            $actions[] = [
                'priority' => 'high',
                'title' => 'Entreprise requise',
                'description' => 'Ajoutez votre entreprise principale pour accéder aux financements',
                'url' => route('client.profile.companies.create'),
                'deadline' => null,
            ];
        }

        return $actions;
    }

    /**
     * Activités récentes
     */
    private function getRecentActivities($user)
    {
        $activities = collect();

        // Dernières demandes via la relation
        $requests = $user->fundingRequests()
            ->with('typeFinancement')
            ->latest()
            ->limit(3)
            ->get()
            ->map(fn ($r) => [
                'type' => 'request',
                'icon' => 'document',
                'title' => $r->title,
                'description' => "Demande {$r->request_number}",
                'status' => $r->status,
                'date' => $r->created_at,
                'url' => route('client.requests.show', $r),
            ]);

        // Dernières notifications via la relation
        $notifications = $user->notifications()
            ->latest()
            ->limit(3)
            ->get()
            ->map(fn ($n) => [
                'type' => 'notification',
                'icon' => 'bell',
                'title' => $n->title,
                'description' => Str::limit($n->message, 60),
                'status' => $n->is_read ? 'read' : 'unread',
                'date' => $n->created_at,
                'url' => route('client.notifications.index'),
            ]);

        // Dernières transactions via la relation
        $transactions = $user->wallet
            ? $user->transactions()
                ->latest()
                ->limit(3)
                ->get()
                ->map(fn ($t) => [
                    'type' => 'transaction',
                    'icon' => $t->type === 'credit' ? 'arrow-down' : ($t->type === 'debit' ? 'arrow-up' : 'credit-card'),
                    'title' => $t->type === 'credit' ? 'Dépôt' : ($t->type === 'debit' ? 'Retrait' : 'Paiement'),
                    'description' => $t->description ?? 'Transaction',
                    'amount' => $t->amount,
                    'amount_formatted' => $this->formatAmount($t->amount),
                    'status' => $t->status,
                    'date' => $t->created_at,
                    'url' => route('client.wallet.transactions'),
                ])
            : collect();

        return $activities
            ->merge($requests)
            ->merge($notifications)
            ->merge($transactions)
            ->sortByDesc('date')
            ->take(5);
    }

    /**
     * Stats du portefeuille
     */
    private function getWalletStats($user): array
    {
        // Utilisation de la relation wallet()
        $wallet = $user->wallet;

        if (! $wallet) {
            return [
                'has_wallet' => false,
                'balance' => 0,
                'transactions_count' => 0,
            ];
        }

        // Utilisation de la relation transactions()
        $lastTransaction = $user->transactions()->latest()->first();

        return [
            'has_wallet' => true,
            'balance' => $wallet->balance,
            'formatted_balance' => $this->formatAmount($wallet->balance),
            'currency' => $wallet->currency,
            'transactions_count' => $user->transactions()->count(),
            'last_transaction' => $lastTransaction ? [
                'date' => $lastTransaction->created_at->diffForHumans(),
                'amount' => $this->formatAmount($lastTransaction->amount),
                'type' => $lastTransaction->type,
            ] : null,
        ];
    }

    // Méthodes utilitaires (inchangées)

    private function calculateProgress($request): int
    {
        $steps = [
            'draft' => 10,
            'submitted' => 30,
            'under_review' => 50,
            'pending_committee' => 70,
            'approved' => 90,
            'funded' => 100,
        ];

        return $steps[$request->status] ?? 0;
    }

    private function getProgressLabel($request): string
    {
        $labels = [
            'draft' => 'En brouillon',
            'submitted' => 'Soumise',
            'under_review' => 'En examen',
            'pending_committee' => 'Comité',
            'approved' => 'Approuvée',
            'funded' => 'Financée',
        ];

        return $labels[$request->status] ?? $request->status;
    }

    private function getNextAction($request): ?array
    {
        return match ($request->status) {
            'draft' => ['text' => 'Payer', 'url' => route('client.requests.payment', $request)],
            'submitted' => ['text' => 'Compléter docs', 'url' => route('client.documents.required', $request)],
            'under_review', 'pending_committee' => ['text' => 'Suivre', 'url' => route('client.requests.show', $request)],
            'approved' => ['text' => 'Finaliser', 'url' => route('client.requests.show', $request)],
            default => null,
        };
    }

    private function formatAmount($amount): string
    {
        return number_format($amount, 0, ',', ' ').' FCFA';
    }
}
