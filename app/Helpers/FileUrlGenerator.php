<?php

namespace App\Helpers;

use App\Models\Company\Item;
use App\Models\Office\Company;
use App\Services\Company\CompanyService;
use Illuminate\Support\Facades\Session;

class FileUrlGenerator
{
    /**
     * @param $elementNumber
     * @param null $model
     * @return array
     */
    public static function getItemImageUrlByElementNumber($elementNumber, $model = null): array
    {
        $imageUrlKeys = CompanyService::getSettingsKeys('Item', 'ImageUrl.');
        $imageUrls = [];
        foreach ($imageUrlKeys as $key => $imageUrlKey) {
            $imageUrls[str_replace('ImageUrl.', '', $imageUrlKey)] = Self::imageUrlByElementNumber($elementNumber, $model, $imageUrlKey);
        }

        return $imageUrls;
    }

    /**
     * @param $elementNumber
     * @param null $model
     * @param string $size ThumbnailSmall|Preview|Fullsize|ThumbnailLarge
     * @return string
     */
    public static function imageUrlByElementNumber($elementNumber, $model = null, string $size = 'ImageUrl.Fullsize'): string
    {
        //$selected_company = Cache::get('company_'.request()->get('CompanyId'));
        $image_url = CompanyService::getSettingValue('Item', $size);

        preg_match_all('/\[(\w*)\]/', $image_url, $matches);
        if ($model) {
            foreach ($matches[1] as $index => $match) {
                $column = ucwords($match);
                if (isset($model->$column)) {
                    $image_url = str_replace($matches[0][$index], $model->$column, $image_url);
                }
            }
        } else {
            foreach ($matches[1] as $index => $match) {
                $image_url = str_replace($matches[0][$index], $elementNumber, $image_url);
            }
        }

        return $image_url;
    }

    /**
     * @param Company $company
     * @param $file_path
     * @return string
     */
    public static function fileUrlByCompanyAndFilePath(Company $company, $file_path)
    {
        $selected_company = Session::get('selected_company');
        $file_url = '';
        if ($selected_company->imageHostAccount) {
            if (strpos($selected_company->imageHostAccount->Home, 'b-cdn')) {
                $file_url = "{$selected_company->imageHostAccount->Home}/$file_path";
            } else {
                $file_url = "https://" . $company->imageHostAccount->FTPDomainName . ".nsales.pics/" . $file_path;
            }
        }
        return $file_url;
    }


    /**
     * @param Item $item
     * @return array
     */
    public static function generateImageUrlsForShopify(Item $item): array
    {
        $image_urls = [];
        $selected_company = Session::get('selected_company');
        //dd($selected_company->module_settings['Shopify']['ImageUrls']);
        $setting_value = json_decode($selected_company->module_settings['Shopify']['ImageUrls'], true);
        // dd($setting_value);

        foreach ($setting_value['urls'] as $url) {
            if ($url) {
                $image_urls [] = self::findAndReplaceForShopifyImageUrl($url, $item);
            }
        }
        return $image_urls;
    }

    private static function findAndReplaceForShopifyImageUrl($url, Item $item): string
    {
        $item_columns = [
            'Number',
            'AltItemNumber',
            'Barcode'
        ];
        $formatted_url = "";
        preg_match('#\[(.*?)]#', $url, $match);
        if (array_key_exists(0, $match) && array_key_exists(1, $match)) {
            $replace_to = $match[0];
            $replace_field = strtolower($match[1]);
            $replace_value = '';

            foreach ($item_columns as $column) {
                if (strtolower($column) == $replace_field) {
                    $replace_value = $item->{$column};
                    break;
                }
            }
            $formatted_url = str_replace($replace_to, $replace_value, $url);
        }

        return str_replace('\/', '/', $formatted_url);
    }


    public static function itemgroupImageUrl($systemKey): string
    {
        $selected_company = Session::get('selected_company');
        $image_url = '';

        if ($selected_company->imageHostAccount) {
            if (strpos($selected_company->imageHostAccount->Home, 'b-cdn')) {
                $image_url = "{$selected_company->imageHostAccount->Home}/itemgroup/$systemKey.jpg";
            } else {
                $image_url = "https://" . $selected_company->imageHostAccount->FTPDomainName .
                    ".nsales.pics/itemgroup/" . $systemKey . ".jpg";
                $image_url = $image_url . "?id=" . uniqid();
            }
        }

        return $image_url;
    }
}
