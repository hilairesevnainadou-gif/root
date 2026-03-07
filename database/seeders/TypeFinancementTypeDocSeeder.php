<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TypeFinancement;
use App\Models\TypeDoc;

class TypeFinancementTypeDocSeeder extends Seeder
{
    public function run(): void
    {
        $cni = TypeDoc::where('name', 'Carte nationale d\'identité')->first();
        $photo = TypeDoc::where('name', 'Photo identité')->first();
        $domicile = TypeDoc::where('name', 'Justificatif de domicile')->first();
        $releve = TypeDoc::where('name', 'Relevé bancaire')->first();

        $sr = TypeFinancement::where('code', 'SR')->first();
        $sf1 = TypeFinancement::where('code', 'SF1')->first();
        $sf2 = TypeFinancement::where('code', 'SF2')->first();
        $sf3 = TypeFinancement::where('code', 'SF3')->first();

        if ($sr) {
            $sr->requiredTypeDocs()->sync([
                $cni?->id,
                $photo?->id,
                $domicile?->id
            ]);
        }

        if ($sf1) {
            $sf1->requiredTypeDocs()->sync([
                $cni?->id,
                $photo?->id
            ]);
        }

        if ($sf2) {
            $sf2->requiredTypeDocs()->sync([
                $cni?->id,
                $photo?->id,
                $domicile?->id
            ]);
        }

        if ($sf3) {
            $sf3->requiredTypeDocs()->sync(array_filter([
                $cni?->id,
                $photo?->id,
                $domicile?->id,
                $releve?->id
            ]));
        }
    }
}
