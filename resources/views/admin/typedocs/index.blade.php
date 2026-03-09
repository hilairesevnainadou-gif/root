@extends('layouts.admin')

@section('title', 'Types de Documents')

@section('header-title', 'Gestion des Documents')

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

    .btn-danger {
        background: #ef4444;
        color: white;
    }

    .btn-danger:hover {
        background: #dc2626;
    }

    .btn-secondary {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
    }

    .btn-secondary:hover {
        background: #e2e8f0;
    }

    /* Header Actions */
    .header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .header-actions h2 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
        margin: 0;
    }

    /* Tabs */
    .tabs {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 0;
    }

    .tab {
        padding: 0.75rem 1.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: #64748b;
        text-decoration: none;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        transition: all 0.2s;
    }

    .tab:hover {
        color: #3b82f6;
    }

    .tab.active {
        color: #3b82f6;
        border-bottom-color: #3b82f6;
    }

    .tab .count {
        background: #e2e8f0;
        color: #475569;
        padding: 0.125rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        margin-left: 0.5rem;
    }

    .tab.active .count {
        background: #dbeafe;
        color: #1e40af;
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

    /* Type Badges */
    .type-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .type-particulier {
        background: #dbeafe;
        color: #1e40af;
    }

    .type-entreprise {
        background: #d1fae5;
        color: #065f46;
    }

    .type-admin {
        background: #fee2e2;
        color: #991b1b;
    }

    /* Doc Info */
    .doc-info {
        display: flex;
        flex-direction: column;
    }

    .doc-name {
        font-weight: 600;
        color: #1e293b;
        font-size: 1rem;
    }

    .doc-description {
        font-size: 0.875rem;
        color: #64748b;
        margin-top: 0.25rem;
    }

    /* Stats */
    .stats-count {
        font-size: 0.875rem;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .stats-count i {
        color: #3b82f6;
    }

    /* Actions */
    .actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
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

    /* MODAL STYLES */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal {
        background: white;
        border-radius: 16px;
        width: 100%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        animation: modalSlideIn 0.3s ease;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-20px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .modal-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: #94a3b8;
        cursor: pointer;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.2s;
    }

    .modal-close:hover {
        background: #f1f5f9;
        color: #1e293b;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
    }

    /* Form Styles */
    .form-group {
        margin-bottom: 1.25rem;
    }

    .form-group:last-child {
        margin-bottom: 0;
    }

    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
    }

    .form-control {
        width: 100%;
        padding: 0.625rem 1rem;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.875rem;
        transition: all 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 80px;
    }

    .form-help {
        font-size: 0.75rem;
        color: #64748b;
        margin-top: 0.375rem;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .data-table {
            display: block;
            overflow-x: auto;
        }

        .tabs {
            overflow-x: auto;
            flex-wrap: nowrap;
        }
    }
</style>
@endsection

@section('content')
    <!-- Header Actions -->
    <div class="header-actions">
        <h2>Types de Documents</h2>
        <button type="button" class="btn btn-success" onclick="openCreateModal()">
            <i class="fas fa-plus"></i> Nouveau Type
        </button>
    </div>

    <!-- Tabs -->
    <div class="tabs">
        <a href="?tab=all" class="tab {{ request('tab', 'all') == 'all' ? 'active' : '' }}">
            Tous
            <span class="count">{{ $docs->count() }}</span>
        </a>
        <a href="?tab=particulier" class="tab {{ request('tab') == 'particulier' ? 'active' : '' }}">
            Particuliers
            <span class="count">{{ $grouped['particulier']->count() }}</span>
        </a>
        <a href="?tab=entreprise" class="tab {{ request('tab') == 'entreprise' ? 'active' : '' }}">
            Entreprises
            <span class="count">{{ $grouped['entreprise']->count() }}</span>
        </a>
        <a href="?tab=admin" class="tab {{ request('tab') == 'admin' ? 'active' : '' }}">
            Administrateurs
            <span class="count">{{ $grouped['admin']->count() }}</span>
        </a>
    </div>

    <!-- Data Table -->
    <div class="data-table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Type d'utilisateur</th>
                    <th>Description</th>
                    <th>Documents fournis</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $currentTab = request('tab', 'all');
                    $displayDocs = $currentTab === 'all'
                        ? $docs
                        : ($grouped[$currentTab] ?? collect());
                @endphp

                @forelse($displayDocs as $doc)
                    <tr>
                        <td>
                            <div class="doc-info">
                                <span class="doc-name">{{ $doc->name }}</span>
                            </div>
                        </td>
                        <td>
                            @if($doc->typeusers === 'particulier')
                                <span class="type-badge type-particulier">
                                    <i class="fas fa-user"></i> Particulier
                                </span>
                            @elseif($doc->typeusers === 'entreprise')
                                <span class="type-badge type-entreprise">
                                    <i class="fas fa-building"></i> Entreprise
                                </span>
                            @else
                                <span class="type-badge type-admin">
                                    <i class="fas fa-shield-alt"></i> Admin
                                </span>
                            @endif
                        </td>
                        <td>
                            {{ $doc->description ?? '-' }}
                        </td>
                        <td>
                            <div class="stats-count">
                                <i class="fas fa-file-alt"></i>
                                {{ $doc->document_users_count ?? 0 }} documents
                            </div>
                        </td>
                        <td>
                            <div class="actions">
                                <button type="button"
                                        class="btn btn-sm btn-primary"
                                        onclick="openEditModal({{ $doc->id }}, '{{ addslashes($doc->name) }}', '{{ $doc->typeusers }}', '{{ addslashes($doc->description) }}')">
                                    <i class="fas fa-edit"></i> Modifier
                                </button>

                                @if(($doc->document_users_count ?? 0) === 0)
                                    <button type="button"
                                            class="btn btn-sm btn-danger"
                                            onclick="openDeleteModal({{ $doc->id }}, '{{ addslashes($doc->name) }}')">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <i class="fas fa-folder-open"></i>
                                <p>Aucun type de document trouvé</p>
                                <button type="button" class="btn btn-success" style="margin-top: 1rem;" onclick="openCreateModal()">
                                    <i class="fas fa-plus"></i> Créer un type
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- CREATE MODAL -->
    <div id="createModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-plus-circle" style="color: #10b981; margin-right: 0.5rem;"></i>
                    Nouveau Type de Document
                </h3>
                <button type="button" class="modal-close" onclick="closeCreateModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.typedocs.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Nom du document *</label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               placeholder="Ex: Carte d'identité"
                               required>
                        <div class="form-help">Nom affiché aux utilisateurs</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Type d'utilisateur *</label>
                        <select name="typeusers" class="form-control" required>
                            <option value="">Sélectionner...</option>
                            <option value="particulier">Particulier</option>
                            <option value="entreprise">Entreprise</option>
                            <option value="admin">Administrateur</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description"
                                  class="form-control"
                                  placeholder="Description optionnelle..."></textarea>
                        <div class="form-help">Information complémentaire visible par les utilisateurs</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeCreateModal()">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Créer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT MODAL -->
    <div id="editModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-edit" style="color: #3b82f6; margin-right: 0.5rem;"></i>
                    Modifier le Type
                </h3>
                <button type="button" class="modal-close" onclick="closeEditModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="editForm" method="POST" action="">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Nom du document *</label>
                        <input type="text"
                               id="editName"
                               name="name"
                               class="form-control"
                               required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Type d'utilisateur *</label>
                        <select id="editType" name="typeusers" class="form-control" required>
                            <option value="particulier">Particulier</option>
                            <option value="entreprise">Entreprise</option>
                            <option value="admin">Administrateur</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea id="editDescription"
                                  name="description"
                                  class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- DELETE MODAL -->
    <div id="deleteModal" class="modal-overlay">
        <div class="modal" style="max-width: 400px;">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-exclamation-triangle" style="color: #ef4444; margin-right: 0.5rem;"></i>
                    Confirmer la suppression
                </h3>
                <button type="button" class="modal-close" onclick="closeDeleteModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p style="color: #64748b; margin-bottom: 1rem;">
                    Êtes-vous sûr de vouloir supprimer le type de document <strong id="deleteDocName" style="color: #1e293b;"></strong> ?
                </p>
                <div style="background: #fef2f2; border-left: 4px solid #ef4444; padding: 1rem; border-radius: 6px;">
                    <p style="color: #991b1b; font-size: 0.875rem; margin: 0;">
                        <i class="fas fa-exclamation-circle"></i>
                        Cette action est irréversible.
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">
                    Annuler
                </button>
                <form id="deleteForm" method="POST" action="" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    // CREATE MODAL
    function openCreateModal() {
        document.getElementById('createModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeCreateModal() {
        document.getElementById('createModal').classList.remove('active');
        document.body.style.overflow = '';
        // Reset form
        document.querySelector('#createModal form').reset();
    }

    // EDIT MODAL
    function openEditModal(id, name, type, description) {
        const form = document.getElementById('editForm');
        form.action = '/admin/typedocs/' + id;

        document.getElementById('editName').value = name;
        document.getElementById('editType').value = type;
        document.getElementById('editDescription').value = description || '';

        document.getElementById('editModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.remove('active');
        document.body.style.overflow = '';
    }

    // DELETE MODAL
    function openDeleteModal(id, name) {
        document.getElementById('deleteDocName').textContent = name;
        document.getElementById('deleteForm').action = '/admin/typedocs/' + id;
        document.getElementById('deleteModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.remove('active');
        document.body.style.overflow = '';
    }

    // Close modals on backdrop click
    document.querySelectorAll('.modal-overlay').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });

    // Close modals on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(modal => {
                modal.classList.remove('active');
            });
            document.body.style.overflow = '';
        }
    });
</script>
@endsection
