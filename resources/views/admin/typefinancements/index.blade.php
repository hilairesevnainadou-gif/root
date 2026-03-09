@extends('layouts.admin')

@section('title', 'Types de Financement')
@section('header-title', 'Types de Financement')

@section('styles')
<style>
    .badge { display: inline-flex; align-items: center; padding: .2rem .65rem; border-radius: 9999px; font-size: .7rem; font-weight: 600; white-space: nowrap; }
    .badge-green  { background: #dcfce7; color: #166534; }
    .badge-gray   { background: #f1f5f9; color: #475569; }
    .badge-blue   { background: #dbeafe; color: #1e40af; }
    .badge-purple { background: #f3e8ff; color: #6d28d9; }

    .btn { display: inline-flex; align-items: center; gap: .375rem; padding: .45rem .875rem; border-radius: 8px; font-size: .8rem; font-weight: 600; text-decoration: none; border: none; cursor: pointer; transition: all .15s; font-family: inherit; }
    .btn-sm { padding: .3rem .65rem; font-size: .75rem; }
    .btn-primary   { background: #3b82f6; color: #fff; }
    .btn-primary:hover   { background: #2563eb; color: #fff; }
    .btn-secondary { background: #f1f5f9; color: #475569; border: 1px solid var(--color-border); }
    .btn-secondary:hover { background: #e2e8f0; }
    .btn-danger    { background: #ef4444; color: #fff; }
    .btn-danger:hover    { background: #dc2626; color: #fff; }
    .btn-warning   { background: #f59e0b; color: #fff; }
    .btn-warning:hover   { background: #d97706; color: #fff; }
    .btn-ghost { background: none; border: 1px solid var(--color-border); color: #475569; }
    .btn-ghost:hover { background: #f1f5f9; }

    .amount-cell { font-weight: 700; font-size: .85rem; }
    .fee-cell    { font-size: .8rem; color: var(--color-text-muted); }

    .tf-actions { display: flex; gap: .375rem; flex-wrap: nowrap; }
</style>
@endsection

@section('content')

    <div style="display:flex; justify-content:flex-end; margin-bottom:1.25rem; gap:.75rem;">
        <a href="{{ route('admin.typefinancements.documents') }}" class="btn btn-secondary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="15" height="15"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Documents requis
        </a>
        <a href="{{ route('admin.typefinancements.create') }}" class="btn btn-primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="15" height="15"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nouveau type
        </a>
    </div>

    <div class="data-table">
        <table>
            <thead>
                <tr>
                    <th>Nom / Code</th>
                    <th>Cible</th>
                    <th>Montant</th>
                    <th>Frais inscription</th>
                    <th>Frais final</th>
                    <th>Durée</th>
                    <th>Demandes</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($types as $type)
                <tr>
                    <td>
                        <div style="font-weight:700; font-size:.875rem;">{{ $type->name }}</div>
                        <div style="font-family:monospace; font-size:.72rem; color:var(--color-text-muted);">{{ $type->code }}</div>
                    </td>
                    <td>
                        @if($type->typeusers === 'particulier')
                            <span class="badge badge-blue">Particulier</span>
                        @elseif($type->typeusers === 'entreprise')
                            <span class="badge badge-purple">Entreprise</span>
                        @else
                            <span class="badge badge-gray">{{ ucfirst($type->typeusers) }}</span>
                        @endif
                    </td>
                    <td class="amount-cell">
                        @if($type->is_variable_amount)
                            <span style="color:#8b5cf6;">Variable</span>
                        @else
                            {{ $type->amount ? number_format($type->amount, 0, ',', ' ').' FCFA' : '—' }}
                        @endif
                    </td>
                    <td class="fee-cell">{{ number_format($type->registration_fee, 0, ',', ' ') }} FCFA</td>
                    <td class="fee-cell">{{ number_format($type->registration_final_fee, 0, ',', ' ') }} FCFA</td>
                    <td style="font-size:.83rem;">{{ $type->duration_months }} mois</td>
                    <td style="font-weight:700;">{{ $type->funding_requests_count }}</td>
                    <td>
                        <span class="badge {{ $type->is_active ? 'badge-green' : 'badge-gray' }}">
                            {{ $type->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                    </td>
                    <td>
                        <div class="tf-actions">
                            <a href="{{ route('admin.typefinancements.edit', $type) }}" class="btn btn-sm btn-ghost">Éditer</a>

                            <form method="POST" action="{{ route('admin.typefinancements.toggle', $type) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $type->is_active ? 'btn-warning' : 'btn-secondary' }}">
                                    {{ $type->is_active ? 'Désactiver' : 'Activer' }}
                                </button>
                            </form>

                            @if($type->funding_requests_count === 0)
                            <form method="POST" action="{{ route('admin.typefinancements.destroy', $type) }}"
                                  onsubmit="return confirm('Supprimer « {{ $type->name }} » ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Suppr.</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center; padding:2.5rem; color:var(--color-text-muted);">
                        Aucun type de financement. <a href="{{ route('admin.typefinancements.create') }}" style="color:#3b82f6;">En créer un</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection
