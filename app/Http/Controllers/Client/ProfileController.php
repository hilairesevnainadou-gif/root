<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProfileController extends Controller
{
    /**
     * Afficher le profil
     */
    public function show(): View
    {
        $user = auth()->user();
        $company = $user->company;

        // Calcul du taux de complétion
        $completionRate = $this->calculateCompletion($user);

        // Si profil à 100%, marquer le modal comme vu pour ne pas le réafficher
        if ($completionRate === 100 && ! session()->has('profile_modal_seen')) {
            session(['profile_modal_seen' => true]);
        }

        return view('client.profile.show', compact('user', 'company', 'completionRate'));
    }

    private function calculateCompletion($user): int
    {
        $fields = ['phone', 'address', 'city'];
        $filled = collect($fields)->filter(fn ($f) => ! empty($user->$f))->count();
        $total = count($fields);

        // Champs optionnels (bonus)
        $optional = ['birth_date', 'gender'];
        $filled += collect($optional)->filter(fn ($f) => ! empty($user->$f))->count();

        // Entreprise seulement si applicable
        if ($user->isEntreprise() || $user->company) {
            $company = $user->company;
            $companyRequired = ['company_name', 'company_type', 'sector'];
            $filled += collect($companyRequired)->filter(fn ($f) => $company && ! empty($company->$f))->count();
            $total += count($companyRequired);
        }

        return min(100, (int) round(($filled / $total) * 100));
    }

    /**
     * Mettre à jour le profil utilisateur et entreprise
     */
    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        // Validation pour les champs utilisateur
        $validatedUser = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone,'.$user->id],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:male,female,other'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:255'],
        ]);

        $user->update($validatedUser);

        // Validation pour les champs entreprise si utilisateur est entreprise
        if ($user->isEntreprise() || $request->filled('company_name')) {
            $validatedCompany = $request->validate([
                'company_name' => ['required', 'string', 'max:255'],
                'company_type' => ['required', 'in:sarl,sa,snc,ei,cooperative,ong,autre'],
                'sector' => ['required', 'in:agriculture,elevage,peche,industrie,commerce,services,tourisme,batiment,technologie,autre'],
                'job_title' => ['nullable', 'string', 'max:255'],
                'employees_count' => ['nullable', 'integer', 'min:0'],
                'annual_turnover' => ['nullable', 'numeric', 'min:0'],
            ]);

            // Crée ou met à jour la société
            if ($user->company) {
                $user->company->update($validatedCompany);
            } else {
                $user->company()->create($validatedCompany);
            }
        }

        return back()->with('success', 'Profil mis à jour.');
    }

    /**
     * Calcul du taux de complétion du profil
     */

    /**
     * Marquer le modal comme vu (pour ne pas le réafficher immédiatement)
     */
    public function acknowledgeModal(): JsonResponse
    {
        session(['profile_completed' => true]);

        return response()->json(['success' => true]);
    }
}
