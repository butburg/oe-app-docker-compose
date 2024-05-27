<?php

namespace App\Http\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true; // or implement authorization logic
    }

    public function rules(): array
    {
        return [
            'comment' => 'required|string|min:1|max:1000',
        ];
    }
}
