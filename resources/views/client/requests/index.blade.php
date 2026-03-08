@extends('layouts.app')

@section('title', 'Mes demandes de financement')
@section('header-title', 'Mes demandes')

@section('header-action')
<a href="{{ route('client.financements.index') }}" class="btn-premium" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: linear-gradient(135deg, #2563eb, #3b82f6); color: white; border-radius: 8px; font-size: 0.875rem; font-weight: 600; text-decoration: none;">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
    </svg>
    Nouvelle demande
</a>
@endsection

@section('content')

@php
    // 🔥 TRADUCTIONS DES STATUTS EN DUR
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
@endphp

<div class="dashboard-container" style="padding-bottom: 100px;">

    {{-- Cartes statistiques --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 0.75rem; margin-bottom: 1.5rem;">

        <a href="{{ route('client.requests.index') }}" style="text-decoration: none;">
            <div class="card-premium {{ !request('status') && !request('payment_status') ? 'active' : '' }}"
                 style="padding: 1rem; text-align: center; {{ !request('status') && !request('payment_status') ? 'border-color: #2563eb; background: #eff6ff;' : '' }}">
                <span style="display: block; font-size: 1.5rem; font-weight: 700; color: #2563eb; line-height: 1;">{{ $stats['all'] }}</span>
                <span style="display: block; font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">Total</span>
            </div>
        </a>

        <a href="{{ route('client.requests.index', ['payment_status' => 'pending']) }}" style="text-decoration: none;">
            <div class="card-premium {{ request('payment_status') === 'pending' ? 'active' : '' }}"
                 style="padding: 1rem; text-align: center; {{ request('payment_status') === 'pending' ? 'border-color: #f59e0b; background: #fef3c7;' : '' }}">
                <span style="display: block; font-size: 1.5rem; font-weight: 700; color: #f59e0b; line-height: 1;">{{ $stats['pending_payment'] }}</span>
                <span style="display: block; font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">Paiement en attente</span>
            </div>
        </a>

        <a href="{{ route('client.requests.index', ['status' => 'submitted']) }}" style="text-decoration: none;">
            <div class="card-premium {{ request('status') === 'submitted' ? 'active' : '' }}"
                 style="padding: 1rem; text-align: center; {{ request('status') === 'submitted' ? 'border-color: #3b82f6; background: #dbeafe;' : '' }}">
                <span style="display: block; font-size: 1.5rem; font-weight: 700; color: #3b82f6; line-height: 1;">{{ $stats['submitted'] }}</span>
                <span style="display: block; font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">Soumises</span>
            </div>
        </a>

        <a href="{{ route('client.requests.index', ['status' => 'under_review']) }}" style="text-decoration: none;">
            <div class="card-premium {{ request('status') === 'under_review' ? 'active' : '' }}"
                 style="padding: 1rem; text-align: center; {{ request('status') === 'under_review' ? 'border-color: #8b5cf6; background: #ede9fe;' : '' }}">
                <span style="display: block; font-size: 1.5rem; font-weight: 700; color: #8b5cf6; line-height: 1;">{{ $stats['under_review'] }}</span>
                <span style="display: block; font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">En examen</span>
            </div>
        </a>

        <a href="{{ route('client.requests.index', ['status' => 'approved']) }}" style="text-decoration: none;">
            <div class="card-premium {{ request('status') === 'approved' ? 'active' : '' }}"
                 style="padding: 1rem; text-align: center; {{ request('status') === 'approved' ? 'border-color: #10b981; background: #d1fae5;' : '' }}">
                <span style="display: block; font-size: 1.5rem; font-weight: 700; color: #10b981; line-height: 1;">{{ $stats['approved'] }}</span>
                <span style="display: block; font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">Approuvées</span>
            </div>
        </a>

        <a href="{{ route('client.requests.index', ['status' => 'funded']) }}" style="text-decoration: none;">
            <div class="card-premium {{ request('status') === 'funded' ? 'active' : '' }}"
                 style="padding: 1rem; text-align: center; {{ request('status') === 'funded' ? 'border-color: #059669; background: #a7f3d0;' : '' }}">
                <span style="display: block; font-size: 1.5rem; font-weight: 700; color: #059669; line-height: 1;">{{ $stats['funded'] }}</span>
                <span style="display: block; font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">Financées</span>
            </div>
        </a>
    </div>

    {{-- Liste des demandes --}}
    <div class="card-premium">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #e2e8f0;">
            <h3 style="font-size: 1rem; font-weight: 700; color: #0f172a; margin: 0;">Liste des demandes</h3>

            @if(request('status') || request('payment_status'))
            <a href="{{ route('client.requests.index') }}" style="font-size: 0.875rem; color: #2563eb; text-decoration: none;">
                Réinitialiser
            </a>
            @endif
        </div>

        @if($requests->count() > 0)
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            @foreach($requests as $request)

            @php
                $statusLabel = $statusLabels[$request->status] ?? $request->status;
                $statusColor = match($request->status) {
                    'draft' => '#6b7280',
                    'submitted' => '#3b82f6',
                    'under_review', 'pending_committee' => '#8b5cf6',
                    'approved' => '#10b981',
                    'funded' => '#059669',
                    'rejected' => '#ef4444',
                    default => '#6b7280',
                };
                $statusBg = match($request->status) {
                    'draft' => '#f3f4f6',
                    'submitted' => '#dbeafe',
                    'under_review', 'pending_committee' => '#ede9fe',
                    'approved' => '#d1fae5',
                    'funded' => '#a7f3d0',
                    'rejected' => '#fee2e2',
                    default => '#f3f4f6',
                };
            @endphp

            <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem; border-left: 4px solid {{ $statusColor }};">

                {{-- En-tête --}}
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                            <span style="font-family: monospace; font-size: 0.875rem; font-weight: 600; color: #2563eb; background: #eff6ff; padding: 0.25rem 0.5rem; border-radius: 6px;">{{ $request->request_number }}</span>
                            <span style="font-size: 0.75rem; color: #94a3b8;">{{ $request->created_at->format('d/m/Y') }}</span>
                        </div>
                        <h4 style="font-size: 1rem; font-weight: 600; color: #0f172a; margin: 0;">{{ $request->title }}</h4>
                    </div>

                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        {{-- Badge paiement --}}
                        @if($request->payment_status === 'pending' && $request->status === 'draft')
                        <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.35rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background: #fef3c7; color: #92400e;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="14" height="14">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Paiement requis
                        </span>
                        @elseif($request->payment_status === 'paid')
                        <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.35rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background: #dcfce7; color: #166534;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="14" height="14">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Payé
                        </span>
                        @endif

                        {{-- Badge statut --}}
                        <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.35rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background: {{ $statusBg }}; color: {{ $statusColor }};">
                            {{ $statusLabel }}
                        </span>
                    </div>
                </div>

                {{-- Corps --}}
                <div style="display: grid; grid-template-columns: 1fr auto; gap: 1rem; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #f1f5f9;">
                    <div>
                        <p style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: #64748b; margin: 0 0 0.5rem;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $request->typeFinancement->name ?? 'N/A' }}
                        </p>
                    </div>

                    <div style="text-align: right;">
                        <div style="font-size: 1rem; font-weight: 700; color: #2563eb;">{{ number_format($request->amount_requested, 0, ',', ' ') }} FCFA</div>
                        <div style="font-size: 0.75rem; color: #94a3b8;">{{ $request->duration }} mois</div>
                    </div>
                </div>

                {{-- Actions --}}
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">

                    {{-- ÉTAPE 1: Paiement en attente --}}
                    @if($request->payment_status === 'pending' && $request->status === 'draft')
                        <a href="{{ route('client.requests.payment', $request) }}" class="btn-premium" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1rem; background: linear-gradient(135deg, #f59e0b, #d97706); color: white; border-radius: 8px; font-size: 0.875rem; font-weight: 600; text-decoration: none;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a1 1 0 11-2 0 1 1 0 012 0z" />
                            </svg>
                            Payer
                        </a>

                        <form action="{{ route('client.requests.destroy', $request) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Annuler cette demande ?')" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1rem; background: #fee2e2; color: #dc2626; border: none; border-radius: 8px; font-size: 0.875rem; font-weight: 600; cursor: pointer;">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Annuler
                            </button>
                        </form>

                    {{-- ÉTAPE 2: Payé mais documents manquants --}}
                    @elseif($request->isPaid() && $request->pendingDocumentsCount() > 0)
                        <a href="{{ route('client.documents.required', $request) }}" class="btn-premium" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1rem; background: linear-gradient(135deg, #f59e0b, #d97706); color: white; border-radius: 8px; font-size: 0.875rem; font-weight: 600; text-decoration: none;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            {{ $request->pendingDocumentsCount() }} document(s) manquant(s)
                        </a>

                    {{-- ÉTAPE 3+: Voir détails --}}
                    @else
                        <a href="{{ route('client.requests.show', $request) }}" class="btn-action" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1rem; background: #f8fafc; border: 1px solid #e2e8f0; color: #475569; border-radius: 8px; font-size: 0.875rem; font-weight: 600; text-decoration: none;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Voir détails
                        </a>
                    @endif

                </div>

            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div style="margin-top: 1.5rem; display: flex; justify-content: center;">
            {{ $requests->links() }}
        </div>

        @else
        {{-- État vide --}}
        <div style="text-align: center; padding: 3rem 1.5rem;">
            <div style="width: 64px; height: 64px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: #94a3b8;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="32" height="32">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h3 style="font-size: 1.125rem; font-weight: 600; color: #0f172a; margin: 0 0 0.5rem;">Aucune demande trouvée</h3>
            <p style="color: #64748b; margin: 0 0 1.5rem; font-size: 0.875rem;">Vous n'avez pas encore de demande de financement{{ request('status') ? ' avec ce statut' : '' }}.</p>
            <a href="{{ route('client.financements.index') }}" class="btn-premium" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.875rem 1.5rem; background: linear-gradient(135deg, #2563eb, #3b82f6); color: white; border-radius: 10px; font-weight: 600; text-decoration: none;">
                Découvrir les financements
            </a>
        </div>
        @endif
    </div>

</div>

@endsection
@section('styles')
<style>
    .requests-index {
        max-width: 1200px;
        margin: 0 auto;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: var(--surface);
        border: 2px solid var(--border);
        border-radius: var(--radius);
        padding: 1.25rem;
        text-align: center;
        transition: all 0.2s;
    }

    .stat-card:hover,
    .stat-card.active {
        border-color: var(--primary);
        background: #eff6ff;
    }

    .stat-link {
        text-decoration: none;
        color: inherit;
        display: block;
    }

    .stat-value {
        display: block;
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--primary);
        line-height: 1;
    }

    .stat-label {
        display: block;
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-top: 0.5rem;
    }

    /* Request Items */
    .requests-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .request-item {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 1.5rem;
        transition: all 0.2s;
    }

    .request-item:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .request-item.draft {
        border-left: 4px solid #f59e0b;
    }

    .request-item.submitted {
        border-left: 4px solid #3b82f6;
    }

    .request-item.under_review,
    .request-item.pending_committee {
        border-left: 4px solid #8b5cf6;
    }

    .request-item.approved {
        border-left: 4px solid #10b981;
    }

    .request-item.funded {
        border-left: 4px solid #059669;
    }

    .request-item.rejected {
        border-left: 4px solid #ef4444;
    }

    /* Header */
    .request-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .request-id {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .request-number {
        font-family: monospace;
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--primary);
        background: #eff6ff;
        padding: 0.25rem 0.5rem;
        border-radius: var(--radius-sm);
    }

    .request-date {
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    .request-badges {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .badge-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-success {
        background: #dcfce7;
        color: #166534;
    }

    .badge-danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-draft {
        background: #f3f4f6;
        color: #6b7280;
    }

    .badge-submitted {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge-under_review,
    .badge-pending_committee {
        background: #ede9fe;
        color: #5b21b6;
    }

    .badge-approved {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-funded {
        background: #a7f3d0;
        color: #064e3b;
    }

    .badge-rejected {
        background: #fee2e2;
        color: #991b1b;
    }

    /* Body */
    .request-body {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 1.5rem;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border);
    }

    @media (max-width: 640px) {
        .request-body {
            grid-template-columns: 1fr;
        }
    }

    .request-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text);
        margin: 0 0 0.5rem;
    }

    .request-type {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
        color: var(--text-muted);
        margin: 0;
    }

    .request-amounts {
        display: flex;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    .amount-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .amount-label {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .amount-value {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text);
    }

    .amount-value.text-success {
        color: #059669;
    }

    /* Actions */
    .request-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        position: relative;
        overflow: hidden;
        transition: all 0.2s ease;
    }

    .btn-icon {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-text {
        font-weight: 500;
    }

    .btn-arrow {
        display: flex;
        align-items: center;
        margin-left: 0.25rem;
        transition: transform 0.2s ease;
    }

    /* Bouton Voir détails amélioré */
    .btn-view-details {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid #cbd5e1;
        color: #475569;
    }

    .btn-view-details:hover {
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        border-color: #94a3b8;
        color: #334155;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .btn-view-details:hover .btn-arrow {
        transform: translateX(4px);
    }

    .inline-form {
        display: inline;
    }

    /* Progress */
    .request-progress {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px dashed var(--border);
    }

    .progress-steps {
        display: flex;
        justify-content: space-between;
        position: relative;
    }

    .progress-steps::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 10%;
        right: 10%;
        height: 2px;
        background: var(--border);
        z-index: 0;
    }

    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        z-index: 1;
        flex: 1;
    }

    .step-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--bg);
        border: 2px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
    }

    .step.completed .step-icon {
        background: #dcfce7;
        border-color: #22c55e;
        color: #166534;
    }

    .step.current .step-icon {
        background: #3b82f6;
        border-color: #3b82f6;
        color: white;
        animation: pulse 2s infinite;
    }

    .step-label {
        font-size: 0.75rem;
        color: var(--text-muted);
        text-align: center;
    }

    .step.completed .step-label,
    .step.current .step-label {
        color: var(--text);
        font-weight: 500;
    }

    @keyframes pulse {

        0%,
        100% {
            box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4);
        }

        50% {
            box-shadow: 0 0 0 8px rgba(59, 130, 246, 0);
        }
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 3rem 1.5rem;
    }

    .empty-icon {
        color: var(--text-muted);
        margin-bottom: 1rem;
    }

    .empty-state h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text);
        margin: 0 0 0.5rem;
    }

    .empty-state p {
        color: var(--text-muted);
        margin: 0 0 1.5rem;
    }

    /* Pagination */
    .pagination-wrapper {
        margin-top: 1.5rem;
        display: flex;
        justify-content: center;
    }
</style>
@endsection
