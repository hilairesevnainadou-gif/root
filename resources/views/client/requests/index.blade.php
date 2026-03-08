@extends('layouts.app')

@section('title', 'Mes demandes de financement')
@section('header-title', 'Mes demandes')

@section('header-action')
<a href="{{ route('client.financements.index') }}" class="btn btn-primary">
    + Nouvelle demande
</a>
@endsection

@section('content')

@php
    $statusLabels = [
        'draft' => 'Brouillon',
        'submitted' => 'Soumise',
        'under_review' => 'En cours d\'examen',
        'pending_committee' => 'En attente du comité',
        'approved' => 'Approuvée',
        'rejected' => 'Rejetée',
        'funded' => 'Financée',
        'completed' => 'Terminée',
        'cancelled' => 'Annulée',
    ];

    $statusColors = [
        'draft' => ['bg' => '#f3f4f6', 'text' => '#374151', 'border' => '#d1d5db'],
        'submitted' => ['bg' => '#dbeafe', 'text' => '#1e40af', 'border' => '#3b82f6'],
        'under_review' => ['bg' => '#ede9fe', 'text' => '#5b21b6', 'border' => '#8b5cf6'],
        'pending_committee' => ['bg' => '#ede9fe', 'text' => '#5b21b6', 'border' => '#8b5cf6'],
        'approved' => ['bg' => '#d1fae5', 'text' => '#065f46', 'border' => '#10b981'],
        'funded' => ['bg' => '#a7f3d0', 'text' => '#064e3b', 'border' => '#059669'],
        'rejected' => ['bg' => '#fee2e2', 'text' => '#991b1b', 'border' => '#ef4444'],
        'completed' => ['bg' => '#f3f4f6', 'text' => '#374151', 'border' => '#9ca3af'],
        'cancelled' => ['bg' => '#f3f4f6', 'text' => '#6b7280', 'border' => '#d1d5db'],
    ];
@endphp

<div class="requests-container">

    {{-- Filtres statistiques --}}
    <div class="stats-grid">
        <a href="{{ route('client.requests.index') }}" class="stat-link {{ !request('status') && !request('payment_status') ? 'active' : '' }}">
            <div class="stat-card">
                <span class="stat-value">{{ $stats['all'] }}</span>
                <span class="stat-label">Total</span>
            </div>
        </a>

        <a href="{{ route('client.requests.index', ['payment_status' => 'pending']) }}" class="stat-link {{ request('payment_status') === 'pending' ? 'active' : '' }}">
            <div class="stat-card stat-warning">
                <span class="stat-value">{{ $stats['pending_payment'] }}</span>
                <span class="stat-label">Paiement en attente</span>
            </div>
        </a>

        <a href="{{ route('client.requests.index', ['status' => 'submitted']) }}" class="stat-link {{ request('status') === 'submitted' ? 'active' : '' }}">
            <div class="stat-card stat-info">
                <span class="stat-value">{{ $stats['submitted'] }}</span>
                <span class="stat-label">Soumises</span>
            </div>
        </a>

        <a href="{{ route('client.requests.index', ['status' => 'under_review']) }}" class="stat-link {{ request('status') === 'under_review' ? 'active' : '' }}">
            <div class="stat-card stat-purple">
                <span class="stat-value">{{ $stats['under_review'] }}</span>
                <span class="stat-label">En examen</span>
            </div>
        </a>

        <a href="{{ route('client.requests.index', ['status' => 'approved']) }}" class="stat-link {{ request('status') === 'approved' ? 'active' : '' }}">
            <div class="stat-card stat-success">
                <span class="stat-value">{{ $stats['approved'] }}</span>
                <span class="stat-label">Approuvées</span>
            </div>
        </a>

        <a href="{{ route('client.requests.index', ['status' => 'funded']) }}" class="stat-link {{ request('status') === 'funded' ? 'active' : '' }}">
            <div class="stat-card stat-funded">
                <span class="stat-value">{{ $stats['funded'] }}</span>
                <span class="stat-label">Financées</span>
            </div>
        </a>
    </div>

    {{-- Liste des demandes --}}
    <div class="card">
        <div class="card-header">
            <h3>Liste des demandes</h3>
            @if(request('status') || request('payment_status'))
                <a href="{{ route('client.requests.index') }}" class="btn-reset">Reinitialiser les filtres</a>
            @endif
        </div>

        @if($requests->count() > 0)
            <div class="requests-list">
                @foreach($requests as $request)
                    @php
                        $colors = $statusColors[$request->status] ?? $statusColors['draft'];
                        $statusLabel = $statusLabels[$request->status] ?? $request->status;
                    @endphp

                    <div class="request-item" style="border-left-color: {{ $colors['border'] }}">

                        {{-- En-tete --}}
                        <div class="request-header">
                            <div class="request-meta">
                                <div class="request-ids">
                                    <span class="request-number">{{ $request->request_number }}</span>
                                    <span class="request-date">{{ $request->created_at->format('d/m/Y') }}</span>
                                </div>
                                <h4 class="request-title">{{ $request->title }}</h4>
                            </div>

                            <div class="request-badges">
                                @if($request->payment_status === 'pending' && $request->status === 'draft')
                                    <span class="badge badge-warning">Paiement requis</span>
                                @elseif($request->payment_status === 'paid')
                                    <span class="badge badge-success">Paye</span>
                                @endif
                                <span class="badge" style="background: {{ $colors['bg'] }}; color: {{ $colors['text'] }}; border: 1px solid {{ $colors['border'] }}">
                                    {{ $statusLabel }}
                                </span>
                            </div>
                        </div>

                        {{-- Corps --}}
                        <div class="request-body">
                            <div class="request-details">
                                <p class="request-type">{{ $request->typeFinancement->name ?? 'Non specifie' }}</p>
                            </div>
                            <div class="request-amount">
                                <span class="amount-value">{{ number_format($request->amount_requested, 0, ',', ' ') }} FCFA</span>
                                <span class="amount-duration">{{ $request->duration }} mois</span>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="request-actions">

                            {{-- Etape 1: Paiement en attente --}}
                            @if($request->payment_status === 'pending' && $request->status === 'draft')
                                <a href="{{ route('client.requests.payment', $request) }}" class="btn btn-warning">
                                    Payer maintenant
                                </a>
                                <form action="{{ route('client.requests.destroy', $request) }}" method="POST" class="form-inline" onsubmit="return confirm('Annuler cette demande ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Annuler</button>
                                </form>

                            {{-- Etape 2: Documents manquants --}}
                            @elseif($request->isPaid() && $request->pendingDocumentsCount() > 0)
                                <a href="{{ route('client.documents.required', $request) }}" class="btn btn-warning">
                                    Completer les documents ({{ $request->pendingDocumentsCount() }})
                                </a>

                            {{-- Etape 3: Voir details --}}
                            @else
                                <a href="{{ route('client.requests.show', $request) }}" class="btn btn-secondary">
                                    Voir les details ->
                                </a>
                            @endif

                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="pagination-wrapper">
                {{ $requests->links() }}
            </div>

        @else
            {{-- Etat vide --}}
            <div class="empty-state">
                <div class="empty-icon">[ ]</div>
                <h3>Aucune demande trouvee</h3>
                <p>Vous n'avez pas encore de demande de financement{{ request('status') ? ' avec ce statut' : '' }}.</p>
                <a href="{{ route('client.financements.index') }}" class="btn btn-primary btn-lg">
                    Decouvrir les financements
                </a>
            </div>
        @endif
    </div>

</div>

@endsection

@section('styles')
<style>
.requests-container {
    max-width: 1000px;
    margin: 0 auto;
    padding-bottom: 2rem;
}

/* Boutons */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.625rem 1.25rem;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 600;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary {
    background: linear-gradient(135deg, #2563eb, #3b82f6);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

.btn-secondary {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #475569;
}

.btn-secondary:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}

.btn-warning {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
}

.btn-warning:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
}

.btn-danger {
    background: #fee2e2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.btn-danger:hover {
    background: #fecaca;
}

.btn-lg {
    padding: 0.875rem 1.5rem;
    font-size: 1rem;
}

.btn-reset {
    font-size: 0.875rem;
    color: #2563eb;
    text-decoration: none;
}

.btn-reset:hover {
    text-decoration: underline;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.stat-link {
    text-decoration: none;
    color: inherit;
}

.stat-card {
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 1rem;
    text-align: center;
    transition: all 0.2s;
}

.stat-card:hover {
    border-color: #cbd5e1;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.stat-link.active .stat-card {
    border-color: #2563eb;
    background: #eff6ff;
}

.stat-value {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: #2563eb;
    line-height: 1;
}

.stat-label {
    display: block;
    font-size: 0.75rem;
    color: #64748b;
    margin-top: 0.25rem;
    font-weight: 500;
}

/* Variantes de couleurs pour les stats */
.stat-warning .stat-value { color: #f59e0b; }
.stat-warning.active .stat-card { border-color: #f59e0b; background: #fef3c7; }

.stat-info .stat-value { color: #3b82f6; }
.stat-info.active .stat-card { border-color: #3b82f6; background: #dbeafe; }

.stat-purple .stat-value { color: #8b5cf6; }
.stat-purple.active .stat-card { border-color: #8b5cf6; background: #ede9fe; }

.stat-success .stat-value { color: #10b981; }
.stat-success.active .stat-card { border-color: #10b981; background: #d1fae5; }

.stat-funded .stat-value { color: #059669; }
.stat-funded.active .stat-card { border-color: #059669; background: #a7f3d0; }

/* Card */
.card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.5rem;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #f1f5f9;
}

.card-header h3 {
    font-size: 1.125rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}

/* Liste des demandes */
.requests-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.request-item {
    background: white;
    border: 1px solid #e2e8f0;
    border-left-width: 4px;
    border-radius: 12px;
    padding: 1.25rem;
    transition: all 0.2s;
}

.request-item:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    border-color: #cbd5e1;
}

/* Header de demande */
.request-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.request-ids {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.5rem;
}

.request-number {
    font-family: monospace;
    font-size: 0.875rem;
    font-weight: 600;
    color: #2563eb;
    background: #eff6ff;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
}

.request-date {
    font-size: 0.75rem;
    color: #94a3b8;
}

.request-title {
    font-size: 1rem;
    font-weight: 600;
    color: #0f172a;
    margin: 0;
}

.request-badges {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.badge {
    display: inline-flex;
    align-items: center;
    padding: 0.35rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-warning {
    background: #fef3c7;
    color: #92400e;
}

.badge-success {
    background: #dcfce7;
    color: #166534;
}

/* Corps de demande */
.request-body {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
    margin-bottom: 1rem;
    border-top: 1px solid #f1f5f9;
    border-bottom: 1px solid #f1f5f9;
}

.request-type {
    font-size: 0.875rem;
    color: #64748b;
    margin: 0;
}

.request-amount {
    text-align: right;
}

.amount-value {
    display: block;
    font-size: 1.125rem;
    font-weight: 700;
    color: #2563eb;
}

.amount-duration {
    font-size: 0.75rem;
    color: #94a3b8;
}

/* Actions */
.request-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.form-inline {
    display: inline;
}

/* Pagination */
.pagination-wrapper {
    margin-top: 1.5rem;
    display: flex;
    justify-content: center;
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 3rem 1.5rem;
}

.empty-icon {
    width: 64px;
    height: 64px;
    background: #f1f5f9;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    color: #94a3b8;
    font-size: 1.5rem;
    font-weight: 300;
}

.empty-state h3 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #0f172a;
    margin: 0 0 0.5rem;
}

.empty-state p {
    color: #64748b;
    margin: 0 0 1.5rem;
    font-size: 0.875rem;
}

/* Responsive */
@media (max-width: 640px) {
    .request-header {
        flex-direction: column;
    }

    .request-body {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .request-amount {
        text-align: left;
    }

    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>
@endsection
