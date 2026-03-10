<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateFundingStatusRequest;
use App\Models\FundingRequest;
use App\Models\Notification;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->typefinancement_id) {
            $query->where('typefinancement_id', $request->typefinancement_id);
        }
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->whereHas('user', function($q2) use ($request) {
                    $q2->where('first_name', 'like', "%{$request->search}%")
                       ->orWhere('last_name',  'like', "%{$request->search}%")
                       ->orWhere('email',       'like', "%{$request->search}%");
                })->orWhere('request_number', 'like', "%{$request->search}%");
            });
        }

        $requests         = $query->orderByDesc('created_at')->paginate(20);
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
            'reviewer',
        ]);

        $documentsStatus  = $this->getDocumentsStatus($fundingRequest);
        $availableActions = $this->getAvailableActions($fundingRequest);

        $finalFee = $fundingRequest->typeFinancement->registration_final_fee ?? 0;
        $regFee   = $fundingRequest->typeFinancement->registration_fee        ?? 0;
        $approved = $fundingRequest->amount_approved ?? 0;

        $amounts = [
            'requested'        => $fundingRequest->amount_requested,
            'approved'         => $fundingRequest->amount_approved,
            'registration_fee' => $regFee,
            'final_fee'        => $finalFee,
            /*
             * CORRECTION : le montant net versé = montant approuvé intégral.
             * Les frais de dossier ont déjà été réglés séparément par le client
             * (via Kkiapay ou wallet) avant que l'admin déclenche le versement.
             * On n'en déduit donc RIEN du montant approuvé.
             */
            'net_amount'       => $approved,
        ];

        return view('admin.requests.show', compact(
            'fundingRequest', 'documentsStatus', 'availableActions', 'amounts'
        ));
    }

    /**
     * Changer le statut manuellement
     *
     * Règles automatiques :
     *  A) under_review   + final_fee == 0 → saute pending_committee → approved → funded
     *  B) approved       + final_fee == 0 → funded + virement
     *  C) funded / pending_disbursement   → virement wallet immédiat
     */
    public function updateStatus(UpdateFundingStatusRequest $request, FundingRequest $fundingRequest): RedirectResponse
    {
        $fundingRequest->load('typeFinancement', 'user.wallet');

        $oldStatus = $fundingRequest->status;
        $newStatus = $request->status;
        $finalFee  = $fundingRequest->typeFinancement->registration_final_fee ?? 0;

        // ── Règle A : under_review + frais final = 0 → skip comité → approved → funded ──
        if ($newStatus === 'under_review' && $finalFee == 0) {
            $fundingRequest->update(['status' => 'under_review', 'reviewed_at' => now()]);
            $fundingRequest->update(['status' => 'pending_committee', 'committee_review_started_at' => now()]);
            $amount = $request->amount_approved ?? $fundingRequest->amount_requested;
            $fundingRequest->update(['status' => 'approved', 'approved_at' => now(), 'amount_approved' => $amount]);
            $this->sendStatusNotification($fundingRequest, 'approved', $request->comment);
            return $this->disburseToWallet($fundingRequest, $amount);
        }

        // ── Règle B : approved + frais final = 0 → funded + virement ──
        if ($newStatus === 'approved' && $finalFee == 0) {
            $amount = $request->amount_approved ?? $fundingRequest->amount_requested;
            $fundingRequest->update(['status' => 'approved', 'approved_at' => now(), 'amount_approved' => $amount]);
            $this->sendStatusNotification($fundingRequest, 'approved', $request->comment);
            return $this->disburseToWallet($fundingRequest, $amount);
        }

        // ── Règle C : funded ou pending_disbursement → virement wallet ──
        if (in_array($newStatus, ['funded', 'pending_disbursement'])) {
            $fundingRequest->update(['status' => $newStatus]);
            return $this->disburseToWallet(
                $fundingRequest,
                $fundingRequest->amount_approved ?? $fundingRequest->amount_requested
            );
        }

        // Cas normal
        $this->applyStatusUpdate($fundingRequest, $oldStatus, $newStatus, $request);

        return back()->with('success', $this->successMessage($newStatus));
    }

    /**
     * Décision du comité
     *
     * Si approuvé + frais final = 0 → funded + virement directement
     * Si approuvé + frais final > 0 → notifie le client pour paiement des frais
     */
    public function committeeDecision(Request $request, FundingRequest $fundingRequest): RedirectResponse
    {
        $request->validate([
            'decision'        => ['required', 'in:approved,rejected'],
            'amount_approved' => ['required_if:decision,approved', 'nullable', 'numeric', 'min:0'],
            'motivation'      => ['required', 'string', 'min:20'],
        ]);

        if ($fundingRequest->status !== 'pending_committee') {
            return back()->with('error', 'Cette demande n\'est pas en attente de décision comité.');
        }

        $fundingRequest->load('typeFinancement', 'user.wallet');

        $decision = $request->decision;
        $finalFee = $fundingRequest->typeFinancement->registration_final_fee ?? 0;

        // ── Rejet ──
        if ($decision === 'rejected') {
            $fundingRequest->update(['status' => 'rejected', 'committee_decision_at' => now()]);

            Notification::create([
                'user_id' => $fundingRequest->user_id,
                'type'    => 'request_rejected',
                'title'   => 'Votre demande n\'a pas été retenue',
                'message' => $request->motivation,
                'data'    => ['funding_request_id' => $fundingRequest->id],
            ]);

            return redirect()
                ->route('admin.requests.show', $fundingRequest)
                ->with('success', 'Demande rejetée. Le client a été notifié.');
        }

        // ── Approbation ──
        $amount = $request->amount_approved;
        $fundingRequest->update([
            'status'               => 'approved',
            'committee_decision_at'=> now(),
            'approved_at'          => now(),
            'amount_approved'      => $amount,
        ]);

        Notification::create([
            'user_id' => $fundingRequest->user_id,
            'type'    => 'request_approved',
            'title'   => 'Félicitations ! Votre demande est approuvée',
            'message' => $request->motivation,
            'data'    => [
                'funding_request_id' => $fundingRequest->id,
                'amount_approved'    => $amount,
            ],
        ]);

        // Frais final = 0 → on verse directement
        if ($finalFee == 0) {
            $fundingRequest->update(['status' => 'funded', 'funded_at' => now()]);
            return $this->disburseToWallet($fundingRequest, $amount);
        }

        // Frais final > 0 → le client doit payer avant le virement
        Notification::create([
            'user_id' => $fundingRequest->user_id,
            'type'    => 'final_fee_required',
            'title'   => 'Paiement des frais de dossier requis',
            'message' => "Votre demande #{$fundingRequest->request_number} est approuvée. "
                       . "Veuillez régler les frais de dossier de "
                       . number_format($finalFee, 0, ',', ' ') . " FCFA pour débloquer "
                       . "le versement de " . number_format($amount, 0, ',', ' ') . " FCFA.",
            'data'    => [
                'funding_request_id' => $fundingRequest->id,
                'final_fee'          => $finalFee,
                'net_amount'         => $amount,   // montant intégral, frais déjà séparés
            ],
        ]);

        return redirect()
            ->route('admin.requests.show', $fundingRequest)
            ->with('success', "Demande approuvée ({$amount} FCFA). Le client sera invité à régler les frais de dossier ("
                . number_format($finalFee, 0, ',', ' ') . " FCFA) avant le versement.");
    }

    /**
     * Valider le versement après paiement des frais de dossier.
     * Appelé manuellement par l'admin depuis le back-office quand la demande
     * est en statut 'pending_disbursement'.
     */
    public function approveDisbursement(FundingRequest $fundingRequest): RedirectResponse
    {
        $fundingRequest->load('typeFinancement', 'user.wallet');

        if ($fundingRequest->status !== 'pending_disbursement') {
            return back()->with('error', 'Cette demande n\'est pas en attente de versement.');
        }

        if (! ($fundingRequest->final_fee_paid ?? false)) {
            return back()->with('error', 'Les frais de dossier n\'ont pas encore été réglés.');
        }

        return $this->disburseToWallet(
            $fundingRequest,
            $fundingRequest->amount_approved ?? $fundingRequest->amount_requested
        );
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
            'status'      => 'under_review',
            'reviewed_at' => now(),
        ]);

        Notification::create([
            'user_id' => $fundingRequest->user_id,
            'type'    => 'request_under_review',
            'title'   => 'Votre demande est en cours d\'examen',
            'message' => "Votre demande #{$fundingRequest->request_number} a été assignée à un examinateur.",
            'data'    => ['funding_request_id' => $fundingRequest->id],
        ]);

        return back()->with('success', 'Demande assignée à ' . $reviewer->full_name . '.');
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
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="demandes-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($requests) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Numéro', 'Demandeur', 'Email', 'Type', 'Montant Demandé', 'Montant Approuvé', 'Statut', 'Date Création']);
            foreach ($requests as $r) {
                fputcsv($file, [
                    $r->id, $r->request_number, $r->user->full_name, $r->user->email,
                    $r->typeFinancement->name, $r->amount_requested,
                    $r->amount_approved ?? '-', $r->status,
                    $r->created_at->format('d/m/Y H:i'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // Méthodes privées
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Applique le changement de statut + timestamp + notification
     */
    private function applyStatusUpdate(FundingRequest $fundingRequest, string $oldStatus, string $newStatus, $request): void
    {
        $updateData = ['status' => $newStatus];

        match($newStatus) {
            'under_review'      => $updateData['reviewed_at']                = now(),
            'pending_committee' => $updateData['committee_review_started_at']= now(),
            'approved'          => $updateData['approved_at']                 = now(),
            'funded'            => $updateData['funded_at']                   = now(),
            default             => null,
        };

        if ($request->has('amount_approved') && $request->amount_approved !== null) {
            $updateData['amount_approved'] = $request->amount_approved;
        }

        $fundingRequest->update($updateData);
        $this->sendStatusNotification($fundingRequest, $newStatus, $request->comment ?? null);
    }

    /**
     * Crédite le wallet du client du montant APPROUVÉ INTÉGRAL et marque comme funded.
     *
     * CORRECTION IMPORTANTE :
     *  Les frais de dossier (final_fee) ont été réglés séparément par le client
     *  avant que l'admin déclenche ce versement. On verse donc le montant approuvé
     *  en totalité, sans en déduire les frais.
     */
    private function disburseToWallet(FundingRequest $fundingRequest, ?float $amountApproved): RedirectResponse
    {
        // Le montant à verser = montant approuvé intégral (frais déjà payés séparément)
        $netAmount = $amountApproved ?? 0;

        if ($netAmount <= 0) {
            return redirect()
                ->route('admin.requests.show', $fundingRequest)
                ->with('error', 'Montant nul ou négatif — vérifiez le montant approuvé.');
        }

        try {
            DB::transaction(function() use ($fundingRequest, $netAmount) {
                if (! in_array($fundingRequest->status, ['funded', 'pending_disbursement'])) {
                    $fundingRequest->update(['status' => 'funded', 'funded_at' => now()]);
                } else {
                    $fundingRequest->update(['status' => 'funded', 'funded_at' => now()]);
                }

                $wallet = $fundingRequest->user->wallet
                    ?? Wallet::createForUser($fundingRequest->user);

                $wallet->credit(
                    $netAmount,
                    'funding_disbursement',
                    "Versement financement #{$fundingRequest->request_number}",
                    ['funding_request_id' => $fundingRequest->id]
                );
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Wallet disbursement failed', [
                'funding_request_id' => $fundingRequest->id,
                'error'              => $e->getMessage(),
            ]);
            return redirect()
                ->route('admin.requests.show', $fundingRequest)
                ->with('error', 'Erreur lors du virement : ' . $e->getMessage());
        }

        $finalFee = $fundingRequest->typeFinancement->registration_final_fee ?? 0;

        Notification::create([
            'user_id' => $fundingRequest->user_id,
            'type'    => 'funding_disbursed',
            'title'   => '🎉 Votre financement a été versé !',
            'message' => "Le montant de " . number_format($netAmount, 0, ',', ' ')
                       . " FCFA a été crédité sur votre portefeuille."
                       . ($finalFee > 0
                            ? " (Frais de dossier de " . number_format($finalFee, 0, ',', ' ')
                              . " FCFA réglés séparément.)"
                            : ""),
            'data' => [
                'funding_request_id' => $fundingRequest->id,
                'amount_approved'    => $fundingRequest->amount_approved,
                'final_fee'          => $finalFee,
                'net_amount'         => $netAmount,
            ],
        ]);

        return redirect()
            ->route('admin.requests.show', $fundingRequest)
            ->with('success',
                '✓ ' . number_format($netAmount, 0, ',', ' ')
                . ' FCFA versés sur le portefeuille de ' . $fundingRequest->user->full_name . '.'
            );
    }

    /**
     * Notifications par statut
     */
    private function sendStatusNotification(FundingRequest $fundingRequest, string $newStatus, ?string $comment): void
    {
        $map = [
            'under_review' => [
                'type'    => 'request_under_review',
                'title'   => 'Votre demande est en cours d\'examen',
                'message' => "Votre demande #{$fundingRequest->request_number} est actuellement examinée par notre équipe.",
            ],
            'pending_committee' => [
                'type'    => 'request_pending_committee',
                'title'   => 'Votre demande est soumise au comité',
                'message' => "Votre demande #{$fundingRequest->request_number} a été transmise au comité de décision.",
            ],
            'approved' => [
                'type'    => 'request_approved',
                'title'   => 'Félicitations ! Votre demande est approuvée',
                'message' => $comment ?? "Votre demande #{$fundingRequest->request_number} a été approuvée.",
            ],
            'rejected' => [
                'type'    => 'request_rejected',
                'title'   => 'Votre demande n\'a pas été retenue',
                'message' => $comment ?? "Votre demande #{$fundingRequest->request_number} n'a pas été retenue. Contactez-nous pour plus d'informations.",
            ],
            'cancelled' => [
                'type'    => 'request_cancelled',
                'title'   => 'Demande annulée',
                'message' => $comment ?? "Votre demande #{$fundingRequest->request_number} a été annulée.",
            ],
        ];

        if (!isset($map[$newStatus])) {
            return;
        }

        $n = $map[$newStatus];

        Notification::create([
            'user_id' => $fundingRequest->user_id,
            'type'    => $n['type'],
            'title'   => $n['title'],
            'message' => $n['message'],
            'data'    => ['funding_request_id' => $fundingRequest->id],
        ]);
    }

    private function successMessage(string $status): string
    {
        return match($status) {
            'under_review'          => 'Demande prise en examen.',
            'pending_committee'     => 'Demande transmise au comité.',
            'approved'              => 'Demande approuvée. Le client sera invité à régler les frais de dossier.',
            'rejected'              => 'Demande rejetée. Le client a été notifié.',
            'funded'                => 'Financement versé sur le portefeuille du client.',
            'pending_disbursement'  => 'En attente de versement — frais de dossier réglés par le client.',
            'cancelled'             => 'Demande annulée.',
            default                 => 'Statut mis à jour.',
        };
    }

    private function getDocumentsStatus(FundingRequest $request): array
    {
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
            'submitted'            => ['under_review', 'rejected'],
            'under_review'         => ['pending_committee', 'rejected'],
            'pending_committee'    => ['approved', 'rejected'],
            'approved'             => ['funded', 'cancelled'],
            'pending_disbursement' => ['funded'],   // admin valide le versement
            default                => [],
        };
    }
}
