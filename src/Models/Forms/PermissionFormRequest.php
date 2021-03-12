<?php

namespace WalkerChiu\Role\Models\Forms;

use Illuminate\Support\Facades\Request;
use WalkerChiu\Core\Models\Forms\FormRequest;

class PermissionFormRequest extends FormRequest
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
            'serial'      => trans('php-role::permission.serial'),
            'identifier'  => trans('php-role::permission.identifier'),
            'is_enabled'  => trans('php-role::permission.is_enabled'),

            'name'        => trans('php-role::permission.name'),
            'description' => trans('php-role::permission.description')
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
            'serial'      => '',
            'identifier'  => 'required|string|max:255',
            'is_enabled'  => 'required|boolean',

            'name'        => 'required|string|max:255',
            'description' => ''
        ];

        $request = Request::instance();
        if ($request->isMethod('put') && isset($request->id)) {
            $rules = array_merge($rules, ['id' => ['required','integer','min:1','exists:'.config('wk-core.table.role.permissions').',id']]);
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
            'id.required'         => trans('php-core::validation.required'),
            'id.integer'          => trans('php-core::validation.integer'),
            'id.min'              => trans('php-core::validation.min'),
            'id.exists'           => trans('php-core::validation.exists'),
            'identifier.required' => trans('php-core::validation.required'),
            'identifier.max'      => trans('php-core::validation.max'),
            'is_enabled.required' => trans('php-core::validation.required'),
            'is_enabled.boolean'  => trans('php-core::validation.boolean'),

            'name.required'       => trans('php-core::validation.required'),
            'name.string'         => trans('php-core::validation.string'),
            'name.max'            => trans('php-core::validation.max')
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
            if (isset($data['identifier'])) {
                $result = config('wk-core.class.role.permission')::where('identifier', $data['identifier'])
                                ->when(isset($data['id']), function ($query) use ($data) {
                                    return $query->where('id', '<>', $data['id']);
                                  })
                                ->exists();
                if ($result)
                    $validator->errors()->add('identifier', trans('php-core::validation.unique', ['attribute' => trans('php-role::permission.identifier')]));
            }
        });
    }
}
