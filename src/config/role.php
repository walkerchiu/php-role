<?php

/**
 * @license MIT
 * @package WalkerChiu\Role
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Switch association of package to On or Off
    |--------------------------------------------------------------------------
    |
    | When you set someone On:
    |     1. Its Foreign Key Constraints will be created together with data table.
    |     2. You may need to change the corresponding class settings in the config/wk-core.php.
    |
    | When you set someone Off:
    |     1. Association check will not be performed on FormRequest and Observer.
    |     2. Cleaner and Initializer will not handle tasks related to it.
    |
    | Note:
    |     The association still exists, which means you can still access related objects.
    |
    */
    'onoff' => [
        'core-lang_core' => 0,

        'user' => 1,

        'group'          => 0,
        'morph-category' => 0,
        'morph-image'    => 0,
        'rule'           => 0,
        'rule-hit'       => 0,
        'site'           => 0
    ],

    /*
    |--------------------------------------------------------------------------
    | Lang Log
    |--------------------------------------------------------------------------
    |
    | 0: Don't keep data.
    | 1: Keep data.
    |
    */
    /* If it is enabled, all packages will use it,
       otherwise it will only be used when the specified package is not enabled.*/
    'lang_log' => 0,

    /*
    |--------------------------------------------------------------------------
    | Command
    |--------------------------------------------------------------------------
    |
    | Location of Commands.
    |
    */
    'command' => [
        'cleaner' => 'WalkerChiu\Role\Console\Commands\RoleCleaner'
    ],

    /*
    |--------------------------------------------------------------------------
    | Redirect the unauthorized user to the specified route.
    |--------------------------------------------------------------------------
    |
    | Route Name.
    |
    */
    'redirect' => [
        'role'       => 'admin.login',
        'permission' => 'admin.login'
    ]
];
