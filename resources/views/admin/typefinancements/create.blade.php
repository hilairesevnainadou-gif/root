@extends('layouts.admin')

@section('title', 'Nouveau type de financement')
@section('header-title', 'Nouveau type de financement')

@section('content')

<div style="margin-bottom:1rem;">
    <a href="{{ route('admin.typefinancements.index') }}" class="btn btn-secondary btn-sm"
       style="display:inline-flex; align-items:center; gap:.375rem; padding:.3rem .65rem; border-radius:8px; font-size:.75rem; font-weight:600; text-decoration:none; background:#f1f5f9; color:#475569; border:1px solid var(--color-border);">
        ← Retour
    </a>
</div>

<div style="max-width:820px;">
    <div style="background:var(--color-white); border:1px solid var(--color-border); border-radius:var(--radius-lg); padding:1.75rem; box-shadow:var(--shadow-sm);">

        <div style="margin-bottom:1.5rem; padding-bottom:1rem; border-bottom:1px solid var(--color-border);">
            <h2 style="font-size:1rem; font-weight:700; color:var(--color-text); margin-bottom:.25rem;">Créer un type de financement</h2>
            <p style="font-size:.82rem; color:var(--color-text-muted);">Définissez les paramètres du financement. Vous pourrez associer les documents requis après la création.</p>
        </div>

        @include('admin.typefinancements._form', [
            'typeFinancement' => null,
            'action'          => route('admin.typefinancements.store'),
            'method'          => 'POST',
            'submitLabel'     => 'Créer le type de financement',
        ])

    </div>
</div>

@endsection
