<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TypeDoc;
use App\Models\TypeFinancement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TypeFinancementDocController extends Controller
{
    /**
     * Vue principale : liste des types de financement avec leurs documents
     */
    public function index(): View
    {
        $typeFinancements = TypeFinancement::with('requiredTypeDocs')
            ->orderBy('name')
            ->get();

        $allTypeDocs = TypeDoc::orderBy('name')->get();

        return view('admin.typefinancements.documents', compact('typeFinancements', 'allTypeDocs'));
    }

    /**
     * Vue détail : gérer les documents d'un type de financement
     */
    public function edit(TypeFinancement $typeFinancement): View
    {
        $typeFinancement->load('requiredTypeDocs');

        // Docs compatibles = même typeusers OU tous si admin
        $compatibleDocs = TypeDoc::whereIn('typeusers', [$typeFinancement->typeusers, 'admin'])
            ->orderBy('name')
            ->get();

        $attachedIds = $typeFinancement->requiredTypeDocs->pluck('id')->toArray();

        return view('admin.typefinancements.documents-edit', compact(
            'typeFinancement',
            'compatibleDocs',
            'attachedIds'
        ));
    }

    /**
     * Synchroniser les documents associés (remplace tout)
     */
    public function sync(Request $request, TypeFinancement $typeFinancement): RedirectResponse
    {
        $request->validate([
            'typedoc_ids'   => ['nullable', 'array'],
            'typedoc_ids.*' => ['integer', 'exists:typedocs,id'],
        ]);

        $ids = $request->input('typedoc_ids', []);
        $typeFinancement->requiredTypeDocs()->sync($ids);

        $count = count($ids);
        return redirect()
            ->route('admin.typefinancements.documents.edit', $typeFinancement)
            ->with('success', $count === 0
                ? 'Tous les documents ont été dissociés.'
                : "{$count} document(s) associé(s) à « {$typeFinancement->name} »."
            );
    }

    /**
     * Attacher un seul document (AJAX ou form)
     */
    public function attach(Request $request, TypeFinancement $typeFinancement): RedirectResponse
    {
        $request->validate([
            'typedoc_id' => ['required', 'integer', 'exists:typedocs,id'],
        ]);

        $typeFinancement->requiredTypeDocs()->syncWithoutDetaching([$request->typedoc_id]);

        return back()->with('success', 'Document ajouté.');
    }

    /**
     * Détacher un seul document
     */
    public function detach(TypeFinancement $typeFinancement, TypeDoc $typeDoc): RedirectResponse
    {
        $typeFinancement->requiredTypeDocs()->detach($typeDoc->id);

        return back()->with('success', "« {$typeDoc->name} » dissocié.");
    }
}
