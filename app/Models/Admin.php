<?php

namespace App\Models;

use App\Enums\UserStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;

#[Fillable(['name', 'email', 'password', 'image', 'bio', 'status', 'forget_password_token'])]
#[Hidden(['password', 'remember_token'])]
class Admin extends Authenticatable
{
    use HasFactory, HasApiTokens, HasRoles, Notifiable;

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'status' => \App\Enums\UserStatusEnum::class,
        ];
    }

    protected $guard_name = 'admin';

    public static function getPermissionGroup()
    {
        return DB::table('permissions')
            ->select('group_name as name')
            ->groupBy('group_name')
            ->get();
    }

    public static function getpermissionsByGroupName($group_name)
    {
        return DB::table('permissions')
            ->select('name', 'id')
            ->where('group_name', $group_name)
            ->get();
    }

    public static function roleHasPermission($role, $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (! $role->hasPermissionTo($permission->name)) {
                return false;
            }
        }

        return true;
    }

    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class, 'admin_id');
    }

}
