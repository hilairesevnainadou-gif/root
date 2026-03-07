<?php

// app/Http/Controllers/Client/FundingRequestController.php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateFundingRequestRequest;
use App\Models\Company;
use App\Models\FundingRequest;
use App\Models\TypeFinancement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FundingRequestController extends Controller
{
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
            'registration' => $fundingRequest->typeFinancement->registration_fee,  // À payer maintenant
            'final' => $fundingRequest->typeFinancement->registration_final_fee,      // À payer plus tard
            'current' => $fundingRequest->typeFinancement->registration_fee,        // Montant du paiement actuel
        ];

        return view('client.requests.payment', compact('fundingRequest', 'fees'));
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

        // Supprimer la demande
        $fundingRequest->delete();

        return redirect()
            ->route('client.requests.index')
            ->with('success', 'Demande annulée avec succès.');
    }

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

        // Récupérer les entreprises de l'utilisateur (vide si aucune)
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
     */
    /**
     * Store - Création AJAX pour le paiement
     * Permet plusieurs demandes du même type de financement
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
            // Champs pour nouvelle entreprise
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

        // Vérifier uniquement les demandes en brouillon (pas encore payées) du même type
        // Permet plusieurs demandes mais évite les doublons de brouillons
        $existingDraft = FundingRequest::where('user_id', $user->id)
            ->where('typefinancement_id', $typeFinancement->id)
            ->where('status', 'draft') // Seulement les brouillons non payés
            ->when($validated['financement_type'] === 'entreprise' && ! empty($validated['company_id']),
                fn ($q) => $q->where('company_id', $validated['company_id'])
            )
            ->first();

        if ($existingDraft) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà une demande en brouillon pour ce financement. Veuillez la finaliser ou l\'annuler.',
                'existing_request' => [
                    'id' => $existingDraft->id,
                    'request_number' => $existingDraft->request_number,
                    'title' => $existingDraft->title,
                    'created_at' => $existingDraft->created_at,
                ],
            ], 400);
        }

        // Gestion de l'entreprise
        $companyId = null;
        $isNewCompany = false;

        if ($validated['financement_type'] === 'entreprise') {
            if (! empty($validated['company_id'])) {
                // Vérifier que l'entreprise appartient bien à l'utilisateur
                $company = Company::where('id', $validated['company_id'])
                    ->where('user_id', $user->id)
                    ->first();

                if (! $company) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Entreprise non trouvée ou non autorisée.',
                    ], 403);
                }
                $companyId = $company->id;
            } elseif (! empty($validated['new_company']['name'])) {
                // Créer nouvelle entreprise
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
                        'message' => 'Erreur lors de la création de l\'entreprise: '.$e->getMessage(),
                    ], 500);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Veuillez sélectionner ou créer une entreprise.',
                ], 422);
            }
        }

        // Gestion explicite de description null
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

        return response()->json([
            'success' => true,
            'funding_request_id' => $fundingRequest->id,
            'request_number' => $fundingRequest->request_number,
            'company_id' => $companyId,
            'is_new_company' => $isNewCompany,
            'message' => 'Demande créée. Procédez au paiement.',
        ]);
    }

    public function show(FundingRequest $fundingRequest): View|RedirectResponse
    {
        // Vérifier que l'utilisateur est propriétaire
        if ($fundingRequest->user_id !== auth()->id()) {
            return redirect()
                ->route('client.requests.index')
                ->with('error', 'Vous n\'êtes pas autorisé à voir cette demande.');
        }

        $fundingRequest->load(['typeFinancement', 'documentUsers.typeDoc']);

        $requiredDocs = $fundingRequest->typeFinancement->requiredTypeDocs()->get();
        $providedDocs = $fundingRequest->documentUsers;

        // Documents manquants = requis - fournis (vérifiés ou en attente)
        $providedDocIds = $providedDocs->pluck('typedoc_id')->toArray();
        $missingDocs = $requiredDocs->whereNotIn('id', $providedDocIds);

        $fees = [
            'registration' => $fundingRequest->typeFinancement->registration_fee,
            'final' => $fundingRequest->typeFinancement->registration_final_fee,
            'total_fees' => $fundingRequest->typeFinancement->registration_fee + $fundingRequest->typeFinancement->registration_final_fee,
            'net_amount' => $fundingRequest->amount_requested - ($fundingRequest->typeFinancement->registration_fee + $fundingRequest->typeFinancement->registration_final_fee),
        ];

        $timeline = $this->buildTimeline($fundingRequest);

        return view('client.requests.show', compact(
            'fundingRequest',
            'requiredDocs',
            'providedDocs',
            'missingDocs',
            'fees',
            'timeline'
        ));
    }

    public function edit(FundingRequest $fundingRequest): View
    {
        $this->authorize('update', $fundingRequest);

        if ($fundingRequest->status !== 'draft') {
            abort(403, 'Seules les demandes en brouillon peuvent être modifiées.');
        }

        return view('client.requests.edit', compact('fundingRequest'));
    }

    public function update(UpdateFundingRequestRequest $request, FundingRequest $fundingRequest): RedirectResponse
    {
        $this->authorize('update', $fundingRequest);

        $fundingRequest->update($request->validated());

        return redirect()
            ->route('client.requests.show', $fundingRequest)
            ->with('success', 'Demande mise à jour.');
    }

    public function track(string $requestNumber): View
    {
        $request = FundingRequest::with(['typeFinancement', 'documentUsers.typeDoc'])
            ->where('request_number', $requestNumber)
            ->firstOrFail();

        $timeline = $this->buildTimeline($request);

        return view('client.requests.track', compact('request', 'timeline'));
    }

    private function generateRequestNumber(): string
    {
        $prefix = 'BHDM-REQ';
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(4));

        return "{$prefix}-{$date}-{$random}";
    }

    private function buildTimeline(FundingRequest $request): array
    {
        $timeline = [];
        $steps = [
            ['key' => 'created', 'label' => 'Création', 'date' => $request->created_at, 'icon' => 'plus'],
            ['key' => 'payment', 'label' => 'Paiement', 'date' => $request->paid_at, 'icon' => 'credit-card'],
            ['key' => 'submitted', 'label' => 'Soumission', 'date' => $request->submitted_at, 'icon' => 'send'],
            ['key' => 'under_review', 'label' => 'Examen', 'date' => $request->reviewed_at, 'icon' => 'search'],
            ['key' => 'committee', 'label' => 'Comité', 'date' => $request->committee_review_started_at, 'icon' => 'users'],
            ['key' => 'decision', 'label' => 'Décision', 'date' => $request->committee_decision_at, 'icon' => 'gavel'],
            ['key' => 'funded', 'label' => 'Financement', 'date' => $request->funded_at, 'icon' => 'money-bill'],
        ];

        $lastCompleted = true;
        foreach ($steps as $step) {
            $timeline[] = [
                'key' => $step['key'],
                'label' => $step['label'],
                'date' => $step['date'],
                'icon' => $step['icon'],
                'completed' => (bool) $step['date'],
                'active' => $lastCompleted && ! $step['date'],
            ];
            $lastCompleted = (bool) $step['date'];
        }

        return $timeline;
    }
}
