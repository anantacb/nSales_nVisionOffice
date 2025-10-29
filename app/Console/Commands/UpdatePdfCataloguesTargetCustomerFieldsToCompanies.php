<?php

namespace App\Console\Commands;

use App\Models\Company\PdfCatalogue;
use App\Repositories\Eloquent\Office\Company\CompanyRepositoryInterface;
use App\Services\Company\CompanyService;
use App\Services\Traits\ModuleHelperTrait;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdatePdfCataloguesTargetCustomerFieldsToCompanies extends Command
{
    use ModuleHelperTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pdf-catalogue:update-target-customer-columns';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update PdfCatalogue table target customer columns in all companies.';

    public function __construct(
        protected CompanyRepositoryInterface $companyRepository
    )
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public
    function handle(): int
    {
        $filter = [];
        $relationFilter = [
            [
                "relation" => "modules", "column" => "Name", "operator" => "=", "values" => "PdfCatalogue"
            ]
        ];
        $companies = $this->companyRepository->getByAttributes($filter, [], '', 'Name', false, $relationFilter);

        $bar = $this->output->createProgressBar($companies->count());
        $bar->start();

        $statusHeaders = ['Id', 'Name', 'TotalRows', 'UpdatedRows', 'Status'];
        $statusRows = [];


        foreach ($companies as $company) {
            $status = 'Success';
            $total = 0;
            $updated = 0;
            try {
                CompanyService::setCompanyDatabaseConnection($company->Id);
                $pdfCataloguesToUpdate = PdfCatalogue::whereAny([
                    'CustomerAccount',
                    'Country',
                    'Statisticsgroup',
                    'Chain',
                    'Channel',
                ], '!=', null)->get();
                $total = $pdfCataloguesToUpdate->count();
                foreach ($pdfCataloguesToUpdate as $pdfCatalogue) {
                    try {
                        $pdfCatalogue->update([
                            'CustomerAccount' => $pdfCatalogue->CustomerAccount ? json_encode([$pdfCatalogue->CustomerAccount]) : null,
                            'Country' => $pdfCatalogue->Country ? json_encode([$pdfCatalogue->Country]) : null,
                            'Statisticsgroup' => $pdfCatalogue->Statisticsgroup ? json_encode([$pdfCatalogue->Statisticsgroup]) : null,
                            'Chain' => $pdfCatalogue->Chain ? json_encode([$pdfCatalogue->Chain]) : null,
                            'Channel' => $pdfCatalogue->Channel ? json_encode([$pdfCatalogue->Channel]) : null,
                        ]);
                        $updated++;
                    } catch (Exception $e) {
                        Log::error("Error updating target customer columns for company {$company->Name}({$company->DomainName}) {$company->Id} Pdf: {$pdfCatalogue->Id}: " . $e->getMessage());
                    }
                }
            } catch (Exception $e) {
                Log::error("Error updating target customer columns for company {$company->Name}({$company->DomainName}) {$company->Id}: " . $e->getMessage());
                $status = 'Failed';
            } finally {
                $statusRows[] = [$company->Id, $company->Name, $total, $updated, $status];
                $bar->advance();
            }

        }

        $bar->finish();
        $this->info(PHP_EOL);
        $this->table($statusHeaders, $statusRows);

        return Command::SUCCESS;
    }
}
