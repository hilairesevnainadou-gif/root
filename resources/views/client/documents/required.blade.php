@extends('layouts.app')

@section('title', 'Documents - ' . $fundingRequest->request_number)
@section('header-title', 'Documents requis')

@section('content')

<div class="dashboard-container" style="padding-bottom: 100px;">

    {{-- Header de succès --}}
    <div class="card-premium" style="background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); border-color: #86efac; margin-bottom: 1rem;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="width: 48px; height: 48px; background: white; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #16a34a; box-shadow: 0 4px 12px rgba(22, 163, 74, 0.15);">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h2 style="font-size: 1.125rem; font-weight: 700; color: #166534; margin: 0;">Paiement confirmé !</h2>
                <p style="margin: 0; color: #166534; font-size: 0.9375rem;">
                    Demande <span style="font-family: monospace; background: rgba(255,255,255,0.6); padding: 0.125rem 0.5rem; border-radius: 4px; font-weight: 600;">{{ $fundingRequest->request_number }}</span>
                </p>
            </div>
        </div>
    </div>

    {{-- Récapitulatif --}}
    <div class="card-premium" style="margin-bottom: 1rem;">
        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; color: #64748b; font-size: 0.875rem; font-weight: 600;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <span>Récapitulatif</span>
        </div>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
            <div>
                <div style="font-size: 0.75rem; color: #94a3b8; text-transform: uppercase;">Financement</div>
                <div style="font-size: 0.9375rem; font-weight: 600; color: #0f172a;">{{ $fundingRequest->typeFinancement->name }}</div>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: #94a3b8; text-transform: uppercase;">Montant</div>
                <div style="font-size: 0.9375rem; font-weight: 600; color: #2563eb;">{{ number_format($fundingRequest->amount_requested, 0, ',', ' ') }} FCFA</div>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: #94a3b8; text-transform: uppercase;">Durée</div>
                <div style="font-size: 0.9375rem; font-weight: 600; color: #0f172a;">{{ $fundingRequest->duration }} mois</div>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: #94a3b8; text-transform: uppercase;">Frais payés</div>
                <div style="font-size: 0.9375rem; font-weight: 600; color: #16a34a;">{{ number_format($fundingRequest->registration_fee_paid, 0, ',', ' ') }} FCFA</div>
            </div>
        </div>
    </div>

    {{-- Section documents --}}
    <div class="card-premium">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="22" height="22" style="color: #2563eb;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 style="font-size: 1rem; font-weight: 700; color: #0f172a; margin: 0;">Documents requis</h3>
        </div>

        @php
            $emptyDocs = $documents->whereNull('file_path');
            $filledDocs = $documents->whereNotNull('file_path');
            $progressPercent = $documents->count() > 0
                ? round(($filledDocs->count() / $documents->count()) * 100)
                : 0;
        @endphp

        {{-- Barre de progression --}}
        <div style="margin-bottom: 1.5rem;">
            <div style="height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden; margin-bottom: 0.5rem;">
                <div style="height: 100%; background: linear-gradient(90deg, #2563eb, #3b82f6); border-radius: 3px; transition: width 0.6s ease; width: {{ $progressPercent }}%;"></div>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #64748b;">
                <span>{{ $filledDocs->count() }}/{{ $documents->count() }} documents</span>
                <span>{{ $progressPercent }}% complété</span>
            </div>
        </div>

        {{-- Documents à compléter --}}
        @if($emptyDocs->count() > 0)
        <div style="margin-bottom: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                <span style="min-width: 24px; height: 24px; border-radius: 50%; background: #f59e0b; color: white; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700;">{{ $emptyDocs->count() }}</span>
                <span style="font-size: 0.875rem; font-weight: 600; color: #374151;">À compléter</span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 0.875rem;">
                @foreach($emptyDocs as $doc)
                <div class="document-item" id="doc-{{ $doc->id }}" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: white; border: 2px solid #e2e8f0; border-radius: 12px;">

                    {{-- Icône --}}
                    <div style="width: 44px; height: 44px; border-radius: 10px; background: #dbeafe; color: #2563eb; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                    </div>

                    {{-- Info --}}
                    <div style="flex: 1; min-width: 0;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.25rem;">
                            <h4 style="font-size: 0.9375rem; font-weight: 600; color: #0f172a; margin: 0;">{{ $doc->typeDoc->name }}</h4>
                            @if($doc->typeDoc->is_required ?? true)
                                <span style="font-size: 0.625rem; font-weight: 700; padding: 0.125rem 0.5rem; border-radius: 9999px; text-transform: uppercase; background: #fee2e2; color: #991b1b;">Obligatoire</span>
                            @else
                                <span style="font-size: 0.625rem; font-weight: 700; padding: 0.125rem 0.5rem; border-radius: 9999px; text-transform: uppercase; background: #e2e8f0; color: #475569;">Optionnel</span>
                            @endif
                        </div>
                        <p style="font-size: 0.8125rem; color: #64748b; margin: 0;">{{ $doc->typeDoc->description ?? 'PDF, JPG, PNG (max 10MB)' }}</p>
                    </div>

                    {{-- Upload --}}
                    <div style="flex-shrink: 0;">
                        <form action="{{ route('client.documents.store') }}" method="POST" enctype="multipart/form-data" class="upload-form" data-doc-id="{{ $doc->id }}">
                            @csrf
                            <input type="hidden" name="document_user_id" value="{{ $doc->id }}">
                            <input type="hidden" name="funding_request_id" value="{{ $fundingRequest->id }}">
                            <input type="hidden" name="typedoc_id" value="{{ $doc->typedoc_id }}"> {{-- 🔥 AJOUTÉ --}}

                            <label class="btn-action" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1rem; background: #eff6ff; border: 1.5px solid #bfdbfe; border-radius: 8px; color: #2563eb; font-size: 0.875rem; font-weight: 600; cursor: pointer;">
                                <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" onchange="handleFileSelect(this, {{ $doc->id }})" style="display: none;">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                <span id="bu-text-{{ $doc->id }}">Choisir</span>
                            </label>

                            {{-- Preview --}}
                            <div id="preview-{{ $doc->id }}" style="display: none; align-items: center; gap: 0.5rem; margin-top: 0.5rem;">
                                <span id="fp-name-{{ $doc->id }}" style="font-size: 0.75rem; color: #166534; max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"></span>
                                <button type="button" onclick="clearFile({{ $doc->id }})" style="width: 20px; height: 20px; border: none; background: #fee2e2; color: #dc2626; border-radius: 4px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            {{-- Bouton confirmer --}}
                            <button type="submit" id="submit-{{ $doc->id }}" style="display: none; margin-top: 0.5rem; padding: 0.625rem 1.25rem; background: linear-gradient(135deg, #2563eb, #3b82f6); color: white; border: none; border-radius: 8px; font-size: 0.875rem; font-weight: 600; cursor: pointer;">
                                Confirmer
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Documents complétés --}}
        @if($filledDocs->count() > 0)
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                <span style="min-width: 24px; height: 24px; border-radius: 50%; background: #10b981; color: white; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700;">{{ $filledDocs->count() }}</span>
                <span style="font-size: 0.875rem; font-weight: 600; color: #374151;">Téléchargés</span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 0.875rem;">
                @foreach($filledDocs as $doc)
                <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: #f0fdf4; border: 2px solid #bbf7d0; border-radius: 12px;">

                    {{-- Icône --}}
                    <div style="width: 44px; height: 44px; border-radius: 10px; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>

                    {{-- Info --}}
                    <div style="flex: 1; min-width: 0;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.25rem;">
                            <h4 style="font-size: 0.9375rem; font-weight: 600; color: #0f172a; margin: 0;">{{ $doc->typeDoc->name }}</h4>
                            <span style="font-size: 0.625rem; font-weight: 700; padding: 0.125rem 0.5rem; border-radius: 9999px; text-transform: uppercase; background: #dcfce7; color: #166534;">
                                {{ $doc->status === 'verified' ? '✓ Vérifié' : '⏳ En attente' }}
                            </span>
                        </div>
                        <p style="font-size: 0.8125rem; color: #16a34a; margin: 0;">
                            {{ $doc->file_name }} • {{ round($doc->file_size / 1024, 1) }} Ko
                        </p>
                    </div>

                    {{-- Actions --}}
                    <div style="flex-shrink: 0; display: flex; gap: 0.5rem;">
                        <a href="{{ route('client.documents.show', $doc) }}" target="_blank" class="btn-action" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: white; border: 1.5px solid #e2e8f0; border-radius: 8px; color: #475569; font-size: 0.875rem; font-weight: 600; text-decoration: none;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Voir
                        </a>

                        {{-- Remplacer --}}
                        <form action="{{ route('client.documents.store') }}" method="POST" enctype="multipart/form-data" style="display: inline;">
                            @csrf
                            <input type="hidden" name="document_user_id" value="{{ $doc->id }}">
                            <input type="hidden" name="funding_request_id" value="{{ $fundingRequest->id }}">
                            <input type="hidden" name="typedoc_id" value="{{ $doc->typedoc_id }}"> {{-- 🔥 AJOUTÉ --}}

                            <label class="btn-action" style="display: inline-flex; align-items: center; padding: 0.5rem; background: #eff6ff; border: 1.5px solid #bfdbfe; border-radius: 8px; color: #2563eb; cursor: pointer;" title="Remplacer">
                                <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" onchange="this.form.submit()" style="display: none;">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                            </label>
                        </form>

                        {{-- Supprimer --}}
                        <form action="{{ route('client.documents.destroy', $doc) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Supprimer ce document ?')" class="btn-action" style="display: inline-flex; align-items: center; padding: 0.5rem; background: #fee2e2; border: 1.5px solid #fecaca; border-radius: 8px; color: #dc2626; cursor: pointer;">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Footer --}}
        <div style="margin-top: 1.5rem; padding: 1rem; background: #f8fafc; border-radius: 10px;">
            @if($emptyDocs->count() === 0)
                <div style="text-align: center;">
                    <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #dcfce7, #bbf7d0); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #16a34a; margin: 0 auto 1rem;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="32" height="32">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h4 style="font-size: 1.125rem; font-weight: 700; color: #166534; margin: 0 0 0.5rem;">Dossier complet !</h4>
                    <p style="color: #64748b; margin: 0 0 1rem;">Tous les documents ont été fournis.</p>
                    <a href="{{ route('client.requests.show', $fundingRequest) }}" class="btn-premium" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.875rem 1.5rem; background: linear-gradient(135deg, #2563eb, #3b82f6); color: white; border-radius: 10px; font-weight: 600; text-decoration: none;">
                        Voir ma demande
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            @else
                <div style="display: flex; align-items: flex-start; gap: 0.75rem; color: #1e40af; font-size: 0.875rem;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20" style="flex-shrink: 0; margin-top: 0.125rem;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p style="margin: 0;">Votre demande ne sera traitée qu'après réception de tous les documents obligatoires.</p>
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
    if (!file) return;

    const maxSize = 10 * 1024 * 1024;
    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

    if (file.size > maxSize) {
        alert('Le fichier est trop volumineux. Maximum 10MB.');
        input.value = '';
        return;
    }

    if (!allowedTypes.includes(file.type)) {
        alert('Format non supporté. Utilisez PDF, JPG, PNG, DOC ou DOCX.');
        input.value = '';
        return;
    }

    document.getElementById('fp-name-' + docId).textContent = file.name;
    document.getElementById('preview-' + docId).style.display = 'flex';
    document.getElementById('bu-text-' + docId).textContent = 'Changer';
    document.getElementById('submit-' + docId).style.display = 'block';
}

function clearFile(docId) {
    const form = document.querySelector('#doc-' + docId + ' .upload-form');
    const fileInput = form.querySelector('input[type="file"]');
    fileInput.value = '';

    document.getElementById('preview-' + docId).style.display = 'none';
    document.getElementById('bu-text-' + docId).textContent = 'Choisir';
    document.getElementById('submit-' + docId).style.display = 'none';
}

// Soumission AJAX
document.querySelectorAll('.upload-form').forEach(form => {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Envoi...';
        submitBtn.disabled = true;

        try {
            const formData = new FormData(this);
            const response = await fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                window.location.reload();
            } else {
                throw new Error(data.message || 'Erreur upload');
            }
        } catch (error) {
            alert(error.message || 'Erreur lors de l\'upload');
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    });
});
</script>
@endsection
