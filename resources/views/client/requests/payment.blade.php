@extends('layouts.app')

@section('title', 'Paiement — ' . $fundingRequest->request_number)
@section('header-title', 'Paiement des frais d\'inscription')

@section('content')

<div class="pay-wrap">

    @if($fundingRequest->status === 'draft' && $fundingRequest->payment_status === 'pending')

    {{-- ── Stepper ─────────────────────────────────────────────── --}}
    <div class="stepper">
        <div class="step done"><span class="dot">✓</span><span class="lbl">Demande</span></div>
        <div class="line on"></div>
        <div class="step on"><span class="dot">2</span><span class="lbl">Paiement</span></div>
        <div class="line"></div>
        <div class="step"><span class="dot">3</span><span class="lbl">Documents</span></div>
    </div>

    {{-- ── Card ───────────────────────────────────────────────── --}}
    <div class="card" id="mainCard">

        {{-- Ref + montant --}}
        <div class="amount-hero">
            <span class="ref-num">{{ $fundingRequest->request_number }}</span>
            <div class="big-amount">
                <span class="cur">FCFA</span>
                <span class="val">{{ number_format($fees['current'], 0, ',', ' ') }}</span>
            </div>
            <p class="amount-sub">Frais d'inscription à régler</p>
            <input type="hidden" id="feeAmount" value="{{ $fees['current'] }}">
        </div>

        {{-- Info frais finals --}}
        <div class="info-banner">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Des frais de dossier de <strong>{{ number_format($fees['final'], 0, ',', ' ') }} FCFA</strong> seront demandés à l'approbation
        </div>

        <div class="divider"></div>

        {{-- ── Sélecteur de méthode ──────────────────────────── --}}
        <p class="method-title">Choisissez votre méthode de paiement</p>

        <div class="method-grid">

            {{-- Wallet --}}
            @php $walletOk = ($walletBalance ?? 0) >= $fees['current']; @endphp
            <label class="method-card {{ $walletOk ? '' : 'method-disabled' }}" id="labelWallet">
                <input type="radio" name="payMethod" value="wallet"
                       id="methodWallet"
                       {{ $walletOk ? 'checked' : 'disabled' }}>
                <div class="mc-inner">
                    <div class="mc-icon wallet-icon">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <div class="mc-text">
                        <span class="mc-label">Mon Wallet</span>
                        <span class="mc-sub {{ $walletOk ? '' : 'mc-insuf' }}">
                            Solde : {{ number_format($walletBalance ?? 0, 0, ',', ' ') }} FCFA
                            @if(!$walletOk)
                                <span class="badge-insuf">Insuffisant</span>
                            @endif
                        </span>
                    </div>
                    <div class="mc-check">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
            </label>

            {{-- Kkiapay --}}
            <label class="method-card" id="labelKkiapay">
                <input type="radio" name="payMethod" value="kkiapay"
                       id="methodKkiapay"
                       {{ !$walletOk ? 'checked' : '' }}>
                <div class="mc-inner">
                    <div class="mc-icon kkiapay-icon">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="mc-text">
                        <span class="mc-label">Mobile Money / Carte</span>
                        <span class="mc-sub">MTN, Moov, Visa, Mastercard</span>
                    </div>
                    <div class="mc-check">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
            </label>

        </div>

        {{-- ── Bouton payer ──────────────────────────────────── --}}
        <button type="button" class="btn-pay" id="btnPay" onclick="initierPaiement()">
            <span class="bp-content">
                <svg width="19" height="19" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a1 1 0 11-2 0 1 1 0 012 0z"/>
                </svg>
                Payer {{ number_format($fees['current'], 0, ',', ' ') }} FCFA
            </span>
            <span class="bp-spinner" style="display:none">
                <span class="spin-ring"></span>
            </span>
        </button>

        {{-- ── Déjà payé / vérifier ─────────────────────────── --}}
        <button type="button" class="btn-recheck" onclick="openRecheckModal()">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            J'ai déjà payé — vérifier le statut
        </button>

        {{-- Annuler --}}
        <form action="{{ route('client.requests.destroy', $fundingRequest) }}" method="POST">
            @csrf @method('DELETE')
            <button type="submit" class="btn-cancel" onclick="return confirmCancel()">Annuler la demande</button>
        </form>

        {{-- Sécurité --}}
        <div class="security-note">
            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            Paiement sécurisé — SSL 256 bits
        </div>
    </div>

    {{-- ── Loading overlay ─────────────────────────────────────── --}}
    <div id="overlayProcessing" class="overlay" style="display:none">
        <div class="overlay-box">
            <div class="ol-ring"></div>
            <p id="overlayMsg">Traitement en cours…</p>
            <small>Ne fermez pas cette page</small>
        </div>
    </div>

    {{-- ── Modal recheck ────────────────────────────────────────── --}}
    <div id="modalRecheck" class="modal-backdrop" style="display:none" onclick="closeModalOutside(event)">
        <div class="modal-box" role="dialog" aria-modal="true">

            <div class="modal-head">
                <div class="modal-head-icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <div>
                    <h3>Vérifier mon paiement</h3>
                    <p class="modal-sub">Paiement effectué mais statut non mis à jour ?</p>
                </div>
                <button class="modal-close" onclick="closeModal()">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="modal-body">
                <p class="modal-desc">
                    Entrez l'identifiant de transaction fourni par Kkiapay, votre opérateur (MTN, Moov…) ou votre relevé bancaire.
                </p>

                <div class="field-group">
                    <label class="field-label" for="recheckTxId">Identifiant de transaction</label>
                    <input type="text" id="recheckTxId" class="field-input"
                           placeholder="ex : KKP-xxxxxxxxxxxxxxxx"
                           autocomplete="off"
                           oninput="clearRecheckError()">
                </div>

                <div id="recheckFeedback" class="recheck-feedback" style="display:none"></div>
            </div>

            <div class="modal-foot">
                <button class="btn-modal-cancel" onclick="closeModal()">Fermer</button>
                <button class="btn-modal-ok" id="btnRecheckOk" onclick="submitRecheck()">
                    <span id="recheckBtnLabel">Vérifier</span>
                    <span id="recheckBtnSpinner" style="display:none">
                        <span class="spin-ring sm"></span>
                    </span>
                </button>
            </div>
        </div>
    </div>

    @else

    {{-- ── État déjà payé ──────────────────────────────────────── --}}
    <div class="card card-paid">
        <div class="paid-circle">
            <svg width="38" height="38" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h2>Paiement déjà effectué</h2>
        <p>Votre demande est en cours de traitement.</p>
        <a href="{{ route('client.documents.required', $fundingRequest) }}" class="btn-pay" style="text-decoration:none">
            Ajouter les documents
        </a>
        <a href="{{ route('client.requests.show', $fundingRequest) }}" class="btn-outline">
            Voir ma demande
        </a>
    </div>

    @endif

</div>

@endsection

@section('scripts')
<script src="https://cdn.kkiapay.me/k.js"></script>
<script>
const CSRF   = document.querySelector('meta[name="csrf-token"]')?.content;
const FRQ_ID = {{ $fundingRequest->id }};
let isProcessing = false;
let kkiapayData  = null;

/* ── Kkiapay listeners ──────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    if (typeof addSuccessListener === 'function') addSuccessListener(onKkiapaySuccess);
    if (typeof addFailedListener  === 'function') addFailedListener(onKkiapayFailed);
    if (typeof addCloseListener   === 'function') addCloseListener(onKkiapayClose);
    syncMethodCards();
    document.querySelectorAll('input[name="payMethod"]').forEach(r => {
        r.addEventListener('change', syncMethodCards);
    });
});

function syncMethodCards() {
    document.querySelectorAll('.method-card').forEach(c => c.classList.remove('selected'));
    const checked = document.querySelector('input[name="payMethod"]:checked');
    if (checked) checked.closest('.method-card').classList.add('selected');
}

/* ── Dispatcher principal ───────────────────────────── */
async function initierPaiement() {
    if (isProcessing) return;
    const method = document.querySelector('input[name="payMethod"]:checked')?.value;
    if (!method) { alert('Veuillez choisir une méthode.'); return; }
    method === 'wallet' ? await payerWallet() : await initierKkiapay();
}

/* ── WALLET ─────────────────────────────────────────── */
async function payerWallet() {
    setBtnLoading(true);
    showOverlay('Débit du wallet en cours…');
    try {
        const r = await fetch('{{ route('client.payment.wallet', $fundingRequest) }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ funding_request_id: FRQ_ID }),
        });
        const d = await r.json();
        if (d.success) {
            updateOverlay('Paiement confirmé — redirection…');
            window.location.href = d.redirect_url || '{{ route('client.documents.required', $fundingRequest) }}';
        } else {
            hideOverlay(); setBtnLoading(false);
            alert('Erreur : ' + (d.message || 'Paiement wallet échoué'));
        }
    } catch (e) {
        hideOverlay(); setBtnLoading(false);
        alert('Erreur réseau. Réessayez.');
    }
}

/* ── KKIAPAY initialize ─────────────────────────────── */
async function initierKkiapay() {
    setBtnLoading(true);
    try {
        const r = await fetch('{{ route('client.payment.initialize', $fundingRequest) }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({}),
        });
        const d = await r.json();
        if (!d.success) throw new Error(d.message || 'Initialisation échouée');
        kkiapayData = d;
        const cfg = d.kkiapay_config;
        openKkiapayWidget({ amount: cfg.amount, key: cfg.key, sandbox: cfg.sandbox, data: cfg.data, theme: '#2563eb', position: 'center' });
    } catch (e) {
        setBtnLoading(false);
        alert('Erreur : ' + e.message);
    }
}

function onKkiapaySuccess(response) {
    showOverlay('Vérification du paiement…');
    if (!kkiapayData) { alert('Erreur interne. Contactez le support.'); hideOverlay(); resetBtn(); return; }
    verifyKkiapay(response.transactionId, kkiapayData.transaction?.transaction_id, FRQ_ID);
}

function onKkiapayFailed(r) {
    resetBtn();
    if (r?.transactionId) alert('Paiement annulé ou échoué. Vous pouvez réessayer.');
}

function onKkiapayClose() { if (isProcessing) resetBtn(); }

async function verifyKkiapay(kkId, intId, frqId, attempt = 1) {
    try {
        const r = await fetch('{{ route('client.payment.verify') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ transactionId: kkId, internal_transaction_id: intId, funding_request_id: frqId }),
        });
        const d = await r.json();
        if (d.success) {
            window.location.href = d.redirect_url || '{{ route('client.documents.required', $fundingRequest) }}';
        } else if (d.status === 'pending') {
            updateOverlay('Attente confirmation' + '.'.repeat((attempt % 3) + 1));
            setTimeout(() => verifyKkiapay(kkId, intId, frqId, attempt + 1), 2500);
        } else {
            throw new Error(d.message);
        }
    } catch (e) {
        setTimeout(() => verifyKkiapay(kkId, intId, frqId, attempt + 1), 3000);
    }
}

/* ── RECHECK ────────────────────────────────────────── */
function openRecheckModal() {
    document.getElementById('recheckTxId').value = '';
    clearRecheckError();
    document.getElementById('modalRecheck').style.display = 'flex';
    setTimeout(() => document.getElementById('recheckTxId').focus(), 100);
}

function closeModal() { document.getElementById('modalRecheck').style.display = 'none'; }

function closeModalOutside(e) { if (e.target.id === 'modalRecheck') closeModal(); }

function clearRecheckError() {
    document.getElementById('recheckFeedback').style.display = 'none';
}

async function submitRecheck() {
    const txId = document.getElementById('recheckTxId').value.trim();
    if (!txId) { showRecheckFeedback('error', 'Veuillez entrer un identifiant de transaction.'); return; }

    setRecheckLoading(true);
    try {
        const r = await fetch('{{ route('client.payment.recheck', $fundingRequest) }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ transactionId: txId, funding_request_id: FRQ_ID }),
        });
        const d = await r.json();

        if (d.success) {
            showRecheckFeedback('success', '✓ Paiement retrouvé ! Redirection en cours…');
            setTimeout(() => {
                closeModal();
                showOverlay('Paiement confirmé — redirection…');
                window.location.href = d.redirect_url || '{{ route('client.documents.required', $fundingRequest) }}';
            }, 900);
        } else if (d.status === 'not_found') {
            showRecheckFeedback('error', 'Aucun paiement trouvé pour cet identifiant. Vérifiez et réessayez.');
        } else if (d.status === 'pending') {
            showRecheckFeedback('warning', 'Ce paiement est en attente de confirmation par l\'opérateur. Réessayez dans quelques minutes.');
        } else {
            showRecheckFeedback('error', d.message || 'Impossible de vérifier ce paiement.');
        }
    } catch (e) {
        showRecheckFeedback('error', 'Erreur réseau. Réessayez dans quelques secondes.');
    } finally {
        setRecheckLoading(false);
    }
}

function showRecheckFeedback(type, msg) {
    const el = document.getElementById('recheckFeedback');
    el.className = 'recheck-feedback recheck-' + type;
    el.textContent = msg;
    el.style.display = 'block';
}

function setRecheckLoading(on) {
    document.getElementById('recheckBtnLabel').style.display  = on ? 'none' : 'inline';
    document.getElementById('recheckBtnSpinner').style.display = on ? 'inline-flex' : 'none';
    document.getElementById('btnRecheckOk').disabled = on;
}

/* ── UI helpers ─────────────────────────────────────── */
function setBtnLoading(on) {
    isProcessing = on;
    const btn = document.getElementById('btnPay');
    if (!btn) return;
    btn.disabled = on;
    btn.querySelector('.bp-content').style.display = on ? 'none' : 'flex';
    btn.querySelector('.bp-spinner').style.display  = on ? 'flex' : 'none';
}

function resetBtn() { setBtnLoading(false); }

function showOverlay(msg) {
    document.getElementById('overlayMsg').textContent = msg;
    document.getElementById('overlayProcessing').style.display = 'flex';
}

function updateOverlay(msg) { document.getElementById('overlayMsg').textContent = msg; }

function hideOverlay() { document.getElementById('overlayProcessing').style.display = 'none'; }

function confirmCancel() { return confirm('Annuler cette demande ?\n\nCette action est irréversible.'); }
</script>
@endsection

@section('styles')
<style>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap');

:root {
    --blue:       #2563eb;
    --blue-d:     #1d4ed8;
    --blue-glow:  rgba(37,99,235,.18);
    --green:      #059669;
    --green-glow: rgba(5,150,105,.15);
    --amber:      #d97706;
    --red:        #dc2626;
    --surface:    #ffffff;
    --bg:         #f1f5f9;
    --ink:        #0f172a;
    --muted:      #64748b;
    --border:     #e2e8f0;
    --border-2:   #cbd5e1;
    --r:          14px;
    --r-sm:       9px;
    --shadow:     0 2px 12px rgba(15,23,42,.08), 0 1px 3px rgba(15,23,42,.06);
    --shadow-md:  0 12px 40px rgba(15,23,42,.14);
    --font:       'Sora', sans-serif;
    --mono:       'JetBrains Mono', monospace;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.pay-wrap {
    font-family: var(--font);
    max-width: 460px;
    margin: 0 auto;
    padding: 1.5rem 1rem 3rem;
    color: var(--ink);
}

/* Stepper */
.stepper { display: flex; align-items: center; justify-content: center; gap: .375rem; margin-bottom: 1.75rem; }
.step    { display: flex; flex-direction: column; align-items: center; gap: .35rem; }
.dot {
    width: 34px; height: 34px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: .78rem; font-weight: 700;
    background: var(--border); color: var(--muted); transition: all .3s;
}
.step.done .dot { background: var(--green); color: #fff; }
.step.on .dot {
    background: var(--blue); color: #fff;
    box-shadow: 0 0 0 5px var(--blue-glow);
    animation: ring-pulse 2.2s infinite;
}
@keyframes ring-pulse {
    0%,100% { box-shadow: 0 0 0 5px var(--blue-glow); }
    50%      { box-shadow: 0 0 0 9px rgba(37,99,235,.07); }
}
.lbl { font-size: .68rem; font-weight: 600; color: var(--muted); }
.step.done .lbl, .step.on .lbl { color: var(--ink); }
.line { width: 36px; height: 2px; background: var(--border); border-radius: 2px; }
.line.on { background: var(--blue); }

/* Card */
.card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    box-shadow: var(--shadow);
    padding: 2rem;
    display: flex; flex-direction: column; gap: 1.25rem;
}

/* Amount hero */
.amount-hero { text-align: center; }
.ref-num {
    display: inline-block;
    font-family: var(--mono); font-size: .72rem; font-weight: 700;
    color: var(--muted); background: var(--bg);
    border: 1px solid var(--border); border-radius: 6px;
    padding: .2rem .65rem; margin-bottom: .75rem; letter-spacing: .04em;
}
.big-amount { display: flex; align-items: baseline; justify-content: center; gap: .4rem; margin-bottom: .3rem; }
.cur { font-size: 1.4rem; font-weight: 600; color: var(--muted); }
.val { font-size: 3.2rem; font-weight: 800; color: var(--blue); line-height: 1; letter-spacing: -.03em; }
.amount-sub { font-size: .8rem; color: var(--muted); }

/* Info banner */
.info-banner {
    display: flex; align-items: flex-start; gap: .6rem;
    font-size: .8rem; color: #1e3a8a;
    background: #eff6ff; border: 1px solid #bfdbfe;
    border-radius: var(--r-sm); padding: .75rem; line-height: 1.5;
}
.info-banner svg { flex-shrink: 0; margin-top: 1px; }

/* Divider */
.divider { height: 1px; background: var(--border); }

/* Method picker */
.method-title {
    font-size: .7rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .09em; color: var(--muted);
}
.method-grid { display: flex; flex-direction: column; gap: .55rem; }

.method-card {
    display: block;
    border: 1.5px solid var(--border);
    border-radius: var(--r-sm);
    cursor: pointer;
    transition: border-color .18s, box-shadow .18s, background .18s;
}
.method-card input[type="radio"] { position: absolute; opacity: 0; pointer-events: none; }
.method-card:hover:not(.method-disabled) { border-color: var(--border-2); }
.method-card.selected  { border-color: var(--blue); box-shadow: 0 0 0 3px var(--blue-glow); background: #f8faff; }
.method-card.method-disabled { opacity: .5; cursor: not-allowed; }

.mc-inner { display: flex; align-items: center; gap: .875rem; padding: .875rem 1rem; }

.mc-icon {
    width: 40px; height: 40px;
    border-radius: var(--r-sm);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.wallet-icon  { background: #eff6ff; color: var(--blue); }
.kkiapay-icon { background: #f0fdf4; color: var(--green); }

.mc-text { flex: 1; display: flex; flex-direction: column; gap: .15rem; }
.mc-label { font-size: .875rem; font-weight: 600; color: var(--ink); }
.mc-sub   { font-size: .75rem; color: var(--muted); display: flex; align-items: center; gap: .35rem; flex-wrap: wrap; }
.mc-insuf { color: var(--amber); }

.badge-insuf {
    font-size: .62rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .05em;
    background: #fef3c7; color: var(--amber);
    border: 1px solid #fde68a;
    border-radius: 4px; padding: .1rem .35rem;
}

.mc-check {
    width: 20px; height: 20px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    background: var(--border); color: transparent;
    flex-shrink: 0; transition: all .2s;
}
.method-card.selected .mc-check { background: var(--blue); color: white; }

/* Btn pay */
.btn-pay {
    width: 100%;
    display: flex; align-items: center; justify-content: center;
    padding: 1rem 1.5rem;
    background: linear-gradient(135deg, var(--blue), var(--blue-d));
    color: white; border: none;
    border-radius: var(--r-sm);
    font-family: var(--font); font-size: .95rem; font-weight: 700;
    cursor: pointer; transition: all .2s;
    box-shadow: 0 4px 16px var(--blue-glow);
    position: relative; overflow: hidden;
    text-decoration: none; gap: .5rem;
}
.btn-pay::before {
    content: ''; position: absolute; top: 0; left: -100%;
    width: 60%; height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,.14), transparent);
    transition: left .5s;
}
.btn-pay:hover:not(:disabled)::before { left: 130%; }
.btn-pay:hover:not(:disabled)  { transform: translateY(-1px); box-shadow: 0 6px 24px rgba(37,99,235,.35); }
.btn-pay:disabled  { opacity: .65; cursor: not-allowed; }
.bp-content { display: flex; align-items: center; gap: .5rem; }
.bp-spinner { display: flex; align-items: center; justify-content: center; position: absolute; inset: 0; }

/* Btn recheck */
.btn-recheck {
    width: 100%;
    display: flex; align-items: center; justify-content: center; gap: .5rem;
    padding: .65rem 1rem;
    background: transparent; color: var(--muted);
    border: 1.5px dashed var(--border-2);
    border-radius: var(--r-sm);
    font-family: var(--font); font-size: .78rem; font-weight: 600;
    cursor: pointer; transition: all .2s;
}
.btn-recheck:hover { color: var(--blue); border-color: var(--blue); background: #f8faff; }

/* Btn cancel */
.btn-cancel {
    width: 100%; padding: .65rem;
    background: transparent; color: var(--muted);
    border: 1.5px solid var(--border);
    border-radius: var(--r-sm);
    font-family: var(--font); font-size: .78rem; font-weight: 500;
    cursor: pointer; transition: all .2s;
}
.btn-cancel:hover { background: #fef2f2; color: var(--red); border-color: var(--red); }

/* Security note */
.security-note {
    display: flex; align-items: center; justify-content: center; gap: .4rem;
    font-size: .7rem; color: var(--muted);
    padding-top: .5rem; border-top: 1px solid var(--border);
}
.security-note svg { color: var(--green); }

/* Spinners */
.spin-ring {
    display: inline-block;
    width: 22px; height: 22px;
    border: 3px solid rgba(255,255,255,.35);
    border-top-color: white; border-radius: 50%;
    animation: spin .75s linear infinite;
}
.spin-ring.sm { width: 14px; height: 14px; border-width: 2px; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Overlay */
.overlay {
    position: fixed; inset: 0;
    background: rgba(255,255,255,.96);
    backdrop-filter: blur(6px);
    display: flex; align-items: center; justify-content: center;
    z-index: 9000; animation: fadein .25s ease;
}
@keyframes fadein { from { opacity: 0; } to { opacity: 1; } }
.overlay-box { text-align: center; padding: 2rem; }
.ol-ring {
    width: 52px; height: 52px;
    border: 4px solid var(--border); border-top-color: var(--blue);
    border-radius: 50%; animation: spin 1s linear infinite;
    margin: 0 auto 1.25rem;
}
.overlay-box p     { font-size: 1rem; font-weight: 700; color: var(--ink); margin-bottom: .35rem; }
.overlay-box small { font-size: .78rem; color: var(--muted); }

/* Modal */
.modal-backdrop {
    position: fixed; inset: 0;
    background: rgba(15,23,42,.5);
    backdrop-filter: blur(4px);
    display: flex; align-items: center; justify-content: center;
    z-index: 9100; padding: 1rem;
    animation: fadein .2s ease;
}
.modal-box {
    background: var(--surface);
    border-radius: var(--r); box-shadow: var(--shadow-md);
    width: 100%; max-width: 410px;
    overflow: hidden; animation: slideup .22s ease;
}
@keyframes slideup { from { transform: translateY(18px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

.modal-head {
    display: flex; align-items: center; gap: .75rem;
    padding: 1.25rem 1.5rem 1rem;
    border-bottom: 1px solid var(--border);
}
.modal-head-icon {
    width: 36px; height: 36px;
    background: #eff6ff; color: var(--blue);
    border-radius: var(--r-sm);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.modal-head h3    { font-size: .95rem; font-weight: 700; line-height: 1.2; }
.modal-sub        { font-size: .72rem; color: var(--muted); margin-top: .15rem; }
.modal-close {
    margin-left: auto;
    background: none; border: none; cursor: pointer;
    color: var(--muted); width: 28px; height: 28px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 6px; transition: background .2s;
}
.modal-close:hover { background: var(--bg); color: var(--ink); }

.modal-body { padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: .75rem; }
.modal-desc { font-size: .8rem; color: var(--muted); line-height: 1.55; }

.field-group { display: flex; flex-direction: column; gap: .35rem; }
.field-label { font-size: .73rem; font-weight: 700; color: var(--ink); }
.field-input {
    padding: .65rem .875rem;
    border: 1.5px solid var(--border);
    border-radius: var(--r-sm);
    font-family: var(--mono); font-size: .82rem; color: var(--ink);
    outline: none; transition: border-color .2s, box-shadow .2s;
    width: 100%;
}
.field-input:focus { border-color: var(--blue); box-shadow: 0 0 0 3px var(--blue-glow); }
.field-input::placeholder { color: #94a3b8; }

/* Recheck feedback states */
.recheck-feedback {
    font-size: .78rem; border-radius: var(--r-sm); padding: .65rem .75rem; line-height: 1.45;
}
.recheck-error   { color: var(--red);   background: #fef2f2; border: 1px solid #fecaca; }
.recheck-warning { color: var(--amber); background: #fffbeb; border: 1px solid #fde68a; }
.recheck-success { color: var(--green); background: #f0fdf4; border: 1px solid #86efac; }

.modal-foot {
    display: flex; gap: .75rem; justify-content: flex-end;
    padding: 1rem 1.5rem; border-top: 1px solid var(--border);
}
.btn-modal-cancel {
    padding: .6rem 1rem; background: transparent; color: var(--muted);
    border: 1.5px solid var(--border); border-radius: var(--r-sm);
    font-family: var(--font); font-size: .8rem; font-weight: 500;
    cursor: pointer; transition: all .2s;
}
.btn-modal-cancel:hover { background: var(--bg); }
.btn-modal-ok {
    padding: .6rem 1.25rem; background: var(--blue); color: white; border: none;
    border-radius: var(--r-sm); font-family: var(--font); font-size: .8rem; font-weight: 700;
    cursor: pointer; transition: all .2s;
    display: flex; align-items: center; gap: .4rem;
}
.btn-modal-ok:hover:not(:disabled) { background: var(--blue-d); }
.btn-modal-ok:disabled { opacity: .65; cursor: not-allowed; }

/* Card paid */
.card-paid { align-items: center; text-align: center; padding: 3rem 2rem; gap: .75rem; }
.paid-circle {
    width: 80px; height: 80px; border-radius: 50%;
    background: linear-gradient(135deg, #d1fae5, #a7f3d0);
    display: flex; align-items: center; justify-content: center;
    color: var(--green); margin-bottom: .25rem;
    box-shadow: 0 8px 24px var(--green-glow);
}
.card-paid h2 { font-size: 1.35rem; font-weight: 800; }
.card-paid p  { font-size: .85rem; color: var(--muted); }

.btn-outline {
    display: inline-flex; align-items: center; justify-content: center;
    padding: .75rem 1.25rem;
    background: transparent; color: var(--muted);
    border: 1.5px solid var(--border); border-radius: var(--r-sm);
    font-family: var(--font); font-size: .85rem; font-weight: 600;
    text-decoration: none; transition: all .2s;
}
.btn-outline:hover { background: var(--bg); color: var(--ink); }

@media (max-width: 480px) {
    .pay-wrap { padding: 1rem .75rem 2.5rem; }
    .card     { padding: 1.5rem; }
    .val      { font-size: 2.6rem; }
    .dot      { width: 30px; height: 30px; font-size: .7rem; }
    .line     { width: 28px; }
}
</style>
@endsection
