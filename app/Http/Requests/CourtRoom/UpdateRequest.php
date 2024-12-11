<?php

namespace App\Http\Requests\CourtRoom;

use App\Traits\ResponseTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class UpdateRequest extends FormRequest
{
    use ResponseTrait;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::guard('api')->check() && Auth::guard('api')->user()->hasRole('employee');
    }

    public function failedAuthorization()
    {
        throw new HttpResponseException($this->error('This action is unauthorized', 422));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'nullable|string|min:3|max:50',
            'court_id'  =>  'nullable|numeric|min:1|exists:courts,id'
        ];
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        return $this->error($validator->errors(), 400);
    }

    public function attributes()
    {
        return [
            'name' => 'Court room name',
            'court_id'  =>  'Court number'
        ];
    }

    public function messages()
    {
        return [
            "string" => "The :attribute must be a string.",
            "min" => "The :attribute must be at least :min.",
            "max" => "The :attribute cannot exceed :max characters.",
            'exists' => 'The specified :attribute does not exist in courts table.',
            "numeric" => "The :attribute must be a number.",
        ];
    }
}
