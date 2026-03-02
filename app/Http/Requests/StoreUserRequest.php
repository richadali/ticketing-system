<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function rules()
    {
        // If 'id' is present, we are editing, so password is not required
        $passwordRule = $this->has('id') ? 'nullable|min:8' : 'required|min:8';

        $rules = [
            'name' => 'required|string|max:255',
            'password' => $passwordRule
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => 'The name field is required.',
            'email.required' => 'The email field is required.',
            'password.required' => 'The password field is required.',
        ];
    }
}
