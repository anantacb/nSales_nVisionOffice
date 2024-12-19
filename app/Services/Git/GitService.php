<?php

namespace App\Services\Git;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Office\Company\CompanyRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GitService implements GitServiceInterface
{
    private CompanyRepositoryInterface $companyRepository;

    public function __construct(CompanyRepositoryInterface $companyRepository)
    {
        $this->companyRepository = $companyRepository;
    }

    public function getCompanyBranches(Request $request): ServiceDto
    {
        if (Cache::has("company_" . $request->get("CompanyId"))) {
            $company = Cache::get("company_" . $request->get("CompanyId"));
        } else {
            $company = $this->companyRepository->findById($request->get("CompanyId"));
        }

        $companyBranches = [
            "company/dev/$company->DomainName",
            "company/prod/$company->DomainName"
        ];

        $response = [];

        $owner = "nsales-dev";
        $repo = "new-b2b-frontend";
        foreach ($companyBranches as $companyBranch) {
            $data = $this->getGithubBranch($owner, $repo, $companyBranch);
            if ($data) {
                $response[$companyBranch] = $data;
            }
        }

        return new ServiceDto("Company Branches Retrieved Successfully.", 200, $response);
    }

    public function createCompanyBranches(Request $request): ServiceDto
    {
        if (Cache::has("company_" . $request->get("CompanyId"))) {
            $company = Cache::get("company_" . $request->get("CompanyId"));
        } else {
            $company = $this->companyRepository->findById($request->get("CompanyId"));
        }

        $companyBranches = [
            "company/dev/$company->DomainName",
            "company/prod/$company->DomainName"
        ];

        $baseBranches = [
            "company/dev/b2bmaster",
            "company/prod/b2bmaster"
        ];

        $owner = "nsales-dev";
        $repo = "new-b2b-frontend";

        $baseBranchesDetails = [];
        foreach ($baseBranches as $baseBranch) {
            $data = $this->getGithubBranch($owner, $repo, $baseBranch);
            if ($data) {
                $baseBranchesDetails[$baseBranch] = $data;
            }
        }

        foreach ($companyBranches as $companyBranch) {
            $data = $this->getGithubBranch($owner, $repo, $companyBranch);
            if (!$data) {
                if (str_contains($companyBranch, "company/prod/")) {
                    $baseBranch = $baseBranchesDetails["company/prod/b2bmaster"] ?? null;
                } else {
                    $baseBranch = $baseBranchesDetails["company/dev/b2bmaster"] ?? null;
                }

                if (!$baseBranch) {
                    throw new \Exception("Base branch for $companyBranch not found!", 404);
                }

                $this->createGithubBranch($owner, $repo, $baseBranch, $companyBranch);
            }
        }

        return new ServiceDto("Company Branches Created Successfully.", 200);
    }

    private function getGithubBranch($owner, $repo, $branch)
    {
        $response = Http::withHeader("Authorization", "Bearer " . env("GITHUB_AUTH_TOKEN"))
            ->get("https://api.github.com/repos/$owner/$repo/branches/$branch");

        $response = $response->json();

        if (isset($response["status"])) {
            if($response["status"] == "404") {
                return null;
            } else if($response["status"] == "401") {
                throw new \Exception("Github Token Expired!", 400);
            } else {
                throw new \Exception($response["message"], 400);
            }
        }

        return $response;
    }

    private function createGithubBranch($owner, $repo, $baseBranch, $newBranchName)
    {
        $response = Http::withHeader("Authorization", "Bearer " . env("GITHUB_AUTH_TOKEN"))
            ->post("https://api.github.com/repos/$owner/$repo/git/refs", [
                "ref" => "refs/heads/" . $newBranchName,
                "sha" => $baseBranch["commit"]["sha"]
            ]);

        return $response->json();
    }
}
