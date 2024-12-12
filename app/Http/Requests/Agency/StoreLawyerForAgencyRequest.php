<?php

namespace App\Http\Requests\Agency;

use App\Traits\ResponseTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class StoreLawyerForAgencyRequest extends FormRequest
{
    use ResponseTrait;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::guard("lawyer")->check();
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
            'agency_id' => 'required|numeric|exists:agencies,id',
            'representative_id' => 'required|numeric|exists:representatives,id',
            'type' => 'required|string|in:public,private,legitimacy',
            'authorizations' => 'required|string|min:1',
            'exceptions' => 'required|string|min:1',
        ];
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new ValidationException($validator, $this->error($validator->errors(), 400));
    }

    public function attributes()
    {
        return [
            'representative_id' => 'Representative number',
            'agency_id' => 'Agency number',
            'type' => 'Agency type',
            'authorizations' => 'Authorizations',
            'exceptions' => 'Exceptions',
        ];
    }

    public function messages()
    {
        return [
            'required' => 'The :attribute is required.',
            'numeric' => 'The :attribute must be a numeric value.',
            'agency_id.exists' => 'The specified :attribute does not exist in the agencies table.',
            'representative_id.exists' => 'The specified :attribute does not exist in the representatives table.',
            'min' => 'The :attribute must be at least :min characters long.',
            'max' => 'The :attribute must not exceed :max characters.',
            "in" => "The selected :attribute is invalid.",
        ];
    }
}
