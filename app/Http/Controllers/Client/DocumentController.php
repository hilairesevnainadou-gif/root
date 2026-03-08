<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadDocumentRequest;
use App\Models\DocumentUser;
use App\Models\FundingRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function index(): View
    {
        $documents = DocumentUser::with(['typeDoc', 'fundingRequest'])
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('client.documents.index', compact('documents'));
    }

    /**
     * Documents requis pour une demande
     */
    public function required(FundingRequest $fundingRequest): View
    {
        // Policy ou vérification manuelle
        $this->authorize('view', $fundingRequest);

        $documents = DocumentUser::with('typeDoc')
            ->where('funding_request_id', $fundingRequest->id)
            ->get();

        return view('client.documents.required', compact('fundingRequest', 'documents'));
    }

    /**
     * Upload avec validation complète
     */
    public function store(UploadDocumentRequest $request): RedirectResponse
    {
        // La validation est déjà faite par UploadDocumentRequest
        $validated = $request->validated();

        $fundingRequest = FundingRequest::findOrFail($validated['funding_request_id']);

        // Vérification supplémentaire du statut
        if ($fundingRequest->status !== 'draft') {
            return back()->with('error', 'Impossible d\'ajouter des documents après soumission.');
        }

        // Upload
        $file = $request->file('document');
        $directory = "documents/{$fundingRequest->user_id}/{$fundingRequest->id}";
        $path = $file->store($directory, 'public');

        DocumentUser::create([
            'user_id' => auth()->id(),
            'funding_request_id' => $validated['funding_request_id'],
            'typedoc_id' => $validated['typedoc_id'],
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Document uploadé avec succès.');
    }

    /**
     * Afficher le document (inline)
     */
    public function show(DocumentUser $document): Response
    {
        $this->authorize('view', $document);

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404);
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
     * Télécharger le document
     */
    public function download(DocumentUser $document): Response
    {
        $this->authorize('view', $document);

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404);
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
     * Supprimer un document
     */
    public function destroy(DocumentUser $document): RedirectResponse
    {
        $this->authorize('delete', $document);

        if ($document->status !== 'pending') {
            return back()->with('error', 'Impossible de supprimer un document déjà traité.');
        }

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Document supprimé.');
    }
}
