<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9\s().-]{7,20}$/'],
            'email' => ['required', 'email:rfc,dns', 'max:255'],
            'comment' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Укажите имя.',
            'name.min' => 'Имя должно содержать минимум 2 символа.',
            'name.max' => 'Имя не должно быть длиннее 100 символов.',
            'phone.required' => 'Укажите телефон.',
            'phone.regex' => 'Телефон должен быть указан в корректном формате.',
            'email.required' => 'Укажите email.',
            'email.email' => 'Email должен быть корректным.',
            'email.max' => 'Email не должен быть длиннее 255 символов.',
            'comment.required' => 'Укажите комментарий.',
            'comment.min' => 'Комментарий должен содержать минимум 10 символов.',
            'comment.max' => 'Комментарий не должен быть длиннее 2000 символов.',
        ];
    }
}
