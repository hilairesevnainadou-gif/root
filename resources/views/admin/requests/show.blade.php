{{-- resources/views/admin/requests/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Demande #' . $fundingRequest->request_number)

@section('styles')
<style>
    .timeline {
        position: relative;
        padding-left: 2rem;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 0.5rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e5e7eb;
    }
    .timeline-item {
        position: relative;
        padding-bottom: 1.5rem;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -1.5rem;
        top: 0.25rem;
        width: 0.75rem;
        height: 0.75rem;
        border-radius: 50%;
        background: #6b7280;
        border: 2px solid white;
        box-shadow: 0 0 0 2px #e5e7eb;
    }
    .timeline-item.active::before {
        background: #10b981;
        box-shadow: 0 0 0 2px #10b981;
    }
    .timeline-item.pending::before {
        background: #f59e0b;
        box-shadow: 0 0 0 2px #f59e0b;
    }
    .document-card {
        transition: all 0.2s;
    }
    .document-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
    .status-badge {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: .7; }
    }
    .amount-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .progress-ring {
        transform: rotate(-90deg);
    }
    .progress-ring-circle {
        transition: stroke-dashoffset 0.35s;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4 py-5">
    {{-- Header avec navigation --}}
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.requests.index') }}" class="text-decoration-none">Demandes</a></li>
                    <li class="breadcrumb-item active" aria-current="page">#{{ $fundingRequest->request_number }}</li>
                </ol>
            </nav>
            <h1 class="display-6 fw-bold text-dark mb-0">
                Demande #{{ $fundingRequest->request_number }}
                <span class="badge bg-{{ $fundingRequest->getStatusClass() }} ms-2 fs-6 align-middle">
                    {{ $fundingRequest->getStatusLabel() }}
                </span>
            </h1>
            <p class="text-muted mt-2 mb-0">
                <i class="fas fa-calendar-alt me-2"></i>
                Soumise le {{ $fundingRequest->created_at->format('d/m/Y à H:i') }}
                @if($fundingRequest->submitted_at)
                    <span class="mx-2">•</span>
                    <i class="fas fa-clock me-2"></i>
                    {{ $fundingRequest->created_at->diffForHumans() }}
                @endif
            </p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <div class="btn-group" role="group">
                <a href="{{ route('admin.requests.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
                <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                    <i class="fas fa-print me-2"></i>Imprimer
                </button>
                @if(in_array($fundingRequest->status, ['under_review', 'pending_committee']))
                <a href="{{ route('admin.requests.assign', $fundingRequest) }}" class="btn btn-outline-info">
                    <i class="fas fa-user-plus me-2"></i>Assigner
                </a>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Colonne principale --}}
        <div class="col-xl-8">
            {{-- Carte Demandeur --}}
            <div class="card border-0 shadow-sm mb-4 overflow-hidden">
                <div class="card-header bg-white border-bottom-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-user-circle text-primary me-2"></i>Demandeur
                    </h5>
                    <a href="{{ route('admin.users.show', $fundingRequest->user) }}" class="btn btn-sm btn-outline-primary">
                        Voir profil <i class="fas fa-external-link-alt ms-1"></i>
                    </a>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    @if($fundingRequest->user->profile_photo)
                                        <img src="{{ asset('storage/' . $fundingRequest->user->profile_photo) }}"
                                             class="rounded-circle" width="64" height="64" alt="Avatar">
                                    @else
                                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold"
                                             style="width: 64px; height: 64px; font-size: 24px;">
                                            {{ strtoupper(substr($fundingRequest->user->first_name, 0, 1) . substr($fundingRequest->user->last_name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-1 fw-bold">{{ $fundingRequest->user->full_name }}</h5>
                                    <p class="mb-0 text-muted">
                                        <i class="fas fa-envelope me-2"></i>{{ $fundingRequest->user->email }}
                                    </p>
                                    <p class="mb-0 text-muted">
                                        <i class="fas fa-phone me-2"></i>{{ $fundingRequest->user->phone ?? 'Non renseigné' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mt-3 mt-md-0">
                            <div class="border-start ps-md-4">
                                <p class="mb-2">
                                    <span class="text-muted">Type :</span>
                                    <span class="badge bg-info">{{ ucfirst($fundingRequest->user->member_type ?? 'Particulier') }}</span>
                                </p>
                                <p class="mb-2">
                                    <span class="text-muted">Membre depuis :</span>
                                    {{ $fundingRequest->user->created_at->format('d/m/Y') }}
                                </p>
                                <p class="mb-0">
                                    <span class="text-muted">Statut :</span>
                                    @if($fundingRequest->user->is_verified)
                                        <span class="badge bg-success"><i class="fas fa-check me-1"></i>Vérifié</span>
                                    @else
                                        <span class="badge bg-warning"><i class="fas fa-clock me-1"></i>En attente</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Détails du projet --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-file-alt text-primary me-2"></i>Détails du projet
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="text-muted small text-uppercase fw-bold">Titre du projet</label>
                        <h4 class="mt-1">{{ $fundingRequest->title }}</h4>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase fw-bold">Type de financement</label>
                            <p class="mb-0 fs-5">
                                <i class="fas fa-tag text-primary me-2"></i>
                                {{ $fundingRequest->typeFinancement->name }}
                            </p>
                            <small class="text-muted">{{ $fundingRequest->typeFinancement->description }}</small>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase fw-bold">Durée souhaitée</label>
                            <p class="mb-0 fs-5">
                                <i class="fas fa-calendar text-primary me-2"></i>
                                {{ $fundingRequest->duration }} mois
                            </p>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="text-muted small text-uppercase fw-bold">Description détaillée</label>
                        <div class="p-3 bg-light rounded mt-2">
                            {{ $fundingRequest->description ?? 'Aucune description fournie.' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Documents requis --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-folder-open text-primary me-2"></i>Documents
                        <span class="badge bg-secondary ms-2">{{ count($documentsStatus) }}</span>
                    </h5>
                    <div class="progress" style="width: 200px; height: 8px;">
                        @php
                            $verifiedCount = collect($documentsStatus)->where('status', 'verified')->count();
                            $totalCount = count($documentsStatus);
                            $progressPercent = $totalCount > 0 ? ($verifiedCount / $totalCount) * 100 : 0;
                        @endphp
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progressPercent }}%"></div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @forelse($documentsStatus as $doc)
                        <div class="col-md-6">
                            <div class="document-card card h-100 border-{{ $doc['status'] === 'verified' ? 'success' : ($doc['status'] === 'pending' ? 'warning' : 'danger') }}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                @switch($doc['status'])
                                                    @case('verified')
                                                        <i class="fas fa-check-circle text-success fa-2x"></i>
                                                        @break
                                                    @case('pending')
                                                        <i class="fas fa-clock text-warning fa-2x"></i>
                                                        @break
                                                    @case('rejected')
                                                        <i class="fas fa-times-circle text-danger fa-2x"></i>
                                                        @break
                                                    @default
                                                        <i class="fas fa-exclamation-circle text-secondary fa-2x"></i>
                                                @endswitch
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-0 fw-bold">{{ $doc['name'] }}</h6>
                                                <small class="text-muted text-uppercase">{{ $doc['status'] }}</small>
                                            </div>
                                        </div>
                                        @if($doc['provided'])
                                        <a href="{{ route('admin.documents.show', $doc['document_id'] ?? 0) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @endif
                                    </div>

                                    @if($doc['provided'])
                                        <p class="mb-0 small text-muted">
                                            <i class="fas fa-upload me-1"></i>
                                            Fourni le {{ $doc['uploaded_at']->format('d/m/Y') }}
                                            @if($doc['verified_by'])
                                                <br><i class="fas fa-user-check me-1"></i>
                                                Vérifié par {{ $doc['verified_by_name'] ?? 'Admin' }}
                                            @endif
                                        </p>
                                    @else
                                        <p class="mb-0 small text-danger">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            Document manquant
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>Aucun document requis pour ce type de financement.
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Timeline de progression --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-history text-primary me-2"></i>Historique de la demande
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item {{ $fundingRequest->created_at ? 'active' : '' }}">
                            <h6 class="fw-bold mb-1">Demande créée</h6>
                            <p class="text-muted small mb-0">{{ $fundingRequest->created_at->format('d/m/Y à H:i') }}</p>
                        </div>

                        @if($fundingRequest->paid_at)
                        <div class="timeline-item active">
                            <h6 class="fw-bold mb-1">Paiement des frais d'inscription</h6>
                            <p class="text-muted small mb-0">{{ $fundingRequest->paid_at->format('d/m/Y à H:i') }}</p>
                            <p class="small text-success mb-0">
                                <i class="fas fa-check me-1"></i>Transaction: {{ $fundingRequest->kkiapay_transaction_id }}
                            </p>
                        </div>
                        @endif

                        @if($fundingRequest->submitted_at)
                        <div class="timeline-item {{ $fundingRequest->submitted_at ? 'active' : '' }}">
                            <h6 class="fw-bold mb-1">Demande soumise</h6>
                            <p class="text-muted small mb-0">{{ $fundingRequest->submitted_at->format('d/m/Y à H:i') }}</p>
                        </div>
                        @endif

                        @if($fundingRequest->reviewed_at)
                        <div class="timeline-item {{ $fundingRequest->reviewed_at ? 'active' : '' }}">
                            <h6 class="fw-bold mb-1">Examen démarré</h6>
                            <p class="text-muted small mb-0">{{ $fundingRequest->reviewed_at->format('d/m/Y à H:i') }}</p>
                            @if($fundingRequest->reviewer)
                                <p class="small text-muted mb-0">Par: {{ $fundingRequest->reviewer->full_name }}</p>
                            @endif
                        </div>
                        @endif

                        @if($fundingRequest->committee_review_started_at)
                        <div class="timeline-item {{ $fundingRequest->committee_review_started_at ? 'active' : '' }}">
                            <h6 class="fw-bold mb-1">Soumis au comité</h6>
                            <p class="text-muted small mb-0">{{ $fundingRequest->committee_review_started_at->format('d/m/Y à H:i') }}</p>
                        </div>
                        @endif

                        @if($fundingRequest->committee_decision_at)
                        <div class="timeline-item {{ $fundingRequest->committee_decision_at ? 'active' : '' }}">
                            <h6 class="fw-bold mb-1">Décision du comité</h6>
                            <p class="text-muted small mb-0">{{ $fundingRequest->committee_decision_at->format('d/m/Y à H:i') }}</p>
                            <span class="badge bg-{{ $fundingRequest->status === 'approved' ? 'success' : 'danger' }}">
                                {{ $fundingRequest->status === 'approved' ? 'Approuvée' : 'Rejetée' }}
                            </span>
                        </div>
                        @endif

                        @if($fundingRequest->funded_at)
                        <div class="timeline-item active">
                            <h6 class="fw-bold mb-1">Financement débloqué</h6>
                            <p class="text-muted small mb-0">{{ $fundingRequest->funded_at->format('d/m/Y à H:i') }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Actions disponibles --}}
            @if(!empty($availableActions))
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-cogs text-primary me-2"></i>Actions disponibles
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($availableActions as $action)
                        <div class="col-md-6">
                            @switch($action)
                                @case('under_review')
                                    <form action="{{ route('admin.requests.status', $fundingRequest) }}" method="POST" class="d-grid">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="under_review">
                                        <button type="submit" class="btn btn-outline-primary btn-lg">
                                            <i class="fas fa-search me-2"></i>Démarrer l'examen
                                        </button>
                                    </form>
                                    @break
                                @case('pending_committee')
                                    <form action="{{ route('admin.requests.status', $fundingRequest) }}" method="POST" class="d-grid">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="pending_committee">
                                        <button type="submit" class="btn btn-outline-warning btn-lg">
                                            <i class="fas fa-users me-2"></i>Envoyer au comité
                                        </button>
                                    </form>
                                    @break
                                @case('approved')
                                    <button type="button" class="btn btn-outline-success btn-lg w-100" data-bs-toggle="modal" data-bs-target="#approveModal">
                                        <i class="fas fa-check-circle me-2"></i>Approuver la demande
                                    </button>
                                    @break
                                @case('rejected')
                                    <button type="button" class="btn btn-outline-danger btn-lg w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                        <i class="fas fa-times-circle me-2"></i>Rejeter la demande
                                    </button>
                                    @break
                                @case('funded')
                                    <form action="{{ route('admin.requests.status', $fundingRequest) }}" method="POST" class="d-grid">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="funded">
                                        <button type="submit" class="btn btn-outline-success btn-lg">
                                            <i class="fas fa-money-bill-wave me-2"></i>Marquer comme financée
                                        </button>
                                    </form>
                                    @break
                                @case('cancelled')
                                    <form action="{{ route('admin.requests.status', $fundingRequest) }}" method="POST" class="d-grid" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette demande ?');">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit" class="btn btn-outline-secondary btn-lg">
                                            <i class="fas fa-ban me-2"></i>Annuler la demande
                                        </button>
                                    </form>
                                    @break
                            @endswitch
                        </div>
                        @endforeach
                    </div>

                    {{-- Formulaire décision comité --}}
                    @if($fundingRequest->status === 'pending_committee')
                    <hr class="my-4">
                    <h6 class="fw-bold mb-3">Décision du comité</h6>
                    <form action="{{ route('admin.requests.committee', $fundingRequest) }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Décision</label>
                                <select name="decision" class="form-select" required>
                                    <option value="">Choisir...</option>
                                    <option value="approved">Approuver</option>
                                    <option value="rejected">Rejeter</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Montant approuvé (si applicable)</label>
                                <div class="input-group">
                                    <input type="number" name="amount_approved" class="form-control" step="0.01" min="0">
                                    <span class="input-group-text">XOF</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Motivation *</label>
                                <textarea name="motivation" class="form-control" rows="3" required minlength="20" placeholder="Minimum 20 caractères..."></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-gavel me-2"></i>Enregistrer la décision
                                </button>
                            </div>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- Colonne latérale --}}
        <div class="col-xl-4">
            {{-- Montants --}}
            <div class="card border-0 shadow-sm mb-4 amount-card text-white">
                <div class="card-body">
                    <h6 class="text-white-50 text-uppercase small fw-bold mb-3">Résumé financier</h6>

                    <div class="mb-4">
                        <label class="text-white-50 small">Montant demandé</label>
                        <h3 class="mb-0 fw-bold">{{ number_format($amounts['requested'], 0, ',', ' ') }} XOF</h3>
                    </div>

                    @if($amounts['approved'])
                    <div class="mb-4">
                        <label class="text-white-50 small">Montant approuvé</label>
                        <h3 class="mb-0 fw-bold text-success">{{ number_format($amounts['approved'], 0, ',', ' ') }} XOF</h3>
                    </div>
                    @endif

                    <hr class="border-white-25">

                    <div class="row g-2 small">
                        <div class="col-6">
                            <span class="text-white-50">Frais d'inscription:</span>
                            <br><span class="fw-bold">{{ number_format($amounts['registration_fee'], 0, ',', ' ') }} XOF</span>
                        </div>
                        <div class="col-6">
                            <span class="text-white-50">Frais de dossier:</span>
                            <br><span class="fw-bold">{{ number_format($amounts['final_fee'], 0, ',', ' ') }} XOF</span>
                        </div>
                    </div>

                    @if($amounts['approved'])
                    <div class="mt-3 pt-3 border-top border-white-25">
                        <label class="text-white-50 small">Montant net à verser</label>
                        <h4 class="mb-0 fw-bold">{{ number_format($amounts['net_amount'], 0, ',', ' ') }} XOF</h4>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Statut de paiement --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">Paiement</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Statut</span>
                        <span class="badge bg-{{ $fundingRequest->payment_status === 'paid' ? 'success' : ($fundingRequest->payment_status === 'failed' ? 'danger' : 'warning') }}">
                            {{ $fundingRequest->getPaymentStatusLabel() }}
                        </span>
                    </div>
                    @if($fundingRequest->registration_fee_paid)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Montant payé</span>
                        <span class="fw-bold">{{ number_format($fundingRequest->registration_fee_paid, 0, ',', ' ') }} XOF</span>
                    </div>
                    @endif
                    @if($fundingRequest->kkiapay_transaction_id)
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Transaction ID</span>
                        <code class="small">{{ Str::limit($fundingRequest->kkiapay_transaction_id, 20) }}</code>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Informations entreprise --}}
            @if($fundingRequest->company)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-building text-primary me-2"></i>Entreprise
                    </h6>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold">{{ $fundingRequest->company->company_name }}</h6>
                    <p class="small text-muted mb-2">{{ $fundingRequest->company->getCompanyTypeLabelAttribute() }}</p>

                    <ul class="list-unstyled small mb-0">
                        <li class="mb-1"><i class="fas fa-industry me-2 text-muted"></i>{{ $fundingRequest->company->getSectorLabelAttribute() }}</li>
                        <li class="mb-1"><i class="fas fa-users me-2 text-muted"></i>{{ $fundingRequest->company->employees_count }} employés</li>
                        <li class="mb-0"><i class="fas fa-map-marker-alt me-2 text-muted"></i>{{ $fundingRequest->company->city }}</li>
                    </ul>
                </div>
            </div>
            @endif

            {{-- Assignation --}}
            @if($fundingRequest->reviewer)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">Assigné à</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-info d-flex align-items-center justify-content-center text-white fw-bold"
                                 style="width: 40px; height: 40px;">
                                {{ strtoupper(substr($fundingRequest->reviewer->first_name, 0, 1)) }}
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">{{ $fundingRequest->reviewer->full_name }}</h6>
                            <small class="text-muted">Depuis {{ $fundingRequest->reviewed_at->diffForHumans() }}</small>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Notes rapides --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">Notes internes</h6>
                </div>
                <div class="card-body">
                    <form action="#" method="POST">
                        @csrf
                        <div class="mb-3">
                            <textarea class="form-control form-control-sm" rows="3" placeholder="Ajouter une note..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary w-100">Ajouter</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Approbation --}}
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.requests.status', $fundingRequest) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="approved">
                <div class="modal-header">
                    <h5 class="modal-title">Approuver la demande</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Montant approuvé (XOF)</label>
                        <input type="number" name="amount_approved" class="form-control" value="{{ $fundingRequest->amount_requested }}" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Commentaire (optionnel)</label>
                        <textarea name="comment" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Confirmer l'approbation</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Rejet --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.requests.status', $fundingRequest) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="rejected">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">Rejeter la demande</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Cette action est irréversible. Le demandeur sera notifié du rejet.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Motif du rejet *</label>
                        <textarea name="comment" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Confirmer le rejet</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Animation des progress bars au chargement
    document.addEventListener('DOMContentLoaded', function() {
        const progressBars = document.querySelectorAll('.progress-bar');
        progressBars.forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.width = width;
            }, 100);
        });
    });
</script>
@endsection
