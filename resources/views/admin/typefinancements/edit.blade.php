@extends('layouts.admin')

@section('title', 'Modifier — ' . $typeFinancement->name)
@section('header-title', 'Modifier le type de financement')

@section('content')

<div style="margin-bottom:1rem; display:flex; align-items:center; gap:.75rem; flex-wrap:wrap;">
    <a href="{{ route('admin.typefinancements.index') }}" class="btn btn-secondary btn-sm"
       style="display:inline-flex; align-items:center; gap:.375rem; padding:.3rem .65rem; border-radius:8px; font-size:.75rem; font-weight:600; text-decoration:none; background:#f1f5f9; color:#475569; border:1px solid var(--color-border);">
        ← Retour
    </a>
    <a href="{{ route('admin.typefinancements.documents.edit', $typeFinancement) }}"
       style="display:inline-flex; align-items:center; gap:.375rem; padding:.3rem .65rem; border-radius:8px; font-size:.75rem; font-weight:600; text-decoration:none; background:#eff6ff; color:#1e40af; border:1px solid #bfdbfe;">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="13" height="13"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Gérer les documents requis
        @php $docCount = $typeFinancement->requiredTypeDocs()->count(); @endphp
        @if($docCount > 0)
            <span style="background:#3b82f6; color:#fff; padding:.05rem .4rem; border-radius:9999px; font-size:.65rem;">{{ $docCount }}</span>
        @endif
    </a>
</div>

<div style="max-width:820px;">
    <div style="background:var(--color-white); border:1px solid var(--color-border); border-radius:var(--radius-lg); padding:1.75rem; box-shadow:var(--shadow-sm);">

        <div style="margin-bottom:1.5rem; padding-bottom:1rem; border-bottom:1px solid var(--color-border); display:flex; align-items:flex-start; justify-content:space-between; gap:1rem;">
            <div>
                <h2 style="font-size:1rem; font-weight:700; color:var(--color-text); margin-bottom:.25rem;">{{ $typeFinancement->name }}</h2>
                <div style="display:flex; align-items:center; gap:.5rem; flex-wrap:wrap;">
                    <span style="font-family:monospace; font-size:.75rem; color:var(--color-text-muted);">{{ $typeFinancement->code }}</span>
                    <span style="font-size:.7rem; font-weight:600; padding:.15rem .55rem; border-radius:9999px; background:{{ $typeFinancement->is_active ? '#dcfce7' : '#f1f5f9' }}; color:{{ $typeFinancement->is_active ? '#166534' : '#475569' }};">
                        {{ $typeFinancement->is_active ? 'Actif' : 'Inactif' }}
                    </span>
                    <span style="font-size:.7rem; color:var(--color-text-muted);">{{ $typeFinancement->funding_requests_count ?? $typeFinancement->fundingRequests()->count() }} demande(s)</span>
                </div>
            </div>

            {{-- Toggle rapide --}}
            <form method="POST" action="{{ route('admin.typefinancements.toggle', $typeFinancement) }}" style="flex-shrink:0;">
                @csrf @method('PATCH')
                <button type="submit"
                    style="display:inline-flex; align-items:center; gap:.375rem; padding:.3rem .65rem; border-radius:8px; font-size:.75rem; font-weight:600; border:none; cursor:pointer; font-family:inherit; background:{{ $typeFinancement->is_active ? '#fff7ed' : '#f0fdf4' }}; color:{{ $typeFinancement->is_active ? '#c2410c' : '#166534' }}; border:1px solid {{ $typeFinancement->is_active ? '#fed7aa' : '#bbf7d0' }};">
                    {{ $typeFinancement->is_active ? 'Désactiver' : 'Activer' }}
                </button>
            </form>
        </div>

        @include('admin.typefinancements._form', [
            'typeFinancement' => $typeFinancement,
            'action'          => route('admin.typefinancements.update', $typeFinancement),
            'method'          => 'PATCH',
            'submitLabel'     => 'Enregistrer les modifications',
        ])

    </div>

    {{-- Danger zone --}}
    @if($typeFinancement->fundingRequests()->count() === 0)
    <div style="background:#fff; border:1px solid #fecaca; border-radius:var(--radius-lg); padding:1.25rem 1.5rem; margin-top:1.25rem; box-shadow:var(--shadow-sm);">
        <div style="font-size:.875rem; font-weight:700; color:#991b1b; margin-bottom:.25rem;">Zone dangereuse</div>
        <p style="font-size:.8rem; color:#b91c1c; margin-bottom:.875rem;">Cette action est irréversible. Ce type n'a aucune demande associée.</p>
        <form method="POST" action="{{ route('admin.typefinancements.destroy', $typeFinancement) }}"
              onsubmit="return confirm('Supprimer définitivement « {{ $typeFinancement->name }} » ?')">
            @csrf @method('DELETE')
            <button type="submit"
                style="display:inline-flex; align-items:center; gap:.375rem; padding:.45rem .875rem; border-radius:8px; font-size:.8rem; font-weight:600; background:#ef4444; color:#fff; border:none; cursor:pointer; font-family:inherit;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Supprimer ce type
            </button>
        </form>
    </div>
    @endif

</div>

@endsection
