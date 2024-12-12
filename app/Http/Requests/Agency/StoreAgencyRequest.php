<?php

namespace App\Http\Requests\Agency;

use App\Traits\ResponseTrait;
use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

class StoreAgencyRequest extends FormRequest
{
    use ResponseTrait;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();
        return Auth::check() && $user->hasRole("user");
    }

    public function failedAuthorization()
    {
        throw new HttpResponseException($this->error('This is unauthorized', 422));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'lawyer_id' => 'required|numeric|exists:lawyers,id',
            'cause' => 'required|string|min:3|max:100',
        ];
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new ValidationException($validator, $this->error($validator->errors(), 401));
    }

    public function attributes()
    {
        return [
            'lawyer_id' => 'Lawyer number',
            'cause' => 'Reason for proxy',
        ];
    }

    public function messages()
    {
        return [
            'required' => 'The :attribute is required.',
            'numeric' => 'The :attribute must be a numeric value.',
            'exists' => 'The selected :attribute does not exist in the lawyers table.',
            'string' => 'The :attribute must be a valid string.',
            'min' => 'The :attribute must be at least :min characters long.',
            'max' => 'The :attribute must not exceed :max characters.',
        ];
    }

}
