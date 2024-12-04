<?php

namespace App\Http\Requests\Issue;

use App\Traits\ResponseTrait;
use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

class FilterForUserRequest extends FormRequest
{
    use ResponseTrait;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::guard("api")->check() && Auth::guard("api")->user()->hasRole('user');
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
            "per_page" => "nullable|integer|min:1",
            'base_number' => 'nullable|string',
            'record_number' => 'nullable|string',
            "court_name" => "nullable|string|in:cassation,reconciliation,beginning,appeal,commercial,banking,arbitration,reconciliation_penalty,start_penalty,misdemeanor_appeal,felonies,islamic,christianity,administrative_disputes,international_disputes,military_judiciary,terrorism",
            "type" => "nullable|string|in:legitimacy,civil,penal,administrative,commercial,terrorism,military,arbitration,international_disputes",
            'status' => 'nullable|string',
        ];
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new ValidationException($validator, $this->success('errors', $validator->errors(), 401));
    }

    public function attributes()
    {
        return [
            'per_page' => 'Items per page',
            "base_number" => "Base number",
            "record_number" => "Record number",
            "court_name" => "Court name",
            "type" => "Issue type",
            "status" => "Issue status",
        ];
    }

    public function messages()
    {
        return [
            'integer' => 'The :attribute must be a valid integer.',
            "string" => "The :attribute must be a string.",
            "in" => "The selected :attribute is invalid.",
        ];
    }
}
