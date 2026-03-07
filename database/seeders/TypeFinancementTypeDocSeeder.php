<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TypeFinancement;
use App\Models\TypeDoc;

class TypeFinancementTypeDocSeeder extends Seeder
{
    public function run(): void
    {
        // Récupération des documents particuliers
        $cni = TypeDoc::where('name', 'Carte nationale d\'identité')->first();
        $photo = TypeDoc::where('name', 'Photo identité')->first();
        $domicile = TypeDoc::where('name', 'Justificatif de domicile')->first();
        $releve = TypeDoc::where('name', 'Relevé bancaire')->first();

        // Récupération des documents entreprise
        $rccm = TypeDoc::where('name', 'RCCM (Registre du Commerce)')->first();
        $dniRep = TypeDoc::where('name', 'DNI du représentant légal')->first();
        $photoRep = TypeDoc::where('name', 'Photo du représentant légal')->first();
        $attestationLoc = TypeDoc::where('name', 'Attestation de localisation')->first();
        $compteEntreprise = TypeDoc::where('name', 'Compte bancaire entreprise')->first();
        $attestationFiscale = TypeDoc::where('name', 'Attestation fiscale')->first();
        $planAffaires = TypeDoc::where('name', 'Plan d\'affaires')->first();

        // Récupération des financements particuliers
        $sr = TypeFinancement::where('code', 'SR')->first();
        $sf1 = TypeFinancement::where('code', 'SF1')->first();
        $sf2 = TypeFinancement::where('code', 'SF2')->first();
        $sf3 = TypeFinancement::where('code', 'SF3')->first();

        // Récupération des financements entreprise
        $se = TypeFinancement::where('code', 'SE')->first();
        $ef1 = TypeFinancement::where('code', 'EF1')->first();
        $ef2 = TypeFinancement::where('code', 'EF2')->first();
        $ef3 = TypeFinancement::where('code', 'EF3')->first();
        $ef4 = TypeFinancement::where('code', 'EF4')->first();

        // ===== RELATIONS PARTICULIERS =====

        if ($sr) {
            $sr->requiredTypeDocs()->sync(array_filter([
                $cni?->id,
                $photo?->id,
                $domicile?->id
            ]));
        }

        if ($sf1) {
            $sf1->requiredTypeDocs()->sync(array_filter([
                $cni?->id,
                $photo?->id
            ]));
        }

        if ($sf2) {
            $sf2->requiredTypeDocs()->sync(array_filter([
                $cni?->id,
                $photo?->id,
                $domicile?->id
            ]));
        }

        if ($sf3) {
            $sf3->requiredTypeDocs()->sync(array_filter([
                $cni?->id,
                $photo?->id,
                $domicile?->id,
                $releve?->id
            ]));
        }

        // ===== RELATIONS ENTREPRISES =====

        if ($se) {
            $se->requiredTypeDocs()->sync(array_filter([
                $rccm?->id,
                $dniRep?->id,
                $photoRep?->id,
                $attestationLoc?->id
            ]));
        }

        if ($ef1) {
            $ef1->requiredTypeDocs()->sync(array_filter([
                $rccm?->id,
                $dniRep?->id,
                $photoRep?->id,
                $attestationLoc?->id,
                $compteEntreprise?->id
            ]));
        }

        if ($ef2) {
            $ef2->requiredTypeDocs()->sync(array_filter([
                $rccm?->id,
                $dniRep?->id,
                $photoRep?->id,
                $attestationLoc?->id,
                $compteEntreprise?->id,
                $attestationFiscale?->id
            ]));
        }

        if ($ef3) {
            $ef3->requiredTypeDocs()->sync(array_filter([
                $rccm?->id,
                $dniRep?->id,
                $photoRep?->id,
                $attestationLoc?->id,
                $compteEntreprise?->id,
                $attestationFiscale?->id,
                $planAffaires?->id
            ]));
        }

        if ($ef4) {
            $ef4->requiredTypeDocs()->sync(array_filter([
                $rccm?->id,
                $dniRep?->id,
                $photoRep?->id,
                $attestationLoc?->id,
                $compteEntreprise?->id,
                $attestationFiscale?->id,
                $planAffaires?->id
            ]));
        }
    }
}