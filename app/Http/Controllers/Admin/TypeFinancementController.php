<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTypeFinancementRequest;
use App\Models\TypeDoc;
use App\Models\TypeFinancement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TypeFinancementController extends Controller
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
        $types = TypeFinancement::withCount('fundingRequests')->orderBy('name')->get();
        return view('admin.typefinancements.index', compact('types'));
    }

    /**
     * Formulaire création
     */
    public function create(): View
    {
        $typeDocs = TypeDoc::orderBy('name')->get();
        return view('admin.typefinancements.create', compact('typeDocs'));
    }

    /**
     * Enregistrer
     */
    public function store(StoreTypeFinancementRequest $request): RedirectResponse
    {
        TypeFinancement::create($request->validated());

        return redirect()
            ->route('admin.typefinancements.index')
            ->with('success', 'Type de financement créé.');
    }

    /**
     * Formulaire édition
     */
    public function edit(TypeFinancement $typeFinancement): View
    {
        $typeDocs = TypeDoc::orderBy('name')->get();
        return view('admin.typefinancements.edit', compact('typeFinancement', 'typeDocs'));
    }

    /**
     * Mettre à jour
     */
    public function update(StoreTypeFinancementRequest $request, TypeFinancement $typeFinancement): RedirectResponse
    {
        $typeFinancement->update($request->validated());

        return redirect()
            ->route('admin.typefinancements.index')
            ->with('success', 'Mis à jour.');
    }

    /**
     * Supprimer
     */
    public function destroy(TypeFinancement $typeFinancement): RedirectResponse
    {
        if ($typeFinancement->fundingRequests()->count() > 0) {
            return back()->with('error', 'Impossible: des demandes existent.');
        }

        $typeFinancement->delete();

        return redirect()
            ->route('admin.typefinancements.index')
            ->with('success', 'Supprimé.');
    }

    /**
     * Activer/Désactiver
     */
    public function toggleActive(TypeFinancement $typeFinancement): RedirectResponse
    {
        $typeFinancement->update(['is_active' => !$typeFinancement->is_active]);

        return back()->with('success', $typeFinancement->is_active ? 'Activé' : 'Désactivé');
    }
}
