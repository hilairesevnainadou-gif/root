@extends('layouts.app')

@section('title', $typeFinancement->name)
@section('header-title', $typeFinancement->name)

@section('header-action')
    <a href="{{ route('client.financements.index') }}" class="btn-back">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
    </a>
@endsection

@section('content')

<div class="show-page">

    {{-- Hero --}}
    <div class="card show-hero">
        <div class="show-hero-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="28" height="28">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <span class="show-eyebrow">Type de financement</span>
            <h1 class="show-title">{{ $typeFinancement->name }}</h1>
        </div>
    </div>

    {{-- Alerte demande existante --}}
    @if($existingRequest)
    <div class="alert alert-warning">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20" style="flex-shrink:0">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div>
            <strong>Vous avez déjà une demande en cours</strong><br>
            <a href="{{ route('client.requests.show', $existingRequest) }}" class="text-primary" style="font-size:0.85rem;">
                Voir ma demande →
            </a>
        </div>
    </div>
    @endif

    {{-- Description --}}
    @if($typeFinancement->description)
    <div class="card">
        <div class="card-header">
            <h2 class="show-section-title">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                À propos
            </h2>
        </div>
        <p class="show-text">{{ $typeFinancement->description }}</p>
    </div>
    @endif

    {{-- Caractéristiques --}}
    <div class="card">
        <div class="card-header">
            <h2 class="show-section-title">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Caractéristiques
            </h2>
        </div>
        <div class="show-specs">
            {{-- SR-Standard : Montant variable choisi par l'utilisateur --}}
            @if($typeFinancement->is_variable_amount && $typeFinancement->max_daily_amount)
            <div class="show-spec-row">
                <span class="show-spec-label">Montant quotidien</span>
                <span class="show-spec-value text-primary font-semibold">
                    Jusqu'à {{ number_format($typeFinancement->max_daily_amount, 0, ',', ' ') }} FCFA/jour
                </span>
            </div>
            <div class="show-spec-row">
                <span class="show-spec-label">Type</span>
                <span class="show-spec-value">
                    <span class="badge badge-primary" style="font-size:0.75rem;">Montant libre</span>
                </span>
            </div>

            {{-- SF1, SF2, SF3 : Gains fixes journaliers --}}
            @elseif($typeFinancement->daily_gain && $typeFinancement->amount)
            <div class="show-spec-row">
                <span class="show-spec-label">Gain journalier</span>
                <span class="show-spec-value text-success font-semibold">
                    {{ number_format($typeFinancement->daily_gain, 0, ',', ' ') }} FCFA/jour
                </span>
            </div>
            <div class="show-spec-row">
                <span class="show-spec-label">Gain total</span>
                <span class="show-spec-value font-semibold">
                    {{ number_format($typeFinancement->amount, 0, ',', ' ') }} FCFA
                </span>
            </div>
            <div class="show-spec-row">
                <span class="show-spec-label">Calcul</span>
                <span class="show-spec-value text-muted">
                    {{ number_format($typeFinancement->daily_gain, 0, ',', ' ') }} F × {{ $typeFinancement->duration_months * 30 }} jours
                </span>
            </div>
            @endif

            @if($typeFinancement->duration_months)
            <div class="show-spec-row">
                <span class="show-spec-label">Durée</span>
                <span class="show-spec-value">{{ $typeFinancement->duration_months }} mois</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Frais --}}
    <div class="card">
        <div class="card-header">
            <h2 class="show-section-title">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Frais de dossier
            </h2>
        </div>
        <div class="show-specs">
            <div class="show-spec-row">
                <span class="show-spec-label">Frais d'inscription</span>
                <span class="show-spec-value">{{ number_format($typeFinancement->registration_fee, 0, ',', ' ') }} FCFA</span>
            </div>
            <div class="show-spec-row">
                <span class="show-spec-label">Frais finaux</span>
                <span class="show-spec-value">{{ number_format($typeFinancement->registration_final_fee, 0, ',', ' ') }} FCFA</span>
            </div>
            <div class="show-spec-row show-spec-total">
                <span class="show-spec-label font-semibold">Total des frais</span>
                <span class="show-spec-value font-bold text-primary">
                    {{ number_format($typeFinancement->registration_fee + $typeFinancement->registration_final_fee, 0, ',', ' ') }} FCFA
                </span>
            </div>
        </div>
    </div>

    {{-- Documents requis --}}
    @if($requiredDocs && count($requiredDocs) > 0)
    <div class="card">
        <div class="card-header">
            <h2 class="show-section-title">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Documents requis
                <span class="badge badge-submitted" style="font-size:0.65rem; margin-left:0.25rem;">{{ count($requiredDocs) }}</span>
            </h2>
        </div>
        <ul class="show-docs">
            @foreach($requiredDocs as $doc)
            <li class="show-doc-item">
                <div class="show-doc-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="show-doc-body">
                    <span class="show-doc-name">{{ $doc->name ?? $doc }}</span>
                </div>
                <span class="badge badge-under_review" style="font-size:0.65rem; white-space:nowrap;">Obligatoire</span>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- CTA principal --}}
    <div class="show-cta">
        @if($existingRequest)
            <a href="{{ route('client.requests.show', $existingRequest) }}" class="btn btn-secondary btn-block btn-lg">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Voir ma demande en cours
            </a>
        @else
            <a href="{{ route('client.requests.create', ['typefinancement_id' => $typeFinancement->id]) }}" class="btn btn-primary btn-block btn-lg">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                @if($typeFinancement->is_variable_amount)
                    Choisir mon montant
                @else
                    Déposer ma demande
                @endif
            </a>
            <p class="show-cta-note text-muted">
                @if($typeFinancement->is_variable_amount)
                    Définissez le montant quotidien souhaité (max {{ number_format($typeFinancement->max_daily_amount, 0, ',', ' ') }} FCFA)
                @else
                    Processus 100% en ligne · Réponse sous 48h
                @endif
            </p>
        @endif
    </div>

    {{-- Suggestions --}}
    @if($suggestions->isNotEmpty())
    <div class="show-suggestions">
        <h2 class="show-section-title mb-3">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
            </svg>
            Autres financements
        </h2>

        <div class="fin-list">
            @foreach($suggestions as $suggestion)
            <a href="{{ route('client.financements.show', $suggestion) }}" class="fin-item">
                <div class="fin-item-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="fin-item-body">
                    <h3 class="fin-item-name">{{ $suggestion->name }}</h3>
                    @if($suggestion->description)
                        <p class="fin-item-desc">{{ Str::limit($suggestion->description, 70) }}</p>
                    @endif
                    <div class="fin-item-tags">
                        @if($suggestion->is_variable_amount && $suggestion->max_daily_amount)
                            <span class="fin-tag fin-tag-primary">Jusqu'à {{ number_format($suggestion->max_daily_amount, 0, ',', ' ') }} F/jour</span>
                        @elseif($suggestion->daily_gain)
                            <span class="fin-tag fin-tag-success">{{ number_format($suggestion->daily_gain, 0, ',', ' ') }} F/jour</span>
                        @endif
                        @if($suggestion->duration_months)
                            <span class="fin-tag">{{ $suggestion->duration_months }} mois</span>
                        @endif
                    </div>
                </div>
                <div class="fin-item-arrow">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

</div>

@endsection

@section('styles')
<style>
.btn-back {
    display: flex; align-items: center; justify-content: center;
    width: 36px; height: 36px; background: var(--bg);
    border-radius: var(--radius-sm); color: var(--text);
    text-decoration: none; border: 1px solid var(--border); transition: all 0.2s;
}
.btn-back:hover { background: var(--border); }

.show-hero { display: flex; align-items: center; gap: 1rem; }
.show-hero-icon {
    width: 54px; height: 54px; background: #eff6ff;
    border-radius: var(--radius-sm);
    display: flex; align-items: center; justify-content: center;
    color: var(--primary); flex-shrink: 0;
}
.show-eyebrow {
    display: block; font-size: 0.7rem; font-weight: 500;
    letter-spacing: 0.1em; text-transform: uppercase;
    color: var(--text-muted); margin-bottom: 0.2rem;
}
.show-title { font-size: 1.125rem; font-weight: 700; color: var(--text); margin: 0; }

.show-section-title {
    display: flex; align-items: center; gap: 0.5rem;
    font-size: 0.9375rem; font-weight: 600; color: var(--text); margin: 0;
}

.show-specs { display: flex; flex-direction: column; }
.show-spec-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 0.75rem 0; border-bottom: 1px solid var(--border);
}
.show-spec-row:last-child { border-bottom: none; padding-bottom: 0; }
.show-spec-row:first-child { padding-top: 0; }
.show-spec-total {
    background: #eff6ff; margin: 0.5rem -1.25rem -1.25rem;
    padding: 0.875rem 1.25rem; border-radius: 0 0 var(--radius) var(--radius);
}
.show-spec-label { font-size: 0.875rem; color: var(--text-muted); }
.show-spec-value { font-size: 0.875rem; font-weight: 500; color: var(--text); }

.text-primary { color: var(--primary) !important; }
.text-success { color: #16a34a !important; }
.font-semibold { font-weight: 600 !important; }
.font-bold { font-weight: 700 !important; }

.show-text { font-size: 0.9rem; color: var(--text-muted); line-height: 1.7; margin: 0; }

.show-docs { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.6rem; }
.show-doc-item {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.75rem; background: var(--bg);
    border-radius: var(--radius-sm); border: 1px solid var(--border);
}
.show-doc-icon {
    width: 34px; height: 34px; background: #dbeafe;
    border-radius: var(--radius-sm);
    display: flex; align-items: center; justify-content: center;
    color: var(--primary); flex-shrink: 0;
}
.show-doc-body { flex: 1; min-width: 0; }
.show-doc-name { display: block; font-size: 0.875rem; font-weight: 500; color: var(--text); }

.show-cta { margin-top: 0.5rem; margin-bottom: 0.25rem; }
.show-cta-note { font-size: 0.78rem; text-align: center; margin-top: 0.6rem; }

/* Badges */
.badge-primary {
    background: #dbeafe;
    color: #1e40af;
    border: 1px solid #3b82f6;
    padding: 0.2rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

/* Suggestions */
.show-suggestions { margin-top: 1.5rem; }
.mb-3 { margin-bottom: 0.75rem !important; }

/* Styles partagés index / suggestions */
.fin-list { display: flex; flex-direction: column; gap: 0.75rem; margin-top: 0.75rem; }
.fin-item {
    display: flex; align-items: center; gap: 0.875rem;
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius); padding: 1rem;
    text-decoration: none; color: var(--text);
    transition: all 0.2s; box-shadow: var(--shadow);
}
.fin-item:hover  { border-color: var(--primary); box-shadow: 0 4px 12px rgba(37,99,235,0.12); transform: translateY(-1px); }
.fin-item:active { transform: scale(0.99); }
.fin-item-icon {
    width: 42px; height: 42px; background: #eff6ff;
    border-radius: var(--radius-sm);
    display: flex; align-items: center; justify-content: center;
    color: var(--primary); flex-shrink: 0;
}
.fin-item-body { flex: 1; min-width: 0; }
.fin-item-name { font-size: 0.9rem; font-weight: 600; color: var(--text); margin: 0 0 0.25rem; }
.fin-item-desc { font-size: 0.78rem; color: var(--text-muted); margin: 0 0 0.4rem; line-height: 1.4; }
.fin-item-tags { display: flex; flex-wrap: wrap; gap: 0.3rem; }
.fin-tag {
    font-size: 0.68rem; font-weight: 500;
    background: var(--bg); color: var(--text-muted);
    border: 1px solid var(--border); padding: 0.15rem 0.45rem; border-radius: 9999px;
}
.fin-tag-primary {
    background: #dbeafe;
    color: #1e40af;
    border-color: #3b82f6;
}
.fin-tag-success {
    background: #dcfce7;
    color: #166534;
    border-color: #22c55e;
}
.fin-item-arrow { color: var(--text-muted); flex-shrink: 0; transition: transform 0.2s, color 0.2s; }
.fin-item:hover .fin-item-arrow { color: var(--primary); transform: translateX(3px); }
</style>
@endsection
