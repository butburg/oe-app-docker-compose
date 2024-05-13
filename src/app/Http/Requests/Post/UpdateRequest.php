<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
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
        // make all of the fields required, set info file to accept only images
        return [
            'title' => 'required|string|min:3|max:255', // minimum length is 3 characters, maximum length is 255 characters
            'info_file' => 'nullable|file|max:4096|mimes:jpeg,png,gif', // optional, file only, max size is 1024 KB, with some allowed mime types
        ];
    }
}
