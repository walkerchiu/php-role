<?php

namespace WalkerChiu\Role\Models\Entities;

use WalkerChiu\Core\Models\Entities\Entity;
use WalkerChiu\Core\Models\Entities\LangTrait;

class Role extends Entity
{
    use LangTrait;

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = array())
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
        if (config('wk-core.onoff.core-lang_core') || config('wk-role.onoff.core-lang_core'))
            return config('wk-core.class.core.langCore');
        else
            return config('wk-core.class.role.roleLang');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function langs()
    {
        if (config('wk-core.onoff.core-lang_core') || config('wk-role.onoff.core-lang_core'))
            return $this->langsCore();
        else
            return $this->hasMany(config('wk-core.class.role.roleLang'), 'morph_id', 'id');
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
     * @param String|Array $value
     * @return Boolean
     */
    public function hasPermission($value)
    {
        if (is_string($value)) {
            return $this->permissions->where('identifier', $value)
                                     ->count() > 0 ? true : false;
        } elseif (is_array($value)) {
            return $this->permissions->whereIn('identifier', $value)
                                     ->count() > 0 ? true : false;
        }

        return false;
    }

    /**
     * Checks if the role has permissions in the same time.
     *
     * @param Array $value
     * @return Boolean
     */
    public function hasPermissions(Array $permissions)
    {
        $result = false;

        foreach ($permissions as $permission) {
            $result = $this->permissions->where('identifier', $value)
                                        ->count() > 0 ? true : false;
            if (!$result) {
                break;
            }
        }

        return $result;
    }

    /**
     * Alias to eloquent many-to-many relation's attach() method.
     *
     * @param mixed $role
     * @return None
     */
    public function attachPermission($permission)
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
     * @param mixed $role
     * @return None
     */
    public function detachPermission($permission)
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
     * @param mixed $roles
     * @return None
     */
    public function attachPermissions($permissions)
    {
        foreach ($permissions as $permission) {
            $this->attachPermission($permission);
        }
    }

    /**
     * Detach multiple permissions from current role
     *
     * @param mixed $roles
     * @return None
     */
    public function detachPermissions($permissions = null)
    {
        if (!$permissions) {
            $permissions = $this->permissions()->get();
        }

        foreach ($permissions as $permission) {
            $this->detachPermission($permission);
        }
    }
}
