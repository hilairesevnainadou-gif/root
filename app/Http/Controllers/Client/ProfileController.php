<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProfileController extends Controller
{
    /**
     * Afficher le profil utilisateur
     */
    public function show(): View
    {
        $user = auth()->user();
        $completionRate = $this->calculateCompletion($user);

        // Récupérer l'entreprise principale (ou null si aucune)
        $primaryCompany = $user->companies()->where('is_primary', true)->first();

        return view('client.profile.show', compact('user', 'completionRate', 'primaryCompany'));
    }

    /**
     * Liste des entreprises
     */
    public function companies(): View
    {
        $user = auth()->user();
        $companies = $user->companies()->latest()->get();

        return view('client.profile.companies.index', compact('companies'));
    }

    /**
     * Afficher le détail d'une entreprise
     */
    public function showCompany(Company $company): View
    {
        $this->authorizeCompany($company);

        return view('client.profile.companies.show', compact('company'));
    }

    /**
     * Créer entreprise - formulaire
     */
    public function createCompany(): View
    {
        return view('client.profile.companies.create');
    }

    /**
     * Stocker nouvelle entreprise
     */
    public function storeCompany(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_type' => ['required', 'in:sarl,sa,snc,ei,eurl,cooperative,ong,association,autre'],
            'sector' => ['required', 'in:agriculture,elevage,peche,industrie,commerce,services,tourisme,batiment,technologie,sante,education,finance,transport,autre'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'employees_count' => ['nullable', 'integer', 'min:0'],
            'annual_turnover' => ['nullable', 'numeric', 'min:0'],
            'registration_number' => ['nullable', 'string', 'max:50'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:20'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        // Si c'est la première entreprise, la définir comme principale
        $isFirst = $user->companies()->count() === 0;
        $validated['is_primary'] = $isFirst;

        $user->companies()->create($validated);

        return redirect()
            ->route('client.profile.companies.index')
            ->with('success', 'Entreprise créée avec succès.');
    }

    /**
     * Modifier entreprise - formulaire
     */
    public function editCompany(Company $company): View
    {
        $this->authorizeCompany($company);
        return view('client.profile.companies.edit', compact('company'));
    }

    /**
     * Mettre à jour entreprise
     */
    public function updateCompany(Request $request, Company $company): RedirectResponse
    {
        $this->authorizeCompany($company);

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_type' => ['required', 'in:sarl,sa,snc,ei,eurl,cooperative,ong,association,autre'],
            'sector' => ['required', 'in:agriculture,elevage,peche,industrie,commerce,services,tourisme,batiment,technologie,sante,education,finance,transport,autre'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'employees_count' => ['nullable', 'integer', 'min:0'],
            'annual_turnover' => ['nullable', 'numeric', 'min:0'],
            'registration_number' => ['nullable', 'string', 'max:50'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:20'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ]);

        $company->update($validated);

        return redirect()
            ->route('client.profile.companies.index')
            ->with('success', 'Entreprise mise à jour avec succès.');
    }

    /**
     * Supprimer entreprise
     */
    public function destroyCompany(Company $company): RedirectResponse
    {
        $this->authorizeCompany($company);

        $user = auth()->user();
        
        // Vérifier qu'il restera au moins une entreprise
        if ($user->companies()->count() <= 1) {
            return back()->with('error', 'Vous devez conserver au moins une entreprise.');
        }

        $wasPrimary = $company->is_primary;
        $company->delete();

        // Si on supprime la principale, définir une autre comme principale
        if ($wasPrimary) {
            $newPrimary = $user->companies()->first();
            if ($newPrimary) {
                $newPrimary->update(['is_primary' => true]);
            }
        }

        return redirect()
            ->route('client.profile.companies.index')
            ->with('success', 'Entreprise supprimée avec succès.');
    }

    /**
     * Définir comme entreprise principale
     */
    public function setPrimaryCompany(Company $company): RedirectResponse
    {
        $this->authorizeCompany($company);

        $user = auth()->user();

        // Retirer le statut primaire des autres
        $user->companies()->update(['is_primary' => false]);
        
        // Définir celle-ci comme primaire
        $company->update(['is_primary' => true]);

        return back()->with('success', 'Entreprise principale définie avec succès.');
    }

    /**
     * Mettre à jour profil utilisateur
     */
    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone,' . $user->id],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:male,female,other'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'id_number' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ]);

        $user->update($validated);

        return back()->with('success', 'Profil mis à jour avec succès.');
    }

    /**
     * Calcul complétion profil
     */
    private function calculateCompletion($user): int
    {
        $fields = ['first_name', 'last_name', 'phone', 'email', 'city'];
        $filled = collect($fields)->filter(fn($f) => !empty($user->$f))->count();
        $total = count($fields);

        $optional = ['birth_date', 'gender', 'address', 'country', 'postal_code', 'bio', 'id_number'];
        $filled += collect($optional)->filter(fn($f) => !empty($user->$f))->count();

        // Bonus entreprise
        if ($user->companies()->exists()) {
            $filled += 2;
        }

        $total += count($optional) + 2;

        return min(100, (int) round(($filled / $total) * 100));
    }

    /**
     * Vérifier autorisation entreprise
     */
    private function authorizeCompany(Company $company): void
    {
        if ($company->user_id !== auth()->id()) {
            abort(403, 'Accès non autorisé à cette entreprise.');
        }
    }

    /**
     * Marquer modal comme vu
     */
    public function acknowledgeModal(): JsonResponse
    {
        session(['profile_completed' => true]);
        return response()->json(['success' => true]);
    }
}