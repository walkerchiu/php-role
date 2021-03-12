<?php

namespace WalkerChiu\Role\Models\Repositories;

use Illuminate\Support\Facades\App;
use WalkerChiu\Core\Models\Forms\FormTrait;
use WalkerChiu\Core\Models\Repositories\Repository;
use WalkerChiu\Core\Models\Repositories\RepositoryTrait;

class RoleRepository extends Repository
{
    use FormTrait;
    use RepositoryTrait;

    protected $entity;

    public function __construct()
    {
        $this->entity = App::make(config('wk-core.class.role.role'));
    }

    /**
     * @param String  $code
     * @param Array   $data
     * @param Int     $page
     * @param Int     $nums per page
     * @param Boolean $is_enabled
     * @return Array
     */
    public function list(String $code, Array $data, $page = null, $nums = null, $is_enabled = null)
    {
        $this->assertForPagination($page, $nums);

        $entity = $this->entity;
        if ($is_enabled === true)      $entity = $entity->ofEnabled();
        elseif ($is_enabled === false) $entity = $entity->ofDisabled();

        $data = array_map('trim', $data);
        $records = $entity->with(['langs' => function ($query) use ($code) {
                                $query->ofCurrent()
                                      ->ofCode($code);
                             }])
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
                                            return $query->whereHas('langs', function($query) use ($data) {
                                                $query->ofCurrent()
                                                      ->where('key', 'name')
                                                      ->where('value', 'LIKE', "%".$data['name']."%");
                                            });
                                        })
                                        ->unless(empty($data['description']), function ($query) use ($data) {
                                            return $query->whereHas('langs', function($query) use ($data) {
                                                $query->ofCurrent()
                                                      ->where('key', 'description')
                                                      ->where('value', 'LIKE', "%".$data['description']."%");
                                            });
                                        });
                            })
                            ->orderBy('updated_at', 'DESC')
                            ->get()
                            ->when(is_integer($page) && is_integer($nums), function ($query) use ($page, $nums) {
                                return $query->forPage($page, $nums);
                            });
        $list = [];
        foreach ($records as $record) {
            $data = $record->toArray();
            array_push($list,
                array_merge($data, [
                    'name'        => $record->findLangByKey('name'),
                    'description' => $record->findLangByKey('description')
                ])
            );
        }

        return $list;
    }

    /**
     * @param Role $entity
     * @param Array|String $code
     * @return Array
     */
    public function show($entity, $code)
    {
        $data = [
            'id'    => $entity->id,
            'basic' => []
        ];

        $this->setEntity($entity);

        if (is_string($code)) {
            $data['basic'] = [
                  'host_type'   => $entity->host_type,
                  'host_id'     => $entity->host_id,
                  'serial'      => $entity->serial,
                  'identifier'  => $entity->identifier,
                  'name'        => $entity->findLang($code, 'name'),
                  'description' => $entity->findLang($code, 'description'),
                  'is_enabled'  => $entity->is_enabled,
                  'updated_at'  => $entity->updated_at
            ];

        } elseif (is_array($code)) {
            foreach ($code as $language) {
                $data['basic'][$language] = [
                      'host_type'   => $entity->host_type,
                      'host_id'     => $entity->host_id,
                      'serial'      => $entity->serial,
                      'identifier'  => $entity->identifier,
                      'name'        => $entity->findLang($language, 'name'),
                      'description' => $entity->findLang($language, 'description'),
                      'is_enabled'  => $entity->is_enabled,
                      'updated_at'  => $entity->updated_at
                ];
            }
        }

        return $data;
    }

    /**
     * @param String $code
     * @return Collection
     */
    public function getRoleSupported(String $code)
    {
        $records = $this->entity->get();

        $data = [];
        foreach ($records as $record) {
            $entity_name = $record->findLang($code, 'name') ? $record->findLang($code, 'name')
                                                            : $record->findLang('en_us', 'name');

            array_push($data, ['id'   => $record->id,
                               'name' => $entity_name]);
        }

        return $data;
    }
}
