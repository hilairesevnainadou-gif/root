@extends('layouts.app')

@section('title', 'Mon Profil - BHDM')

@section('header-title', 'Mon Profil')

@section('content')
<div class="profile-wrapper">

    {{-- Header Compact --}}
    <div class="profile-header-card">
        <div class="profile-identity-row">
            <div class="profile-avatar-md">
                @if($user->profile_photo)
                    <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="{{ $user->first_name }}">
                @else
                    <span class="avatar-letters">{{ substr($user->first_name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}</span>
                @endif
            </div>
            <div class="profile-id-block">
                <h1 class="profile-name-line">{{ $user->full_name }}</h1>
                <span class="profile-type-badge {{ ($user->isEntreprise() || $company) ? 'type-entreprise' : 'type-particulier' }}">
                    {{ ($user->isEntreprise() || $company) ? 'Entreprise' : 'Particulier' }}
                </span>
            </div>
        </div>

        {{-- Barre progression - CACHÉE si 100% --}}
        @if($completionRate < 100)
            <div class="progress-micro">
                <div class="progress-micro-row">
                    <span class="progress-micro-label">Profil complété</span>
                    <span class="progress-micro-value">{{ $completionRate }}%</span>
                </div>
                <div class="progress-micro-bar">
                    <div class="progress-micro-fill" style="width: {{ $completionRate }}%"></div>
                </div>
            </div>
        @endif
    </div>

    {{-- Formulaire --}}
    <form action="{{ route('client.profile.update') }}" method="POST" class="profile-form-single">
        @csrf
        @method('PATCH')

        {{-- Section Personnelle --}}
        <div class="form-card">
            <h2 class="form-card-title">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Informations personnelles
            </h2>

            {{-- Ligne 1: Prénom + Nom --}}
            <div class="form-row">
                <div class="form-col">
                    <label for="first_name">Prénom *</label>
                    <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
                    @error('first_name')<span class="input-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-col">
                    <label for="last_name">Nom *</label>
                    <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                    @error('last_name')<span class="input-error">{{ $message }}</span>@enderror
                </div>
            </div>

            {{-- Ligne 2: Téléphone + Email --}}
            <div class="form-row">
                <div class="form-col">
                    <label for="phone">Téléphone *</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" required placeholder="77 123 45 67">
                    @error('phone')<span class="input-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-col">
                    <label for="email">Email</label>
                    <input type="email" id="email" value="{{ $user->email }}" disabled class="input-disabled">
                </div>
            </div>

            {{-- Ligne 3: Date naissance + Genre --}}
            <div class="form-row">
                <div class="form-col">
                    <label for="birth_date">Date naissance</label>
                    <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date', $user->birth_date?->format('Y-m-d')) }}">
                    @error('birth_date')<span class="input-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-col">
                    <label for="gender">Genre</label>
                    <select id="gender" name="gender">
                        <option value="">--</option>
                        <option value="male" {{ old('gender', $user->gender) == 'male' ? 'selected' : '' }}>Homme</option>
                        <option value="female" {{ old('gender', $user->gender) == 'female' ? 'selected' : '' }}>Femme</option>
                        <option value="other" {{ old('gender', $user->gender) == 'other' ? 'selected' : '' }}>Autre</option>
                    </select>
                    @error('gender')<span class="input-error">{{ $message }}</span>@enderror
                </div>
            </div>

            {{-- Ligne 4: Adresse (pleine largeur) --}}
            <div class="form-row">
                <div class="form-col form-col-full">
                    <label for="address">Adresse</label>
                    <input type="text" id="address" name="address" value="{{ old('address', $user->address) }}" placeholder="Quartier, rue, numéro">
                    @error('address')<span class="input-error">{{ $message }}</span>@enderror
                </div>
            </div>

            {{-- Ligne 5: Ville (moitié) --}}
            <div class="form-row">
                <div class="form-col form-col-half">
                    <label for="city">Ville *</label>
                    <input type="text" id="city" name="city" value="{{ old('city', $user->city) }}" required placeholder="Dakar">
                    @error('city')<span class="input-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        {{-- Section Entreprise - CONDITIONNELLE --}}
        @if($user->isEntreprise() || $company)
            <div class="form-card">
                <h2 class="form-card-title">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Informations entreprise
                </h2>

                {{-- Nom entreprise (pleine largeur) --}}
                <div class="form-row">
                    <div class="form-col form-col-full">
                        <label for="company_name">Nom entreprise *</label>
                        <input type="text" id="company_name" name="company_name" value="{{ old('company_name', $company->company_name ?? '') }}" required>
                        @error('company_name')<span class="input-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                {{-- Type + Secteur --}}
                <div class="form-row">
                    <div class="form-col">
                        <label for="company_type">Type *</label>
                        <select id="company_type" name="company_type" required>
                            <option value="">Choisir</option>
                            @foreach(['sarl' => 'SARL', 'sa' => 'SA', 'snc' => 'SNC', 'ei' => 'EI', 'cooperative' => 'Coopérative', 'ong' => 'ONG', 'autre' => 'Autre'] as $val => $lab)
                                <option value="{{ $val }}" {{ old('company_type', $company->company_type ?? '') == $val ? 'selected' : '' }}>{{ $lab }}</option>
                            @endforeach
                        </select>
                        @error('company_type')<span class="input-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-col">
                        <label for="sector">Secteur *</label>
                        <select id="sector" name="sector" required>
                            <option value="">Choisir</option>
                            @foreach(['agriculture' => 'Agriculture', 'elevage' => 'Élevage', 'peche' => 'Pêche', 'commerce' => 'Commerce', 'services' => 'Services', 'technologie' => 'Tech/IT', 'autre' => 'Autre'] as $val => $lab)
                                <option value="{{ $val }}" {{ old('sector', $company->sector ?? '') == $val ? 'selected' : '' }}>{{ $lab }}</option>
                            @endforeach
                        </select>
                        @error('sector')<span class="input-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                {{-- Fonction + Employés --}}
                <div class="form-row">
                    <div class="form-col">
                        <label for="job_title">Fonction</label>
                        <input type="text" id="job_title" name="job_title" value="{{ old('job_title', $company->job_title ?? '') }}" placeholder="Gérant">
                        @error('job_title')<span class="input-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-col">
                        <label for="employees_count">Employés</label>
                        <input type="number" id="employees_count" name="employees_count" value="{{ old('employees_count', $company->employees_count ?? '') }}" min="0">
                        @error('employees_count')<span class="input-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                {{-- CA (moitié) --}}
                <div class="form-row">
                    <div class="form-col form-col-half">
                        <label for="annual_turnover">CA annuel (FCFA)</label>
                        <input type="number" id="annual_turnover" name="annual_turnover" value="{{ old('annual_turnover', $company->annual_turnover ?? '') }}" min="0" placeholder="0">
                        @error('annual_turnover')<span class="input-error">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>
        @endif

        {{-- Bouton Submit --}}
        <div class="submit-zone">
            <button type="submit" class="btn-submit-main">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Enregistrer
            </button>
        </div>
    </form>
</div>

{{-- Modal Profil Incomplet --}}
@if($completionRate < 100 && !session('profile_modal_seen'))
<div id="profile-modal" class="profile-modal" style="display: flex;">
    <div class="modal-overlay" onclick="closeProfileModal()"></div>
    <div class="modal-box">
        <button class="modal-close-btn" onclick="closeProfileModal()">×</button>

        <div class="modal-icon-box">
            <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </div>

        <h3 class="modal-box-title">Profil incomplet</h3>
        <p class="modal-box-text">{{ $completionRate }}% complété</p>

        <div class="missing-tags">
            @php
                $missingItems = [];
                if(empty($user->phone)) $missingItems[] = 'Téléphone';
                if(empty($user->address)) $missingItems[] = 'Adresse';
                if(empty($user->city)) $missingItems[] = 'Ville';
            @endphp
            @foreach($missingItems as $item)
                <span class="tag-missing">{{ $item }}</span>
            @endforeach
        </div>

        <div class="modal-box-actions">
            <button type="button" class="btn-modal-cancel" onclick="closeProfileModal()">Plus tard</button>
            <button type="button" class="btn-modal-confirm" onclick="focusEmptyField()">Compléter</button>
        </div>
    </div>
</div>
@endif
@endsection

@section('styles')
<style>
/* ===== LAYOUT ===== */
.profile-wrapper {
    max-width: 600px;
    margin: 0 auto;
    padding: 0.75rem;
    padding-bottom: 100px;
}

/* ===== HEADER ===== */
.profile-header-card {
    background: white;
    border-radius: 16px;
    padding: 1rem;
    margin-bottom: 0.75rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.profile-identity-row {
    display: flex;
    align-items: center;
    gap: 0.875rem;
}

.profile-avatar-md {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
    font-weight: 700;
    flex-shrink: 0;
    overflow: hidden;
}

.profile-avatar-md img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-letters {
    text-transform: uppercase;
}

.profile-id-block {
    min-width: 0;
}

.profile-name-line {
    font-size: 1.125rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 0.25rem 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.profile-type-badge {
    display: inline-block;
    padding: 0.25rem 0.625rem;
    border-radius: 9999px;
    font-size: 0.6875rem;
    font-weight: 600;
    text-transform: uppercase;
}

.type-particulier {
    background: #d1fae5;
    color: #065f46;
}

.type-entreprise {
    background: #dbeafe;
    color: #1e40af;
}

/* Progress caché si 100% */
.progress-micro {
    margin-top: 0.875rem;
    padding-top: 0.75rem;
    border-top: 1px solid #f3f4f6;
}

.progress-micro-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.8125rem;
    margin-bottom: 0.375rem;
}

.progress-micro-label {
    color: #6b7280;
}

.progress-micro-value {
    font-weight: 700;
    color: #f59e0b;
}

.progress-micro-bar {
    height: 4px;
    background: #e5e7eb;
    border-radius: 9999px;
    overflow: hidden;
}

.progress-micro-fill {
    height: 100%;
    background: #f59e0b;
    border-radius: 9999px;
}

/* ===== FORM ===== */
.form-card {
    background: white;
    border-radius: 16px;
    padding: 1rem;
    margin-bottom: 0.75rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.form-card-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9375rem;
    font-weight: 700;
    color: #374151;
    margin: 0 0 1rem 0;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #f3f4f6;
}

.form-card-title svg {
    color: #2563eb;
}

/* ===== GRID SYSTEM ===== */
.form-row {
    display: flex;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}

.form-row:last-child {
    margin-bottom: 0;
}

.form-col {
    flex: 1;
    min-width: 0; /* Important pour éviter le débordement */
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.form-col-full {
    flex: 0 0 100%;
}

.form-col-half {
    flex: 0 0 calc(50% - 0.375rem);
}

.form-col label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #4b5563;
    text-transform: uppercase;
}

.form-col input,
.form-col select {
    padding: 0.625rem 0.875rem;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    font-size: 0.9375rem;
    height: 44px;
    background: #fafafa;
    width: 100%;
    box-sizing: border-box;
}

.form-col input:focus,
.form-col select:focus {
    outline: none;
    border-color: #2563eb;
    background: white;
}

.input-disabled {
    background: #f3f4f6 !important;
    color: #6b7280;
}

.input-error {
    font-size: 0.75rem;
    color: #ef4444;
}

/* ===== SUBMIT ===== */
.submit-zone {
    margin-top: 0.5rem;
}

.btn-submit-main {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.875rem;
    background: #2563eb;
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 0.9375rem;
    font-weight: 600;
    cursor: pointer;
}

.btn-submit-main:active {
    transform: scale(0.98);
}

/* ===== MODAL ===== */
.profile-modal {
    position: fixed;
    inset: 0;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.modal-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
}

.modal-box {
    position: relative;
    background: white;
    border-radius: 20px;
    padding: 1.5rem;
    width: 100%;
    max-width: 300px;
    text-align: center;
    animation: modalPop 0.3s ease;
}

.modal-close-btn {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    width: 28px;
    height: 28px;
    border: none;
    background: #f3f4f6;
    border-radius: 50%;
    font-size: 1.125rem;
    color: #6b7280;
    cursor: pointer;
}

.modal-icon-box {
    width: 56px;
    height: 56px;
    background: #eff6ff;
    color: #2563eb;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.875rem;
}

.modal-box-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 0.375rem 0;
}

.modal-box-text {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0 0 1rem 0;
}

.missing-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    justify-content: center;
    margin-bottom: 1.25rem;
}

.tag-missing {
    background: #fef3c7;
    color: #92400e;
    padding: 0.375rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.modal-box-actions {
    display: flex;
    gap: 0.625rem;
}

.btn-modal-cancel,
.btn-modal-confirm {
    flex: 1;
    padding: 0.75rem;
    border-radius: 10px;
    font-size: 0.875rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
}

.btn-modal-cancel {
    background: #f3f4f6;
    color: #4b5563;
}

.btn-modal-confirm {
    background: #2563eb;
    color: white;
}

@keyframes modalPop {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
}

/* ===== DESKTOP ===== */
@media (min-width: 640px) {
    .profile-wrapper {
        padding: 1.5rem;
        padding-bottom: 2rem;
    }

    .btn-submit-main {
        max-width: 200px;
        margin-left: auto;
    }
}
</style>
@endsection

@section('scripts')
<script>
function closeProfileModal() {
    const modal = document.getElementById('profile-modal');
    if (modal) {
        modal.style.opacity = '0';
        setTimeout(() => modal.remove(), 200);
    }
    fetch('{{ route("client.profile.acknowledge") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    });
}

function focusEmptyField() {
    closeProfileModal();
    const fields = ['phone', 'address', 'city'];
    for (let fieldId of fields) {
        const field = document.getElementById(fieldId);
        if (field && !field.value) {
            field.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => field.focus(), 300);
            field.style.borderColor = '#f59e0b';
            setTimeout(() => field.style.borderColor = '#e5e7eb', 2000);
            break;
        }
    }
}

// Validation visuelle
document.querySelectorAll('input, select').forEach(field => {
    field.addEventListener('blur', function() {
        if (this.checkValidity() && this.value) {
            this.style.borderColor = '#10b981';
            setTimeout(() => this.style.borderColor = '#e5e7eb', 1500);
        }
    });
});
</script>
@endsection
