<?php

namespace App\Http\Requests\Issue;

use App\Traits\ResponseTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class FilterRequest extends FormRequest
{
    use ResponseTrait;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
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
            "court_id" => "nullable|numeric|min:1|exists:courts,id",
            "court_room_id" => "nullable|numeric|min:1|exists:court_rooms,id",
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
            "court_id" => "Court number",
            "court_room_id" => "Court room number",
            "status" => "Issue status",
        ];
    }

    public function messages()
    {
        return [
            'integer' => 'The :attribute must be a valid integer.',
            "string" => "The :attribute must be a string.",
            'court_id.exists' => 'The specified :attribute does not exist in courts table.',
            'court_room_id.exists' => 'The specified :attribute does not exist in court_rooms table.',
        ];
    }
}
