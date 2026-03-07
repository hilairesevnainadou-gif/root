@extends('layouts.app')

@section('title', 'Financements')
@section('header-title', 'Financements')

@section('content')

<div class="fin-index">

    {{-- Banner --}}
    <div class="fin-banner">
        <div class="fin-banner-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="28" height="28">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <h1 class="fin-banner-title">Nos financements</h1>
            <p class="fin-banner-sub">{{ $financements->count() }} solution(s) disponible(s)</p>
        </div>
    </div>

    {{-- Barre de recherche --}}
    @if($financements->count() > 0)
    <div class="fin-search-wrap">
        <div class="fin-search-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <input type="text" id="fin-search" class="fin-search-input" placeholder="Rechercher un financement...">
        <button type="button" id="fin-search-clear" class="fin-search-clear" style="display:none;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    @endif

    {{-- Liste --}}
    @if($financements->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="40" height="40">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="empty-state-title">Aucun financement disponible</h3>
            <p class="empty-state-text">De nouvelles offres arrivent bientôt.</p>
        </div>
    @else
        <div class="fin-list" id="fin-list">
            @foreach($financements as $financement)
            <a href="{{ route('client.financements.show', $financement) }}"
               class="fin-item"
               data-name="{{ strtolower($financement->name) }}">

                <div class="fin-item-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="22" height="22">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>

                <div class="fin-item-body">
                    <h3 class="fin-item-name">{{ $financement->name }}</h3>

                    @if($financement->description)
                        <p class="fin-item-desc">{{ Str::limit($financement->description, 80) }}</p>
                    @endif

                    <div class="fin-item-tags">
                        {{-- SR-Standard : Montant variable choisi par l'utilisateur --}}
                        @if($financement->is_variable_amount && $financement->max_daily_amount)
                            <span class="fin-tag fin-tag-primary">
                                Jusqu'à {{ number_format($financement->max_daily_amount, 0, ',', ' ') }} FCFA/jour
                            </span>
                            <span class="fin-tag">Montant libre</span>

                        {{-- SF1, SF2, SF3 : Gains fixes journaliers --}}
                        @elseif($financement->daily_gain && $financement->amount)
                            <span class="fin-tag fin-tag-success">
                                {{ number_format($financement->daily_gain, 0, ',', ' ') }} FCFA/jour
                            </span>
                            <span class="fin-tag">
                                Total {{ number_format($financement->amount, 0, ',', ' ') }} FCFA
                            </span>
                        @endif

                        @if($financement->duration_months)
                            <span class="fin-tag">{{ $financement->duration_months }} mois</span>
                        @endif

                        @if($financement->registration_fee)
                            <span class="fin-tag">
                                Frais {{ number_format($financement->registration_fee, 0, ',', ' ') }} FCFA
                            </span>
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

        <div id="fin-no-result" class="empty-state" style="display:none;">
            <div class="empty-state-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="40" height="40">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <h3 class="empty-state-title">Aucun résultat</h3>
            <p class="empty-state-text">Essayez un autre terme de recherche.</p>
        </div>
    @endif

</div>

@endsection

@section('styles')
<style>
.fin-banner {
    display: flex; align-items: center; gap: 1rem;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white; border-radius: var(--radius); padding: 1.25rem; margin-bottom: 1rem;
}
.fin-banner-icon {
    width: 52px; height: 52px; background: rgba(255,255,255,0.15);
    border-radius: var(--radius-sm);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.fin-banner-title { font-size: 1.125rem; font-weight: 700; margin: 0 0 0.2rem; color: white; }
.fin-banner-sub   { font-size: 0.8rem; opacity: 0.85; margin: 0; }

.fin-search-wrap  { position: relative; margin-bottom: 1rem; }
.fin-search-icon  {
    position: absolute; left: 0.875rem; top: 50%; transform: translateY(-50%);
    color: var(--text-muted); pointer-events: none;
}
.fin-search-input {
    width: 100%; padding: 0.75rem 2.75rem;
    border: 1px solid var(--border); border-radius: var(--radius);
    background: var(--surface); color: var(--text); font-size: 0.9rem; outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.fin-search-input:focus {
    border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
}
.fin-search-clear {
    position: absolute; right: 0.875rem; top: 50%; transform: translateY(-50%);
    background: none; border: none; cursor: pointer; color: var(--text-muted);
    display: flex; align-items: center; padding: 0.25rem; border-radius: 50%;
    transition: color 0.2s, background 0.2s;
}
.fin-search-clear:hover { color: var(--text); background: var(--border); }

.fin-list { display: flex; flex-direction: column; gap: 0.75rem; }

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
    width: 46px; height: 46px; background: #eff6ff;
    border-radius: var(--radius-sm);
    display: flex; align-items: center; justify-content: center;
    color: var(--primary); flex-shrink: 0;
}
.fin-item-body { flex: 1; min-width: 0; }
.fin-item-name { font-size: 0.9375rem; font-weight: 600; color: var(--text); margin: 0 0 0.3rem; }
.fin-item-desc { font-size: 0.8rem; color: var(--text-muted); margin: 0 0 0.5rem; line-height: 1.4; }
.fin-item-tags { display: flex; flex-wrap: wrap; gap: 0.35rem; }
.fin-tag {
    font-size: 0.7rem; font-weight: 500;
    background: var(--bg); color: var(--text-muted);
    border: 1px solid var(--border); padding: 0.2rem 0.5rem; border-radius: 9999px;
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

@section('scripts')
<script>
(function () {
    const input    = document.getElementById('fin-search');
    const clear    = document.getElementById('fin-search-clear');
    const list     = document.getElementById('fin-list');
    const noResult = document.getElementById('fin-no-result');
    if (!input || !list) return;

    function filter(query) {
        const q = query.trim().toLowerCase();
        let visible = 0;
        list.querySelectorAll('.fin-item').forEach(item => {
            const match = item.dataset.name.includes(q);
            item.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        if (noResult) noResult.style.display = visible === 0 ? 'block' : 'none';
        if (clear)    clear.style.display    = q.length > 0  ? 'flex'  : 'none';
    }

    input.addEventListener('input', () => filter(input.value));
    if (clear) {
        clear.addEventListener('click', () => { input.value = ''; filter(''); input.focus(); });
    }
})();
</script>
@endsection
