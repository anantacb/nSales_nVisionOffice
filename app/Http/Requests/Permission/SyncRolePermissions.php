<?php

namespace App\Http\Requests\Permission;

use App\Models\Office\Permission;
use App\Models\Office\Role;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class SyncRolePermissions extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'RoleId'          => 'required|integer|exists:Role,Id',
            'PermissionIds'   => 'present|array',
            'PermissionIds.*' => 'integer|exists:Permission,Id',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $role = Role::find($this->input('RoleId'));
            if (!$role || $role->Type === 'Developer') {
                return;
            }

            $submittedIds = array_map('intval', (array) $this->input('PermissionIds', []));
            if (empty($submittedIds)) {
                return;
            }

            $developerOnlyHit = Permission::whereIn('Id', $submittedIds)
                ->where('IsDeveloperOnly', 1)
                ->pluck('Aliases')
                ->all();

            if (!empty($developerOnlyHit)) {
                $validator->errors()->add(
                    'PermissionIds',
                    'Developer-only permissions cannot be granted to a non-Developer role: '
                        . implode(', ', $developerOnlyHit)
                );
            }
        });
    }
}
