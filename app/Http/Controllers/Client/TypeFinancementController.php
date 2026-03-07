<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\FundingRequest;
use App\Models\TypeFinancement;
use Illuminate\View\View;

class TypeFinancementController extends Controller
{
    /**
     * Liste de tous les financements actifs
     */
    public function index(): View
    {
        $financements = TypeFinancement::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('client.financements.index', compact('financements'));
    }

    /**
     * Détails d'un financement + suggestions + accès à la demande
     */
    public function show(TypeFinancement $typeFinancement): View
    {
        $user = auth()->user();

        // ✅ ->get() pour obtenir une Collection (pas une relation)
        $requiredDocs = $typeFinancement->requiredTypeDocs()->get();

        // Calcul des frais
        $fees = [
            'registration' => $typeFinancement->registration_fee,
            'final'        => $typeFinancement->registration_final_fee,
            'total'        => $typeFinancement->registration_fee + $typeFinancement->registration_final_fee,
        ];

        // Demande en cours
        $existingRequest = FundingRequest::where('user_id', $user->id)
            ->where('typefinancement_id', $typeFinancement->id)
            ->whereIn('status', ['draft', 'submitted', 'under_review', 'pending_committee'])
            ->first();

        // Suggestions : 3 autres financements aléatoires
        $suggestions = TypeFinancement::where('is_active', true)
            ->where('id', '!=', $typeFinancement->id)
            ->inRandomOrder()
            ->limit(3)
            ->get();

        return view('client.financements.show', compact(
            'typeFinancement',
            'requiredDocs',
            'fees',
            'existingRequest',
            'suggestions'
        ));
    }
}
