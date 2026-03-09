@extends('layouts.app')

@section('title', 'Modifier ' . $company->company_name . ' - BHDM')
@section('header-title', 'Modifier l\'entreprise')

@section('content')
<div class="company-edit-container">
    <!-- En-tête -->
    <div class="edit-header">
        <a href="{{ route('client.profile.companies.index') }}" class="btn-icon-back">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div class="header-content">
            <h1>Modifier l'entreprise</h1>
            <p>{{ $company->company_name }}</p>
        </div>
    </div>

    <!-- Aperçu entreprise -->
    <div class="preview-card">
        <div class="preview-avatar" style="background: {{ $company->color }}">
            {{ $company->initials }}
        </div>
        <div class="preview-info">
            <span class="preview-name">{{ $company->company_name }}</span>
            <span class="preview-meta">{{ $company->company_type_label }} • {{ $company->sector_label }}</span>
        </div>
        @if($company->is_primary)
            <span class="primary-badge">Entreprise principale</span>
        @endif
    </div>

    <!-- Formulaire -->
    <form action="{{ route('client.profile.companies.update', $company) }}" method="POST" class="edit-form">
        @csrf
        @method('PATCH')

        <!-- Section identité -->
        <div class="form-section">
            <h3 class="section-title">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Identité de l'entreprise
            </h3>
            
            <div class="form-grid grid-2">
                <div class="form-group">
                    <label for="company_name">Nom de l'entreprise <span class="required">*</span></label>
                    <input type="text" id="company_name" name="company_name" value="{{ old('company_name', $company->company_name) }}" placeholder="Ex: Société ABC" required>
                    @error('company_name')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="company_type">Type de structure <span class="required">*</span></label>
                    <select id="company_type" name="company_type" required>
                        @foreach(['sarl' => 'SARL', 'sa' => 'SA', 'snc' => 'SNC', 'ei' => 'Entreprise Individuelle', 'eurl' => 'EURL', 'cooperative' => 'Coopérative', 'ong' => 'ONG', 'association' => 'Association', 'autre' => 'Autre'] as $value => $label)
                            <option value="{{ $value }}" {{ old('company_type', $company->company_type) == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('company_type')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="sector">Secteur d'activité <span class="required">*</span></label>
                    <select id="sector" name="sector" required>
                        @foreach(['agriculture' => 'Agriculture', 'elevage' => 'Élevage', 'peche' => 'Pêche', 'industrie' => 'Industrie', 'commerce' => 'Commerce', 'services' => 'Services', 'tourisme' => 'Tourisme', 'batiment' => 'Bâtiment & Travaux Publics', 'technologie' => 'Technologie & IT', 'sante' => 'Santé', 'education' => 'Éducation', 'finance' => 'Finance & Assurance', 'transport' => 'Transport & Logistique', 'autre' => 'Autre secteur'] as $value => $label)
                            <option value="{{ $value }}" {{ old('sector', $company->sector) == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('sector')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="registration_number">Numéro RCCM</label>
                    <input type="text" id="registration_number" name="registration_number" value="{{ old('registration_number', $company->registration_number) }}" placeholder="RB/COT/...">
                    @error('registration_number')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="tax_id">Numéro IFU</label>
                    <input type="text" id="tax_id" name="tax_id" value="{{ old('tax_id', $company->tax_id) }}" placeholder="Numéro IFU">
                    @error('tax_id')<span class="error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <!-- Section taille -->
        <div class="form-section">
            <h3 class="section-title">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Structure et effectif
            </h3>
            
            <div class="form-grid grid-3">
                <div class="form-group">
                    <label for="job_title">Votre fonction/poste</label>
                    <input type="text" id="job_title" name="job_title" value="{{ old('job_title', $company->job_title) }}" placeholder="Directeur Général">
                    @error('job_title')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="employees_count">Nombre d'employés</label>
                    <input type="number" id="employees_count" name="employees_count" value="{{ old('employees_count', $company->employees_count) }}" min="0" placeholder="0">
                    @error('employees_count')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="annual_turnover">Chiffre d'affaires (FCFA)</label>
                    <input type="number" id="annual_turnover" name="annual_turnover" value="{{ old('annual_turnover', $company->annual_turnover) }}" min="0" placeholder="0">
                    @error('annual_turnover')<span class="error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <!-- Section contact -->
        <div class="form-section">
            <h3 class="section-title">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Coordonnées
            </h3>
            
            <div class="form-grid grid-2">
                <div class="form-group full-width">
                    <label for="address">Adresse complète</label>
                    <textarea id="address" name="address" rows="2" placeholder="Rue, quartier, immeuble...">{{ old('address', $company->address) }}</textarea>
                    @error('address')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="city">Ville</label>
                    <input type="text" id="city" name="city" value="{{ old('city', $company->city) }}" placeholder="Cotonou">
                    @error('city')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="company_phone">Téléphone professionnel</label>
                    <input type="tel" id="company_phone" name="company_phone" value="{{ old('company_phone', $company->company_phone) }}" placeholder="+229 21 00 00 00">
                    @error('company_phone')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label for="company_email">Email professionnel</label>
                    <input type="email" id="company_email" name="company_email" value="{{ old('company_email', $company->company_email) }}" placeholder="contact@entreprise.com">
                    @error('company_email')<span class="error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <!-- Section description -->
        <div class="form-section">
            <h3 class="section-title">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                </svg>
                Présentation
            </h3>
            
            <div class="form-group full-width">
                <label for="description">Description de l'activité</label>
                <textarea id="description" name="description" rows="4" placeholder="Décrivez l'activité principale de votre entreprise...">{{ old('description', $company->description) }}</textarea>
                @error('description')<span class="error">{{ $message }}</span>@enderror
            </div>
        </div>

        <!-- Section statut -->
        <div class="form-section">
            <h3 class="section-title">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Statut de l'entreprise
            </h3>
            
            <div class="form-group checkbox-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $company->is_active) ? 'checked' : '' }}>
                    <span class="checkmark"></span>
                    <div class="checkbox-text">
                        <strong>Entreprise active</strong>
                        <span>Décochez pour désactiver temporairement cette entreprise</span>
                    </div>
                </label>
            </div>
        </div>

        <!-- Actions -->
        <div class="form-actions">
            <a href="{{ route('client.profile.companies.show', $company) }}" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Enregistrer les modifications
            </button>
        </div>
    </form>
</div>
@endsection

@section('styles')
<style>
.company-edit-container {
    padding: 1rem;
    max-width: 800px;
    margin: 0 auto;
    padding-bottom: 100px;
}

/* En-tête */
.edit-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.btn-icon-back {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    text-decoration: none;
    transition: all 0.2s;
    flex-shrink: 0;
}

.btn-icon-back:hover {
    background: #e2e8f0;
    color: #1e293b;
}

.header-content h1 {
    margin: 0 0 0.25rem 0;
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
}

.header-content p {
    margin: 0;
    color: #64748b;
    font-size: 0.875rem;
}

/* Preview Card */
.preview-card {
    background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
    border: 2px solid #bbf7d0;
    border-radius: 1rem;
    padding: 1.25rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.preview-avatar {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.25rem;
    color: white;
    flex-shrink: 0;
    text-transform: uppercase;
}

.preview-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    flex: 1;
}

.preview-name {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1e293b;
}

.preview-meta {
    font-size: 0.875rem;
    color: #64748b;
}

.primary-badge {
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* Formulaire */
.edit-form {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.form-section {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #f3f4f6;
}

.form-section:last-of-type {
    border-bottom: none;
    margin-bottom: 0;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0 0 1.25rem 0;
    font-size: 1rem;
    font-weight: 600;
    color: #374151;
}

.section-title svg {
    color: #3b82f6;
}

.form-grid {
    display: grid;
    gap: 1rem;
}

.grid-2 {
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
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

.form-group select {
    cursor: pointer;
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.error {
    color: #dc2626;
    font-size: 0.75rem;
}

/* Checkbox */
.checkbox-group {
    margin-top: 0.5rem;
}

.checkbox-label {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    cursor: pointer;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 0.5rem;
    transition: all 0.2s;
}

.checkbox-label:hover {
    background: #f3f4f6;
}

.checkbox-label input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
}

.checkmark {
    width: 20px;
    height: 20px;
    border: 2px solid #d1d5db;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 2px;
    transition: all 0.2s;
}

.checkbox-label input:checked + .checkmark {
    background: #1e40af;
    border-color: #1e40af;
}

.checkmark::after {
    content: '';
    width: 5px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
    display: none;
}

.checkbox-label input:checked + .checkmark::after {
    display: block;
}

.checkbox-text {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.checkbox-text strong {
    font-size: 0.9375rem;
    color: #1e293b;
    font-weight: 500;
}

.checkbox-text span {
    font-size: 0.8125rem;
    color: #6b7280;
}

/* Actions */
.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    padding-top: 1.5rem;
    border-top: 2px solid #f3f4f6;
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

.btn-secondary {
    background: #f1f5f9;
    color: #475569;
}

.btn-secondary:hover {
    background: #e2e8f0;
}

.btn-primary {
    background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
    color: white;
    box-shadow: 0 2px 4px rgba(30, 64, 175, 0.2);
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(30, 64, 175, 0.3);
}

/* Responsive */
@media (max-width: 640px) {
    .grid-2, .grid-3 {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column-reverse;
    }
    
    .btn {
        width: 100%;
    }
    
    .preview-card {
        flex-direction: column;
        text-align: center;
    }
}
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animation des sections au scroll
    const sections = document.querySelectorAll('.form-section');
    sections.forEach((section, index) => {
        section.style.opacity = '0';
        section.style.transform = 'translateY(20px)';
        setTimeout(() => {
            section.style.transition = 'all 0.5s ease';
            section.style.opacity = '1';
            section.style.transform = 'translateY(0)';
        }, index * 100);
    });
});
</script>
@endsection