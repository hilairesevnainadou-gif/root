@extends('layouts.app')

@section('title', 'Mon Profil - BHDM')
@section('header-title', 'Mon Profil')

@section('content')
<div class="profile-container">
    <!-- Carte de complétion -->
    <div class="completion-card">
        <div class="completion-header">
            <div>
                <h2>Complétion du profil</h2>
                <p class="completion-subtitle">
                    @if($completionRate < 100)
                        Complétez votre profil pour accéder à toutes les fonctionnalités
                    @else
                        Votre profil est complet ! 🎉
                    @endif
                </p>
            </div>
            <div class="completion-circle">
                <svg viewBox="0 0 36 36" class="circular-chart">
                    <path class="circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    <path class="circle" stroke-dasharray="{{ $completionRate }}, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                </svg>
                <span class="percentage">{{ $completionRate }}%</span>
            </div>
        </div>
        <div class="progress-bar-linear">
            <div class="progress-fill-linear" style="width: {{ $completionRate }}%"></div>
        </div>
    </div>

    <!-- Informations personnelles -->
    <div class="profile-section">
        <div class="section-header">
            <div class="header-title-group">
                <div class="icon-wrapper user-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <h3>Informations personnelles</h3>
                    <p class="section-subtitle">Vos coordonnées et identité</p>
                </div>
            </div>
            <a href="{{ route('client.profile.companies.index') }}" class="btn-companies">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span>Mes entreprises</span>
                <span class="badge-count">{{ auth()->user()->companies()->count() }}</span>
            </a>
        </div>

        <form action="{{ route('client.profile.update') }}" method="POST" class="profile-form" id="profile-form">
            @csrf
            @method('PATCH')

            <div class="form-section">
                <h4 class="form-section-title">Identité</h4>
                <div class="form-grid grid-2">
                    <div class="form-group">
                        <label for="last_name">Nom <span class="required">*</span></label>
                        <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                        @error('last_name')<span class="error-message">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label for="first_name">Prénom <span class="required">*</span></label>
                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
                        @error('first_name')<span class="error-message">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h4 class="form-section-title">Contact</h4>
                <div class="form-grid grid-2">
                    <div class="form-group">
                        <label for="email">Email <span class="required">*</span></label>
                        <input type="email" id="email" value="{{ $user->email }}" readonly class="readonly-field" title="L'email ne peut pas être modifié">
                        <span class="field-hint">Email de connexion (non modifiable)</span>
                    </div>

                    <div class="form-group">
                        <label for="phone">Téléphone <span class="required">*</span></label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+229 97 00 00 00" required>
                        @error('phone')<span class="error-message">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h4 class="form-section-title">Informations complémentaires</h4>
                <div class="form-grid grid-3">
                    <div class="form-group">
                        <label for="birth_date">Date de naissance</label>
                        <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date', $user->birth_date?->format('Y-m-d')) }}">
                        @error('birth_date')<span class="error-message">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label for="gender">Genre</label>
                        <select id="gender" name="gender">
                            <option value="">Non spécifié</option>
                            <option value="male" {{ old('gender', $user->gender) == 'male' ? 'selected' : '' }}>Homme</option>
                            <option value="female" {{ old('gender', $user->gender) == 'female' ? 'selected' : '' }}>Femme</option>
                            <option value="other" {{ old('gender', $user->gender) == 'other' ? 'selected' : '' }}>Autre</option>
                        </select>
                        @error('gender')<span class="error-message">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label for="id_number">Numéro d'identité</label>
                        <input type="text" id="id_number" name="id_number" value="{{ old('id_number', $user->id_number) }}" placeholder="CNI, Passeport...">
                        @error('id_number')<span class="error-message">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h4 class="form-section-title">Adresse</h4>
                <div class="form-grid grid-2">
                    <div class="form-group full-width">
                        <label for="address">Adresse complète</label>
                        <textarea id="address" name="address" rows="2" placeholder="Rue, quartier, immeuble...">{{ old('address', $user->address) }}</textarea>
                        @error('address')<span class="error-message">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label for="city">Ville <span class="required">*</span></label>
                        <input type="text" id="city" name="city" value="{{ old('city', $user->city) }}" placeholder="Cotonou" required>
                        @error('city')<span class="error-message">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label for="postal_code">Code postal</label>
                        <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code', $user->postal_code) }}" placeholder="01 BP 1234">
                        @error('postal_code')<span class="error-message">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label for="country">Pays</label>
                        <input type="text" id="country" name="country" value="{{ old('country', $user->country ?? 'Bénin') }}">
                        @error('country')<span class="error-message">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h4 class="form-section-title">Présentation</h4>
                <div class="form-group full-width">
                    <label for="bio">Biographie / Description</label>
                    <textarea id="bio" name="bio" rows="4" placeholder="Présentez-vous en quelques mots...">{{ old('bio', $user->bio) }}</textarea>
                    @error('bio')<span class="error-message">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-save">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>

    <!-- Aperçu entreprise principale -->
    @php
        $primaryCompany = auth()->user()->companies()->where('is_primary', true)->first() 
            ?? auth()->user()->companies()->first();
    @endphp

    @if($primaryCompany)
    <div class="company-preview-card">
        <div class="preview-header">
            <h3>Entreprise principale</h3>
            <a href="{{ route('client.profile.companies.show', $primaryCompany) }}" class="btn-view">
                Voir détails →
            </a>
        </div>
        <div class="company-preview-content">
            <div class="company-avatar-large" style="background: {{ $primaryCompany->color }}">
                {{ $primaryCompany->initials }}
            </div>
            <div class="company-preview-info">
                <h4>{{ $primaryCompany->company_name }}</h4>
                <div class="company-meta">
                    <span class="badge-type">{{ $primaryCompany->company_type_label }}</span>
                    <span class="separator">•</span>
                    <span>{{ $primaryCompany->sector_label }}</span>
                </div>
                @if($primaryCompany->city)
                    <p class="company-location">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="14" height="14">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                        {{ $primaryCompany->city }}
                    </p>
                @endif
            </div>
        </div>
        <div class="preview-actions">
            <a href="{{ route('client.profile.companies.index') }}" class="btn btn-outline btn-sm">
                Gérer toutes mes entreprises
            </a>
        </div>
    </div>
    @else
    <div class="no-company-card">
        <div class="no-company-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="32" height="32">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </div>
        <h4>Aucune entreprise</h4>
        <p>Ajoutez votre première entreprise pour compléter votre profil professionnel.</p>
        <a href="{{ route('client.profile.companies.create') }}" class="btn btn-primary">
            Ajouter une entreprise
        </a>
    </div>
    @endif
</div>
@endsection

@section('styles')
<style>
.profile-container {
    padding: 1rem;
    max-width: 900px;
    margin: 0 auto;
    padding-bottom: 100px;
}

/* Carte complétion */
.completion-card {
    background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 1rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 10px 25px rgba(30, 64, 175, 0.3);
}

.completion-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.completion-header h2 {
    margin: 0 0 0.25rem 0;
    font-size: 1.25rem;
    font-weight: 700;
}

.completion-subtitle {
    margin: 0;
    opacity: 0.9;
    font-size: 0.875rem;
}

.completion-circle {
    position: relative;
    width: 60px;
    height: 60px;
}

.circular-chart {
    display: block;
    width: 100%;
    height: 100%;
}

.circle-bg {
    fill: none;
    stroke: rgba(255,255,255,0.3);
    stroke-width: 3;
}

.circle {
    fill: none;
    stroke: white;
    stroke-width: 3;
    stroke-linecap: round;
    transition: stroke-dasharray 0.5s ease;
}

.percentage {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 0.875rem;
    font-weight: 700;
}

.progress-bar-linear {
    height: 6px;
    background: rgba(255,255,255,0.3);
    border-radius: 3px;
    overflow: hidden;
}

.progress-fill-linear {
    height: 100%;
    background: white;
    border-radius: 3px;
    transition: width 0.5s ease;
}

/* Section profil */
.profile-section {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;
    flex-wrap: wrap;
    gap: 1rem;
}

.header-title-group {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.icon-wrapper {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #dbeafe;
    color: #1d4ed8;
}

.icon-wrapper svg {
    width: 20px;
    height: 20px;
}

.section-header h3 {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 600;
    color: #1e293b;
}

.section-subtitle {
    margin: 0.25rem 0 0 0;
    font-size: 0.875rem;
    color: #64748b;
}

.btn-companies {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: #f1f5f9;
    color: #475569;
    padding: 0.625rem 1rem;
    border-radius: 0.5rem;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s;
}

.btn-companies:hover {
    background: #e2e8f0;
    color: #1e293b;
}

.badge-count {
    background: #3b82f6;
    color: white;
    font-size: 0.75rem;
    padding: 0.125rem 0.5rem;
    border-radius: 9999px;
    font-weight: 600;
}

/* Formulaire */
.profile-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-section {
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #f3f4f6;
}

.form-section:last-of-type {
    border-bottom: none;
    padding-bottom: 0;
}

.form-section-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin: 0 0 1rem 0;
}

.form-grid {
    display: grid;
    gap: 1rem;
}

.grid-2 {
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
}

.grid-3 {
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.required {
    color: #dc2626;
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 0.625rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 0.9375rem;
    transition: all 0.2s;
    background: white;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-group input.readonly-field {
    background: #f3f4f6;
    color: #6b7280;
    cursor: not-allowed;
}

.field-hint {
    font-size: 0.75rem;
    color: #9ca3af;
}

.error-message {
    color: #dc2626;
    font-size: 0.75rem;
}

.form-actions {
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
    text-decoration: none;
    font-size: 0.9375rem;
}

.btn-primary {
    background: #1e40af;
    color: white;
}

.btn-primary:hover {
    background: #1e3a8a;
}

.btn-outline {
    background: white;
    color: #475569;
    border: 1px solid #d1d5db;
}

.btn-outline:hover {
    background: #f9fafb;
    border-color: #9ca3af;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

.btn-save {
    width: 100%;
}

/* Carte entreprise */
.company-preview-card {
    background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
    border: 2px solid #bbf7d0;
    border-radius: 1rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.preview-header h3 {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 600;
    color: #166534;
}

.btn-view {
    color: #16a34a;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
}

.company-preview-content {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.company-avatar-large {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.5rem;
    color: white;
    flex-shrink: 0;
}

.company-preview-info h4 {
    margin: 0 0 0.5rem 0;
    font-size: 1.125rem;
    color: #1e293b;
}

.company-meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #64748b;
    flex-wrap: wrap;
}

.badge-type {
    background: #dcfce7;
    color: #166534;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
}

.separator {
    color: #d1d5db;
}

.company-location {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    margin: 0.5rem 0 0 0;
    font-size: 0.875rem;
    color: #64748b;
}

.preview-actions {
    padding-top: 1rem;
    border-top: 1px solid #bbf7d0;
}

/* No company card */
.no-company-card {
    text-align: center;
    padding: 2.5rem;
    background: #f8fafc;
    border: 2px dashed #e2e8f0;
    border-radius: 1rem;
    margin-bottom: 1.5rem;
}

.no-company-icon {
    width: 64px;
    height: 64px;
    margin: 0 auto 1rem;
    background: #e2e8f0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
}

.no-company-card h4 {
    margin: 0 0 0.5rem 0;
    color: #475569;
}

.no-company-card p {
    margin: 0 0 1.5rem 0;
    color: #64748b;
    font-size: 0.875rem;
}

/* Responsive */
@media (max-width: 640px) {
    .completion-header {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .grid-2, .grid-3 {
        grid-template-columns: 1fr;
    }
    
    .company-preview-content {
        flex-direction: column;
        text-align: center;
    }
}
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animation au chargement
    const form = document.getElementById('profile-form');
    if (form) {
        form.style.opacity = '0';
        form.style.transform = 'translateY(20px)';
        setTimeout(() => {
            form.style.transition = 'all 0.5s ease';
            form.style.opacity = '1';
            form.style.transform = 'translateY(0)';
        }, 100);
    }
});
</script>
@endsection