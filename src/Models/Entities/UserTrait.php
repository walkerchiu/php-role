<?php

namespace WalkerChiu\Role\Models\Entities;

trait UserTrait
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(config('wk-core.class.role.role'),
                                    config('wk-core.table.role.users_roles'),
                                    'user_id',
                                    'role_id');
    }

    /**
     * Get all roles.
     *
     * @param Boolean $is_enabled
     * @return \Illuminate\Support\Collection
     */
    public function getIdentifiersOfRoles($is_enabled = true)
    {
        return $this->roles()
                    ->unless(is_null($is_enabled), function ($query) use ($is_enabled) {
                        return $query->where('is_enabled', $is_enabled);
                    })
                    ->pluck('identifier');
    }

    /**
     * Get all permissions.
     *
     * @param Boolean $role_is_enabled
     * @param Boolean $permission_is_enabled
     * @return \Illuminate\Support\Collection
     */
    public function getIdentifiersOfPermissions($role_is_enabled = true, $permission_is_enabled = true)
    {
        return $this->roles()
                    ->unless(is_null($role_is_enabled), function ($query) use ($role_is_enabled) {
                        return $query->where('is_enabled', $role_is_enabled);
                    })
                    ->get()
                    ->map(function ($role, $key) use ($permission_is_enabled) {
                        return $role->permissions()
                                    ->unless(is_null($permission_is_enabled), function ($query) use ($permission_is_enabled) {
                                        return $query->where('is_enabled', $permission_is_enabled);
                                    })
                                    ->pluck('identifier');
                    })
                    ->collapse()
                    ->unique();
    }

    /**
     * Checks if the user has a role.
     *
     * @param String|Array $value
     * @return Boolean
     */
    public function hasRole($value)
    {
        if (is_string($value)) {
            return $this->roles->where('identifier', $value)
                               ->count() > 0 ? true : false;
        } elseif (is_array($value)) {
            return $this->roles->whereIn('identifier', $value)
                               ->count() > 0 ? true : false;
        }

        return false;
    }

    /**
     * Checks if the user has roles in the same time.
     *
     * @param Array $roles
     * @return Boolean
     */
    public function hasRoles(Array $roles)
    {
        $result = false;

        foreach ($roles as $role) {
            $result = $this->roles->where('identifier', $role)
                                  ->count() > 0 ? true : false;
            if (!$result) {
                break;
            }
        }

        return $result;
    }

    /**
     * Check if user has permissions in the same time.
     *
     * @param String|Array $value
     * @return Boolean
     */
    public function canDo($value)
    {
        $result = false;
        $roles = $this->roles;

        if (is_string($value)) {
            foreach ($roles as $role) {
                $result = $this->permissions->where('identifier', $value)
                                            ->count() > 0 ? true : false;
                if ($result) {
                    break;
                }
            }
        } elseif (is_array($value)) {
            foreach ($value as $permission) {
                $result = $this->can($permission);
                if (!$result) {
                    break;
                }
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
    public function attachRole($role)
    {
        if(is_object($role)) {
            $role = $role->getKey();
        }

        if(is_array($role)) {
            $role = $role['id'];
        }

        $this->roles()->detach($role);
        $this->roles()->attach($role);
    }

    /**
     * Alias to eloquent many-to-many relation's detach() method.
     *
     * @param mixed $role
     * @return None
     */
    public function detachRole($role)
    {
        if (is_object($role)) {
            $role = $role->getKey();
        }

        if (is_array($role)) {
            $role = $role['id'];
        }

        $this->roles()->detach($role);
    }

    /**
     * Attach multiple roles to a user
     *
     * @param mixed $roles
     * @return None
     */
    public function attachRoles($roles)
    {
        foreach ($roles as $role) {
            $this->detachRole($role);
            $this->attachRole($role);
        }
    }

    /**
     * Detach multiple roles from a user
     *
     * @param mixed $roles
     * @return None
     */
    public function detachRoles($roles = null)
    {
        if (!$roles) {
            $roles = $this->roles()->get();
        }

        foreach ($roles as $role) {
            $this->detachRole($role);
        }
    }
}
