<?php

namespace WalkerChiu\Role\Models\Observers;

class PermissionObserver
{
    /**
     * Handle the entity "retrieved" event.
     *
     * @param  $entity
     * @return void
     */
    public function retrieved($entity)
    {
        //
    }

    /**
     * Handle the entity "creating" event.
     *
     * @param  $entity
     * @return void
     */
    public function creating($entity)
    {
        //
    }

    /**
     * Handle the entity "created" event.
     *
     * @param  $entity
     * @return void
     */
    public function created($entity)
    {
        //
    }

    /**
     * Handle the entity "updating" event.
     * 
     * History should not be modify.
     *
     * @param  $entity
     * @return void
     */
    public function updating($entity)
    {
        //
    }

    /**
     * Handle the entity "updated" event.
     *
     * @param  $entity
     * @return void
     */
    public function updated($entity)
    {
        //
    }

    /**
     * Handle the entity "saving" event.
     *
     * @param  $entity
     * @return void
     */
    public function saving($entity)
    {
        if (config('wk-core.class.role.permission')::where('id', '<>', $entity->id)
                                                   ->where('identifier', $entity->identifier)
                                                   ->exists())
            return false;
    }

    /**
     * Handle the entity "saved" event.
     *
     * @param  $entity
     * @return void
     */
    public function saved($entity)
    {
        //
    }

    /**
     * Handle the entity "deleting" event.
     *
     * @param  $entity
     * @return void
     */
    public function deleting($entity)
    {
        //
    }

    /**
     * Handle the entity "deleted" event.
     *
     * @param  $entity
     * @return void
     */
    public function deleted($entity)
    {
        if ($entity->isForceDeleting()) {
            $entity->langs->withTrashed()->forceDelete();
        }
    }

    /**
     * Handle the entity "restoring" event.
     *
     * @param  $entity
     * @return void
     */
    public function restoring($entity)
    {
        if (config('wk-core.class.role.permission')::where('id', '<>', $entity->id)
                                                   ->where('identifier', $entity->identifier)
                                                   ->exists())
            return false;
    }

    /**
     * Handle the entity "restored" event.
     *
     * @param  $entity
     * @return void
     */
    public function restored($entity)
    {
        //
    }
}
