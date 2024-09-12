<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest {
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array {
        $rules = [];

        if ($this->routeIs('profile.updateName')) {
            $rules['name'] = ['required', 'string', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)];
        }

        if ($this->routeIs('profile.updateDescription')) {
            $rules['description'] = ['nullable', 'string', 'max:500'];
        }

        if ($this->routeIs('profile.updateEmail')) {
            $rules['email'] = ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)];
        }

        if ($this->routeIs('profile.updateImage')) {
            $rules['profile_image']  = ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:4096'];
        }

        return $rules;
    }
}
