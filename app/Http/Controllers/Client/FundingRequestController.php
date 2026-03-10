<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFundingRequestRequest;
use App\Http\Requests\UpdateFundingRequestRequest;
use App\Models\Company;
use App\Models\DocumentUser;
use App\Models\FundingRequest;
use App\Models\Transaction;
use App\Models\TypeFinancement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FundingRequestController extends Controller
{
    /**
     * Liste des demandes de financement
     */
    public function index(Request $request): View
    {
        $userId = auth()->id();

        $query = FundingRequest::with(['typeFinancement', 'transactions'])
            ->where('user_id', $userId);

        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtre par statut de paiement
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $requests = $query->orderByDesc('created_at')->paginate(10);

        // Statistiques pour les cartes récapitulatives
        $stats = [
            'all' => FundingRequest::where('user_id', $userId)->count(),
            'draft' => FundingRequest::where('user_id', $userId)->where('status', 'draft')->count(),
            'pending_payment' => FundingRequest::where('user_id', $userId)->where('payment_status', 'pending')->where('status', 'draft')->count(),
            'submitted' => FundingRequest::where('user_id', $userId)->where('status', 'submitted')->count(),
            'under_review' => FundingRequest::where('user_id', $userId)->whereIn('status', ['under_review', 'pending_committee'])->count(),
            'approved' => FundingRequest::where('user_id', $userId)->where('status', 'approved')->count(),
            'funded' => FundingRequest::where('user_id', $userId)->where('status', 'funded')->count(),
            'rejected' => FundingRequest::where('user_id', $userId)->where('status', 'rejected')->count(),
        ];

        return view('client.requests.index', compact('requests', 'stats'));
    }

    /**
     * Affiche la page de paiement pour une demande existante
     */
    public function payment(FundingRequest $fundingRequest): View|RedirectResponse
    {
        // Vérification propriétaire
        if ($fundingRequest->user_id !== auth()->id()) {
            abort(403, 'Accès non autorisé.');
        }

        // Vérifier que la demande est en brouillon avec paiement en attente
        if ($fundingRequest->status !== 'draft' || $fundingRequest->payment_status !== 'pending') {
            return redirect()
                ->route('client.requests.show', $fundingRequest)
                ->with('info', 'Cette demande ne nécessite pas de paiement.');
        }

        $fundingRequest->load('typeFinancement');

        // UNIQUEMENT les frais d'inscription initiaux à payer maintenant
        $fees = [
            'registration' => $fundingRequest->typeFinancement->registration_fee,
            'final'        => $fundingRequest->typeFinancement->registration_final_fee,
            'current'      => $fundingRequest->typeFinancement->registration_fee,
        ];

        // Solde wallet de l'utilisateur (pour proposer le paiement direct)
        $wallet        = \App\Models\Wallet::where('user_id', auth()->id())->where('status', 'active')->first();
        $walletBalance = (float) ($wallet?->balance ?? 0);

        return view('client.requests.payment', compact('fundingRequest', 'fees', 'walletBalance'));
    }

    /**
     * Annule une demande en brouillon (suppression)
     */
    public function destroy(FundingRequest $fundingRequest): RedirectResponse
    {
        // Vérifier que l'utilisateur est propriétaire
        if ($fundingRequest->user_id !== auth()->id()) {
            abort(403);
        }

        // Vérifier que la demande est bien en draft
        if ($fundingRequest->status !== 'draft') {
            return back()->with('error', 'Seules les demandes en brouillon peuvent être annulées.');
        }

        // Supprimer la demande (les documents seront supprimés en cascade)
        $fundingRequest->delete();

        return redirect()
            ->route('client.requests.index')
            ->with('success', 'Demande annulée avec succès.');
    }

    /**
     * Formulaire de création
     */
    public function create(Request $request): View
    {
        $preselectedType = null;
        $isPreselected = false;

        if ($request->filled('typefinancement_id')) {
            $preselectedType = TypeFinancement::where('id', $request->typefinancement_id)
                ->where('is_active', true)
                ->first();

            if ($preselectedType) {
                $isPreselected = true;
            }
        }

        // Récupérer tous les types de financement actifs
        $availableTypes = TypeFinancement::where('is_active', true)
            ->orderBy('typeusers')
            ->orderBy('name')
            ->get();

        // Récupérer les entreprises de l'utilisateur
        $userCompanies = auth()->user()->companies()
            ->select('id', 'company_name', 'company_type', 'sector', 'job_title', 'employees_count')
            ->get();

        return view('client.requests.create', compact(
            'availableTypes',
            'preselectedType',
            'isPreselected',
            'userCompanies'
        ));
    }

    /**
     * Store - Création AJAX pour le paiement
     * Crée aussi les DocumentUser vides pour chaque document requis
     */
       /**
     * Store - Création AJAX pour le paiement
     * Permet plusieurs demandes du même type
     * Réutilise les documents existants si disponibles
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'typefinancement_id' => 'required|exists:typefinanciements,id',
            'title' => 'required|string|max:100',
            'amount_requested' => 'required|numeric|min:1000',
            'duration' => 'required|integer|min:1',
            'description' => 'nullable|string|max:500',
            'financement_type' => 'required|in:particulier,entreprise',
            'company_id' => 'nullable|exists:companies,id',
            'new_company.name' => 'required_if:company_id,null|nullable|string|max:255',
            'new_company.company_type' => 'required_with:new_company.name|nullable|string|max:50',
            'new_company.sector' => 'required_with:new_company.name|nullable|string|max:100',
            'new_company.job_title' => 'required_with:new_company.name|nullable|string|max:100',
            'new_company.employees_count' => 'nullable|string|max:20',
            'new_company.annual_turnover' => 'nullable|numeric|min:0',
        ]);

        $user = auth()->user();
        $typeFinancement = TypeFinancement::findOrFail($validated['typefinancement_id']);

        // Vérifier cohérence type financement
        if ($typeFinancement->typeusers !== $validated['financement_type']) {
            return response()->json([
                'success' => false,
                'message' => 'Type de financement invalide.',
            ], 400);
        }

        // 🔥 SUPPRIMÉ : Vérification des demandes en brouillon existantes
        // L'utilisateur peut créer autant de demandes qu'il veut du même type

        // Gestion de l'entreprise
        $companyId = null;
        $isNewCompany = false;

        if ($validated['financement_type'] === 'entreprise') {
            if (!empty($validated['company_id'])) {
                $company = Company::where('id', $validated['company_id'])
                    ->where('user_id', $user->id)
                    ->first();

                if (!$company) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Entreprise non trouvée ou non autorisée.',
                    ], 403);
                }
                $companyId = $company->id;
            } elseif (!empty($validated['new_company']['name'])) {
                try {
                    $newCompany = Company::create([
                        'user_id' => $user->id,
                        'company_name' => $validated['new_company']['name'],
                        'company_type' => $validated['new_company']['company_type'],
                        'sector' => $validated['new_company']['sector'],
                        'job_title' => $validated['new_company']['job_title'],
                        'employees_count' => $validated['new_company']['employees_count'] ?? null,
                        'annual_turnover' => $validated['new_company']['annual_turnover'] ?? null,
                    ]);
                    $companyId = $newCompany->id;
                    $isNewCompany = true;
                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erreur création entreprise: ' . $e->getMessage(),
                    ], 500);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Veuillez sélectionner ou créer une entreprise.',
                ], 422);
            }
        }

        // Gestion description
        $description = isset($validated['description']) && $validated['description'] !== ''
            ? $validated['description']
            : null;

        // Créer la demande
        $fundingRequest = FundingRequest::create([
            'user_id' => $user->id,
            'typefinancement_id' => $validated['typefinancement_id'],
            'company_id' => $companyId,
            'request_number' => $this->generateRequestNumber(),
            'title' => $validated['title'],
            'amount_requested' => $validated['amount_requested'],
            'duration' => $validated['duration'],
            'description' => $description,
            'status' => 'draft',
            'payment_status' => 'pending',
        ]);

        // 🔥 CRÉER LES DOCUMENTS : réutilise existants ou crée vides
        $this->createOrAttachDocuments($fundingRequest, $typeFinancement, $user->id, $companyId);

        return response()->json([
            'success' => true,
            'funding_request_id' => $fundingRequest->id,
            'request_number' => $fundingRequest->request_number,
            'company_id' => $companyId,
            'is_new_company' => $isNewCompany,
            'message' => 'Demande créée. Procédez au paiement.',
        ]);
    }

    /**
     * Crée ou rattache les documents pour la demande
     * Réutilise les documents existants de l'utilisateur si disponibles
     */
    private function createOrAttachDocuments(
        FundingRequest $fundingRequest,
        TypeFinancement $typeFinancement,
        int $userId,
        ?int $companyId
    ): void {

        // Récupérer les types de documents requis
        $requiredTypeDocs = $typeFinancement->requiredTypeDocs()->get();

        // Récupérer les documents existants de l'utilisateur pour ce type/entreprise
        // Qui ne sont pas déjà rattachés à une autre demande active
        $existingDocs = DocumentUser::where('user_id', $userId)
            ->where('company_id', $companyId)
            ->whereNotNull('file_path') // Documents déjà uploadés
            ->where('status', 'verified') // Uniquement documents vérifiés
            ->whereDoesntHave('fundingRequest', function($query) {
                $query->whereIn('status', ['draft', 'submitted', 'under_review', 'pending_committee']);
            })
            ->with('fundingRequest')
            ->get()
            ->keyBy('typedoc_id');

        foreach ($requiredTypeDocs as $typeDoc) {

            // 🔥 Vérifier si un document existant peut être réutilisé
            if (isset($existingDocs[$typeDoc->id])) {
                $existingDoc = $existingDocs[$typeDoc->id];

                // Vérifier que le fichier existe physiquement
                if (Storage::disk('public')->exists($existingDoc->file_path)) {

                    // Créer une copie/lien pour cette nouvelle demande
                    DocumentUser::create([
                        'user_id' => $userId,
                        'company_id' => $companyId,
                        'funding_request_id' => $fundingRequest->id,
                        'typedoc_id' => $typeDoc->id,
                        'file_path' => $existingDoc->file_path, // Même fichier
                        'file_name' => $existingDoc->file_name,
                        'file_type' => $existingDoc->file_type,
                        'file_size' => $existingDoc->file_size,
                        'status' => 'verified', // Déjà vérifié

                    ]);

                    continue; // Passer au suivant
                }
            }

            // 🔥 Sinon, créer un document vide
            DocumentUser::create([
                'user_id' => $userId,
                'company_id' => $companyId,
                'funding_request_id' => $fundingRequest->id,
                'typedoc_id' => $typeDoc->id,
                'file_path' => null,
                'file_name' => null,
                'file_type' => null,
                'file_size' => 0,
                'status' => 'pending',

            ]);
        }
    }

    /**
     * Affiche une demande spécifique
     */
    // public function show(FundingRequest $fundingRequest): View|RedirectResponse
    // {
    //     // Vérifier que l'utilisateur est propriétaire
    //     if ($fundingRequest->user_id !== auth()->id()) {
    //         return redirect()
    //             ->route('client.requests.index')
    //             ->with('error', 'Vous n\'êtes pas autorisé à voir cette demande.');
    //     }

    //     $fundingRequest->load(['typeFinancement', 'documentUsers.typeDoc']);

    //     // Tous les documents sont déjà créés, juste les récupérer
    //     $documents = $fundingRequest->documentUsers;

    //     $fees = [
    //         'registration' => $fundingRequest->typeFinancement->registration_fee,
    //         'final' => $fundingRequest->typeFinancement->registration_final_fee,
    //         'total_fees' => $fundingRequest->typeFinancement->registration_fee + $fundingRequest->typeFinancement->registration_final_fee,
    //         'net_amount' => $fundingRequest->amount_requested - ($fundingRequest->typeFinancement->registration_fee + $fundingRequest->typeFinancement->registration_final_fee),
    //     ];

    //     $timeline = $this->buildTimeline($fundingRequest);

    //     return view('client.requests.show', compact(
    //         'fundingRequest',
    //         'documents',
    //         'fees',
    //         'timeline'
    //     ));
    // }
/**
 * Affiche une demande spécifique
 */
public function show(FundingRequest $fundingRequest): View|RedirectResponse
{
    // Vérifier que l'utilisateur est propriétaire
    if ($fundingRequest->user_id !== auth()->id()) {
        return redirect()
            ->route('client.requests.index')
            ->with('error', 'Vous n\'êtes pas autorisé à voir cette demande.');
    }

    // Charger les relations
    $fundingRequest->load(['typeFinancement', 'documentUsers.typeDoc']);

    // 🔥 DÉFINIR $documents (c'est ce qui manquait)
    $documents = $fundingRequest->documentUsers;

    // Séparer les documents
    $providedDocs = $documents->filter(fn($doc) => !empty($doc->file_path));
    $missingDocs = $documents->filter(fn($doc) => empty($doc->file_path));

    // Si aucun document mais des docs requis existent
    if ($documents->isEmpty() && $fundingRequest->typeFinancement) {
        $missingDocs = $fundingRequest->typeFinancement->requiredTypeDocs;
    }

    $regFee   = $fundingRequest->typeFinancement->registration_fee          ?? 0;
    $finalFee = $fundingRequest->typeFinancement->registration_final_fee     ?? 0;
    // Montant de référence : amount_approved si disponible, sinon amount_requested
    $refAmount = $fundingRequest->amount_approved ?? $fundingRequest->amount_requested ?? 0;

    $fees = [
        'registration'      => $regFee,
        'final'             => $finalFee,
        'total_fees'        => $regFee + $finalFee,
        // Montant réel que le client recevra sur son portefeuille
        'net_amount'        => $refAmount - $finalFee,
        // true = demande approuvée ET frais finals > 0 ET pas encore payés → affiche bouton paiement
        'final_fee_pending' => $fundingRequest->status === 'approved'
                               && $finalFee > 0
                               && ! (bool) ($fundingRequest->final_fee_paid ?? false),
    ];

    $timeline = $this->buildTimeline($fundingRequest);

    // Toutes les variables sont définies
    return view('client.requests.show', compact(
        'fundingRequest',
        'documents',
        'providedDocs',
        'missingDocs',
        'fees',
        'timeline'
    ));
}
    /**
     * Formulaire d'édition
     */
    public function edit(FundingRequest $fundingRequest): View
    {
        $this->authorize('update', $fundingRequest);

        if ($fundingRequest->status !== 'draft') {
            abort(403, 'Seules les demandes en brouillon peuvent être modifiées.');
        }

        return view('client.requests.edit', compact('fundingRequest'));
    }

    /**
     * Mise à jour d'une demande
     */
    public function update(UpdateFundingRequestRequest $request, FundingRequest $fundingRequest): RedirectResponse
    {
        $this->authorize('update', $fundingRequest);

        $fundingRequest->update($request->validated());

        return redirect()
            ->route('client.requests.show', $fundingRequest)
            ->with('success', 'Demande mise à jour.');
    }

    /**
     * Suivi d'une demande par numéro
     */
    public function track(string $requestNumber): View
    {
        $request = FundingRequest::with(['typeFinancement', 'documentUsers.typeDoc'])
            ->where('request_number', $requestNumber)
            ->firstOrFail();

        $timeline = $this->buildTimeline($request);

        return view('client.requests.track', compact('request', 'timeline'));
    }
 /**
     * Affiche la page de paiement (votre première vue)
     */
    public function showPayment(FundingRequest $fundingRequest)
    {
        if ($fundingRequest->user_id !== auth()->id()) {
            abort(403);
        }

        // Si déjà payé, rediriger vers la confirmation
        if ($fundingRequest->isPaid()) {
            return redirect()->route('client.requests.payment.success', $fundingRequest);
        }

        $fees = [
            'current' => $fundingRequest->typeFinancement->registration_fee ?? 0,
            'final' => $fundingRequest->typeFinancement->final_fee ?? 0,
        ];

        return view('client.requests.payment', compact('fundingRequest', 'fees'));
    }


    /**
     * Page de paiement des frais de dossier finals (après approbation)
     * Distincte de payment() qui gère les frais d'inscription initiaux
     */
    public function paymentFinal(FundingRequest $fundingRequest): View|RedirectResponse
    {
        if ($fundingRequest->user_id !== auth()->id()) {
            abort(403, 'Accès non autorisé.');
        }

        $fundingRequest->load('typeFinancement');

        // La demande doit être approuvée
        if ($fundingRequest->status !== 'approved') {
            return redirect()
                ->route('client.requests.show', $fundingRequest)
                ->with('info', 'Cette demande ne nécessite pas de paiement de frais de dossier.');
        }

        $finalFee = $fundingRequest->typeFinancement->registration_final_fee ?? 0;

        // Pas de frais finals configurés
        if ($finalFee <= 0) {
            return redirect()
                ->route('client.requests.show', $fundingRequest)
                ->with('info', 'Aucun frais de dossier n\'est requis pour ce financement.');
        }

        // Frais déjà payés
        if ($fundingRequest->final_fee_paid ?? false) {
            return redirect()
                ->route('client.requests.show', $fundingRequest)
                ->with('info', 'Les frais de dossier ont déjà été réglés.');
        }

        $amountApproved = $fundingRequest->amount_approved ?? $fundingRequest->amount_requested;

        $fees = [
            'final'      => $finalFee,
            'current'    => $finalFee,
            'net_amount' => $amountApproved - $finalFee,
            'approved'   => $amountApproved,
        ];

        // Solde wallet pour proposer le paiement direct
        $wallet        = \App\Models\Wallet::where('user_id', auth()->id())->where('status', 'active')->first();
        $walletBalance = (float) ($wallet?->balance ?? 0);

        return view('client.requests.payment-final', compact('fundingRequest', 'fees', 'walletBalance'));
    }

/**
 * Page de succès après paiement
 */
public function paymentSuccess(FundingRequest $fundingRequest)
{
    if ($fundingRequest->user_id !== auth()->id()) {
        abort(403);
    }

    if (!$fundingRequest->isPaid()) {
        return redirect()->route('client.requests.payment', $fundingRequest);
    }

    return view('client.requests.payment-success', compact('fundingRequest'));
}


    /**
     * Génère un numéro de demande unique
     */
    private function generateRequestNumber(): string
    {
        $prefix = 'BHDM-REQ';
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(4));

        return "{$prefix}-{$date}-{$random}";
    }

    /**
     * Construit la timeline pour une demande
     */
    private function buildTimeline(FundingRequest $request): array
    {
        $finalFee = $request->typeFinancement->registration_final_fee ?? 0;

        $steps = [
            ['key' => 'created',   'label' => 'Création',             'date' => $request->created_at],
            ['key' => 'payment',   'label' => 'Frais d\'inscription',  'date' => $request->paid_at],
            ['key' => 'submitted', 'label' => 'Dossier soumis',        'date' => $request->submitted_at],
            ['key' => 'review',    'label' => 'Examen',                'date' => $request->reviewed_at],
            ['key' => 'committee', 'label' => 'Comité',                'date' => $request->committee_review_started_at],
            ['key' => 'decision',  'label' => 'Décision',              'date' => $request->committee_decision_at ?? $request->approved_at],
        ];

        // Étape "frais de dossier" uniquement si le type de financement en a
        if ($finalFee > 0) {
            $steps[] = [
                'key'   => 'final_fee',
                'label' => 'Frais de dossier (' . number_format($finalFee, 0, ',', ' ') . ' FCFA)',
                'date'  => $request->final_fee_paid_at ?? null,
            ];
        }

        $steps[] = ['key' => 'funded', 'label' => 'Financement versé', 'date' => $request->funded_at];

        $timeline    = [];
        $prevDone    = true;
        foreach ($steps as $step) {
            $done   = (bool) $step['date'];
            $active = $prevDone && ! $done;

            $timeline[] = [
                'key'       => $step['key'],
                'label'     => $step['label'],
                'date'      => $step['date'],
                'completed' => $done,
                'active'    => $active,
            ];

            $prevDone = $done;
        }

        return $timeline;
    }
}
