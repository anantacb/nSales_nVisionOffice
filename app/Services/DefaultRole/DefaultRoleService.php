<?php

namespace App\Services\DefaultRole;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Office\Role\RoleRepositoryInterface;
use Illuminate\Http\Request;

class DefaultRoleService implements DefaultRoleServiceInterface
{
    protected RoleRepositoryInterface $roleRepository;
    protected DefaultRolePermissionProjector $projector;

    public function __construct(
        RoleRepositoryInterface         $roleRepository,
        DefaultRolePermissionProjector  $projector
    )
    {
        $this->roleRepository = $roleRepository;
        $this->projector = $projector;
    }

    public function getDefaultRoles(Request $request): ServiceDto
    {
        $roles = $this->roleRepository->paginatedDataDefault($request->all());
        return new ServiceDto('Default roles retrieved!!!', 200, $roles);
    }

    public function create(Request $request): ServiceDto
    {
        $role = $this->roleRepository->create([
            'CompanyId'   => null,
            'Name'        => $request->input('Name'),
            'Type'        => $request->input('Type'),
            'Description' => $request->input('Description'),
        ]);
        return new ServiceDto('Default Role Created Successfully.', 200, $role);
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
