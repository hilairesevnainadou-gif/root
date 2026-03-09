{{--
    Partial partagé entre create.blade.php et edit.blade.php
    Variables attendues :
      - $typeFinancement (null pour create, model pour edit)
      - $action (URL du formulaire)
      - $method ('POST' pour create, 'PATCH' pour edit)
      - $submitLabel (libellé du bouton)
--}}

@php
    $tf = $typeFinancement ?? null;
    $old = fn(string $key, $default = '') => old($key, $tf?->$key ?? $default);
@endphp

<style>
    .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .form-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; }
    .col-span-2  { grid-column: span 2; }
    .col-span-3  { grid-column: span 3; }

    .form-group { margin-bottom: 0; }
    .form-label { display: block; font-size: .8rem; font-weight: 600; color: var(--color-text); margin-bottom: .35rem; }
    .form-label span { color: #ef4444; margin-left: .1rem; }
    .form-control { width: 100%; padding: .55rem .875rem; border: 1px solid var(--color-border); border-radius: 8px; font-size: .875rem; font-family: inherit; outline: none; transition: border-color .15s; background: #fff; }
    .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.1); }
    .form-control.is-invalid { border-color: #ef4444; }
    .form-hint  { font-size: .73rem; color: var(--color-text-muted); margin-top: .25rem; }
    .form-error { font-size: .73rem; color: #ef4444; margin-top: .25rem; }

    .section-title {
        font-size: .72rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .07em; color: var(--color-text-muted);
        border-bottom: 1px solid var(--color-border);
        padding-bottom: .5rem; margin-bottom: 1rem; margin-top: 1.5rem;
    }
    .section-title:first-child { margin-top: 0; }

    .toggle-row { display: flex; align-items: center; justify-content: space-between; padding: .75rem 1rem; background: #f8fafc; border-radius: 8px; border: 1px solid var(--color-border); }
    .toggle-label { font-size: .875rem; font-weight: 600; color: var(--color-text); }
    .toggle-hint  { font-size: .75rem; color: var(--color-text-muted); margin-top: .1rem; }
    /* Toggle switch */
    .switch { position: relative; width: 44px; height: 24px; flex-shrink: 0; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .switch-slider { position: absolute; inset: 0; background: #cbd5e1; border-radius: 9999px; cursor: pointer; transition: background .2s; }
    .switch-slider::before { content: ''; position: absolute; width: 18px; height: 18px; left: 3px; bottom: 3px; background: #fff; border-radius: 50%; transition: transform .2s; box-shadow: 0 1px 3px rgba(0,0,0,.2); }
    .switch input:checked + .switch-slider { background: #3b82f6; }
    .switch input:checked + .switch-slider::before { transform: translateX(20px); }

    .btn { display: inline-flex; align-items: center; gap: .375rem; padding: .55rem 1.25rem; border-radius: 8px; font-size: .875rem; font-weight: 600; text-decoration: none; border: none; cursor: pointer; transition: all .15s; font-family: inherit; }
    .btn-primary  { background: #3b82f6; color: #fff; }
    .btn-primary:hover  { background: #2563eb; color: #fff; }
    .btn-secondary { background: #f1f5f9; color: #475569; border: 1px solid var(--color-border); }
    .btn-secondary:hover { background: #e2e8f0; }

    .variable-amount-row { display: none; }
    .variable-amount-row.visible { display: contents; }
</style>

<form method="POST" action="{{ $action }}">
    @csrf
    @if($method === 'PATCH') @method('PATCH') @endif

    {{-- ── Identité ── --}}
    <div class="section-title">Identité</div>
    <div class="form-grid-2" style="gap:1rem; margin-bottom:1rem;">

        <div class="form-group">
            <label class="form-label">Nom <span>*</span></label>
            <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                value="{{ $old('name') }}" required placeholder="ex : Prêt Agricole">
            @error('name') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Code unique <span>*</span></label>
            <input type="text" name="code" class="form-control {{ $errors->has('code') ? 'is-invalid' : '' }}"
                value="{{ $old('code') }}" required placeholder="ex : PRET-AGR-001"
                style="text-transform:uppercase;" oninput="this.value=this.value.toUpperCase()">
            @error('code') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group col-span-2">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="2"
                placeholder="Description courte du type de financement…">{{ $old('description') }}</textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Cible utilisateurs <span>*</span></label>
            <select name="typeusers" class="form-control" required>
                <option value="">Choisir…</option>
                <option value="particulier" {{ $old('typeusers') === 'particulier' ? 'selected' : '' }}>Particulier</option>
                <option value="entreprise"  {{ $old('typeusers') === 'entreprise'  ? 'selected' : '' }}>Entreprise</option>
                <option value="admin"       {{ $old('typeusers') === 'admin'       ? 'selected' : '' }}>Admin</option>
            </select>
            @error('typeusers') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Durée (mois) <span>*</span></label>
            <input type="number" name="duration_months" class="form-control"
                value="{{ $old('duration_months', 1) }}" min="1" max="360" required>
            @error('duration_months') <div class="form-error">{{ $message }}</div> @enderror
        </div>

    </div>

    {{-- ── Montants ── --}}
    <div class="section-title">Montants & Frais</div>
    <div style="margin-bottom:1rem;">
        <div class="toggle-row" style="margin-bottom:1rem;">
            <div>
                <div class="toggle-label">Montant variable</div>
                <div class="toggle-hint">Le client choisit librement le montant demandé</div>
            </div>
            <label class="switch">
                <input type="hidden" name="is_variable_amount" value="0">
                <input type="checkbox" name="is_variable_amount" value="1" id="isVariable"
                    {{ $old('is_variable_amount', '0') == '1' ? 'checked' : '' }}
                    onchange="toggleVariableAmount(this)">
                <span class="switch-slider"></span>
            </label>
        </div>
    </div>

    <div class="form-grid-3" style="margin-bottom:1rem;" id="amount-fields">

        <div class="form-group" id="fixed-amount-group">
            <label class="form-label">Montant fixe (FCFA)</label>
            <input type="number" name="amount" class="form-control"
                value="{{ $old('amount') }}" min="0" step="1000" placeholder="ex : 500000">
            <div class="form-hint">Laisser vide si variable</div>
            @error('amount') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group variable-amount-row {{ $old('is_variable_amount', '0') == '1' ? 'visible' : '' }}" id="max-daily-group" style="display:none;">
            <label class="form-label">Montant max/jour (FCFA)</label>
            <input type="number" name="max_daily_amount" class="form-control"
                value="{{ $old('max_daily_amount') }}" min="0" step="1000">
            @error('max_daily_amount') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group variable-amount-row {{ $old('is_variable_amount', '0') == '1' ? 'visible' : '' }}" id="daily-gain-group" style="display:none;">
            <label class="form-label">Gain journalier (FCFA)</label>
            <input type="number" name="daily_gain" class="form-control"
                value="{{ $old('daily_gain') }}" min="0">
            @error('daily_gain') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Frais d'inscription <span>*</span></label>
            <input type="number" name="registration_fee" class="form-control {{ $errors->has('registration_fee') ? 'is-invalid' : '' }}"
                value="{{ $old('registration_fee', 0) }}" min="0" step="100" required>
            <div class="form-hint">Frais payés lors de la soumission</div>
            @error('registration_fee') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Frais final <span>*</span></label>
            <input type="number" name="registration_final_fee" class="form-control {{ $errors->has('registration_final_fee') ? 'is-invalid' : '' }}"
                value="{{ $old('registration_final_fee', 0) }}" min="0" step="100" required>
            <div class="form-hint">Frais à la validation finale</div>
            @error('registration_final_fee') <div class="form-error">{{ $message }}</div> @enderror
        </div>

    </div>

    {{-- ── Activation ── --}}
    <div class="section-title">Activation</div>
    <div class="toggle-row" style="margin-bottom:1.5rem;">
        <div>
            <div class="toggle-label">Actif</div>
            <div class="toggle-hint">Les clients peuvent soumettre une demande de ce type</div>
        </div>
        <label class="switch">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1"
                {{ $old('is_active', '1') == '1' ? 'checked' : '' }}>
            <span class="switch-slider"></span>
        </label>
    </div>

    {{-- ── Boutons ── --}}
    <div style="display:flex; gap:.75rem;">
        <button type="submit" class="btn btn-primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="15" height="15"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ $submitLabel }}
        </button>
        <a href="{{ route('admin.typefinancements.index') }}" class="btn btn-secondary">Annuler</a>
    </div>

</form>

<script>
    function toggleVariableAmount(cb) {
        const variableRows = document.querySelectorAll('.variable-amount-row');
        const fixedGroup   = document.getElementById('fixed-amount-group');
        if (cb.checked) {
            variableRows.forEach(el => { el.style.display = ''; el.classList.add('visible'); });
            fixedGroup.style.opacity = '.4';
        } else {
            variableRows.forEach(el => { el.style.display = 'none'; el.classList.remove('visible'); });
            fixedGroup.style.opacity = '1';
        }
    }
    // Init au chargement
    const cbInit = document.getElementById('isVariable');
    if (cbInit) toggleVariableAmount(cbInit);
</script>
