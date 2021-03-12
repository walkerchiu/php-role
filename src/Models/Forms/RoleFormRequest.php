<?php

namespace WalkerChiu\Role\Models\Forms;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use WalkerChiu\Core\Models\Forms\FormRequest;

class RoleFormRequest extends FormRequest
{
    /**
     * @Override Illuminate\Foundation\Http\FormRequest::getValidatorInstance
     */
    protected function getValidatorInstance()
    {
        $request = Request::instance();
        $data = $this->all();
        if ($request->isMethod('put') && empty($data['id']) && isset($request->id)) {
            $data['id'] = (int) $request->id;
            $this->getInputSource()->replace($data);
        }

        return parent::getValidatorInstance();
    }

    public function attributes()
    {
        return [
            'host_type'   => trans('php-role::role.host_type'),
            'host_id'     => trans('php-role::role.host_id'),
            'serial'      => trans('php-role::role.serial'),
            'identifier'  => trans('php-role::role.identifier'),
            'is_enabled'  => trans('php-role::role.is_enabled'),

            'name'        => trans('php-role::role.name'),
            'description' => trans('php-role::role.description')
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'host_type'   => 'required_with:host_id|string',
            'host_id'     => 'required_with:host_type|integer|min:1',
            'serial'      => '',
            'identifier'  => 'required|string|max:255',
            'is_enabled'  => 'required|boolean',

            'name'        => 'required|string|max:255',
            'description' => ''
        ];

        $request = Request::instance();
        if ($request->isMethod('put') && isset($request->id)) {
            $rules = array_merge($rules, ['id' => ['required','integer','min:1','exists:'.config('wk-core.table.role.roles').',id']]);
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'id.required'             => trans('php-core::validation.required'),
            'id.integer'              => trans('php-core::validation.integer'),
            'id.min'                  => trans('php-core::validation.min'),
            'id.exists'               => trans('php-core::validation.exists'),
            'host_type.required_with' => trans('php-core::validation.required_with'),
            'host_type.string'        => trans('php-core::validation.string'),
            'host_id.required_with'   => trans('php-core::validation.required_with'),
            'host_id.integer'         => trans('php-core::validation.integer'),
            'host_id.min'             => trans('php-core::validation.min'),
            'identifier.required'     => trans('php-core::validation.required'),
            'identifier.max'          => trans('php-core::validation.max'),
            'is_enabled.required'     => trans('php-core::validation.required'),
            'is_enabled.boolean'      => trans('php-core::validation.boolean'),

            'name.required'           => trans('php-core::validation.required'),
            'name.string'             => trans('php-core::validation.string'),
            'name.max'                => trans('php-core::validation.max')
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $validator->getData();
            if (isset($data['host_type']) && isset($data['host_id'])) {
                if ( config('wk-role.onoff.site') && !empty(config('wk-core.class.site.site')) && $data['host_type'] == config('wk-core.class.site.site') ) {
                    $result = DB::table(config('wk-core.table.site.sites'))
                                 ->where('id', $data['host_id'])
                                 ->exists();
                    if (!$result)
                        $validator->errors()->add('host_id', trans('php-core::validation.exists'));

                } elseif ( config('wk-role.onoff.group') && !empty(config('wk-core.class.group.group')) && $data['host_type'] == config('wk-core.class.group.group') ) {
                    $result = DB::table(config('wk-core.table.group.groups'))
                                 ->where('id', $data['host_id'])
                                 ->exists();
                    if (!$result)
                        $validator->errors()->add('host_id', trans('php-core::validation.exists'));
                }
            }
            if (isset($data['identifier'])) {
                $result = config('wk-core.class.role.role')::where('identifier', $data['identifier'])
                                ->when(isset($data['host_type']), function ($query) use ($data) {
                                    return $query->where('host_type', $data['host_type']);
                                  })
                                ->when(isset($data['host_id']), function ($query) use ($data) {
                                    return $query->where('host_id', $data['host_id']);
                                  })
                                ->when(isset($data['id']), function ($query) use ($data) {
                                    return $query->where('id', '<>', $data['id']);
                                  })
                                ->exists();
                if ($result)
                    $validator->errors()->add('identifier', trans('php-core::validation.unique', ['attribute' => trans('php-role::role.identifier')]));
            }
        });
    }
}
