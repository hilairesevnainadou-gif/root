@extends('layouts.admin')

@section('title', 'Dashboard')

@section('header-title', 'Tableau de Bord')

@section('styles')
<style>
    /* Stats Cards */
    .admin-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .admin-stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgb(0 0 0 / 0.1);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .admin-stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    .admin-stat-label {
        font-size: 0.875rem;
        color: #64748b;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .admin-stat-value {
        font-size: 1.875rem;
        font-weight: 700;
        color: #1e293b;
    }

    .admin-stat-change {
        font-size: 0.875rem;
        margin-top: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .admin-stat-change.positive {
        color: #22c55e;
    }

    .admin-stat-change.negative {
        color: #ef4444;
    }

    /* Charts Section */
    .charts-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .chart-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgb(0 0 0 / 0.1);
    }

    .chart-card h3 {
        font-size: 1rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 1rem;
    }

    /* Activity Section */
    .activity-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }

    .activity-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgb(0 0 0 / 0.1);
        overflow: hidden;
    }

    .activity-card-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .activity-card-header h3 {
        font-size: 1rem;
        font-weight: 600;
        color: #1e293b;
    }

    .activity-card-header a {
        font-size: 0.875rem;
        color: #3b82f6;
        text-decoration: none;
    }

    .activity-card-header a:hover {
        color: #1d4ed8;
    }

    .activity-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .activity-item {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: background-color 0.2s ease;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-item:hover {
        background-color: #f8fafc;
    }

    .activity-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 0.875rem;
        flex-shrink: 0;
    }

    .activity-avatar.orange {
        background: linear-gradient(135deg, #f97316, #ea580c);
    }

    .activity-details {
        flex: 1;
        min-width: 0;
    }

    .activity-title {
        font-weight: 500;
        color: #1e293b;
        margin-bottom: 0.25rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .activity-subtitle {
        font-size: 0.875rem;
        color: #64748b;
    }

    .activity-meta {
        font-size: 0.75rem;
        color: #94a3b8;
    }

    .activity-amount {
        text-align: right;
    }

    .activity-amount .amount {
        font-weight: 600;
        color: #1e293b;
        font-size: 0.875rem;
    }

    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status-draft { background: #f3f4f6; color: #6b7280; }
    .status-submitted { background: #dbeafe; color: #1e40af; }
    .status-under_review { background: #fef3c7; color: #92400e; }
    .status-pending_committee { background: #ffedd5; color: #c2410c; }
    .status-approved { background: #d1fae5; color: #065f46; }
    .status-funded { background: #dcfce7; color: #166534; }
    .status-rejected { background: #fee2e2; color: #991b1b; }

    .btn-verify {
        padding: 0.375rem 0.75rem;
        background: #dbeafe;
        color: #1e40af;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn-verify:hover {
        background: #bfdbfe;
    }

    .empty-state {
        padding: 2rem;
        text-align: center;
        color: #94a3b8;
    }

    .empty-state i {
        font-size: 2.5rem;
        margin-bottom: 0.75rem;
        display: block;
    }

    /* Quick Actions */
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .quick-action-card {
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        border-radius: 12px;
        padding: 1.25rem;
        color: white;
        text-decoration: none;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .quick-action-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(30, 64, 175, 0.3);
        color: white;
    }

    .quick-action-card i {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
        opacity: 0.9;
    }

    .quick-action-card h4 {
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .quick-action-card p {
        font-size: 0.75rem;
        opacity: 0.8;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .admin-stats {
            grid-template-columns: repeat(2, 1fr);
        }
        .charts-grid,
        .activity-grid {
            grid-template-columns: 1fr;
        }
        .quick-actions {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .admin-stats {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
    <!-- Quick Actions -->
    <div class="quick-actions">
        <a href="{{ route('admin.requests.index') }}" class="quick-action-card">
            <i class="fas fa-clipboard-check"></i>
            <h4>Demandes en attente</h4>
            <p>{{ $stats['pending_review'] }} à vérifier</p>
        </a>
        <a href="{{ route('admin.documents.pending') }}" class="quick-action-card">
            <i class="fas fa-file-signature"></i>
            <h4>Documents</h4>
            <p>{{ $stats['pending_documents'] }} en attente</p>
        </a>
        <a href="{{ route('admin.users.index') }}" class="quick-action-card">
            <i class="fas fa-user-plus"></i>
            <h4>Utilisateurs</h4>
            <p>{{ $stats['pending_verification_users'] }} à vérifier</p>
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="admin-stats">
        <div class="admin-stat-card">
            <div class="admin-stat-label">
                <i class="fas fa-users text-blue-500"></i>
                Utilisateurs Totaux
            </div>
            <div class="admin-stat-value">{{ number_format($stats['total_users']) }}</div>
            <div class="admin-stat-change positive">
                <i class="fas fa-arrow-up"></i>
                +{{ $stats['new_users_this_month'] }} ce mois
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-label">
                <i class="fas fa-user-clock text-orange-500"></i>
                En Attente Vérification
            </div>
            <div class="admin-stat-value">{{ $stats['pending_verification_users'] }}</div>
            <div class="admin-stat-change">
                Utilisateurs non vérifiés
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-label">
                <i class="fas fa-file-alt text-purple-500"></i>
                Demandes Totales
            </div>
            <div class="admin-stat-value">{{ number_format($stats['total_requests']) }}</div>
            <div class="admin-stat-change">
                {{ $stats['pending_review'] }} en révision
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-label">
                <i class="fas fa-money-bill-wave text-green-500"></i>
                Montant Financé
            </div>
            <div class="admin-stat-value">{{ number_format($stats['total_funded_amount'], 0, ',', ' ') }} FCFA</div>
            <div class="admin-stat-change positive">
                <i class="fas fa-check-circle"></i>
                {{ $stats['approved_this_month'] }} ce mois
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="charts-grid">
        <div class="chart-card">
            <h3>Évolution des Demandes (6 derniers mois)</h3>
            <canvas id="monthlyChart" height="250"></canvas>
        </div>
        <div class="chart-card">
            <h3>Répartition par Statut</h3>
            <canvas id="statusChart" height="250"></canvas>
        </div>
    </div>

    <!-- Activity Lists -->
    <div class="activity-grid">
        <!-- Recent Requests -->
        <div class="activity-card">
            <div class="activity-card-header">
                <h3>Dernières Demandes</h3>
                <a href="{{ route('admin.requests.index') }}">Voir tout</a>
            </div>
            <ul class="activity-list">
                @forelse($recentRequests as $request)
                    <li class="activity-item">
                        <div class="activity-avatar">
                            {{ substr($request->user->full_name ?? 'U', 0, 1) }}
                        </div>
                        <div class="activity-details">
                            <div class="activity-title">{{ $request->user->full_name ?? 'Utilisateur' }}</div>
                            <div class="activity-subtitle">{{ $request->typeFinancement->name ?? 'Type inconnu' }}</div>
                            <div class="activity-meta">{{ $request->created_at->diffForHumans() }}</div>
                        </div>
                        <div class="activity-amount">
                            @php
                                $statusClass = str_replace('_', '_', $request->status);
                                $statusLabels = [
                                    'draft' => 'Brouillon',
                                    'submitted' => 'Soumise',
                                    'under_review' => 'En révision',
                                    'pending_committee' => 'En attente comité',
                                    'approved' => 'Approuvée',
                                    'funded' => 'Financée',
                                    'rejected' => 'Rejetée',
                                ];
                            @endphp
                            <span class="status-badge status-{{ $statusClass }}">
                                {{ $statusLabels[$request->status] ?? $request->status }}
                            </span>
                            <div class="amount">{{ number_format($request->amount_requested ?? 0, 0, ',', ' ') }} FCFA</div>
                        </div>
                    </li>
                @empty
                    <li class="empty-state">
                        <i class="fas fa-inbox text-gray-300"></i>
                        Aucune demande récente
                    </li>
                @endforelse
            </ul>
        </div>

        <!-- Pending Documents -->
        <div class="activity-card">
            <div class="activity-card-header">
                <h3>Documents en Attente</h3>
                <a href="{{ route('admin.documents.pending') }}">Voir tout</a>
            </div>
            <ul class="activity-list">
                @forelse($pendingDocuments as $doc)
                    <li class="activity-item">
                        <div class="activity-avatar orange">
                            <i class="fas fa-file"></i>
                        </div>
                        <div class="activity-details">
                            <div class="activity-title">{{ $doc->typeDoc->name ?? 'Document' }}</div>
                            <div class="activity-subtitle">{{ $doc->user->full_name ?? 'Utilisateur' }}</div>
                            <div class="activity-meta">Soumis {{ $doc->created_at->diffForHumans() }}</div>
                        </div>
                        <div class="activity-amount">
                            <a href="{{ route('admin.documents.show', $doc) }}" class="btn-verify">
                                Vérifier
                            </a>
                        </div>
                    </li>
                @empty
                    <li class="empty-state">
                        <i class="fas fa-check-circle text-green-400"></i>
                        Aucun document en attente
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Configuration des couleurs
    const colors = {
        primary: '#3b82f6',
        primaryLight: 'rgba(59, 130, 246, 0.1)',
        success: '#10b981',
        successLight: 'rgba(16, 185, 129, 0.1)',
        warning: '#f59e0b',
        danger: '#ef4444',
        info: '#06b6d4',
        purple: '#8b5cf6',
        orange: '#f97316',
        gray: '#9ca3af'
    };

    // Données depuis le contrôleur
    const monthlyData = @json($chartData['by_month']);
    const statusData = @json($chartData['by_status']);

    // Graphique Mensuel (Ligne)
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: monthlyData.map(d => d.month),
            datasets: [{
                label: 'Demandes créées',
                data: monthlyData.map(d => d.created),
                borderColor: colors.primary,
                backgroundColor: colors.primaryLight,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: colors.primary,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }, {
                label: 'Montant financé (millions)',
                data: monthlyData.map(d => d.funded / 1000000),
                borderColor: colors.success,
                backgroundColor: colors.successLight,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: colors.success,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 15,
                        font: { size: 12 }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(30, 41, 59, 0.9)',
                    padding: 12,
                    cornerRadius: 8,
                    titleFont: { size: 13 },
                    bodyFont: { size: 12 }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 } }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    grid: { color: '#f1f5f9' },
                    ticks: { font: { size: 11 } }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    ticks: { 
                        font: { size: 11 },
                        callback: function(value) {
                            return value + 'M';
                        }
                    }
                }
            }
        }
    });

    // Graphique par Statut (Doughnut)
    const statusColors = {
        draft: '#9ca3af',
        submitted: '#3b82f6',
        under_review: '#f59e0b',
        pending_committee: '#f97316',
        approved: '#10b981',
        funded: '#059669',
        rejected: '#ef4444'
    };

    const statusLabels = {
        draft: 'Brouillon',
        submitted: 'Soumise',
        under_review: 'En révision',
        pending_committee: 'En attente comité',
        approved: 'Approuvée',
        funded: 'Financée',
        rejected: 'Rejetée'
    };

    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: Object.values(statusLabels),
            datasets: [{
                data: Object.values(statusData),
                backgroundColor: Object.values(statusColors),
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 12,
                        font: { size: 11 },
                        boxWidth: 8
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(30, 41, 59, 0.9)',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
</script>
@endsection