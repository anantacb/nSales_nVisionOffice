<?php

namespace App\Console\Commands\Translation;

use App\Repositories\Eloquent\Office\Language\LanguageRepositoryInterface;
use App\Repositories\Eloquent\Office\Translation\TranslationRepositoryInterface;
use Exception;
use Google\Cloud\Translate\V2\TranslateClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TranslateAllElements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:translate-all-elements {--B|baseLanguageCode=en}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Translate all elements';

    protected LanguageRepositoryInterface $languageRepository;
    protected TranslationRepositoryInterface $translationRepository;

    public function __construct(
        LanguageRepositoryInterface    $languageRepository,
        TranslationRepositoryInterface $translationRepository)
    {
        $this->languageRepository = $languageRepository;
        $this->translationRepository = $translationRepository;
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $baseLanguageCode = $this->option('baseLanguageCode');
        $baseLanguage = $this->languageRepository->firstByAttributes([
            ['column' => 'Code', 'operand' => '=', 'value' => $baseLanguageCode]
        ]);
        $otherLanguages = $this->languageRepository->getByAttributes([
            ['column' => 'Code', 'operand' => '!=', 'value' => $baseLanguageCode]
        ]);
        $baseLanguageTranslations = $this->translationRepository->getByAttributes([
            ['column' => 'LanguageId', 'operand' => '=', 'value' => $baseLanguage['Id']]
        ]);
        foreach ($otherLanguages as $otherLanguage) {
            $otherLanguageTranslations = $this->translationRepository->getByAttributes([
                ['column' => 'LanguageId', 'operand' => '=', 'value' => $otherLanguage['Id']]
            ]);
            foreach ($baseLanguageTranslations as $baseLanguageTranslation) {
                $otherLanguageTranslation = $otherLanguageTranslations
                    ->where('Type', $baseLanguageTranslation['Type'])
                    ->where('ElementName', $baseLanguageTranslation['ElementName'])->first();
                if ($otherLanguageTranslation) {
                    // Check for missing keys
                    // If any get the translation
                    foreach ($baseLanguageTranslation['Translations'] as $key => $translation) {
                        if (!isset($otherLanguageTranslation['Translations'][$key])) {
                            Log::debug("Missing Key: $key Element: {$baseLanguageTranslation['ElementName']} Language: {$otherLanguage["Name"]} Code: {$otherLanguage["Code"]}");
                            $newTranslation = $otherLanguageTranslation['Translations'];
                            $newTranslation[$key] = $this->translateText($baseLanguageTranslation['Translations'][$key], $otherLanguage['Code']); // Todo
                            $otherLanguageTranslation->update([
                                'Translations' => $newTranslation
                            ]);
                        }
                    }
                } else {
                    Log::debug("Missing Element: {$baseLanguageTranslation['ElementName']} Language: {$otherLanguage["Name"]} Code: {$otherLanguage["Code"]}");
                    $newTranslations = [];
                    foreach ($baseLanguageTranslation['Translations'] as $key => $translation) {
                        $newTranslations[$key] = $translation;
                    }

                    $this->translationRepository->create([
                        "LanguageId" => $otherLanguage['Id'],
                        "Type" => $baseLanguageTranslation['Type'],
                        "ElementName" => $baseLanguageTranslation['ElementName'],
                        "Translations" => $newTranslations
                    ]);

                    // Get Translations For all elements and save to translation table with all data
                    //dd($otherLanguageTranslation);
                }
            }
        }
    }

    private function translateText($text, $targetLanguageCode)
    {
        try {
            // Creates a client
            $translate = new TranslateClient([
                'keyFilePath' => storage_path("app/public/gcp/nsales-translations.json") // Set the path to your service account JSON file
            ]);

            // Translates the text
            $result = $translate->translate($text, [
                'target' => $targetLanguageCode
            ]);
            return $result['text'];
        } catch (Exception $exception) {
            Log::error("Translate Error: {$exception->getMessage()}");
            return "";
        }
    }
}
