<?php

namespace App\Http\Requests\Auth;

use App\Traits\ResponseTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LogoutRequest extends FormRequest
{
    use ResponseTrait;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (Auth::guard('api')->check() && !Auth::guard('api')->user()->hasRole('user')) || Auth::guard('lawyer')->check() || Auth::guard('representative')->check();
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
            'role' => 'required|string|in:admin,employee,user,lawyer,representative'
        ];
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new ValidationException($validator, $this->error($validator->errors(), 400));
    }

    public function attributes()
    {
        return [
            'role' => 'Role name'
        ];
    }
}
