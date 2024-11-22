<?php

namespace App\Http\Requests\Issue;

use App\Traits\ResponseTrait;
use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

class FinishIssueStatusRequest extends FormRequest
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
        throw new HttpResponseException($this->getResponse('error', 'This action is unauthorize', 422));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'end_date' => 'nullable|date|date_format:Y-m-d',
            "success_rate" => "nullable|boolean"
        ];
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new ValidationException($validator, $this->getResponse("errors", $validator->errors(), 400));
    }

    public function attributes()
    {
        return [
            'end_date' => 'Issue end date',
            "success_rate" => 'Success rate',
        ];
    }

    public function messages()
    {
        return [
            "string" => "The :attribute must be a string.",
            "min" => "The :attribute must be at least :min.",
            "max" => "The :attribute cannot exceed :max characters.",
            "date" => "The :attribute must be a valid date.",
            "date_format" => "The :attribute must be in the format Y-m-d.",
            "boolean" => "The :attribute field must be true or false.",
        ];
    }
}
