<?php

namespace App\Services\DefaultRole;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Office\Role\RoleRepositoryInterface;
use Illuminate\Http\Request;

class DefaultRoleService implements DefaultRoleServiceInterface
{
    protected RoleRepositoryInterface $roleRepository;

    public function __construct(RoleRepositoryInterface $roleRepository)
    {
        $this->roleRepository = $roleRepository;
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
}
