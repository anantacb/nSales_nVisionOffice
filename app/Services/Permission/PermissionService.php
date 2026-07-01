<?php

namespace App\Services\Permission;

use App\Contracts\ServiceDto;
use App\Models\Office\Permission;
use App\Models\Office\Role;
use App\Repositories\Eloquent\Office\Permission\PermissionRepositoryInterface;
use App\Repositories\Eloquent\Office\RolePermission\RolePermissionRepositoryInterface;
use App\Services\Concerns\FlushesUserAccessCache;
use Illuminate\Http\Request;

class PermissionService implements PermissionServiceInterface
{
    use FlushesUserAccessCache;

    protected RolePermissionRepositoryInterface $rolePermissionRepository;
    protected PermissionRepositoryInterface $permissionRepository;

    public function __construct(
        RolePermissionRepositoryInterface $rolePermissionRepository,
        PermissionRepositoryInterface     $permissionRepository
    )
    {
        $this->rolePermissionRepository = $rolePermissionRepository;
        $this->permissionRepository = $permissionRepository;
    }

    public function listAll(Request $request): ServiceDto
    {
        $permissions = Permission::query()
            ->with([
                'module:Id,Name',
            ])
            ->whereNotNull('Aliases')
            ->where('Aliases', '<>', '')
            ->orderBy('ModuleId')
            ->orderBy('Aliases')
            ->get();

        return new ServiceDto('Permissions retrieved!!!', 200, $permissions);
    }

    public function getRolePermissions(Request $request): ServiceDto
    {
        $role = Role::with('permissions:Id')->find($request->input('RoleId'));
        $permissionIds = $role
            ? $role->permissions->pluck('Id')->all()
            : [];

        return new ServiceDto('Role permissions retrieved!!!', 200, [
            'PermissionIds' => $permissionIds,
        ]);
    }

    public function syncRolePermissions(Request $request): ServiceDto
    {
        $roleId = (int) $request->input('RoleId');
        $permissionIds = $request->input('PermissionIds', []);

        $this->rolePermissionRepository->syncForRole($roleId, $permissionIds);

        // Affected users see the new grants immediately rather than after the 60s cache TTL.
        $this->flushAccessCacheForRole($roleId);

        return new ServiceDto('Permissions updated.', 200, [
            'RoleId'        => $roleId,
            'PermissionIds' => array_values(array_unique(array_map('intval', $permissionIds))),
        ]);
    }

    public function getPermissions(Request $request): ServiceDto
    {
        $params = $request->all();
        $params['relations'] = [
            ['name' => 'module', 'columns' => ['Id', 'Name']],
        ];

        $permissions = $this->permissionRepository->paginatedData($params);

        return new ServiceDto('Permissions retrieved!!!', 200, $permissions);
    }

    public function create(Request $request): ServiceDto
    {
        $permission = $this->permissionRepository->create([
            'Aliases' => $request->input('Aliases'),
            'Name' => $request->input('Name'),
            'ModuleId' => $request->input('ModuleId'),
            'Description' => $request->input('Description'),
            'IsDeveloperOnly' => (int) $request->input('IsDeveloperOnly'),
        ]);

        $this->flushPermissionCatalogCache();

        return new ServiceDto('Permission Created Successfully.', 200, $permission);
    }

    public function update(Request $request): ServiceDto
    {
        $permission = $this->permissionRepository->findByIdAndUpdate(
            $request->input('Id'),
            [
                'Aliases' => $request->input('Aliases'),
                'Name' => $request->input('Name'),
                'ModuleId' => $request->input('ModuleId'),
                'Description' => $request->input('Description'),
                'IsDeveloperOnly' => (int) $request->input('IsDeveloperOnly'),
            ]
        );

        $this->flushPermissionCatalogCache();

        return new ServiceDto('Permission Updated Successfully.', 200, $permission);
    }

    public function details(Request $request): ServiceDto
    {
        $permission = $this->permissionRepository->firstByAttributes(
            [['column' => 'Id', 'operand' => '=', 'value' => $request->input('Id')]],
            ['module:Id,Name']
        );

        return new ServiceDto('Permission Retrieved Successfully.', 200, $permission);
    }

    public function delete(Request $request): ServiceDto
    {
        $this->permissionRepository->findByIdAndDelete($request->input('Id'));

        $this->flushPermissionCatalogCache();

        return new ServiceDto('Permission Deleted Successfully.', 200, []);
    }
}
