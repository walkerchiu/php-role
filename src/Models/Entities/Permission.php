<?php

namespace WalkerChiu\Role\Models\Entities;

use WalkerChiu\Core\Models\Entities\Entity;
use WalkerChiu\Core\Models\Entities\LangTrait;

class Permission extends Entity
{
    use LangTrait;

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = array())
    {
        $this->table = config('wk-core.table.role.permissions');
        $this->fillable = array_merge($this->fillable, [
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
            return config('wk-core.class.role.permissionLang');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function langs()
    {
        if (config('wk-core.onoff.core-lang_core') || config('wk-role.onoff.core-lang_core'))
            return $this->langsCore();
        else
            return $this->hasMany(config('wk-core.class.role.permissionLang'), 'morph_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function roles()
    {
        return $this->belongsToMany(config('wk-core.class.role.role'),
                                    config('wk-core.table.role.roles_permissions'),
                                    'permission_id',
                                    'role_id');
    }
}
