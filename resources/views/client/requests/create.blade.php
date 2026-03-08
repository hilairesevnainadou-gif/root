@extends('layouts.app')

@section('title', 'Nouvelle demande de financement')
@section('header-title', 'Nouvelle demande')

@section('header-action')
<a href="{{ route('client.requests.index') }}" class="btn-back" data-transition="slide-right">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
    </svg>
</a>
@endsection

@section('content')

<div class="request-create">

    {{-- Indicateur d'étapes --}}
    <div class="stepper">
        <div class="stepper-track">
            <div class="stepper-progress" id="stepper-progress" style="width: 0%"></div>
        </div>
        <div class="stepper-steps">
            <div class="step active" data-step="1">
                <div class="step-bubble">
                    <span class="step-number">1</span>
                    <svg class="step-check" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="14" height="14">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <span class="step-label">Type de financement</span>
            </div>
            <div class="step" data-step="2">
                <div class="step-bubble">
                    <span class="step-number">2</span>
                    <svg class="step-check" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="14" height="14">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <span class="step-label">Détails</span>
            </div>
        </div>
    </div>

    {{-- ÉTAPE 1 : Sélection du financement --}}
    <div class="step-panel active" id="step-1">
        <div class="step-intro">
            <h2 class="step-title">Choisissez votre financement</h2>
            <p class="step-desc">Sélectionnez l'offre qui correspond à vos besoins</p>
        </div>

        <div class="financement-grid">
            @forelse($availableTypes as $financement)
            <div class="financement-card"
                 onclick="selectFinancement({{ $financement->id }})"
                 data-id="{{ $financement->id }}"
                 data-name="{{ $financement->name }}"
                 data-type="{{ $financement->typeusers }}"
                 data-variable="{{ $financement->is_variable_amount ? '1' : '0' }}"
                 data-max-daily="{{ $financement->max_daily_amount }}"
                 data-daily-gain="{{ $financement->daily_gain }}"
                 data-amount="{{ $financement->amount }}"
                 data-duration="{{ $financement->duration_months }}"
                 data-reg-fee="{{ $financement->registration_fee }}">

                <div class="fc-selector">
                    <div class="fc-radio">
                        <div class="radio-inner"></div>
                    </div>
                </div>

                <div class="fc-content">
                    <div class="fc-header">
                        <div class="fc-icon-wrapper {{ $financement->typeusers }}">
                            @if($financement->typeusers === 'entreprise')
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            @else
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            @endif
                        </div>

                        <div class="fc-badges">
                            <span class="badge-type {{ $financement->typeusers }}">
                                {{ $financement->typeusers === 'entreprise' ? 'Entreprise' : 'Particulier' }}
                            </span>
                        </div>
                    </div>

                    <h3 class="fc-name">{{ $financement->name }}</h3>
                    <p class="fc-description">{{ Str::limit($financement->description, 90) }}</p>

                    <div class="fc-stats">
                        @if($financement->is_variable_amount && $financement->max_daily_amount)
                        <div class="stat-item highlight">
                            <span class="stat-value">~{{ number_format($financement->max_daily_amount, 0, ',', ' ') }} F</span>
                            <span class="stat-label">/jour max</span>
                        </div>
                        @elseif($financement->daily_gain)
                        <div class="stat-item highlight">
                            <span class="stat-value">{{ number_format($financement->daily_gain, 0, ',', ' ') }} F</span>
                            <span class="stat-label">/jour</span>
                        </div>
                        @endif

                        @if($financement->duration_months)
                        <div class="stat-item">
                            <span class="stat-value">{{ $financement->duration_months }}</span>
                            <span class="stat-label">mois</span>
                        </div>
                        @endif

                        <div class="stat-item">
                            <span class="stat-value">{{ number_format($financement->registration_fee, 0, ',', ' ') }} F</span>
                            <span class="stat-label">frais</span>
                        </div>
                    </div>
                </div>

                <div class="fc-arrow">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </div>
            @empty
            <div class="empty-state">
                <div class="empty-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="64" height="64">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3>Aucun financement disponible</h3>
                <p>Aucune offre ne correspond à votre profil actuellement.</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- ÉTAPE 2 : Formulaire de détails --}}
    <div class="step-panel" id="step-2">
        {{-- Récapitulatif du choix --}}
        <div class="choice-summary" id="choice-summary">
            <button type="button" class="cs-back" onclick="goToStep(1)">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <div class="cs-content">
                <div class="cs-icon" id="cs-icon"></div>
                <div class="cs-info">
                    <span class="cs-label">Votre choix</span>
                    <span class="cs-name" id="cs-name">-</span>
                    <span class="cs-badge" id="cs-badge">-</span>
                </div>
            </div>
        </div>

        <form id="requestForm" class="request-form" onsubmit="return false;">
            @csrf
            <input type="hidden" name="typefinancement_id" id="selected_type_id">
            <input type="hidden" name="financement_type" id="financement_type">
            <input type="hidden" name="amount_requested" id="amount_requested_hidden" value="">
            <input type="hidden" name="duration" id="duration_input">

            {{-- SECTION ENTREPRISE - Uniquement si type entreprise --}}
            <div class="section-company" id="company-section" style="display: none;">
                <div class="section-header">
                    <div class="sh-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div class="sh-text">
                        <h3>Entreprise concernée</h3>
                        <p>Sélectionnez une entreprise existante ou créez-en une nouvelle</p>
                    </div>
                </div>

                {{-- Liste des entreprises existantes --}}
                <div class="companies-container" id="existing-companies">
                    <div class="companies-list" id="companies-grid"></div>
                </div>

                <div class="divider-or">
                    <span>ou</span>
                </div>

                <button type="button" class="btn-create-company" id="btn-toggle-company" onclick="toggleNewCompanyForm()">
                    <span class="bcc-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </span>
                    <span class="bcc-text" id="toggle-company-text">Créer une nouvelle entreprise</span>
                    <svg class="bcc-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                {{-- Formulaire nouvelle entreprise --}}
                <div class="new-company-panel" id="new-company-form" style="display: none;">
                    <div class="ncp-header">
                        <h4>Nouvelle entreprise</h4>
                        <button type="button" class="ncp-close" onclick="toggleNewCompanyForm()">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="form-grid cols-2">
                        <div class="form-group">
                            <label class="form-label">Nom de l'entreprise <span class="text-danger">*</span></label>
                            <input type="text" name="new_company[name]" id="new_company_name" class="form-control company-field"
                                placeholder="Ex: Ma Société SARL">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Type d'entreprise <span class="text-danger">*</span></label>
                            <select name="new_company[company_type]" id="new_company_type" class="form-control company-field">
                                <option value="">Choisir...</option>
                                <option value="SARL">SARL</option>
                                <option value="SA">SA</option>
                                <option value="SAS">SAS</option>
                                <option value="Entreprise Individuelle">Entreprise Individuelle</option>
                                <option value="GIE">GIE</option>
                                <option value="Autre">Autre</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Secteur d'activité <span class="text-danger">*</span></label>
                            <input type="text" name="new_company[sector]" id="new_company_sector" class="form-control company-field"
                                placeholder="Ex: Agriculture, Commerce...">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Votre poste <span class="text-danger">*</span></label>
                            <input type="text" name="new_company[job_title]" id="new_company_job" class="form-control company-field"
                                placeholder="Ex: Directeur Général">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Nombre d'employés</label>
                            <select name="new_company[employees_count]" id="new_company_employees" class="form-control company-field">
                                <option value="">Choisir...</option>
                                <option value="1">1 (Auto-entrepreneur)</option>
                                <option value="2-5">2 à 5</option>
                                <option value="6-10">6 à 10</option>
                                <option value="11-50">11 à 50</option>
                                <option value="51-200">51 à 200</option>
                                <option value="200+">Plus de 200</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Chiffre d'affaires annuel (FCFA)</label>
                            <input type="number" name="new_company[annual_turnover]" id="new_company_turnover"
                                class="form-control company-field" placeholder="Ex: 50000000" min="0" step="100000">
                        </div>
                    </div>
                </div>

                <input type="hidden" name="company_id" id="selected_company_id" value="">
            </div>

            {{-- MONTANT --}}
            <div class="form-section">
                <div class="section-title-sm">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Montant du financement
                </div>

                <div class="amount-container" id="variable-amount-input" style="display: none;">
                    <div class="amount-field">
                        <input type="number" id="amount_requested_variable" class="form-control form-control-xl"
                            placeholder="50000" min="1000" step="1000" oninput="updateVariableAmount(this.value)">
                        <span class="amount-suffix">FCFA / jour</span>
                    </div>
                    <div class="amount-limit" id="amount-hint"></div>

                    <div class="calculation-preview">
                        <div class="calc-row">
                            <span>Montant quotidien</span>
                            <strong id="preview-daily">0 FCFA</strong>
                        </div>
                        <div class="calc-row">
                            <span>Durée</span>
                            <span id="preview-duration">-</span>
                        </div>
                        <div class="calc-row total">
                            <span>Total estimé</span>
                            <strong id="preview-total">0 FCFA</strong>
                        </div>
                    </div>
                </div>

                <div class="amount-container" id="fixed-amount-display" style="display: none;">
                    <div class="fixed-amount-box">
                        <div class="fab-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="28" height="28">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="fab-content">
                            <div class="fab-amount" id="fixed-amount-value">-</div>
                            <div class="fab-detail" id="fixed-amount-detail">-</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DURÉE --}}
            <div class="form-section">
                <div class="section-title-sm">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Durée
                </div>
                <div class="duration-box">
                    <span class="db-main" id="duration-value">-</span>
                    <span class="db-sub" id="duration-detail">-</span>
                </div>
            </div>

            {{-- INFORMATIONS DEMANDE --}}
            <div class="form-section">
                <div class="section-title-sm">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Informations de la demande
                </div>

                <div class="form-group">
                    <label class="form-label" for="title">Titre de la demande <span class="text-danger">*</span></label>
                    <input type="text" name="title" id="title" class="form-control"
                        placeholder="Ex: Financement pour mon projet agricole" maxlength="100" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Description du projet <span class="text-muted">(optionnel)</span></label>
                    <textarea name="description" id="description" class="form-control" rows="3" maxlength="500"
                        placeholder="Décrivez brièvement l'objet de votre demande..."></textarea>
                    <div class="char-count"><span id="desc-count">0</span> / 500 caractères</div>
                </div>
            </div>

            {{-- RÉCAPITULATIF FRAIS --}}
            <div class="fees-summary-card">
                <div class="fsc-header">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a1 1 0 11-2 0 1 1 0 012 0z" />
                    </svg>
                    <span>Récapitulatif des frais</span>
                </div>
                <div class="fsc-body">
                    <div class="fsc-row">
                        <span>Frais d'inscription</span>
                        <span id="reg-fee-display">-</span>
                    </div>
                    <div class="fsc-row total">
                        <span>Total à payer maintenant</span>
                        <strong id="total-fee-display">-</strong>
                    </div>
                </div>
            </div>

            {{-- ZONE DE PAIEMENT --}}
            <div class="payment-zone" id="payment-section" style="display: none;">
                <div class="pz-loading" id="payment-loading">
                    <div class="spinner-dual"></div>
                    <p>Préparation du paiement sécurisé...</p>
                </div>
                <div id="kkiapay-widget-container" style="display: none;"></div>
                <div class="pz-loading" id="payment-polling" style="display: none;">
                    <div class="spinner-dual"></div>
                    <p>Vérification du paiement...</p>
                    <small>Veuillez patienter quelques instants</small>
                </div>
            </div>

            {{-- ACTIONS --}}
            <div class="form-actions" id="form-actions">
                <button type="button" class="btn-submit" id="submitBtn" onclick="preparePayment()" disabled>
                    <span class="btn-text">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a1 1 0 11-2 0 1 1 0 012 0z" />
                        </svg>
                        Procéder au paiement
                    </span>
                    <span class="btn-spinner">
                        <div class="spinner-dual small"></div>
                    </span>
                </button>
                <p class="security-note">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="14" height="14">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Paiement 100% sécurisé via Kkiapay
                </p>
            </div>
        </form>
    </div>

</div>

@endsection

@section('scripts')
<script src="https://cdn.kkiapay.me/k.js"></script>

<script>
// Données injectées
const financements = @json($availableTypes->keyBy('id'));
const userCompanies = @json($userCompanies ?? []);
let currentStep = 1;
let currentSelection = null;
let selectedCompanyId = null;
let isProcessing = false;
let currentFundingRequestId = null;
let currentTransaction = null;
let currentAmount = 0;

document.addEventListener('DOMContentLoaded', function () {
    setupEventListeners();

    if (typeof addSuccessListener === 'function') {
        addSuccessListener(onKkiapaySuccess);
        addFailedListener(onKkiapayFailed);
    }
});

function setupEventListeners() {
    const descInput = document.getElementById('description');
    if (descInput) {
        descInput.addEventListener('input', function() {
            document.getElementById('desc-count').textContent = this.value.length;
        });
    }
}

// Navigation entre étapes
function goToStep(step) {
    if (step === 2 && !currentSelection) return;

    const currentPanel = document.querySelector('.step-panel.active');
    const nextPanel = document.getElementById(`step-${step}`);
    const isForward = step > currentStep;

    currentStep = step;
    updateStepper(step);

    currentPanel.classList.add(isForward ? 'exit-to-left' : 'exit-to-right');

    setTimeout(() => {
        currentPanel.classList.remove('active', 'exit-to-left', 'exit-to-right');
        nextPanel.classList.add('active', isForward ? 'enter-from-right' : 'enter-from-left');

        setTimeout(() => {
            nextPanel.classList.remove('enter-from-right', 'enter-from-left');
        }, 400);
    }, 300);
}

function updateStepper(step) {
    const progress = document.getElementById('stepper-progress');
    const steps = document.querySelectorAll('.step');

    progress.style.width = step === 1 ? '0%' : '100%';

    steps.forEach((s, index) => {
        const stepNum = index + 1;
        s.classList.remove('active', 'completed');

        if (stepNum === step) {
            s.classList.add('active');
        } else if (stepNum < step) {
            s.classList.add('completed');
        }
    });
}

// Sélection financement
function selectFinancement(id) {
    currentSelection = id;
    const fin = financements[id];
    if (!fin) return;

    document.querySelectorAll('.financement-card').forEach(card => {
        card.classList.remove('selected');
    });

    const selectedCard = document.querySelector(`[data-id="${id}"]`);
    if (selectedCard) {
        selectedCard.classList.add('selected');
    }

    prepareStep2(fin);

    setTimeout(() => {
        goToStep(2);
    }, 350);
}

function prepareStep2(fin) {
    // Champs cachés
    document.getElementById('selected_type_id').value = fin.id;
    document.getElementById('financement_type').value = fin.typeusers;
    document.getElementById('duration_input').value = fin.duration_months;

    // Récapitulatif
    const isEnt = fin.typeusers === 'entreprise';
    document.getElementById('cs-name').textContent = fin.name;
    document.getElementById('cs-badge').textContent = isEnt ? 'Entreprise' : 'Particulier';
    document.getElementById('cs-badge').className = `cs-badge ${fin.typeusers}`;

    document.getElementById('cs-icon').innerHTML = isEnt
        ? `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>`
        : `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;

    // Section entreprise - IMPORTANT: Vider les champs si particulier
    const companySection = document.getElementById('company-section');
    if (isEnt) {
        companySection.style.display = 'block';
        renderCompaniesList();
        selectedCompanyId = null;
        document.getElementById('selected_company_id').value = '';
    } else {
        companySection.style.display = 'none';
        // IMPORTANT: Vider les champs entreprise pour ne pas les envoyer
        clearCompanyFields();
    }

    // Montant
    const isVariable = fin.is_variable_amount == 1;
    const varInput = document.getElementById('variable-amount-input');
    const fixedDisplay = document.getElementById('fixed-amount-display');
    const hiddenAmount = document.getElementById('amount_requested_hidden');

    if (isVariable) {
        varInput.style.display = 'block';
        fixedDisplay.style.display = 'none';

        const inputVar = document.getElementById('amount_requested_variable');
        inputVar.value = '';
        inputVar.max = fin.max_daily_amount;

        document.getElementById('amount-hint').textContent =
            `Maximum: ${parseInt(fin.max_daily_amount).toLocaleString('fr-FR')} FCFA/jour • Minimum: 1,000 FCFA`;

        updateVariableAmount(0);

    } else {
        varInput.style.display = 'none';
        fixedDisplay.style.display = 'block';

        const fixedAmount = parseInt(fin.amount);
        currentAmount = fixedAmount;
        hiddenAmount.value = fixedAmount;

        document.getElementById('fixed-amount-value').textContent =
            `${fixedAmount.toLocaleString('fr-FR')} FCFA`;
        document.getElementById('fixed-amount-detail').textContent =
            `${parseInt(fin.daily_gain).toLocaleString('fr-FR')} FCFA × ${fin.duration_months * 30} jours`;
    }

    // Durée
    document.getElementById('duration-value').textContent = `${fin.duration_months} mois`;
    document.getElementById('duration-detail').textContent = `${fin.duration_months * 30} jours`;
    document.getElementById('preview-duration').textContent =
        `${fin.duration_months} mois (${fin.duration_months * 30} jours)`;

    // Frais
    const regFee = parseInt(fin.registration_fee);
    document.getElementById('reg-fee-display').textContent =
        `${regFee.toLocaleString('fr-FR')} FCFA`;
    document.getElementById('total-fee-display').textContent =
        `${regFee.toLocaleString('fr-FR')} FCFA`;

    document.getElementById('submitBtn').disabled = false;
}

// NOUVELLE FONCTION: Vider les champs entreprise
function clearCompanyFields() {
    // Vider company_id
    document.getElementById('selected_company_id').value = '';

    // Vider tous les champs new_company
    const companyFields = document.querySelectorAll('.company-field');
    companyFields.forEach(field => {
        field.value = '';
    });

    // Masquer le formulaire nouvelle entreprise
    document.getElementById('new-company-form').style.display = 'none';
    document.getElementById('toggle-company-text').textContent = 'Créer une nouvelle entreprise';
}

// Mise à jour montant variable
function updateVariableAmount(value) {
    const daily = parseInt(value) || 0;
    currentAmount = daily;

    const fin = financements[currentSelection];
    if (!fin) return;

    const duration = fin.duration_months;
    const total = daily * duration * 30;

    document.getElementById('preview-daily').textContent =
        `${daily.toLocaleString('fr-FR')} FCFA`;
    document.getElementById('preview-total').textContent =
        `${total.toLocaleString('fr-FR')} FCFA`;

    document.getElementById('amount_requested_hidden').value = daily;
}

// Gestion entreprises
function renderCompaniesList() {
    const grid = document.getElementById('companies-grid');

    if (userCompanies.length === 0) {
        grid.innerHTML = `
            <div class="no-companies-message">
                <p>Vous n'avez aucune entreprise enregistrée.</p>
            </div>
        `;
        return;
    }

    grid.innerHTML = userCompanies.map(company => `
        <div class="company-select-card ${selectedCompanyId == company.id ? 'selected' : ''}"
             onclick="selectCompany(${company.id})">
            <div class="csc-radio">
                <div class="radio-inner ${selectedCompanyId == company.id ? 'checked' : ''}"></div>
            </div>
            <div class="csc-info">
                <div class="csc-name">${escapeHtml(company.company_name)}</div>
                <div class="csc-meta">${escapeHtml(company.company_type)} • ${escapeHtml(company.sector)}</div>
                <div class="csc-poste">${escapeHtml(company.job_title)}</div>
            </div>
        </div>
    `).join('');
}

function selectCompany(id) {
    selectedCompanyId = id;
    document.getElementById('selected_company_id').value = id;

    // Vider les champs nouvelle entreprise car on sélectionne une existante
    const companyFields = document.querySelectorAll('.company-field');
    companyFields.forEach(field => {
        field.value = '';
    });

    document.getElementById('new-company-form').style.display = 'none';
    document.getElementById('toggle-company-text').textContent = 'Créer une nouvelle entreprise';

    renderCompaniesList();
}

function toggleNewCompanyForm() {
    const form = document.getElementById('new-company-form');
    const isVisible = form.style.display === 'block';
    const btnText = document.getElementById('toggle-company-text');

    if (isVisible) {
        form.style.display = 'none';
        btnText.textContent = 'Créer une nouvelle entreprise';
        // Ne pas vider les champs, l'utilisateur pourrait vouloir les garder
    } else {
        form.style.display = 'block';
        form.classList.add('animate-in');
        // Désélectionner l'entreprise existante
        selectedCompanyId = null;
        document.getElementById('selected_company_id').value = '';
        btnText.textContent = 'Annuler la création';
        renderCompaniesList();

        setTimeout(() => {
            form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }, 100);
    }
}

// Validation
function validateForm(fin) {
    const token = getCsrfToken();
    if (!token) {
        alert('Erreur de sécurité. Rechargez la page.');
        return false;
    }

    // Vérifier montant
    if (fin.is_variable_amount == 1) {
        const amount = parseInt(document.getElementById('amount_requested_hidden').value);
        if (!amount || amount < 1000) {
            alert('Veuillez saisir un montant valide (minimum 1,000 FCFA/jour)');
            document.getElementById('amount_requested_variable').focus();
            return false;
        }
        if (amount > fin.max_daily_amount) {
            alert(`Le montant maximum est de ${fin.max_daily_amount.toLocaleString('fr-FR')} FCFA/jour`);
            return false;
        }
    } else {
        const fixedAmount = parseInt(document.getElementById('amount_requested_hidden').value);
        if (!fixedAmount || fixedAmount <= 0) {
            alert('Erreur: montant non défini. Veuillez recharger la page.');
            return false;
        }
    }

    // Vérifier titre
    const title = document.getElementById('title')?.value.trim();
    if (!title) {
        alert('Veuillez saisir un titre pour votre demande');
        document.getElementById('title').focus();
        return false;
    }

    // Vérifier entreprise UNIQUEMENT si type entreprise
    if (fin.typeusers === 'entreprise') {
        const companyId = document.getElementById('selected_company_id')?.value;
        const newName = document.getElementById('new_company_name')?.value.trim();

        if (!companyId && !newName) {
            alert('Veuillez sélectionner une entreprise existante ou en créer une nouvelle');
            return false;
        }

        if (!companyId && newName) {
            const requiredFields = [
                { id: 'new_company_type', name: 'Type d\'entreprise' },
                { id: 'new_company_sector', name: 'Secteur d\'activité' },
                { id: 'new_company_job', name: 'Votre poste' }
            ];

            for (const field of requiredFields) {
                const el = document.getElementById(field.id);
                if (!el || !el.value.trim()) {
                    alert(`Veuillez remplir le champ: ${field.name}`);
                    el?.focus();
                    return false;
                }
            }
        }
    }

    return true;
}

// CORRECTION PRINCIPALE: Construire FormData manuellement pour contrôler les champs envoyés
async function preparePayment() {
    if (isProcessing) return;

    const fin = financements[currentSelection];
    if (!validateForm(fin)) return;

    const finalAmount = document.getElementById('amount_requested_hidden').value;
    if (!finalAmount || finalAmount <= 0) {
        alert('Erreur: le montant est invalide');
        return;
    }

    isProcessing = true;
    const btn = document.getElementById('submitBtn');
    btn.classList.add('loading');
    btn.disabled = true;

    const csrfToken = getCsrfToken();

    try {
        // CORRECTION: Construire FormData manuellement pour ne pas envoyer les champs vides
        const formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('typefinancement_id', document.getElementById('selected_type_id').value);
        formData.append('financement_type', document.getElementById('financement_type').value);
        formData.append('amount_requested', document.getElementById('amount_requested_hidden').value);
        formData.append('duration', document.getElementById('duration_input').value);
        formData.append('title', document.getElementById('title').value);

        const description = document.getElementById('description').value;
        if (description) {
            formData.append('description', description);
        }

        // CORRECTION: N'envoyer les champs entreprise QUE si c'est un type entreprise
        if (fin.typeusers === 'entreprise') {
            const companyId = document.getElementById('selected_company_id').value;

            if (companyId) {
                // Entreprise existante
                formData.append('company_id', companyId);
            } else {
                // Nouvelle entreprise - n'envoyer QUE si on a un nom
                const newName = document.getElementById('new_company_name').value.trim();
                if (newName) {
                    formData.append('new_company[name]', newName);
                    formData.append('new_company[company_type]', document.getElementById('new_company_type').value);
                    formData.append('new_company[sector]', document.getElementById('new_company_sector').value);
                    formData.append('new_company[job_title]', document.getElementById('new_company_job').value);

                    const employees = document.getElementById('new_company_employees').value;
                    if (employees) {
                        formData.append('new_company[employees_count]', employees);
                    }

                    const turnover = document.getElementById('new_company_turnover').value;
                    if (turnover) {
                        formData.append('new_company[annual_turnover]', turnover);
                    }
                }
            }
        }
        // Si c'est un particulier, on n'envoie PAS company_id ni new_company

        // Debug
        console.log('FormData contents (filtré):');
        for (let [key, value] of formData.entries()) {
            console.log(`${key}: ${value}`);
        }

        const storeRes = await fetch('{{ route("client.requests.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });

        const storeData = await storeRes.json();

        if (!storeRes.ok) {
            if (storeData.errors) {
                const errorMessages = Object.entries(storeData.errors)
                    .map(([field, errors]) => `${field}: ${errors.join(', ')}`)
                    .join('\n');
                throw new Error(errorMessages);
            }
            throw new Error(storeData.message || 'Erreur lors de la création');
        }

        if (!storeData.success) {
            throw new Error(storeData.message || 'Erreur inconnue');
        }

        currentFundingRequestId = storeData.funding_request_id;

        const initRes = await fetch(`/requests/${currentFundingRequestId}/payment/initialize`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            }
        });

        const initData = await initRes.json();

        if (!initRes.ok || !initData.success) {
            throw new Error(initData.message || 'Erreur initialisation paiement');
        }

        currentTransaction = initData.transaction;

        document.getElementById('form-actions').style.display = 'none';
        document.getElementById('payment-section').style.display = 'block';

        openKkiapayWidget({
            amount: initData.kkiapay_config.amount,
            key: initData.kkiapay_config.key,
            sandbox: initData.kkiapay_config.sandbox,
            data: initData.kkiapay_config.data,
            theme: '#2563eb',
            name: 'BHDM',
        });

    } catch (error) {
        console.error('Payment error:', error);
        alert(error.message || 'Une erreur est survenue');

        btn.classList.remove('loading');
        btn.disabled = false;
        isProcessing = false;
    }
}

// Callbacks Kkiapay
async function onKkiapaySuccess(response) {
    console.log('Kkiapay success:', response);

    document.getElementById('payment-loading').style.display = 'none';
    document.getElementById('payment-polling').style.display = 'block';

    const maxAttempts = 20;
    let attempt = 0;

    while (attempt < maxAttempts) {
        attempt++;
        const delay = Math.min(1000 * Math.pow(1.2, attempt), 5000);

        try {
            const verifyRes = await fetch('/payment/verify', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    transactionId: response.transactionId,
                    funding_request_id: currentFundingRequestId,
                    internal_transaction_id: currentTransaction.transaction_id,
                }),
            });

            const data = await verifyRes.json();

            if (data.status === 'paid') {
                showPaymentSuccess();
                setTimeout(() => {
                    window.location.href = data.redirect_url || '/my-requests';
                }, 1500);
                return;
            }

            if (data.status === 'failed') {
                alert('Paiement échoué: ' + (data.message || 'Erreur de transaction'));
                resetPaymentUI();
                return;
            }

            await new Promise(r => setTimeout(r, delay));

        } catch (err) {
            console.error('Polling error:', err);
            await new Promise(r => setTimeout(r, 2000));
        }
    }

    alert('Votre paiement est en cours de traitement. Vous recevrez une confirmation par email.');
    window.location.href = '/my-requests';
}

function onKkiapayFailed(response) {
    console.error('Kkiapay failed:', response);
    alert('Le paiement a été annulé ou a échoué. Veuillez réessayer.');
    resetPaymentUI();
}

function resetPaymentUI() {
    document.getElementById('payment-section').style.display = 'none';
    document.getElementById('form-actions').style.display = 'block';
    const btn = document.getElementById('submitBtn');
    btn.classList.remove('loading');
    btn.disabled = false;
    isProcessing = false;
}

function showPaymentSuccess() {
    document.getElementById('payment-polling').innerHTML = `
        <div class="success-check">
            <svg viewBox="0 0 52 52" class="checkmark">
                <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
            </svg>
        </div>
        <p class="success-message">Paiement confirmé !</p>
        <small>Redirection en cours...</small>
    `;
}

// Utilitaires
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content;
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endsection

@section('styles')

@section('styles')
<style>
/* ============================================
   STEPPER - Indicateur d'étapes moderne
   ============================================ */
.stepper {
    background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
    padding: 1.5rem 1rem;
    border-bottom: 1px solid #e2e8f0;
}

.stepper-track {
    height: 3px;
    background: #e2e8f0;
    border-radius: 2px;
    margin-bottom: 1rem;
    position: relative;
    overflow: hidden;
}

.stepper-progress {
    height: 100%;
    background: linear-gradient(90deg, #2563eb, #3b82f6);
    border-radius: 2px;
    transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.stepper-steps {
    display: flex;
    justify-content: space-between;
    max-width: 400px;
    margin: 0 auto;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    flex: 1;
    position: relative;
}

.step-bubble {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    transition: all 0.3s ease;
}

.step-number {
    font-size: 0.875rem;
    font-weight: 700;
    color: #64748b;
    transition: all 0.3s ease;
}

.step-check {
    position: absolute;
    opacity: 0;
    transform: scale(0);
    transition: all 0.3s ease;
    color: white;
}

.step-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.025em;
    transition: all 0.3s ease;
}

.step.active .step-bubble {
    background: linear-gradient(135deg, #2563eb, #3b82f6);
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.4);
    transform: scale(1.1);
}

.step.active .step-number {
    color: white;
}

.step.active .step-label {
    color: #2563eb;
    font-weight: 700;
}

.step.completed .step-bubble {
    background: #10b981;
}

.step.completed .step-number {
    opacity: 0;
}

.step.completed .step-check {
    opacity: 1;
    transform: scale(1);
}

.step.completed .step-label {
    color: #10b981;
}

/* ============================================
   PANELS & TRANSITIONS
   ============================================ */
.step-panel {
    display: none;
    padding: 1.5rem;
    max-width: 720px;
    margin: 0 auto;
}

.step-panel.active {
    display: block;
}

/* Animations de transition */
@keyframes enterFromRight {
    from { opacity: 0; transform: translateX(40px); }
    to { opacity: 1; transform: translateX(0); }
}

@keyframes enterFromLeft {
    from { opacity: 0; transform: translateX(-40px); }
    to { opacity: 1; transform: translateX(0); }
}

@keyframes exitToLeft {
    from { opacity: 1; transform: translateX(0); }
    to { opacity: 0; transform: translateX(-40px); }
}

@keyframes exitToRight {
    from { opacity: 1; transform: translateX(0); }
    to { opacity: 0; transform: translateX(40px); }
}

.enter-from-right { animation: enterFromRight 0.4s ease forwards; }
.enter-from-left { animation: enterFromLeft 0.4s ease forwards; }
.exit-to-left { animation: exitToLeft 0.3s ease forwards; }
.exit-to-right { animation: exitToRight 0.3s ease forwards; }

/* ============================================
   ÉTAPE 1 - LISTE FINANCEMENTS
   ============================================ */
.step-intro {
    text-align: center;
    margin-bottom: 1.5rem;
}

.step-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 0.25rem;
}

.step-desc {
    font-size: 0.875rem;
    color: #64748b;
}

.financement-grid {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.financement-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 16px;
    padding: 1.25rem;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}

.financement-card:hover {
    border-color: #bfdbfe;
    box-shadow: 0 8px 30px -5px rgba(37, 99, 235, 0.15);
    transform: translateY(-2px);
}

.financement-card.selected {
    border-color: #2563eb;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
}

.fc-selector {
    flex-shrink: 0;
}

.fc-radio {
    width: 24px;
    height: 24px;
    border: 2px solid #cbd5e1;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    background: white;
}

.radio-inner {
    width: 10px;
    height: 10px;
    background: #2563eb;
    border-radius: 50%;
    transform: scale(0);
    transition: transform 0.2s ease;
}

.financement-card:hover .fc-radio,
.financement-card.selected .fc-radio {
    border-color: #2563eb;
}

.financement-card.selected .radio-inner {
    transform: scale(1);
}

.fc-content {
    flex: 1;
    min-width: 0;
}

.fc-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.fc-icon-wrapper {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.fc-icon-wrapper.entreprise {
    background: linear-gradient(135deg, #ec4899, #f472b6);
}

.fc-icon-wrapper.particulier {
    background: linear-gradient(135deg, #2563eb, #3b82f6);
}

.badge-type {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.25rem 0.625rem;
    border-radius: 9999px;
}

.badge-type.entreprise {
    background: #fce7f3;
    color: #9d174d;
}

.badge-type.particulier {
    background: #dbeafe;
    color: #1e40af;
}

.fc-name {
    font-size: 1rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 0.375rem;
}

.fc-description {
    font-size: 0.8125rem;
    color: #64748b;
    line-height: 1.5;
    margin-bottom: 0.75rem;
}

.fc-stats {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.stat-item {
    display: flex;
    flex-direction: column;
    background: #f8fafc;
    padding: 0.5rem 0.75rem;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.stat-item.highlight {
    background: #eff6ff;
    border-color: #bfdbfe;
}

.stat-value {
    font-size: 0.875rem;
    font-weight: 700;
    color: #0f172a;
}

.stat-item.highlight .stat-value {
    color: #2563eb;
}

.stat-label {
    font-size: 0.625rem;
    color: #94a3b8;
    text-transform: uppercase;
}

.fc-arrow {
    color: #cbd5e1;
    transition: all 0.2s;
}

.financement-card:hover .fc-arrow {
    color: #2563eb;
    transform: translateX(4px);
}

/* ============================================
   ÉTAPE 2 - RÉCAPITULATIF CHOIX
   ============================================ */
.choice-summary {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    border: 1px solid #bfdbfe;
    border-radius: 14px;
    padding: 0.875rem;
    margin-bottom: 1.5rem;
}

.cs-back {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: white;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #2563eb;
    cursor: pointer;
    transition: all 0.2s;
    flex-shrink: 0;
}

.cs-back:hover {
    background: #2563eb;
    color: white;
}

.cs-content {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    min-width: 0;
}

.cs-icon {
    width: 40px;
    height: 40px;
    background: white;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #2563eb;
    flex-shrink: 0;
}

.cs-info {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
    min-width: 0;
}

.cs-label {
    font-size: 0.6875rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 600;
}

.cs-name {
    font-size: 0.9375rem;
    font-weight: 700;
    color: #0f172a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.cs-badge {
    font-size: 0.625rem;
    font-weight: 700;
    padding: 0.125rem 0.5rem;
    border-radius: 9999px;
    width: fit-content;
    text-transform: uppercase;
}

.cs-badge.entreprise {
    background: #fce7f3;
    color: #9d174d;
}

.cs-badge.particulier {
    background: #dbeafe;
    color: #1e40af;
}

/* ============================================
   SECTION ENTREPRISE - DESIGN SPÉCIAL
   ============================================ */
.section-company {
    background: linear-gradient(180deg, #fdf2f8 0%, #ffffff 100%);
    border: 2px solid #fbcfe8;
    border-radius: 16px;
    padding: 1.25rem;
    margin-bottom: 1.5rem;
    animation: fadeInUp 0.4s ease;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.section-header {
    display: flex;
    align-items: flex-start;
    gap: 0.875rem;
    margin-bottom: 1.25rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #fbcfe8;
}

.sh-icon {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, #ec4899, #f472b6);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
}

.sh-text h3 {
    font-size: 1rem;
    font-weight: 700;
    color: #831843;
    margin-bottom: 0.25rem;
}

.sh-text p {
    font-size: 0.8125rem;
    color: #9d174d;
    line-height: 1.5;
}

.companies-list {
    display: flex;
    flex-direction: column;
    gap: 0.625rem;
    margin-bottom: 1rem;
}

.company-select-card {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    background: white;
    border: 2px solid #fbcfe8;
    border-radius: 12px;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.company-select-card:hover {
    border-color: #f472b6;
    box-shadow: 0 4px 12px rgba(236, 72, 153, 0.1);
}

.company-select-card.selected {
    border-color: #ec4899;
    background: #fdf2f8;
}

.csc-radio {
    flex-shrink: 0;
}

.csc-radio .radio-inner {
    width: 20px;
    height: 20px;
    border: 2px solid #fbcfe8;
    border-radius: 50%;
    position: relative;
    transition: all 0.2s;
}

.csc-radio .radio-inner.checked {
    border-color: #ec4899;
    background: #ec4899;
}

.csc-radio .radio-inner.checked::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 8px;
    height: 8px;
    background: white;
    border-radius: 50%;
}

.csc-info {
    flex: 1;
    min-width: 0;
}

.csc-name {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #0f172a;
    margin-bottom: 0.25rem;
}

.csc-meta {
    font-size: 0.75rem;
    color: #64748b;
    margin-bottom: 0.25rem;
}

.csc-poste {
    font-size: 0.6875rem;
    color: #9d174d;
    font-weight: 500;
    background: #fce7f3;
    padding: 0.125rem 0.5rem;
    border-radius: 4px;
    display: inline-block;
}

.divider-or {
    display: flex;
    align-items: center;
    margin: 1rem 0;
    color: #9d174d;
    font-size: 0.75rem;
    font-weight: 600;
}

.divider-or::before,
.divider-or::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #fbcfe8;
}

.divider-or span {
    padding: 0 0.75rem;
}

.btn-create-company {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: white;
    border: 2px dashed #fbcfe8;
    border-radius: 12px;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.2s;
    color: #9d174d;
}

.btn-create-company:hover {
    border-color: #ec4899;
    background: #fdf2f8;
}

.bcc-icon {
    width: 36px;
    height: 36px;
    background: #fce7f3;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.bcc-text {
    flex: 1;
    font-size: 0.875rem;
    font-weight: 600;
    text-align: left;
}

.bcc-arrow {
    transition: transform 0.2s;
}

.btn-create-company[aria-expanded="true"] .bcc-arrow {
    transform: rotate(180deg);
}

.new-company-panel {
    margin-top: 1rem;
    background: white;
    border: 2px solid #ec4899;
    border-radius: 12px;
    padding: 1.25rem;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.ncp-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #fbcfe8;
}

.ncp-header h4 {
    font-size: 0.9375rem;
    font-weight: 700;
    color: #831843;
}

.ncp-close {
    background: none;
    border: none;
    color: #9d174d;
    cursor: pointer;
    padding: 0.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
}

.ncp-close:hover {
    background: #fce7f3;
}

/* ============================================
   FORMULAIRES GÉNÉRAUX
   ============================================ */
.form-section {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 1.25rem;
    margin-bottom: 1rem;
}

.section-title-sm {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    font-weight: 700;
    color: #374151;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e2e8f0;
}

.section-title-sm svg {
    color: #6b7280;
}

.form-grid {
    display: grid;
    gap: 1rem;
}

.form-grid.cols-2 {
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
}

.form-group {
    margin-bottom: 0;
}

.form-label {
    display: block;
    font-size: 0.8125rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.375rem;
}

.text-danger {
    color: #dc2626;
}

.text-muted {
    color: #9ca3af;
    font-weight: 400;
}

.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    background: white;
    color: #111827;
    font-size: 0.9375rem;
    transition: all 0.2s;
}

.form-control:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    outline: none;
}

.form-control-xl {
    font-size: 1.25rem;
    padding: 1rem;
    font-weight: 600;
}

select.form-control {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg fill='none' stroke='%236b7280' viewBox='0 0 24 24' width='16' height='16'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    padding-right: 2.5rem;
}

/* Montant */
.amount-container {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.amount-field {
    position: relative;
}

.amount-suffix {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.875rem;
    font-weight: 600;
    color: #6b7280;
    pointer-events: none;
}

.amount-limit {
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 0.375rem;
}

.calculation-preview {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
    background: #f8fafc;
    border-radius: 10px;
    padding: 1rem;
    margin-top: 0.75rem;
}

.calc-row {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    text-align: center;
}

.calc-row span {
    font-size: 0.6875rem;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.calc-row strong {
    font-size: 0.9375rem;
    color: #111827;
    font-weight: 700;
}

.calc-row.total strong {
    color: #2563eb;
    font-size: 1rem;
}

/* Montant fixe */
.fixed-amount-box {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    border: 1px solid #86efac;
    border-radius: 12px;
    padding: 1.25rem;
}

.fab-icon {
    width: 52px;
    height: 52px;
    background: white;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #22c55e;
    box-shadow: 0 2px 8px rgba(34, 197, 94, 0.1);
}

.fab-content {
    flex: 1;
}

.fab-amount {
    font-size: 1.375rem;
    font-weight: 700;
    color: #166534;
}

.fab-detail {
    font-size: 0.8125rem;
    color: #15803d;
    margin-top: 0.25rem;
}

/* Durée */
.duration-box {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.25rem;
    background: #f8fafc;
    border-radius: 10px;
    padding: 1rem;
    text-align: center;
}

.db-main {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
}

.db-sub {
    font-size: 0.8125rem;
    color: #6b7280;
}

/* Compteur caractères */
.char-count {
    text-align: right;
    font-size: 0.75rem;
    color: #9ca3af;
    margin-top: 0.25rem;
}

/* ============================================
   RÉCAPITULATIF FRAIS
   ============================================ */
.fees-summary-card {
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    overflow: hidden;
    margin: 1.5rem 0;
}

.fsc-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 1.25rem;
    background: white;
    border-bottom: 1px solid #e2e8f0;
    font-size: 0.875rem;
    font-weight: 700;
    color: #374151;
}

.fsc-header svg {
    color: #6b7280;
}

.fsc-body {
    padding: 1rem 1.25rem;
}

.fsc-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    font-size: 0.9375rem;
}

.fsc-row span:first-child {
    color: #6b7280;
}

.fsc-row span:last-child {
    color: #111827;
    font-weight: 500;
}

.fsc-row.total {
    border-top: 1px solid #e2e8f0;
    margin-top: 0.5rem;
    padding-top: 0.75rem;
}

.fsc-row.total span:first-child {
    color: #374151;
    font-weight: 700;
}

.fsc-row.total strong {
    font-size: 1.25rem;
    color: #2563eb;
}

/* ============================================
   BOUTON & PAIEMENT
   ============================================ */
.form-actions {
    margin-top: 1.5rem;
}

.btn-submit {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 1rem 1.5rem;
    background: linear-gradient(135deg, #2563eb, #3b82f6);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
    position: relative;
    overflow: hidden;
}

.btn-submit:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(37, 99, 235, 0.45);
}

.btn-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-text,
.btn-spinner {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: opacity 0.2s;
}

.btn-spinner {
    position: absolute;
    inset: 0;
    opacity: 0;
}

.btn-submit.loading .btn-text {
    opacity: 0;
}

.btn-submit.loading .btn-spinner {
    opacity: 1;
}

.security-note {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.375rem;
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 0.75rem;
}

.security-note svg {
    color: #22c55e;
}

/* Zone paiement */
.payment-zone {
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 14px;
    padding: 2rem;
    margin: 1.5rem 0;
    text-align: center;
}

.pz-loading {
    color: #6b7280;
}

.pz-loading p {
    margin-top: 1rem;
    font-weight: 600;
    color: #374151;
}

.pz-loading small {
    display: block;
    margin-top: 0.5rem;
    font-size: 0.75rem;
}

/* Spinner */
.spinner-dual {
    width: 40px;
    height: 40px;
    position: relative;
}

.spinner-dual.small {
    width: 24px;
    height: 24px;
}

.spinner-dual::before,
.spinner-dual::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 50%;
    border: 3px solid transparent;
}

.spinner-dual::before {
    border-top-color: #2563eb;
    border-right-color: #2563eb;
    animation: spin 1s linear infinite;
}

.spinner-dual::after {
    border-bottom-color: #bfdbfe;
    border-left-color: #bfdbfe;
    animation: spin 1.5s linear infinite reverse;
}

.spinner-dual.small::before,
.spinner-dual.small::after {
    border-width: 2px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Success */
.success-check {
    margin-bottom: 1rem;
}

.checkmark {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    display: block;
    stroke-width: 2;
    stroke: #fff;
    stroke-miterlimit: 10;
    margin: 0 auto;
    box-shadow: inset 0px 0px 0px #22c55e;
    animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
}

.checkmark-circle {
    stroke-dasharray: 166;
    stroke-dashoffset: 166;
    stroke-width: 2;
    stroke-miterlimit: 10;
    stroke: #22c55e;
    fill: none;
    animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
}

.checkmark-check {
    transform-origin: 50% 50%;
    stroke-dasharray: 48;
    stroke-dashoffset: 48;
    animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
}

@keyframes stroke {
    100% { stroke-dashoffset: 0; }
}

@keyframes scale {
    0%, 100% { transform: none; }
    50% { transform: scale3d(1.1, 1.1, 1); }
}

@keyframes fill {
    100% { box-shadow: inset 0px 0px 0px 30px #22c55e; }
}

.success-message {
    color: #15803d;
    font-size: 1.125rem;
    font-weight: 700;
}

/* ============================================
   ÉTAT VIDE
   ============================================ */
.empty-state {
    text-align: center;
    padding: 3rem 1.5rem;
    color: #9ca3af;
}

.empty-icon {
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state h3 {
    color: #374151;
    font-size: 1.125rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.empty-state p {
    font-size: 0.875rem;
}

.no-companies-message {
    text-align: center;
    padding: 1.5rem;
    background: white;
    border-radius: 10px;
    color: #9d174d;
    font-size: 0.875rem;
}

/* ============================================
   RESPONSIVE
   ============================================ */
@media (min-width: 640px) {
    .stepper {
        padding: 2rem;
    }

    .step-panel {
        padding: 2rem;
    }

    .financement-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    }
}

/* Dark mode */
@media (prefers-color-scheme: dark) {
    .stepper {
        background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
        border-bottom-color: #334155;
    }

    .step-bubble {
        background: #334155;
    }

    .step-number {
        color: #94a3b8;
    }

    .step-label {
        color: #94a3b8;
    }

    .financement-card {
        background: #1e293b;
        border-color: #334155;
    }

    .financement-card.selected {
        background: linear-gradient(135deg, #1e3a8a, #1e40af);
        border-color: #3b82f6;
    }

    .fc-name {
        color: #f8fafc;
    }

    .stat-value {
        color: #f8fafc;
    }

    .section-company {
        background: linear-gradient(180deg, #4c1d3d 0%, #1e293b 100%);
        border-color: #831843;
    }

    .form-section {
        background: #1e293b;
        border-color: #334155;
    }

    .section-title-sm {
        color: #e2e8f0;
        border-color: #334155;
    }

    .form-control {
        background: #0f172a;
        border-color: #334155;
        color: #f8fafc;
    }

    .calculation-preview,
    .duration-box {
        background: #0f172a;
    }

    .fees-summary-card {
        background: linear-gradient(135deg, #1e293b, #0f172a);
        border-color: #334155;
    }

    .fsc-header {
        background: #1e293b;
        border-color: #334155;
    }
}
</style>
@endsection
