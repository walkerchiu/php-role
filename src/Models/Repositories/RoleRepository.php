<?php

namespace WalkerChiu\Role\Models\Repositories;

use Illuminate\Support\Facades\App;
use WalkerChiu\Core\Models\Forms\FormHasHostTrait;
use WalkerChiu\Core\Models\Repositories\Repository;
use WalkerChiu\Core\Models\Repositories\RepositoryHasHostTrait;
use WalkerChiu\Core\Models\Services\PackagingFactory;

class RoleRepository extends Repository
{
    use FormHasHostTrait;
    use RepositoryHasHostTrait;

    protected $instance;



    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->instance = App::make(config('wk-core.class.role.role'));
    }

    /**
     * @param String  $code
     * @param Array   $data
     * @param Bool    $is_enabled
     * @param Bool    $auto_packing
     * @return Array|Collection|Eloquent
     */
    public function list(string $code, array $data, $is_enabled = null, $auto_packing = false)
    {
        $instance = $this->instance;
        if ($is_enabled === true)      $instance = $instance->ofEnabled();
        elseif ($is_enabled === false) $instance = $instance->ofDisabled();

        $data = array_map('trim', $data);
        $repository = $instance->with(['langs' => function ($query) use ($code) {
                                    $query->ofCurrent()
                                          ->ofCode($code);
                                }])
                                ->whereHas('langs', function ($query) use ($code) {
                                    return $query->ofCurrent()
                                                 ->ofCode($code);
                                })
                                ->when($data, function ($query, $data) {
                                    return $query->unless(empty($data['id']), function ($query) use ($data) {
                                                return $query->where('id', $data['id']);
                                            })
                                            ->unless(empty($data['serial']), function ($query) use ($data) {
                                                return $query->where('serial', $data['serial']);
                                            })
                                            ->unless(empty($data['identifier']), function ($query) use ($data) {
                                                return $query->where('identifier', $data['identifier']);
                                            })
                                            ->unless(empty($data['name']), function ($query) use ($data) {
                                                return $query->whereHas('langs', function ($query) use ($data) {
                                                    $query->ofCurrent()
                                                          ->where('key', 'name')
                                                          ->where('value', 'LIKE', "%".$data['name']."%");
                                                });
                                            })
                                            ->unless(empty($data['description']), function ($query) use ($data) {
                                                return $query->whereHas('langs', function ($query) use ($data) {
                                                    $query->ofCurrent()
                                                          ->where('key', 'description')
                                                          ->where('value', 'LIKE', "%".$data['description']."%");
                                                });
                                            });
                                })
                                ->orderBy('updated_at', 'DESC');

        if ($auto_packing) {
            $factory = new PackagingFactory(config('wk-role.output_format'), config('wk-role.pagination.pageName'), config('wk-role.pagination.perPage'));
            $factory->setFieldsLang(['name', 'description']);
            return $factory->output($repository);
        }

        return $repository;
    }

    /**
     * @param Role          $instance
     * @param Array|String  $code
     * @return Array
     */
    public function show($instance, $code): array
    {
        $data = [
            'id'    => $instance->id,
            'basic' => []
        ];

        $this->setEntity($instance);

        if (is_string($code)) {
            $data['basic'] = [
                  'host_type'   => $instance->host_type,
                  'host_id'     => $instance->host_id,
                  'serial'      => $instance->serial,
                  'identifier'  => $instance->identifier,
                  'name'        => $instance->findLang($code, 'name'),
                  'description' => $instance->findLang($code, 'description'),
                  'is_enabled'  => $instance->is_enabled,
                  'updated_at'  => $instance->updated_at
            ];

        } elseif (is_array($code)) {
            foreach ($code as $language) {
                $data['basic'][$language] = [
                      'host_type'   => $instance->host_type,
                      'host_id'     => $instance->host_id,
                      'serial'      => $instance->serial,
                      'identifier'  => $instance->identifier,
                      'name'        => $instance->findLang($language, 'name'),
                      'description' => $instance->findLang($language, 'description'),
                      'is_enabled'  => $instance->is_enabled,
                      'updated_at'  => $instance->updated_at
                ];
            }
        }

        return $data;
    }

    /**
     * @param String  $code
     * @return Array
     */
    public function getRoleSupported(string $code): array
    {
        $records = $this->instance->get();

        $data = [];
        foreach ($records as $record) {
            $instance_name = $record->findLang($code, 'name') ? $record->findLang($code, 'name')
                                                            : $record->findLang('en_us', 'name');

            array_push($data, ['id'   => $record->id,
                               'name' => $instance_name]);
        }

        return $data;
    }
}
