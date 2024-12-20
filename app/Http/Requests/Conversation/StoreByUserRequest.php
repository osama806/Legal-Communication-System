<?php

namespace App\Http\Requests\Conversation;

use App\Traits\ResponseTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class StoreByUserRequest extends FormRequest
{
    use ResponseTrait;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::guard('api')->check() && Auth::guard('api')->user()->hasRole('user');
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
            'lawyer_id' => 'required|numeric|min:1|exists:lawyers,id'
        ];
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new ValidationException($validator, $this->error($validator->errors(), 400));
    }

    public function attributes()
    {
        return [
            'lawyer_id' => 'Lawyer number'
        ];
    }

    public function messages()
    {
        return [
            "min" => "The :attribute must be at least :min.",
            'exists' => 'The specified :attribute does not exist in lawyers table.',
            "numeric" => "The :attribute must be a number.",
        ];
    }
}
