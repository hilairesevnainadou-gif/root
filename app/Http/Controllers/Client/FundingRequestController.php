<?php

// app/Http/Controllers/Client/FundingRequestController.php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateFundingRequestRequest;
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

        $availableTypes = TypeFinancement::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('client.requests.create', compact(
            'availableTypes',
            'preselectedType',
            'isPreselected'
        ));
    }

    /**
     * Store - Création AJAX pour le paiement
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'typefinancement_id' => 'required|exists:typefinanciements,id',
            'title' => 'required|string|max:100',
            'amount_requested' => 'required|numeric|min:1000',
            'duration' => 'required|integer|min:1',
            'description' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $typeFinancement = TypeFinancement::findOrFail($validated['typefinancement_id']);

        // Vérifier si une demande similaire existe déjà
        $existingDraft = FundingRequest::where('user_id', $user->id)
            ->where('typefinancement_id', $typeFinancement->id)
            ->whereIn('status', ['draft', 'submitted', 'under_review', 'pending_committee', 'approved'])
            ->first();

        if ($existingDraft) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà une demande en cours pour ce type de financement.',
                'existing_request' => $existingDraft,
            ], 400);
        }

        // Gestion explicite de description null
        $description = isset($validated['description']) && $validated['description'] !== ''
            ? $validated['description']
            : null;

        $fundingRequest = FundingRequest::create([
            'user_id' => auth()->id(),
            'typefinancement_id' => $validated['typefinancement_id'],
            'request_number' => $this->generateRequestNumber(),
            'title' => $validated['title'],
            'amount_requested' => $validated['amount_requested'],
            'duration' => $validated['duration'],
            'description' => $description, // Sera null si vide ou non fourni
            'status' => 'draft',
            'payment_status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'funding_request_id' => $fundingRequest->id,
            'request_number' => $fundingRequest->request_number,
            'message' => 'Demande créée. Procédez au paiement.',
        ]);
    }

    public function show(FundingRequest $fundingRequest): View
    {
        $this->authorize('view', $fundingRequest);

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

    public function destroy(FundingRequest $fundingRequest): RedirectResponse
    {
        $this->authorize('delete', $fundingRequest);

        if (! in_array($fundingRequest->status, ['draft', 'submitted'])) {
            return back()->with('error', 'Impossible d\'annuler une demande en cours de traitement.');
        }

        $fundingRequest->delete();

        return redirect()
            ->route('client.requests.index')
            ->with('success', 'Demande annulée.');
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
