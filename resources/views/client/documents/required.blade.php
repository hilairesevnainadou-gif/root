@extends('layouts.app')

@section('title', 'Documents requis - ' . $fundingRequest->request_number)
@section('header-title', 'Documents requis')

@section('header-action')
    <a href="{{ route('client.requests.show', $fundingRequest) }}" class="btn-back">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
    </a>
@endsection

@section('content')

<div class="documents-required">

    {{-- Message de succès paiement --}}
    <div class="alert alert-success mb-4">
        <div class="alert-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="alert-content">
            <h4>Paiement confirmé !</h4>
            <p>Votre demande <strong>{{ $fundingRequest->request_number }}</strong> a été soumise avec succès.</p>
            <p class="mb-0">Pour finaliser votre dossier, veuillez télécharger les documents requis ci-dessous.</p>
        </div>
    </div>

    {{-- Résumé de la demande --}}
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="section-title">Résumé de votre demande</h3>
        </div>
        <div class="card-body">
            <div class="request-summary">
                <div class="rs-item">
                    <span>Financement</span>
                    <strong>{{ $fundingRequest->typeFinancement->name }}</strong>
                </div>
                <div class="rs-item">
                    <span>Montant demandé</span>
                    <strong>{{ number_format($fundingRequest->amount_requested, 0, ',', ' ') }} FCFA</strong>
                </div>
                <div class="rs-item">
                    <span>Durée</span>
                    <strong>{{ $fundingRequest->duration }} mois</strong>
                </div>
                <div class="rs-item">
                    <span>Frais payés</span>
                    <strong class="text-success">{{ number_format($fundingRequest->registration_fee_paid, 0, ',', ' ') }} FCFA</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- Liste des documents --}}
    <div class="card">
        <div class="card-header">
            <h3 class="section-title">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Documents à fournir
            </h3>
        </div>
        <div class="card-body">

            @php
                $pendingDocs = $documents->where('status', 'pending');
                $verifiedDocs = $documents->where('status', 'verified');
            @endphp

            {{-- Documents en attente --}}
            @if($pendingDocs->count() > 0)
                <div class="docs-section">
                    <h4 class="docs-section-title">
                        <span class="badge badge-warning">{{ $pendingDocs->count() }}</span>
                        En attente d'upload
                    </h4>

                    <div class="documents-list">
                        @foreach($pendingDocs as $doc)
                        <div class="document-card" id="doc-{{ $doc->id }}">
                            <div class="document-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="32" height="32">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                            </div>

                            <div class="document-info">
                                <h5>{{ $doc->typeDoc->name }}</h5>
                                <p class="text-muted">{{ $doc->typeDoc->description ?? 'Format accepté : PDF, JPG, PNG (max 5MB)' }}</p>

                                @if($doc->typeDoc->is_required)
                                    <span class="badge badge-danger">Obligatoire</span>
                                @endif
                            </div>

                            <div class="document-action">
                                <form action="{{ route('client.documents.store') }}" method="POST" enctype="multipart/form-data" class="upload-form" data-doc-id="{{ $doc->id }}">
                                    @csrf
                                    <input type="hidden" name="document_user_id" value="{{ $doc->id }}">
                                    <input type="hidden" name="funding_request_id" value="{{ $fundingRequest->id }}">

                                    <label class="btn btn-primary btn-sm">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                        </svg>
                                        Choisir un fichier
                                        <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" style="display: none;" onchange="handleFileSelect(this, {{ $doc->id }})">
                                    </label>

                                    <div class="file-name" id="filename-{{ $doc->id }}"></div>
                                    <button type="submit" class="btn btn-success btn-sm mt-2" style="display: none;" id="submit-{{ $doc->id }}">
                                        Confirmer l'upload
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Documents déjà vérifiés --}}
            @if($verifiedDocs->count() > 0)
                <div class="docs-section mt-4">
                    <h4 class="docs-section-title">
                        <span class="badge badge-success">{{ $verifiedDocs->count() }}</span>
                        Déjà vérifiés
                    </h4>

                    <div class="documents-list">
                        @foreach($verifiedDocs as $doc)
                        <div class="document-card verified">
                            <div class="document-icon verified">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="32" height="32">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>

                            <div class="document-info">
                                <h5>{{ $doc->typeDoc->name }}</h5>
                                <p class="text-success">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="14" height="14">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Document déjà vérifié
                                </p>
                            </div>

                            <div class="document-action">
                                <a href="{{ route('client.documents.show', $doc) }}" class="btn btn-outline-primary btn-sm">
                                    Voir
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Bouton continuer --}}
            @if($pendingDocs->count() === 0)
                <div class="text-center mt-4">
                    <a href="{{ route('client.requests.show', $fundingRequest) }}" class="btn btn-primary btn-lg">
                        Voir ma demande
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            @else
                <div class="alert alert-info mt-4">
                    <p class="mb-0">
                        <strong>Important :</strong> Votre demande ne sera traitée qu'après réception de tous les documents obligatoires.
                    </p>
                </div>
            @endif

        </div>
    </div>

</div>

@endsection

@section('scripts')
<script>
function handleFileSelect(input, docId) {
    const file = input.files[0];
    if (file) {
        document.getElementById('filename-' + docId).textContent = file.name;
        document.getElementById('submit-' + docId).style.display = 'inline-block';
    }
}

// Gestion des formulaires d'upload
document.querySelectorAll('.upload-form').forEach(form => {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Upload...';

        try {
            const formData = new FormData(this);
            const response = await fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });

            const data = await response.json();

            if (data.success) {
                // Recharger la page pour voir le document mis à jour
                window.location.reload();
            } else {
                alert(data.message || 'Erreur lors de l\'upload');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        } catch (error) {
            console.error('Upload error:', error);
            alert('Erreur lors de l\'upload. Veuillez réessayer.');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });
});
</script>
@endsection

@section('styles')
<style>
.documents-required { max-width: 800px; margin: 0 auto; }

.alert-success {
    background: #dcfce7;
    border: 1px solid #86efac;
    border-radius: var(--radius);
    padding: 1.25rem;
    display: flex;
    gap: 1rem;
}
.alert-icon { color: #16a34a; flex-shrink: 0; }
.alert-content h4 { margin: 0 0 0.5rem; color: #166534; }
.alert-content p { margin: 0; color: #166534; }

.request-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}
.rs-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}
.rs-item span { font-size: 0.875rem; color: var(--text-muted); }
.rs-item strong { font-size: 1rem; color: var(--text); }

.docs-section-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--border);
}

.documents-list { display: flex; flex-direction: column; gap: 1rem; }

.document-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem;
    background: var(--surface);
    border: 2px solid var(--border);
    border-radius: var(--radius);
    transition: all 0.2s;
}
.document-card:hover {
    border-color: var(--primary);
    box-shadow: 0 4px 12px rgba(37,99,235,0.08);
}
.document-card.verified {
    background: #f0fdf4;
    border-color: #bbf7d0;
}

.document-icon {
    width: 48px;
    height: 48px;
    background: #dbeafe;
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary);
    flex-shrink: 0;
}
.document-icon.verified {
    background: #dcfce7;
    color: #16a34a;
}

.document-info { flex: 1; }
.document-info h5 { margin: 0 0 0.25rem; font-size: 0.95rem; }
.document-info p { margin: 0; font-size: 0.8rem; }

.document-action { text-align: right; }

.file-name {
    font-size: 0.8rem;
    color: var(--text-muted);
    margin-top: 0.5rem;
}

.badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}
.badge-warning { background: #fef3c7; color: #92400e; }
.badge-success { background: #dcfce7; color: #166534; }
.badge-danger { background: #fee2e2; color: #991b1b; }

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}
</style>
@endsection
