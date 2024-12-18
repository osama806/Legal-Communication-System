<?php

namespace App\Http\Requests\Rate;

use App\Traits\ResponseTrait;
use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

class UpdateRatingRequest extends FormRequest
{
    use ResponseTrait;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::guard('api')->user()->hasRole('user');
    }

    public function failedAuthorization()
    {
        throw new HttpResponseException($this->getResponse('error', 'This action is unauthorized', 422));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "rating" => "nullable|numeric|min:1|max:5",
            "review" => "nullable|string|max:256",
        ];
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new ValidationException($validator, $this->getResponse("error", $validator->errors(), 400));
    }

    public function attributes()
    {
        return [
            "rating" => "Rating",
            "review" => "Review",
        ];
    }

    public function messages()
    {
        return [
            'string' => 'The :attribute must be a valid string.',
            'numeric' => 'The :attribute must be a numeric value.',
            'max' => ':attribute must not exceed :max characters.',
            'min' => ':attribute must be at least :min characters long.',
        ];
    }
}
