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
}
