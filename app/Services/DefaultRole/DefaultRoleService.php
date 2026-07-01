<?php

namespace App\Services\DefaultRole;

use App\Contracts\ServiceDto;
use App\Models\Office\Role;
use App\Repositories\Eloquent\Office\Company\CompanyRepositoryInterface;
use App\Repositories\Eloquent\Office\Role\RoleRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DefaultRoleService implements DefaultRoleServiceInterface
{
    protected RoleRepositoryInterface $roleRepository;
    protected CompanyRepositoryInterface $companyRepository;
    protected DefaultRolePermissionProjector $projector;

    public function __construct(
        RoleRepositoryInterface         $roleRepository,
        CompanyRepositoryInterface      $companyRepository,
        DefaultRolePermissionProjector  $projector
    )
    {
        $this->roleRepository = $roleRepository;
        $this->companyRepository = $companyRepository;
        $this->projector = $projector;
    }

    public function getDefaultRoles(Request $request): ServiceDto
    {
        $roles = $this->roleRepository->paginatedDataDefault($request->all());
        return new ServiceDto('Default roles retrieved!!!', 200, $roles);
    }

    public function create(Request $request): ServiceDto
    {
        [$role, $companiesAdded] = DB::transaction(function () use ($request) {
            $role = $this->roleRepository->create([
                'CompanyId'   => null,
                'Name'        => $request->input('Name'),
                'Type'        => $request->input('Type'),
                'Description' => $request->input('Description'),
            ]);

            return [$role, $this->propagateToCompanies($role)];
        });

        return new ServiceDto(
            "Default Role Created Successfully and added to $companiesAdded company(ies).",
            200,
            $role
        );
    }

    /**
     * Fan the newly created default role out to every existing (non-deleted) company by inserting
     * a company-specific copy (CompanyId set) — mirroring CompanyService::createRoles() which does
     * this at company-provisioning time. Idempotent: companies that already own a role with this
     * Name are skipped, so partial state / re-runs never create duplicates.
     */
    private function propagateToCompanies(Role $template): int
    {
        $companyIds = $this->companyRepository->getByAttributes([])->pluck('Id');

        // All roles sharing this Name: the template (CompanyId NULL) plus any existing company copies.
        // Filter out the NULL template so we're left with company IDs that already own the role.
        $alreadyHasIds = $this->roleRepository->getByAttributes([
            ['column' => 'Name', 'operand' => '=', 'value' => $template->Name],
        ])->pluck('CompanyId')->filter(fn ($id) => !is_null($id));

        $targetCompanyIds = $companyIds->diff($alreadyHasIds)->values();

        if ($targetCompanyIds->isEmpty()) {
            return 0;
        }

        $now = Carbon::now();
        $rows = $targetCompanyIds->map(fn ($companyId) => [
            'CompanyId'   => $companyId,
            'Name'        => $template->Name,
            'Type'        => $template->Type,
            'Description' => $template->Description,
            'InsertTime'  => $now,
            'UpdateTime'  => $now,
        ])->all();

        foreach (array_chunk($rows, 500) as $chunk) {
            $this->roleRepository->insert($chunk);
        }

        return count($rows);
    }

    public function update(Request $request): ServiceDto
    {
        $role = $this->roleRepository->findByIdAndUpdate(
            $request->input('Id'),
            [
                'Name'        => $request->input('Name'),
                'Description' => $request->input('Description'),
            ]
        );
        return new ServiceDto('Default Role Updated Successfully.', 200, $role);
    }

    public function details(Request $request): ServiceDto
    {
        $role = $this->roleRepository->firstByAttributes([
            ['column' => 'Id',        'operand' => '=', 'value' => $request->input('RoleId')],
            ['column' => 'CompanyId', 'operand' => '=', 'value' => null],
        ]);
        return new ServiceDto('Default Role Retrieved Successfully.', 200, $role);
    }

    public function delete(Request $request): ServiceDto
    {
        $this->roleRepository->findByIdAndDelete($request->input('RoleId'));
        return new ServiceDto('Default Role Deleted Successfully.', 200);
    }

    public function syncToCompanyRoles(Request $request): ServiceDto
    {
        $template = $this->roleRepository->firstByAttributes([
            ['column' => 'Id',        'operand' => '=', 'value' => $request->input('Id')],
            ['column' => 'CompanyId', 'operand' => '=', 'value' => null],
        ]);

        if (!$template) {
            return new ServiceDto('Default Role not found.', 404);
        }

        $report = $this->projector->project([$template->Type], null, null, false);

        return new ServiceDto($this->summarize($report, $template->Type), 200, $report);
    }

    public function syncAllToCompanyRoles(Request $request): ServiceDto
    {
        $report = $this->projector->project(null, null, null, false);

        return new ServiceDto($this->summarize($report), 200, $report);
    }

    /**
     * @param array{rolesProcessed:int, grantsInserted:int, skippedTypes:string[], unknownSlugs:string[]} $report
     */
    private function summarize(array $report, ?string $type = null): string
    {
        $scope = $type !== null ? "$type template" : 'All templates';

        return sprintf(
            'Synced %s: processed %d company role(s), granted %d new permission(s).',
            $scope,
            $report['rolesProcessed'],
            $report['grantsInserted']
        );
    }
}
