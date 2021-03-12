<?php

namespace WalkerChiu\Role\Models\Entities;

use WalkerChiu\Core\Models\Entities\Lang;

class PermissionLang extends Lang
{
    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = array())
    {
        $this->table = config('wk-core.table.role.permissions_lang');

        parent::__construct($attributes);
    }
}
