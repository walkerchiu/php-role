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
     * @param Bool  $is_enabled
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
     * @param Bool  $role_is_enabled
     * @param Bool  $permission_is_enabled
     * @return \Illuminate\Support\Collection
     */
    public function getIdentifiersOfPermissions($role_is_enabled = true, $permission_is_enabled = true)
    {
        return $this->roles()
                    ->unless(is_null($role_is_enabled), function ($query) use ($role_is_enabled) {
                        return $query->where('is_enabled', $role_is_enabled);
                    })
                    ->get()
                    ->map( function ($role, $key) use ($permission_is_enabled) {
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
     * @param String|Array  $value
     * @return Bool
     */
    public function hasRole($value): bool
    {
        if (is_string($value)) {
            return $this->roles()->where('identifier', $value)
                                 ->exists();
        } elseif (is_array($value)) {
            return $this->roles()->whereIn('identifier', $value)
                                 ->exists();
        }

        return false;
    }

    /**
     * Checks if the user has roles in the same time.
     *
     * @param Array  $roles
     * @return Bool
     */
    public function hasRoles(array $roles): bool
    {
        $result = false;

        foreach ($roles as $role) {
            $result = $this->roles()->where('identifier', $role)
                                    ->exists();
            if (!$result) {
                break;
            }
        }

        return $result;
    }

    /**
     * Check if user has permissions in the same time.
     *
     * @param String|Array  $value
     * @return Bool
     */
    public function canDo($value)
    {
        $result = false;
        $roles = $this->roles;

        if (is_string($value)) {
            foreach ($roles as $role) {
                $result = $this->permissions()->where('identifier', $value)
                                            ->exists();
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
     * @param Mixed  $role
     * @return void
     */
    public function attachRole($role): void
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
     * @param Mixed  $role
     * @return void
     */
    public function detachRole($role): void
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
     * @param Mixed  $roles
     * @return void
     */
    public function attachRoles($roles): void
    {
        foreach ($roles as $role) {
            $this->detachRole($role);
            $this->attachRole($role);
        }
    }

    /**
     * Detach multiple roles from a user
     *
     * @param Mixed  $roles
     * @return void
     */
    public function detachRoles($roles = null): void
    {
        if (!$roles) {
            $roles = $this->roles()->get();
        }

        foreach ($roles as $role) {
            $this->detachRole($role);
        }
    }
}
