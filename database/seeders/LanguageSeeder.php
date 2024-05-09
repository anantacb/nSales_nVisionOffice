<?php

namespace Database\Seeders;

use App\Models\Office\Language;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Language::truncate();
        $languages = Storage::get('public/languages.json');
        $languages = json_decode($languages, true);
        foreach ($languages as $language) {
            Language::create([
                'Name' => $language['Name'],
                'Locale' => $language['Locale'],
                'Code' => $language['Code'],
                'IsDefault' => $language['IsDefault']
            ]);
        }
    }
}
