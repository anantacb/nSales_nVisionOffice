<?php

namespace App\Models\Office;

use App\Models\Traits\ModelHelper;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, ModelHelper;

    public const CREATED_AT = 'InsertTime';
    public const UPDATED_AT = 'UpdateTime';

    protected $table = 'User';
    protected $primaryKey = 'Id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'Hash',
        'Salt',
        'Disabled',
        'AllowResetPassword',
        'FailedAttemptCount',
        'IsLocked'
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function companyUsers(): HasMany
    {
        return $this->hasMany(CompanyUser::class, 'UserId', 'Id');
    }

    public function companyUser(): HasOne
    {
        return $this->hasOne(CompanyUser::class, 'UserId', 'Id');
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'CompanyUser', 'UserId', 'CompanyId');
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class, 'UserId', 'Id');
    }

    /**
     * Whether the user holds a permission slug within a given company.
     *
     * Mirrors the UserHasPermission middleware: Developer passes everything; Administrator passes
     * everything except developer-only permissions; everyone else needs the explicit grant.
     */
    public function hasPermission(string $slug, int $companyId): bool
    {
        $companyUser = $this->companyUsers->firstWhere('CompanyId', $companyId);
        if (!$companyUser) {
            return false;
        }

        $roleTypes = $companyUser->roles->pluck('Type')->all();

        if (in_array('Developer', $roleTypes, true)) {
            return true;
        }

        $developerOnly = Cache::remember('PermissionCatalog-DeveloperOnly', 3600, function () {
            return Permission::where('IsDeveloperOnly', 1)->pluck('Aliases')->all();
        });
        if (in_array($slug, $developerOnly, true)) {
            return false;
        }

        if (in_array('Administrator', $roleTypes, true)) {
            return true;
        }

        return $companyUser->roles
            ->pluck('permissions')
            ->flatten()
            ->contains(fn ($permission) => $permission->Aliases === $slug);
    }

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
