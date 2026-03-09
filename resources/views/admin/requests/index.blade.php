@extends('layouts.admin')

@section('title', 'Demandes de Financement')

@section('header-title', 'Gestion des Demandes')

@section('styles')
<style>
    /* BUTTON STYLES - ESSENTIAL */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.75rem;
    }

    .btn-primary {
        background: #3b82f6;
        color: white;
    }

    .btn-primary:hover {
        background: #2563eb;
    }

    .btn-success {
        background: #10b981;
        color: white;
    }

    .btn-success:hover {
        background: #059669;
    }

    .btn-secondary {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
    }

    .btn-secondary:hover {
        background: #e2e8f0;
    }

    /* Filter Bar */
    .filter-bar {
        background: white;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .filter-input, .filter-select {
        padding: 0.5rem 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.875rem;
        min-width: 200px;
    }

    .filter-input:focus, .filter-select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    /* Data Table */
    .data-table-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th {
        background: #f8fafc;
        padding: 1rem;
        text-align: left;
        font-size: 0.75rem;
        text-transform: uppercase;
        color: #64748b;
        font-weight: 600;
        border-bottom: 1px solid #e2e8f0;
    }

    .data-table td {
        padding: 1rem;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.875rem;
        color: #334155;
        vertical-align: middle;
    }

    .data-table tr:hover {
        background: #f8fafc;
    }

    .data-table tr:last-child td {
        border-bottom: none;
    }

    /* Status Badges */
    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: capitalize;
    }

    .status-draft { background: #f3f4f6; color: #6b7280; }
    .status-submitted { background: #dbeafe; color: #1e40af; }
    .status-under_review { background: #fef3c7; color: #92400e; }
    .status-pending_committee { background: #ffedd5; color: #c2410c; }
    .status-approved { background: #d1fae5; color: #065f46; }
    .status-funded { background: #dcfce7; color: #166534; }
    .status-rejected { background: #fee2e2; color: #991b1b; }
    .status-cancelled { background: #f3f4f6; color: #6b7280; }
    .status-completed { background: #e0e7ff; color: #3730a3; }

    /* User Info */
    .user-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .user-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .user-details {
        display: flex;
        flex-direction: column;
    }

    .user-name {
        font-weight: 600;
        color: #1e293b;
    }

    .user-email {
        font-size: 0.75rem;
        color: #64748b;
    }

    /* Amount */
    .amount {
        font-weight: 600;
        color: #1e293b;
    }

    .amount-approved {
        color: #059669;
    }

    /* Actions */
    .actions {
        display: flex;
        gap: 0.5rem;
    }

    /* Pagination */
    .pagination-container {
        padding: 1rem 1.5rem;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .pagination-info {
        font-size: 0.875rem;
        color: #64748b;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #94a3b8;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        display: block;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .data-table {
            display: block;
            overflow-x: auto;
        }

        .filter-bar {
            flex-direction: column;
            align-items: stretch;
        }

        .filter-input, .filter-select {
            width: 100%;
        }
    }
</style>
@endsection

@section('content')
    <!-- Filters -->
    <div class="filter-bar">
        <form method="GET" action="{{ route('admin.requests.index') }}" style="display: contents;">
            <input type="text" 
                   name="search" 
                   class="filter-input" 
                   placeholder="Rechercher (nom, email, n° demande...)" 
                   value="{{ request('search') }}">

            <select name="status" class="filter-select">
                <option value="">Tous les statuts</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Brouillon</option>
                <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Soumise</option>
                <option value="under_review" {{ request('status') == 'under_review' ? 'selected' : '' }}>En révision</option>
                <option value="pending_committee" {{ request('status') == 'pending_committee' ? 'selected' : '' }}>En attente comité</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approuvée</option>
                <option value="funded" {{ request('status') == 'funded' ? 'selected' : '' }}>Financée</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejetée</option>
            </select>

            <select name="typefinancement_id" class="filter-select">
                <option value="">Tous les types</option>
                @foreach($typeFinancements as $id => $name)
                    <option value="{{ $id }}" {{ request('typefinancement_id') == $id ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter"></i> Filtrer
            </button>

            @if(request()->hasAny(['search', 'status', 'typefinancement_id']))
                <a href="{{ route('admin.requests.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Réinitialiser
                </a>
            @endif
        </form>

        <a href="{{ route('admin.requests.export') }}?{{ http_build_query(request()->only(['status', 'typefinancement_id'])) }}" class="btn btn-success" style="margin-left: auto;">
            <i class="fas fa-download"></i> Export CSV
        </a>
    </div>

    <!-- Data Table -->
    <div class="data-table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>N° Demande</th>
                    <th>Demandeur</th>
                    <th>Type de Financement</th>
                    <th>Montant Demandé</th>
                    <th>Montant Approuvé</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $request)
                    <tr>
                        <td>
                            <strong>{{ $request->request_number }}</strong>
                        </td>
                        <td>
                            <div class="user-info">
                                <div class="user-avatar">
                                    {{ substr($request->user->full_name ?? 'U', 0, 1) }}
                                </div>
                                <div class="user-details">
                                    <span class="user-name">{{ $request->user->full_name ?? 'N/A' }}</span>
                                    <span class="user-email">{{ $request->user->email ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </td>
                        <td>{{ $request->typeFinancement->name ?? 'N/A' }}</td>
                        <td class="amount">{{ number_format($request->amount_requested, 0, ',', ' ') }} FCFA</td>
                        <td class="amount {{ $request->amount_approved ? 'amount-approved' : '' }}">
                            {{ $request->amount_approved ? number_format($request->amount_approved, 0, ',', ' ') . ' FCFA' : '-' }}
                        </td>
                        <td>
                            @php
                                $statusLabels = [
                                    'draft' => 'Brouillon',
                                    'submitted' => 'Soumise',
                                    'under_review' => 'En révision',
                                    'pending_committee' => 'En attente comité',
                                    'approved' => 'Approuvée',
                                    'funded' => 'Financée',
                                    'rejected' => 'Rejetée',
                                    'cancelled' => 'Annulée',
                                    'completed' => 'Terminée',
                                ];
                            @endphp
                            <span class="status-badge status-{{ $request->status }}">
                                {{ $statusLabels[$request->status] ?? $request->status }}
                            </span>
                        </td>
                        <td>{{ $request->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('admin.requests.show', $request) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> Voir
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <p>Aucune demande trouvée</p>
                                @if(request()->hasAny(['search', 'status', 'typefinancement_id']))
                                    <a href="{{ route('admin.requests.index') }}" style="color: #3b82f6; margin-top: 0.5rem; display: inline-block;">
                                        Voir toutes les demandes
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($requests->hasPages())
            <div class="pagination-container">
                <div class="pagination-info">
                    Affichage de {{ $requests->firstItem() }} à {{ $requests->lastItem() }} sur {{ $requests->total() }} résultats
                </div>
                <div class="pagination-links">
                    {{ $requests->appends(request()->except('page'))->links('pagination::simple-tailwind') }}
                </div>
            </div>
        @endif
    </div>
@endsection

@section('scripts')
<script>
    // Auto-submit form on select change (optional)
    document.querySelectorAll('.filter-select').forEach(select => {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });
</script>
@endsection