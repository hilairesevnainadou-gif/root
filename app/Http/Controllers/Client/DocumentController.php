<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadDocumentRequest;
use App\Models\DocumentUser;
use App\Models\FundingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DocumentController extends Controller
{
    /**
     * Liste des documents de l'utilisateur
     */
    public function index(): View
    {
        $documents = DocumentUser::with(['typeDoc', 'fundingRequest'])
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('client.documents.index', compact('documents'));
    }

    /**
     * Documents requis pour une demande spécifique
     */
    public function required(FundingRequest $fundingRequest): View
    {
        if ($fundingRequest->user_id !== auth()->id()) {
            abort(403);
        }

        $documents = DocumentUser::with('typeDoc')
            ->where('funding_request_id', $fundingRequest->id)
            ->get();

        return view('client.documents.required', compact('fundingRequest', 'documents'));
    }

    /**
     * 🔥 UPLOAD - Met à jour un DocumentUser existant
     * Permis si status: draft, submitted, under_review, pending_committee
     */
    public function store(UploadDocumentRequest $request): JsonResponse
    {
        $fundingRequest = FundingRequest::findOrFail($request->funding_request_id);

        // Autorisations
        if ($fundingRequest->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé.'
            ], 403);
        }

        // 🔥 STATUTS PERMIS pour l'upload
        $allowedStatuses = ['draft', 'submitted', 'under_review', 'pending_committee'];

        if (!in_array($fundingRequest->status, $allowedStatuses)) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de modifier les documents à ce stade.'
            ], 403);
        }

        // Récupérer le DocumentUser
        $documentUser = DocumentUser::where('id', $request->document_user_id)
            ->where('funding_request_id', $request->funding_request_id)
            ->where('typedoc_id', $request->typedoc_id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$documentUser) {
            return response()->json([
                'success' => false,
                'message' => 'Document non trouvé.'
            ], 422);
        }

        // Supprimer l'ancien fichier
        if ($documentUser->file_path && Storage::disk('public')->exists($documentUser->file_path)) {
            Storage::disk('public')->delete($documentUser->file_path);
        }

        // Stocker le nouveau fichier
        $file = $request->file('document');
        $directory = 'documents/' . auth()->id() . '/' . $fundingRequest->id;
        $path = $file->store($directory, 'public');

        // Mise à jour
        $documentUser->update([
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'status' => 'pending',
        ]);

        // 🔥 VÉRIFIER SI TOUS LES DOCUMENTS SONT COMPLÉTÉS
        $this->checkAndUpdateFundingRequestStatus($fundingRequest);

        return response()->json([
            'success' => true,
            'message' => 'Document uploadé avec succès.',
            'document' => [
                'id' => $documentUser->id,
                'file_name' => $documentUser->file_name,
                'status' => $documentUser->status,
            ]
        ]);
    }

    /**
     * 🔥 Vérifie si tous les documents sont uploadés et met à jour le statut
     */
    private function checkAndUpdateFundingRequestStatus(FundingRequest $fundingRequest): void
    {
        // Compter les documents
        $totalDocs = DocumentUser::where('funding_request_id', $fundingRequest->id)->count();
        $filledDocs = DocumentUser::where('funding_request_id', $fundingRequest->id)
            ->whereNotNull('file_path')
            ->count();

        // Si tous les documents sont présents et statut permet la transition
        if ($totalDocs > 0 && $totalDocs === $filledDocs) {
            if (in_array($fundingRequest->status, ['draft', 'submitted'])) {
                $fundingRequest->update([
                    'status' => 'under_review',
                    'reviewed_at' => now(), // Date de soumission
                ]);
            }
        }
    }

    /**
     * Voir un document (inline)
     */
    public function show(DocumentUser $document): Response
    {
        $this->authorize('view', $document);

        if (!$document->file_path || !Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'Fichier non trouvé');
        }

        return response(
            Storage::disk('public')->get($document->file_path),
            200,
            [
                'Content-Type' => $document->file_type,
                'Content-Disposition' => 'inline; filename="' . $document->file_name . '"',
            ]
        );
    }

    /**
     * Télécharger un document (force download)
     */
    public function download(DocumentUser $document): Response
    {
        $this->authorize('view', $document);

        if (!$document->file_path || !Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'Fichier non trouvé');
        }

        return response(
            Storage::disk('public')->get($document->file_path),
            200,
            [
                'Content-Type' => $document->file_type,
                'Content-Disposition' => 'attachment; filename="' . $document->file_name . '"',
            ]
        );
    }

    /**
     * Supprimer un document (remet à vide)
     */
    public function destroy(DocumentUser $document): RedirectResponse
    {
        $this->authorize('delete', $document);

        // 🔥 Même vérification de statut que pour l'upload
        $fundingRequest = $document->fundingRequest;
        $allowedStatuses = ['draft', 'submitted', 'under_review', 'pending_committee'];

        if (!in_array($fundingRequest->status, $allowedStatuses)) {
            return back()->with('error', 'Impossible de supprimer un document à ce stade.');
        }

        // Supprimer le fichier
        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        // Remet à vide
        $document->update([
            'file_path' => null,
            'file_name' => null,
            'file_type' => null,
            'file_size' => 0,
        ]);

        return back()->with('success', 'Document supprimé. Vous pouvez en télécharger un nouveau.');
    }
}
