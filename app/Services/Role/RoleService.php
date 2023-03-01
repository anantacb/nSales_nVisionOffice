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
        $roles = $this->roleRepository->getByAttributes([
            ['column' => 'CompanyId', 'operand' => '=', 'value' => $request->get('CompanyId')],
            ['column' => 'Type', 'operand' => '!=', 'value' => 'Developer']
        ], '', ['Id', 'Name', 'Type', 'CompanyId']);

        return new ServiceDto("Roles retrieved!!!", 200, $roles);
    }
}
