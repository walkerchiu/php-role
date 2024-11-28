<?php

namespace WalkerChiu\Role;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use WalkerChiu\Role\Models\Entities\Permission;
use WalkerChiu\Role\Models\Entities\PermissionLang;

class PermissionTest extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ .'/../migrations');
        $this->withFactories(__DIR__ .'/../../src/database/factories');
    }

    /**
     * To load your package service provider, override the getPackageProviders.
     *
     * @param \Illuminate\Foundation\Application  $app
     * @return Array
     */
    protected function getPackageProviders($app)
    {
        return [\WalkerChiu\Core\CoreServiceProvider::class,
                \WalkerChiu\Role\RoleServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
    }

    /**
     * A basic functional test on Permission.
     *
     * For WalkerChiu\Role\Models\Entities\Permission
     * 
     * @return void
     */
    public function testPermission()
    {
        // Config
        Config::set('wk-core.onoff.core-lang_core', 0);
        Config::set('wk-role.onoff.core-lang_core', 0);
        Config::set('wk-core.lang_log', 1);
        Config::set('wk-role.lang_log', 1);
        Config::set('wk-core.soft_delete', 1);
        Config::set('wk-role.soft_delete', 1);

        // Give
        $db_record_1 = factory(Permission::class)->create();
        $db_record_2 = factory(Permission::class)->create();
        $db_record_3 = factory(Permission::class)->create(['is_enabled' => 1]);

        // Get records after creation
            // When
            $records = Permission::all();
            // Then
            $this->assertCount(3, $records);

        // Delete someone
            // When
            $db_record_2->delete();
            $records = Permission::all();
            // Then
            $this->assertCount(2, $records);

        // Resotre someone
            // When
            Permission::withTrashed()
                      ->find($db_record_2->id)
                      ->restore();
            $record_2 = Permission::find($db_record_2->id);
            $records = Permission::all();
            // Then
            $this->assertNotNull($record_2);
            $this->assertCount(3, $records);

        // Return Lang class
            // When
            $class = $record_2->lang();
            // Then
            $this->assertEquals($class, PermissionLang::class);

        // Scope query on enabled records
            // When
            $records = Permission::ofEnabled()
                                 ->get();
            // Then
            $this->assertCount(1, $records);

        // Scope query on disabled records
            // When
            $records = Permission::ofDisabled()
                                 ->get();
            // Then
            $this->assertCount(2, $records);
    }
}
