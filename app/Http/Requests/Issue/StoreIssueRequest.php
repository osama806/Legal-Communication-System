<?php

namespace App\Http\Requests\Issue;

use App\Traits\ResponseTrait;
use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

class StoreIssueRequest extends FormRequest
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
        throw new HttpResponseException($this->success("error", "This action is unauthorized", 422));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "base_number" => "required|string|digits:8|unique:issues,base_number",
            "record_number" => "required|string|digits:8|unique:issues,record_number",
            "agency_id" => "required|numeric|min:1|exists:agencies,id",
            "court_name" => "required|string|in:cassation,reconciliation,beginning,appeal,commercial,banking,arbitration,reconciliation_penalty,start_penalty,misdemeanor_appeal,felonies,islamic,christianity,administrative_disputes,international_disputes,military_judiciary,terrorism",
            "type" => "required|string|in:legitimacy,civil,penal,administrative,commercial,terrorism,military,arbitration,international_disputes",
            "start_date" => "required|date|date_format:Y-m-d",
            "estimated_cost" => "required|numeric|min:1",
        ];
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new ValidationException($validator, $this->success("errors", $validator->errors(), 400));
    }

    public function attributes(): array
    {
        return [
            "base_number" => "Base number",
            "record_number" => "Record number",
            "agency_id" => "Agency number",
            "court_name" => "Court name",
            "type" => "Issue type",
            "start_date" => "Issue start date",
            "estimated_cost" => "Estimated cost for issue",
        ];
    }
    public function messages(): array
    {
        return [
            "required" => "The :attribute is required.",
            "string" => "The :attribute must be a string.",
            "digits" => "The :attribute must be exactly 8 digits.",
            "unique" => "The :attribute must be unique.",
            "numeric" => "The :attribute must be a number.",
            "min" => "The :attribute must be at least :min.",
            "max" => "The :attribute cannot exceed :max characters.",
            "exists" => "The :attribute must exist in the agencies table.",
            "date" => "The :attribute must be a valid date.",
            "date_format" => "The :attribute must be in the format Y-m-d.",
            "boolean" => "The :attribute field must be true or false.",
            "in" => "The selected :attribute is invalid.",
        ];
    }

}
