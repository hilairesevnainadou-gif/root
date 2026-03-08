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
        // Vérifier que la demande appartient à l'utilisateur
        if ($fundingRequest->user_id !== auth()->id()) {
            abort(403);
        }

        // Charger les documents avec leur type (déjà créés lors du store FundingRequest)
        $documents = DocumentUser::with('typeDoc')
            ->where('funding_request_id', $fundingRequest->id)
            ->get();

        return view('client.documents.required', compact('fundingRequest', 'documents'));
    }

    /**
     * 🔥 UPLOAD - Met à jour un DocumentUser existant (pas de création)
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

        if ($fundingRequest->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Impossible d\'ajouter des documents après soumission.'
            ], 403);
        }

        // 🔥 RÉCUPÉRER LE DocumentUser EXISTANT (créé lors de la création de la demande)
        $documentUser = DocumentUser::where('funding_request_id', $request->funding_request_id)
            ->where('typedoc_id', $request->typedoc_id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$documentUser) {
            return response()->json([
                'success' => false,
                'message' => 'Type de document invalide pour cette demande.'
            ], 422);
        }

        // Supprimer l'ancien fichier s'il existe
        if ($documentUser->file_path && Storage::disk('public')->exists($documentUser->file_path)) {
            Storage::disk('public')->delete($documentUser->file_path);
        }

        // Stocker le nouveau fichier
        $file = $request->file('document');
        $directory = 'documents/' . auth()->id() . '/' . $fundingRequest->id;
        $path = $file->store($directory, 'public');

        // 🔥 MISE À JOUR (pas de création)
        $documentUser->update([
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'status' => 'pending', // Remet à pending si rejeté précédemment

        ]);

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
     * Voir/Télécharger un document (inline)
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
     * Supprimer un document (remet à vide, ne supprime pas l'entrée)
     */
    public function destroy(DocumentUser $document): RedirectResponse
    {
        $this->authorize('delete', $document);

        if ($document->status !== 'pending') {
            return back()->with('error', 'Impossible de supprimer un document déjà traité.');
        }

        // Supprimer le fichier
        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        // 🔥 REMET À VIDE (ne supprime pas l'entrée)
        $document->update([
            'file_path' => null,
            'file_name' => null,
            'file_type' => null,
            'file_size' => 0,
        ]);

        return back()->with('success', 'Document supprimé. Vous pouvez en télécharger un nouveau.');
    }
}
