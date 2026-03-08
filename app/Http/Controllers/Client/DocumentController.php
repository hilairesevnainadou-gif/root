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
    // public function required(FundingRequest $fundingRequest): View
    // {
    //     $this->authorize('view', $fundingRequest);

    //     $requiredDocs = $fundingRequest->typeFinancement->requiredTypeDocs();
    //     $providedDocs = $fundingRequest->documentUsers()->with('typeDoc')->get();
    //     $missingDocs = $fundingRequest->missingDocuments();

    //     return view('client.documents.required', compact(
    //         'fundingRequest',
    //         'requiredDocs',
    //         'providedDocs',
    //         'missingDocs'
    //     ));
    // }

    /**
     * Upload un document (stockage PUBLIC)
     */
    public function store(UploadDocumentRequest $request): RedirectResponse
    {
        $fundingRequest = FundingRequest::findOrFail($request->funding_request_id);

        if ($fundingRequest->user_id !== auth()->id()) {
            return back()->with('error', 'Non autorisé.');
        }

        if ($fundingRequest->status !== 'draft') {
            return back()->with('error', 'Impossible d\'ajouter des documents après soumission.');
        }

        // Stocker dans PUBLIC (pas private)
        $file = $request->file('document');
        $directory = 'documents/' . auth()->id() . '/' . $fundingRequest->id;
        $path = $file->store($directory, 'public'); // DISK = public

        DocumentUser::create([
            'user_id' => auth()->id(),
            'funding_request_id' => $request->funding_request_id,
            'typedoc_id' => $request->typedoc_id,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'status' => 'pending',
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Document uploadé avec succès.');
    }


    /**
 * Afficher les documents requis pour une demande
 */
public function required(FundingRequest $fundingRequest): View
{
    // Vérifier que la demande appartient à l'utilisateur
    if ($fundingRequest->user_id !== auth()->id()) {
        abort(403);
    }

    // Charger les documents avec leur type
    $documents = DocumentUser::with('typeDoc')
        ->where('funding_request_id', $fundingRequest->id)
        ->get();

    return view('client.documents.required', compact('fundingRequest', 'documents'));
}
    /**
     * Voir/Télécharger un document (retourne Response, pas RedirectResponse)
     */
    public function show(DocumentUser $document): Response
    {
        $this->authorize('view', $document);

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'Fichier non trouvé');
        }

        // Retourne le fichier pour affichage ou téléchargement
        $fileContent = Storage::disk('public')->get($document->file_path);
        $mimeType = $document->file_type;

        return response($fileContent, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $document->file_name . '"',
        ]);
    }

    /**
     * Télécharger un document (force download)
     */
    public function download(DocumentUser $document): Response
    {
        $this->authorize('view', $document);

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'Fichier non trouvé');
        }

        $fileContent = Storage::disk('public')->get($document->file_path);
        $mimeType = $document->file_type;

        return response($fileContent, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'attachment; filename="' . $document->file_name . '"',
        ]);
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

        // Supprimer du stockage public
        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Document supprimé.');
    }
}
