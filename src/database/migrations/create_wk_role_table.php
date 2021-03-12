<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateWkRoleTable extends Migration
{
    public function up()
    {
        Schema::create(config('wk-core.table.role.roles'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->nullableMorphs('host');
            $table->string('serial')->nullable();
            $table->string('identifier');
            $table->boolean('is_enabled')->default(0);

            $table->timestampsTz();
            $table->softDeletes();

            $table->index('serial');
            $table->index('identifier');
            $table->index('is_enabled');
            $table->index(['host_type', 'host_id', 'is_enabled']);
        });
        if (!config('wk-role.onoff.core-lang_core')) {
            Schema::create(config('wk-core.table.role.roles_lang'), function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->nullableMorphs('morph');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('code');
                $table->string('key');
                $table->text('value')->nullable();
                $table->boolean('is_current')->default(1);

                $table->timestampsTz();
                $table->softDeletes();

                $table->foreign('user_id')->references('id')
                    ->on(config('wk-core.table.user'))
                    ->onDelete('set null')
                    ->onUpdate('cascade');
            });
        }

        Schema::create(config('wk-core.table.role.permissions'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('serial')->nullable();
            $table->string('identifier');
            $table->boolean('is_enabled')->default(0);

            $table->timestampsTz();
            $table->softDeletes();

            $table->index('serial');
            $table->index('identifier');
            $table->index('is_enabled');
        });
        if (!config('wk-role.onoff.core-lang_core')) {
            Schema::create(config('wk-core.table.role.permissions_lang'), function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->nullableMorphs('morph');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('code');
                $table->string('key');
                $table->text('value')->nullable();
                $table->boolean('is_current')->default(1);

                $table->timestampsTz();
                $table->softDeletes();

                $table->foreign('user_id')->references('id')
                    ->on(config('wk-core.table.user'))
                    ->onDelete('set null')
                    ->onUpdate('cascade');
            });
        }

        Schema::create(config('wk-core.table.role.roles_permissions'), function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('permission_id');

            $table->foreign('role_id')->references('id')
                  ->on(config('wk-core.table.role.roles'))
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
            $table->foreign('permission_id')->references('id')
                  ->on(config('wk-core.table.role.permissions'))
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });

        Schema::create(config('wk-core.table.role.users_roles'), function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');

            $table->foreign('user_id')->references('id')
                  ->on(config('wk-core.table.user'))
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
            $table->foreign('role_id')->references('id')
                  ->on(config('wk-core.table.role.roles'))
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    public function down() {
        Schema::dropIfExists(config('wk-core.table.role.users_roles'));
        Schema::dropIfExists(config('wk-core.table.role.roles_permissions'));
        Schema::dropIfExists(config('wk-core.table.role.permissions_lang'));
        Schema::dropIfExists(config('wk-core.table.role.permissions'));
        Schema::dropIfExists(config('wk-core.table.role.roles_lang'));
        Schema::dropIfExists(config('wk-core.table.role.roles'));
    }
}
