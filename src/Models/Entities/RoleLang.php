<?php

namespace WalkerChiu\Role\Models\Entities;

use WalkerChiu\Core\Models\Entities\Lang;

class RoleLang extends Lang
{
    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = array())
    {
        $this->table = config('wk-core.table.role.roles_lang');

        parent::__construct($attributes);
    }
}
