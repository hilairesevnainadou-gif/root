@extends('layouts.admin')

@section('title', 'Documents en Attente')

@section('header-title', 'Vérification des Documents')

@section('styles')
<style>
    /* Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.25rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .stat-icon.primary { background: #dbeafe; color: #1e40af; }
    .stat-icon.warning { background: #fef3c7; color: #92400e; }
    .stat-icon.success { background: #d1fae5; color: #065f46; }

    .stat-content h3 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }

    .stat-content p {
        font-size: 0.875rem;
        color: #64748b;
        margin: 0;
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

    .filter-select {
        padding: 0.5rem 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.875rem;
        min-width: 250px;
    }

    .filter-select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .btn-filter {
        padding: 0.5rem 1rem;
        background: #3b82f6;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-filter:hover {
        background: #2563eb;
    }

    /* Bulk Actions */
    .bulk-actions {
        background: #f8fafc;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        display: none;
        align-items: center;
        gap: 1rem;
    }

    .bulk-actions.show {
        display: flex;
    }

    .bulk-actions span {
        font-size: 0.875rem;
        color: #64748b;
    }

    .btn-bulk {
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        border: none;
        transition: all 0.2s;
    }

    .btn-bulk-verify {
        background: #10b981;
        color: white;
    }

    .btn-bulk-verify:hover {
        background: #059669;
    }

    .btn-bulk-reject {
        background: #ef4444;
        color: white;
    }

    .btn-bulk-reject:hover {
        background: #dc2626;
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

    .data-table th:first-child {
        width: 40px;
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

    /* Checkbox */
    .custom-checkbox {
        width: 18px;
        height: 18px;
        border: 2px solid #cbd5e1;
        border-radius: 4px;
        cursor: pointer;
        position: relative;
        transition: all 0.2s;
    }

    .custom-checkbox:checked {
        background: #3b82f6;
        border-color: #3b82f6;
    }

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

    /* Document Info */
    .doc-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .doc-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background: #fee2e2;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #dc2626;
        font-size: 1.25rem;
    }

    .doc-icon.pdf { background: #fee2e2; color: #dc2626; }
    .doc-icon.image { background: #dbeafe; color: #1e40af; }
    .doc-icon.word { background: #dbeafe; color: #2563eb; }
    .doc-icon.excel { background: #d1fae5; color: #059669; }

    .doc-details {
        display: flex;
        flex-direction: column;
    }

    .doc-name {
        font-weight: 600;
        color: #1e293b;
    }

    .doc-meta {
        font-size: 0.75rem;
        color: #64748b;
    }

    /* Status Badge */
    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }

    /* Request Link */
    .request-link {
        color: #3b82f6;
        text-decoration: none;
        font-weight: 500;
    }

    .request-link:hover {
        text-decoration: underline;
    }

    /* Actions */
    .actions {
        display: flex;
        gap: 0.5rem;
    }

    .btn-action {
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }

    .btn-view {
        background: #dbeafe;
        color: #1e40af;
    }

    .btn-view:hover {
        background: #bfdbfe;
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
        color: #22c55e;
    }

    .empty-state h3 {
        font-size: 1.25rem;
        color: #1e293b;
        margin-bottom: 0.5rem;
    }

    /* Type Breakdown */
    .type-breakdown {
        background: white;
        border-radius: 12px;
        padding: 1.25rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        margin-bottom: 1.5rem;
    }

    .type-breakdown h4 {
        font-size: 0.875rem;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 1rem;
        text-transform: uppercase;
    }

    .type-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .type-item {
        background: #f1f5f9;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.875rem;
        color: #334155;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .type-item .count {
        background: #3b82f6;
        color: white;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 600;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .data-table {
            display: block;
            overflow-x: auto;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-content">
                <h3>{{ $stats['total_pending'] }}</h3>
                <p>Documents en attente</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3>{{ $documents->count() }}</h3>
                <p>Sur cette page</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3>{{ $stats['by_type']->count() }}</h3>
                <p>Types de documents</p>
            </div>
        </div>
    </div>

    <!-- Type Breakdown -->
    @if($stats['by_type']->count() > 0)
        <div class="type-breakdown">
            <h4>Répartition par type</h4>
            <div class="type-list">
                @foreach($stats['by_type'] as $type)
                    <div class="type-item">
                        <span>{{ $type->typeDoc->name ?? 'Inconnu' }}</span>
                        <span class="count">{{ $type->count }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Filters -->
    <div class="filter-bar">
        <form method="GET" action="{{ route('admin.documents.pending') }}" style="display: contents;">
            <select name="typedoc_id" class="filter-select">
                <option value="">Tous les types de documents</option>
                @foreach($stats['by_type'] as $type)
                    <option value="{{ $type->typedoc_id }}" {{ request('typedoc_id') == $type->typedoc_id ? 'selected' : '' }}>
                        {{ $type->typeDoc->name ?? 'Inconnu' }} ({{ $type->count }})
                    </option>
                @endforeach
            </select>

            <button type="submit" class="btn-filter">
                <i class="fas fa-filter"></i> Filtrer
            </button>

            @if(request('typedoc_id'))
                <a href="{{ route('admin.documents.pending') }}" class="btn-action" style="background: #f1f5f9; color: #64748b;">
                    <i class="fas fa-times"></i> Réinitialiser
                </a>
            @endif
        </form>
    </div>

    <!-- Bulk Actions -->
    <div class="bulk-actions" id="bulkActions">
        <span><span id="selectedCount">0</span> document(s) sélectionné(s)</span>
        <button type="button" class="btn-bulk btn-bulk-verify" onclick="bulkVerify()">
            <i class="fas fa-check"></i> Approuver
        </button>
        <button type="button" class="btn-bulk btn-bulk-reject" onclick="bulkReject()">
            <i class="fas fa-times"></i> Rejeter
        </button>
    </div>

    <!-- Data Table -->
    <div class="data-table-container">
        <form id="bulkForm" method="POST" action="{{ route('admin.documents.bulk') }}">
            @csrf
            <input type="hidden" name="status" id="bulkStatus">
            <input type="hidden" name="rejection_reason" id="bulkRejectionReason">

            <table class="data-table">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" class="custom-checkbox" id="selectAll" onchange="toggleSelectAll()">
                        </th>
                        <th>Utilisateur</th>
                        <th>Document</th>
                        <th>Type</th>
                        <th>Demande associée</th>
                        <th>Date de soumission</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $doc)
                        <tr>
                            <td>
                                <input type="checkbox" 
                                       class="custom-checkbox doc-checkbox" 
                                       name="document_ids[]" 
                                       value="{{ $doc->id }}"
                                       onchange="updateBulkActions()">
                            </td>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar">
                                        {{ substr($doc->user->full_name ?? 'U', 0, 1) }}
                                    </div>
                                    <div class="user-details">
                                        <span class="user-name">{{ $doc->user->full_name ?? 'N/A' }}</span>
                                        <span class="user-email">{{ $doc->user->email ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="doc-info">
                                    @php
                                        $iconClass = match($doc->file_type) {
                                            'application/pdf' => 'pdf',
                                            'image/jpeg', 'image/png', 'image/gif' => 'image',
                                            'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'word',
                                            'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'excel',
                                            default => 'pdf'
                                        };
                                        $icon = match($iconClass) {
                                            'pdf' => 'fas fa-file-pdf',
                                            'image' => 'fas fa-file-image',
                                            'word' => 'fas fa-file-word',
                                            'excel' => 'fas fa-file-excel',
                                            default => 'fas fa-file'
                                        };
                                    @endphp
                                    <div class="doc-icon {{ $iconClass }}">
                                        <i class="{{ $icon }}"></i>
                                    </div>
                                    <div class="doc-details">
                                        <span class="doc-name">{{ $doc->file_name ?? 'Document' }}</span>
                                        <span class="doc-meta">{{ $doc->formatted_size ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge status-pending">
                                    {{ $doc->typeDoc->name ?? 'Inconnu' }}
                                </span>
                            </td>
                            <td>
                                @if($doc->fundingRequest)
                                    <a href="{{ route('admin.requests.show', $doc->fundingRequest) }}" class="request-link">
                                        {{ $doc->fundingRequest->request_number ?? 'N/A' }}
                                    </a>
                                @else
                                    <span style="color: #94a3b8;">-</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; flex-direction: column;">
                                    <span>{{ $doc->created_at->format('d/m/Y') }}</span>
                                    <span style="font-size: 0.75rem; color: #94a3b8;">{{ $doc->created_at->diffForHumans() }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('admin.documents.show', $doc) }}" class="btn-action btn-view">
                                        <i class="fas fa-eye"></i> Vérifier
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fas fa-check-circle"></i>
                                    <h3>Tout est à jour !</h3>
                                    <p>Aucun document en attente de vérification</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </form>

        @if($documents->hasPages())
            <div class="pagination-container">
                <div class="pagination-info">
                    Affichage de {{ $documents->firstItem() }} à {{ $documents->lastItem() }} sur {{ $documents->total() }} résultats
                </div>
                <div class="pagination-links">
                    {{ $documents->appends(request()->except('page'))->links('pagination::simple-tailwind') }}
                </div>
            </div>
        @endif
    </div>
@endsection

@section('scripts')
<script>
    // Select All Checkbox
    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.doc-checkbox');

        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });

        updateBulkActions();
    }

    // Update Bulk Actions visibility
    function updateBulkActions() {
        const checkboxes = document.querySelectorAll('.doc-checkbox:checked');
        const bulkActions = document.getElementById('bulkActions');
        const selectedCount = document.getElementById('selectedCount');

        selectedCount.textContent = checkboxes.length;

        if (checkboxes.length > 0) {
            bulkActions.classList.add('show');
        } else {
            bulkActions.classList.remove('show');
        }
    }

    // Bulk Verify
    function bulkVerify() {
        if (!confirm('Êtes-vous sûr de vouloir approuver les documents sélectionnés ?')) {
            return;
        }

        document.getElementById('bulkStatus').value = 'verified';
        document.getElementById('bulkForm').submit();
    }

    // Bulk Reject
    function bulkReject() {
        const reason = prompt('Motif du rejet :');
        if (!reason || reason.trim() === '') {
            alert('Veuillez indiquer un motif de rejet.');
            return;
        }

        if (!confirm('Êtes-vous sûr de vouloir rejeter les documents sélectionnés ?')) {
            return;
        }

        document.getElementById('bulkStatus').value = 'rejected';
        document.getElementById('bulkRejectionReason').value = reason;
        document.getElementById('bulkForm').submit();
    }

    // Auto-submit on filter change
    document.querySelector('.filter-select')?.addEventListener('change', function() {
        this.form.submit();
    });
</script>
@endsection