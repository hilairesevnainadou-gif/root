<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateFundingStatusRequest;
use App\Models\FundingRequest;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FundingRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Liste des demandes
     */
    public function index(Request $request): View
    {
        $query = FundingRequest::with(['user', 'typeFinancement']);

        // Filtres
        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->typefinancement_id) {
            $query->where('typefinancement_id', $request->typefinancement_id);
        }

        if ($request->search) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            })->orWhere('request_number', 'like', "%{$request->search}%");
        }

        $requests = $query->orderByDesc('created_at')->paginate(20);

        // Pour filtres
        $typeFinancements = \App\Models\TypeFinancement::pluck('name', 'id');

        return view('admin.requests.index', compact('requests', 'typeFinancements'));
    }

    /**
     * Détails d'une demande
     */
    public function show(FundingRequest $fundingRequest): View
    {
        $fundingRequest->load([
            'user',
            'typeFinancement.requiredTypeDocs',
            'documentUsers.typeDoc',
            'documentUsers.verifiedBy',
        ]);

        // Documents status détaillé
        $documentsStatus = $this->getDocumentsStatus($fundingRequest);

        // Actions disponibles selon le statut
        $availableActions = $this->getAvailableActions($fundingRequest);

        // Montants et frais (sans taux)
        $amounts = [
            'requested' => $fundingRequest->amount_requested,
            'approved' => $fundingRequest->amount_approved,
            'registration_fee' => $fundingRequest->typeFinancement->registration_fee,
            'final_fee' => $fundingRequest->typeFinancement->registration_final_fee,
            'net_amount' => ($fundingRequest->amount_approved ?? 0)
                - $fundingRequest->typeFinancement->registration_fee
                - $fundingRequest->typeFinancement->registration_final_fee,
        ];

        return view('admin.requests.show', compact(
            'fundingRequest',
            'documentsStatus',
            'availableActions',
            'amounts'
        ));
    }

    /**
     * Changer le statut
     */
    public function updateStatus(UpdateFundingStatusRequest $request, FundingRequest $fundingRequest): RedirectResponse
    {
        $oldStatus = $fundingRequest->status;
        $newStatus = $request->status;

        $updateData = ['status' => $newStatus];

        // Timestamps selon statut
        match($newStatus) {
            'under_review' => $updateData['reviewed_at'] = now(),
            'pending_committee' => $updateData['committee_review_started_at'] = now(),
            'approved' => $updateData['approved_at'] = now(),
            'funded' => $updateData['funded_at'] = now(),
            default => null,
        };

        if ($request->has('amount_approved')) {
            $updateData['amount_approved'] = $request->amount_approved;
        }

        $fundingRequest->update($updateData);

        // Notifier le client
        $this->notifyStatusChange($fundingRequest, $oldStatus, $newStatus, $request->comment);

        return back()->with('success', 'Statut mis à jour: ' . $oldStatus . ' → ' . $newStatus);
    }

    /**
     * Décision du comité
     */
    public function committeeDecision(Request $request, FundingRequest $fundingRequest): RedirectResponse
    {
        $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'amount_approved' => ['required_if:decision,approved', 'numeric', 'min:0'],
            'motivation' => ['required', 'string', 'min:20'],
        ]);

        if ($fundingRequest->status !== 'pending_committee') {
            return back()->with('error', 'Cette demande n\'est pas en attente de décision comité.');
        }

        $status = $request->decision === 'approved' ? 'approved' : 'rejected';

        $updateData = [
            'status' => $status,
            'committee_decision_at' => now(),
        ];

        if ($status === 'approved') {
            $updateData['amount_approved'] = $request->amount_approved;
            $updateData['approved_at'] = now();
        }

        $fundingRequest->update($updateData);

        // Notification
        Notification::create([
            'user_id' => $fundingRequest->user_id,
            'type' => $status === 'approved' ? 'request_approved' : 'request_rejected',
            'title' => $status === 'approved' ? 'Demande approuvée!' : 'Demande rejetée',
            'message' => $request->motivation,
            'data' => [
                'funding_request_id' => $fundingRequest->id,
                'amount_approved' => $request->amount_approved ?? null,
            ],
        ]);

        return redirect()
            ->route('admin.requests.show', $fundingRequest)
            ->with('success', 'Décision du comité enregistrée.');
    }

    /**
     * Assigner un reviewer
     */
    public function assign(Request $request, FundingRequest $fundingRequest): RedirectResponse
    {
        $request->validate([
            'reviewer_id' => ['required', 'exists:users,id'],
        ]);

        $reviewer = User::findOrFail($request->reviewer_id);

        $fundingRequest->update([
            'reviewer_id' => $request->reviewer_id,
            'status' => 'under_review',
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Assigné à ' . $reviewer->full_name);
    }

    /**
     * Export CSV
     */
    public function export(Request $request)
    {
        $requests = FundingRequest::with(['user', 'typeFinancement'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="demandes-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($requests) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Numéro', 'Demandeur', 'Email', 'Type', 'Montant Demandé', 'Montant Approuvé', 'Statut', 'Date Création']);

            foreach ($requests as $r) {
                fputcsv($file, [
                    $r->id,
                    $r->request_number,
                    $r->user->full_name,
                    $r->user->email,
                    $r->typeFinancement->name,
                    $r->amount_requested,
                    $r->amount_approved ?? '-',
                    $r->status,
                    $r->created_at->format('d/m/Y H:i'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Méthodes privées

    private function getDocumentsStatus(FundingRequest $request): array
    {
        // Utilise la relation BelongsToMany (plus fiable que l'attribut JSON)
        $requiredDocs = $request->typeFinancement->requiredTypeDocs ?? collect();
        $provided     = $request->documentUsers;

        return $requiredDocs->map(function($typeDoc) use ($provided) {
            $doc = $provided->firstWhere('typedoc_id', $typeDoc->id);
            return [
                'typedoc_id'       => $typeDoc->id,
                'name'             => $typeDoc->name,
                'provided'         => (bool) $doc,
                'status'           => $doc?->status ?? 'missing',
                'uploaded_at'      => $doc?->created_at,
                'document_id'      => $doc?->id,
                'verified_by'      => $doc?->verified_by,
                'verified_by_name' => $doc?->verifiedBy?->full_name,
            ];
        })->toArray();
    }

    private function getAvailableActions(FundingRequest $request): array
    {
        return match($request->status) {
            'submitted' => ['under_review', 'rejected'],
            'under_review' => ['pending_committee', 'rejected'],
            'pending_committee' => ['approved', 'rejected'],
            'approved' => ['funded', 'cancelled'],
            default => [],
        };
    }

    private function notifyStatusChange($request, $oldStatus, $newStatus, $comment): void
    {
        $titles = [
            'under_review' => 'Votre demande est en cours d\'examen',
            'pending_committee' => 'Votre demande est soumise au comité',
            'approved' => 'Félicitations! Votre demande est approuvée',
            'rejected' => 'Votre demande a été rejetée',
            'funded' => 'Votre financement a été débloqué',
        ];

        Notification::create([
            'user_id' => $request->user_id,
            'type' => 'funding_status_changed',
            'title' => $titles[$newStatus] ?? 'Mise à jour de votre demande',
            'message' => $comment ?? "Changement de statut: {$oldStatus} → {$newStatus}",
            'data' => [
                'funding_request_id' => $request->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ],
        ]);
    }
}
