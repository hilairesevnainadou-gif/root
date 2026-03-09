@extends('layouts.admin')

@section('title', 'Dashboard')

@section('header-title', 'Tableau de Bord')

@section('styles')
<style>
    /* ── Quick Actions ────────────────────────── */
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .quick-action-card {
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        border-radius: var(--radius-lg);
        padding: 1.25rem 1.5rem;
        color: white;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .quick-action-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 24px rgba(30, 64, 175, 0.3);
        color: white;
    }

    .quick-action-card svg {
        width: 28px;
        height: 28px;
        margin-bottom: 0.5rem;
        opacity: 0.9;
    }

    .quick-action-card h4 {
        font-size: 0.9rem;
        font-weight: 700;
        letter-spacing: -0.01em;
    }

    .quick-action-card p {
        font-size: 0.78rem;
        opacity: 0.75;
    }

    /* ── Charts ───────────────────────────────── */
    .charts-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .chart-card {
        background: var(--color-white);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--color-border);
    }

    .chart-card h3 {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--color-text);
        margin-bottom: 1.25rem;
        letter-spacing: -0.02em;
    }

    .chart-canvas-wrap {
        position: relative;
        height: 240px;
    }

    /* ── Activity Grid ────────────────────────── */
    .activity-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.25rem;
    }

    .activity-card {
        background: var(--color-white);
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--color-border);
    }

    .activity-card-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--color-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .activity-card-header h3 {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--color-text);
        letter-spacing: -0.02em;
    }

    /* ── Activity List ────────────────────────── */
    .activity-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .activity-item {
        padding: 0.875rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 0.875rem;
        transition: background 0.15s;
    }

    .activity-item:last-child { border-bottom: none; }
    .activity-item:hover { background: #f8fafc; }

    .activity-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 0.8rem;
        flex-shrink: 0;
    }

    .activity-avatar.orange {
        background: linear-gradient(135deg, #f97316, #ea580c);
    }

    .activity-details { flex: 1; min-width: 0; }

    .activity-title {
        font-weight: 600;
        font-size: 0.85rem;
        color: var(--color-text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 0.15rem;
    }

    .activity-subtitle {
        font-size: 0.78rem;
        color: var(--color-text-muted);
    }

    .activity-meta {
        font-size: 0.72rem;
        color: #94a3b8;
        margin-top: 0.1rem;
    }

    .activity-right { text-align: right; flex-shrink: 0; }

    .activity-right .amount {
        font-weight: 600;
        font-size: 0.8rem;
        color: var(--color-text);
        margin-top: 0.35rem;
        display: block;
    }

    /* ── Status Badges ────────────────────────── */
    .status-badge {
        display: inline-block;
        padding: 0.2rem 0.6rem;
        border-radius: 9999px;
        font-size: 0.7rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .status-draft            { background: #f3f4f6; color: #6b7280; }
    .status-submitted        { background: #dbeafe; color: #1e40af; }
    .status-under_review     { background: #fef3c7; color: #92400e; }
    .status-pending_committee{ background: #ffedd5; color: #c2410c; }
    .status-approved         { background: #d1fae5; color: #065f46; }
    .status-funded           { background: #dcfce7; color: #166534; }
    .status-rejected         { background: #fee2e2; color: #991b1b; }

    /* ── Buttons ──────────────────────────────── */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.45rem 0.875rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.15s;
        font-family: inherit;
    }

    .btn-primary  { background: #3b82f6; color: white; }
    .btn-primary:hover  { background: #2563eb; color: white; }
    .btn-success  { background: #10b981; color: white; }
    .btn-success:hover  { background: #059669; color: white; }
    .btn-secondary { background: #f1f5f9; color: #475569; border: 1px solid var(--color-border); }
    .btn-secondary:hover { background: #e2e8f0; }

    /* ── Empty state ─────────────────────────── */
    .empty-state {
        padding: 2.5rem 1.5rem;
        text-align: center;
        color: #94a3b8;
        font-size: 0.875rem;
    }

    .empty-state svg {
        width: 36px;
        height: 36px;
        margin: 0 auto 0.75rem;
        display: block;
        opacity: 0.35;
    }

    /* ── Responsive ──────────────────────────── */
    @media (max-width: 1024px) {
        .quick-actions,
        .charts-grid,
        .activity-grid { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('content')

    {{-- Quick Actions --}}
    <div class="quick-actions">
        <a href="{{ route('admin.requests.index') }}" class="quick-action-card">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            <h4>Demandes en attente</h4>
            <p>{{ $stats['pending_review'] }} à vérifier</p>
        </a>
        <a href="{{ route('admin.documents.pending') }}" class="quick-action-card" style="background: linear-gradient(135deg, #7c3aed 0%, #a78bfa 100%);">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <h4>Documents</h4>
            <p>{{ $stats['pending_documents'] }} en attente</p>
        </a>
        <a href="{{ route('admin.users.index') }}" class="quick-action-card" style="background: linear-gradient(135deg, #0e7490 0%, #22d3ee 100%);">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            <h4>Utilisateurs</h4>
            <p>{{ $stats['pending_verification_users'] }} à vérifier</p>
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="admin-stats">
        <div class="admin-stat-card">
            <div class="admin-stat-label">Utilisateurs Totaux</div>
            <div class="admin-stat-value">{{ number_format($stats['total_users']) }}</div>
            <div class="admin-stat-change positive">↑ +{{ $stats['new_users_this_month'] }} ce mois</div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-label">En Attente Vérification</div>
            <div class="admin-stat-value">{{ $stats['pending_verification_users'] }}</div>
            <div class="admin-stat-change">Utilisateurs non vérifiés</div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-label">Demandes Totales</div>
            <div class="admin-stat-value">{{ number_format($stats['total_requests']) }}</div>
            <div class="admin-stat-change">{{ $stats['pending_review'] }} en révision</div>
        </div>
        <div class="admin-stat-card">
            <div class="admin-stat-label">Montant Financé</div>
            <div class="admin-stat-value" style="font-size:1.4rem;">{{ number_format($stats['total_funded_amount'], 0, ',', ' ') }}<span style="font-size:0.85rem;font-weight:500;color:var(--color-text-muted);margin-left:4px;">FCFA</span></div>
            <div class="admin-stat-change positive">✓ {{ $stats['approved_this_month'] }} approuvées ce mois</div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="charts-grid">
        <div class="chart-card">
            <h3>Évolution des Demandes — 6 derniers mois</h3>
            <div class="chart-canvas-wrap">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
        <div class="chart-card">
            <h3>Répartition par Statut</h3>
            <div class="chart-canvas-wrap">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Activity --}}
    <div class="activity-grid">

        {{-- Dernières Demandes --}}
        <div class="activity-card">
            <div class="activity-card-header">
                <h3>Dernières Demandes</h3>
                <a href="{{ route('admin.requests.index') }}" class="btn btn-primary">Voir tout</a>
            </div>
            <ul class="activity-list">
                @forelse($recentRequests as $request)
                    @php
                        $statusLabels = [
                            'draft'              => 'Brouillon',
                            'submitted'          => 'Soumise',
                            'under_review'       => 'En révision',
                            'pending_committee'  => 'Comité',
                            'approved'           => 'Approuvée',
                            'funded'             => 'Financée',
                            'rejected'           => 'Rejetée',
                        ];
                    @endphp
                    <li class="activity-item">
                        <div class="activity-avatar">
                            {{ strtoupper(substr($request->user->full_name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="activity-details">
                            <div class="activity-title">{{ $request->user->full_name ?? 'Utilisateur' }}</div>
                            <div class="activity-subtitle">{{ $request->typeFinancement->name ?? 'Type inconnu' }}</div>
                            <div class="activity-meta">{{ $request->created_at->diffForHumans() }}</div>
                        </div>
                        <div class="activity-right">
                            <span class="status-badge status-{{ $request->status }}">
                                {{ $statusLabels[$request->status] ?? $request->status }}
                            </span>
                            <span class="amount">{{ number_format($request->amount_requested ?? 0, 0, ',', ' ') }} FCFA</span>
                        </div>
                    </li>
                @empty
                    <li class="empty-state">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        Aucune demande récente
                    </li>
                @endforelse
            </ul>
        </div>

        {{-- Documents en Attente --}}
        <div class="activity-card">
            <div class="activity-card-header">
                <h3>Documents en Attente</h3>
                <a href="{{ route('admin.documents.pending') }}" class="btn btn-primary">Voir tout</a>
            </div>
            <ul class="activity-list">
                @forelse($pendingDocuments as $doc)
                    <li class="activity-item">
                        <div class="activity-avatar orange">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div class="activity-details">
                            <div class="activity-title">{{ $doc->typeDoc->name ?? 'Document' }}</div>
                            <div class="activity-subtitle">{{ $doc->user->full_name ?? 'Utilisateur' }}</div>
                            <div class="activity-meta">Soumis {{ $doc->created_at->diffForHumans() }}</div>
                        </div>
                        <div class="activity-right">
                            <a href="{{ route('admin.documents.show', $doc) }}" class="btn btn-success">Vérifier</a>
                        </div>
                    </li>
                @empty
                    <li class="empty-state">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
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
    const monthlyData = @json($chartData['by_month']);
    const statusData  = @json($chartData['by_status']);

    const statusLabels = {
        draft:             'Brouillon',
        submitted:         'Soumise',
        under_review:      'En révision',
        pending_committee: 'Comité',
        approved:          'Approuvée',
        funded:            'Financée',
        rejected:          'Rejetée'
    };

    const statusColors = {
        draft:             '#9ca3af',
        submitted:         '#3b82f6',
        under_review:      '#f59e0b',
        pending_committee: '#f97316',
        approved:          '#10b981',
        funded:            '#059669',
        rejected:          '#ef4444'
    };

    // Graphique ligne mensuel
    new Chart(document.getElementById('monthlyChart'), {
        type: 'line',
        data: {
            labels: monthlyData.map(d => d.month),
            datasets: [{
                label: 'Demandes créées',
                data: monthlyData.map(d => d.created),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59,130,246,0.08)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#3b82f6',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }, {
                label: 'Montant financé (M FCFA)',
                data: monthlyData.map(d => d.funded / 1000000),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16,185,129,0.08)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#10b981',
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
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { usePointStyle: true, padding: 14, font: { size: 11, family: "'Plus Jakarta Sans', sans-serif" } }
                },
                tooltip: {
                    backgroundColor: 'rgba(15,23,42,0.92)',
                    padding: 12,
                    cornerRadius: 8,
                    titleFont: { size: 12, family: "'Plus Jakarta Sans', sans-serif" },
                    bodyFont:  { size: 11, family: "'Plus Jakarta Sans', sans-serif" }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11, family: "'Plus Jakarta Sans', sans-serif" } }
                },
                y: {
                    position: 'left',
                    grid: { color: '#f1f5f9' },
                    ticks: { font: { size: 11, family: "'Plus Jakarta Sans', sans-serif" } }
                },
                y1: {
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    ticks: {
                        font: { size: 11, family: "'Plus Jakarta Sans', sans-serif" },
                        callback: v => v + ' M'
                    }
                }
            }
        }
    });

    // Graphique doughnut statuts
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(statusData).map(k => statusLabels[k] ?? k),
            datasets: [{
                data: Object.values(statusData),
                backgroundColor: Object.keys(statusData).map(k => statusColors[k] ?? '#9ca3af'),
                borderWidth: 0,
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { usePointStyle: true, padding: 12, font: { size: 11, family: "'Plus Jakarta Sans', sans-serif" }, boxWidth: 8 }
                },
                tooltip: {
                    backgroundColor: 'rgba(15,23,42,0.92)',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: ctx => {
                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            const pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                            return ` ${ctx.label}: ${ctx.parsed} (${pct}%)`;
                        }
                    }
                }
            }
        }
    });
</script>
@endsection
