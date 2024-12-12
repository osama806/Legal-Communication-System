<?php

namespace App\Http\Requests\Issue;

use App\Traits\ResponseTrait;
use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

class UpdateStatusRequest extends FormRequest
{
    use ResponseTrait;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::guard('lawyer')->check();
    }

    public function failedAuthorization()
    {
        return $this->error('This action is unauthorized', 422);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "status" => "required|string|min:3|max:256"
        ];
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        return $this->error($validator->errors(), 400);
    }

    public function attributes()
    {
        return [
            "status" => "Issue status",
        ];
    }

    public function messages()
    {
        return [
            "max" => "The :attribute cannot exceed :max characters.",
            "min" => "The :attribute must be at least :min.",
        ];
    }
}
