<?php

namespace App\Http\Requests\Post;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {

        $rules = [
            'title' => [
                'required',
                'string',
                'min:3',
                'max:120',
                Rule::unique('posts', 'title')
                    ->where('user_id', $this->user()->id),
            ],
        ];

        // Check if the request is a POST request (store operation)
        if ($this->isMethod('post')) {
            $rules['image_file'] = 'required|image|max:10000|mimes:jpeg,png,jpg,gif';
        } else {
            // For update operation (PUT/PATCH), image is not required
            $rules['image_file'] = 'nullable|image|max:10000|mimes:jpeg,png,jpg,gif';
        }

        return $rules;
    }
}
