<?php

namespace App\Repositories\Eloquent\Office\RolePermission;

use App\Models\Office\RolePermission;
use App\Repositories\Eloquent\Base\BaseRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RolePermissionRepository extends BaseRepository implements RolePermissionRepositoryInterface
{
    public function __construct(RolePermission $model)
    {
        parent::__construct($model);
    }

    public function syncForRole(int $roleId, array $permissionIds): void
    {
        DB::transaction(function () use ($roleId, $permissionIds) {
            RolePermission::where('RoleId', $roleId)->delete();

            $uniqueIds = array_values(array_unique(array_map('intval', $permissionIds)));
            if (empty($uniqueIds)) {
                return;
            }

            $now = Carbon::now();
            $rows = array_map(fn ($pid) => [
                'RoleId'       => $roleId,
                'PermissionId' => $pid,
                'InsertTime'   => $now,
                'UpdateTime'   => $now,
            ], $uniqueIds);

            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('RolePermission')->insert($chunk);
            }
        });
    }
}
