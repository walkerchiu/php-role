<?php

namespace WalkerChiu\Role\Models\Entities;

use WalkerChiu\Core\Models\Entities\LangTrait;
use WalkerChiu\Core\Models\Entities\UuidEntity;

class Role extends UuidEntity
{
    use LangTrait;



    /**
     * Create a new instance.
     *
     * @param Array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->table = config('wk-core.table.role.roles');

        $this->fillable = array_merge($this->fillable, [
            'host_type', 'host_id',
            'serial', 'identifier'
        ]);

        parent::__construct($attributes);
    }

    /**
     * Get it's lang entity.
     *
     * @return Lang
     */
    public function lang()
    {
        if (
            config('wk-core.onoff.core-lang_core')
            || config('wk-role.onoff.core-lang_core')
        ) {
            return config('wk-core.class.core.langCore');
        } else {
            return config('wk-core.class.role.roleLang');
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function langs()
    {
        if (
            config('wk-core.onoff.core-lang_core')
            || config('wk-role.onoff.core-lang_core')
        ) {
            return $this->langsCore();
        } else {
            return $this->hasMany(config('wk-core.class.role.roleLang'), 'morph_id', 'id');
        }
    }

    /**
     * Get the owning host model.
     */
    public function host()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(config('wk-core.class.user'),
                                    config('wk-core.table.role.users_roles'),
                                    'role_id',
                                    'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function permissions()
    {
        return $this->belongsToMany(config('wk-core.class.role.permission'),
                                    config('wk-core.table.role.roles_permissions'),
                                    'role_id',
                                    'permission_id');
    }

    /**
     * Checks if the role has a permission.
     *
     * @param String|Array  $value
     * @return Bool
     */
    public function hasPermission($value): bool
    {
        if (is_string($value)) {
            return $this->permissions()->where('identifier', $value)
                                       ->exists();
        } elseif (is_array($value)) {
            return $this->permissions()->whereIn('identifier', $value)
                                       ->exists();
        }

        return false;
    }

    /**
     * Checks if the role has permissions in the same time.
     *
     * @param Array  $value
     * @return Bool
     */
    public function hasPermissions(array $permissions): bool
    {
        $result = false;

        foreach ($permissions as $permission) {
            $result = $this->permissions()->where('identifier', $value)
                                          ->exists();
            if (!$result) {
                break;
            }
        }

        return $result;
    }

    /**
     * Alias to eloquent many-to-many relation's attach() method.
     *
     * @param Mixed  $role
     * @return void
     */
    public function attachPermission($permission): void
    {
        if (is_object($permission)) {
            $permission = $permission->getKey();
        }

        if (is_array($permission)) {
            $permission = $permission['id'];
        }

        $this->perms()->attach($permission);
    }

    /**
     * Alias to eloquent many-to-many relation's detach() method.
     *
     * @param Mixed  $role
     * @return void
     */
    public function detachPermission($permission): void
    {
        if (is_object($permission)) {
            $permission = $permission->getKey();
        }

        if (is_array($permission)) {
            $permission = $permission['id'];
        }

        $this->perms()->detach($permission);
    }

    /**
     * Attach multiple permissions to current role.
     *
     * @param Mixed  $roles
     * @return void
     */
    public function attachPermissions($permissions): void
    {
        foreach ($permissions as $permission) {
            $this->attachPermission($permission);
        }
    }

    /**
     * Detach multiple permissions from current role
     *
     * @param Mixed  $roles
     * @return void
     */
    public function detachPermissions($permissions = null): void
    {
        if (!$permissions) {
            $permissions = $this->permissions()->get();
        }

        foreach ($permissions as $permission) {
            $this->detachPermission($permission);
        }
    }
}
