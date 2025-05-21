<?php

use Google\Cloud\Translate\V2\TranslateClient;
use Illuminate\Support\Facades\Log;

function getTranslation($text, $targetLanguageCode): string
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
