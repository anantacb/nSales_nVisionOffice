<?php

namespace App\Services\Role;


use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Office\Role\RoleRepositoryInterface;
use Illuminate\Http\Request;

class RoleService implements RoleServiceInterface
{
    protected RoleRepositoryInterface $roleRepository;

    public function __construct(RoleRepositoryInterface $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    public function getAssignableRolesByCompany(Request $request): ServiceDto
    {
        $filters = [
            ['column' => 'CompanyId', 'operand' => '=', 'value' => $request->get('CompanyId')]
        ];
        if (!$request->get('WithDeveloper')) {
            $filters[] = ['column' => 'Type', 'operand' => '!=', 'value' => 'Developer'];
        }
        $roles = $this->roleRepository->getByAttributes($filters, '', ['Id', 'Name', 'Type', 'CompanyId']);

        return new ServiceDto("Roles retrieved!!!", 200, $roles);
    }

    public function getCompanyRoles(Request $request): ServiceDto
    {
        $request = $request->all();
        $roles = $this->roleRepository->paginatedDataCompanyWise($request);
        return new ServiceDto("Roles retrieved!!!", 200, $roles);
    }

    public function create(Request $request): ServiceDto
    {
        $role = $this->roleRepository->create([
            'CompanyId' => $request->get('CompanyId'),
            'Name' => $request->get('Name'),
            'Type' => $request->get('Type'),
            'Description' => $request->get('Description'),
        ]);
        return new ServiceDto("Role Created Successfully.", 200, $role);
    }

    public function update(Request $request): ServiceDto
    {
        $role = $this->roleRepository->findByIdAndUpdate(
            $request->get('Id'),
            [
                'Name' => $request->get('Name'),
                'Description' => $request->get('Description')
            ]
        );
        return new ServiceDto("Role Updated Successfully.", 200, $role);
    }

    public function delete(Request $request): ServiceDto
    {
        $this->roleRepository->findByIdAndDelete($request->get('RoleId'));
        return new ServiceDto("Role Deleted Successfully.", 200);
    }

    public function details(Request $request): ServiceDto
    {
        $relations = [
            'company' => function ($q) {
                $q->select(['Id', 'Name', 'CompanyName']);
            }
        ];

        $role = $this->roleRepository->firstByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $request->get('RoleId')]
        ], $relations);

        return new ServiceDto("DataFilter Retrieved Successfully.", 200, $role);
    }
}
