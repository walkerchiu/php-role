<?php

namespace WalkerChiu\Role\Models\Observers;

class RoleLangObserver
{
    /**
     * Handle the entity "retrieved" event.
     *
     * @param Entity  $entity
     * @return void
     */
    public function retrieved($entity)
    {
        //
    }

    /**
     * Handle the entity "creating" event.
     *
     * @param Entity  $entity
     * @return void
     */
    public function creating($entity)
    {
        //
    }

    /**
     * Handle the entity "created" event.
     *
     * @param Entity  $entity
     * @return void
     */
    public function created($entity)
    {
        $query =
            config('wk-core.class.role.roleLang')
                ::withTrashed()
                ->where('morph_type', $entity->morph_type)
                ->where('morph_id', $entity->morph_id)
                ->where('code', $entity->code)
                ->where('key', $entity->key)
                ->where('id', '<>', $entity->id);

        if (
            config('wk-role.soft_delete')
            && (
                config('wk-core.lang_log')
                || config('wk-role.lang_log')
            )
        ) {
            $query->update(['is_current' => 0]);
        } else {
            $query->forceDelete();
        }
    }

    /**
     * Handle the entity "updating" event.
     *
     * History should not be modify.
     *
     * @param Entity  $entity
     * @return void
     */
    public function updating($entity)
    {
        return false;
    }

    /**
     * Handle the entity "updated" event.
     *
     * @param Entity  $entity
     * @return void
     */
    public function updated($entity)
    {
        //
    }

    /**
     * Handle the entity "saving" event.
     *
     * @param Entity  $entity
     * @return void
     */
    public function saving($entity)
    {
        //
    }

    /**
     * Handle the entity "saved" event.
     *
     * @param Entity  $entity
     * @return void
     */
    public function saved($entity)
    {
        //
    }

    /**
     * Handle the entity "deleting" event.
     *
     * @param Entity  $entity
     * @return void
     */
    public function deleting($entity)
    {
        //
    }

    /**
     * Handle the entity "deleted" event.
     *
     * @param Entity  $entity
     * @return void
     */
    public function deleted($entity)
    {
        //
    }

    /**
     * Handle the entity "restoring" event.
     *
     * @param Entity  $entity
     * @return void
     */
    public function restoring($entity)
    {
        //
    }

    /**
     * Handle the entity "restored" event.
     *
     * @param Entity  $entity
     * @return void
     */
    public function restored($entity)
    {
        //
    }
}
