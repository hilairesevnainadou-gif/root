<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyDocumentRequest;
use App\Models\DocumentUser;
use App\Models\FundingRequest;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DocumentVerificationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Liste des documents en attente
     */
    public function pending(Request $request): View
    {
        $query = DocumentUser::with(['user', 'typeDoc', 'fundingRequest']);

        if ($request->typedoc_id) {
            $query->where('typedoc_id', $request->typedoc_id);
        }

        $documents = $query->where('status', 'pending')
            ->orderBy('created_at')
            ->paginate(20);

        $stats = [
            'total_pending' => DocumentUser::where('status', 'pending')->count(),
            'by_type' => DocumentUser::where('status', 'pending')
                ->selectRaw('typedoc_id, count(*) as count')
                ->groupBy('typedoc_id')
                ->with('typeDoc:id,name')
                ->get(),
        ];

        return view('admin.documents.pending', compact('documents', 'stats'));
    }

    /**
     * Voir un document
     */
    public function show(DocumentUser $document): View
    {
        $document->load(['user', 'typeDoc', 'fundingRequest.typeFinancement']);

        // URL publique (pas de temporaryUrl qui nécessite S3)
        $viewUrl = Storage::disk('public')->url($document->file_path);

        // Autres documents de la même demande
        $relatedDocuments = DocumentUser::where('funding_request_id', $document->funding_request_id)
            ->where('id', '!=', $document->id)
            ->with('typeDoc')
            ->get();

        return view('admin.documents.show', compact('document', 'viewUrl', 'relatedDocuments'));
    }

    /**
     * Vérifier un document
     */
    public function verify(VerifyDocumentRequest $request, DocumentUser $document): RedirectResponse
    {
        if ($document->status !== 'pending') {
            return back()->with('error', 'Ce document a déjà été traité.');
        }

        $data = [
            'status' => $request->status,
            'verified_by' => auth()->id(),
        ];

        if ($request->status === 'verified') {
            $data['verified_at'] = now();
        } else {
            $data['rejection_reason'] = $request->rejection_reason;
        }

        if ($request->notes) {
            $data['notes'] = $document->notes
                ? $document->notes . "\n\n[Admin]: " . $request->notes
                : $request->notes;
        }

        $document->update($data);

        // Notifier l'utilisateur
        $this->notifyUser($document, $request->status, $request->rejection_reason);

        // Vérifier si tous les documents sont validés
        $this->checkFundingRequestCompletion($document->fundingRequest);

        $next = DocumentUser::where('status', 'pending')
            ->where('id', '!=', $document->id)
            ->first();

        if ($next && $request->has('next')) {
            return redirect()->route('admin.documents.show', $next);
        }

        return redirect()
            ->route('admin.documents.pending')
            ->with('success', 'Document ' . ($request->status === 'verified' ? 'approuvé' : 'rejeté'));
    }

    /**
     * Vérification en masse
     */
    public function bulkVerify(Request $request): RedirectResponse
    {
        $request->validate([
            'document_ids' => ['required', 'array'],
            'document_ids.*' => ['exists:document_users,id'],
            'status' => ['required', 'in:verified,rejected'],
            'rejection_reason' => ['required_if:status,rejected'],
        ]);

        $count = 0;
        foreach ($request->document_ids as $id) {
            $doc = DocumentUser::find($id);
            if ($doc && $doc->status === 'pending') {
                $doc->update([
                    'status' => $request->status,
                    'verified_by' => auth()->id(),
                    'verified_at' => $request->status === 'verified' ? now() : null,
                    'rejection_reason' => $request->rejection_reason ?? null,
                ]);
                $this->notifyUser($doc, $request->status, $request->rejection_reason);
                $count++;
            }
        }

        return back()->with('success', "{$count} documents traités.");
    }

    // Méthodes privées

    private function notifyUser(DocumentUser $document, string $status, ?string $reason): void
    {
        $title = $status === 'verified' ? 'Document vérifié ✓' : 'Document rejeté ✗';
        $message = $status === 'verified'
            ? "Votre document '{$document->typeDoc->name}' a été vérifié et approuvé."
            : "Votre document '{$document->typeDoc->name}' a été rejeté. Motif: {$reason}";

        Notification::create([
            'user_id' => $document->user_id,
            'type' => $status === 'verified' ? 'document_verified' : 'document_rejected',
            'title' => $title,
            'message' => $message,
            'data' => [
                'document_id' => $document->id,
                'funding_request_id' => $document->funding_request_id,
            ],
        ]);
    }

    private function checkFundingRequestCompletion(FundingRequest $request): void
    {
        $totalRequired = count($request->typeFinancement->required_documents ?? []);
        $verified = $request->documentUsers()->where('status', 'verified')->count();

        if ($verified >= $totalRequired && $request->status === 'submitted') {
            Notification::create([
                'user_id' => $request->user_id,
                'type' => 'documents_complete',
                'title' => 'Documents complets',
                'message' => 'Tous vos documents ont été vérifiés. Votre demande va passer en examen approfondi.',
            ]);
        }
    }
}
