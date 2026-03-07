<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTypeDocRequest;
use App\Models\TypeDoc;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TypeDocController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Liste
     */
    public function index(): View
    {
        $docs = TypeDoc::withCount('documentUsers')->orderBy('name')->get();

        $grouped = [
            'particulier' => $docs->where('typeusers', 'particulier'),
            'entreprise' => $docs->where('typeusers', 'entreprise'),
            'admin' => $docs->where('typeusers', 'admin'),
        ];

        return view('admin.typedocs.index', compact('docs', 'grouped'));
    }

    /**
     * Créer
     */
    public function store(StoreTypeDocRequest $request): RedirectResponse
    {
        TypeDoc::create($request->validated());

        return back()->with('success', 'Type de document créé.');
    }

    /**
     * Mettre à jour
     */
    public function update(StoreTypeDocRequest $request, TypeDoc $typeDoc): RedirectResponse
    {
        $typeDoc->update($request->validated());

        return back()->with('success', 'Mis à jour.');
    }

    /**
     * Supprimer
     */
    public function destroy(TypeDoc $typeDoc): RedirectResponse
    {
        // Vérifier utilisation
        $usedIn = \App\Models\TypeFinancement::whereJsonContains('required_documents', $typeDoc->id)->count();

        if ($usedIn > 0 || $typeDoc->documentUsers()->count() > 0) {
            return back()->with('error', 'Document utilisé, suppression impossible.');
        }

        $typeDoc->delete();

        return back()->with('success', 'Supprimé.');
    }
}
