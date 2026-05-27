<?php

namespace App\Http\Requests;

use App\Models\Membros;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = auth()->guard('web')->user() ?: auth()->guard('membro')->user();

        // Use the correct model for uniqueness checks depending on the guard/user type.
        $userClass = $user instanceof Membros ? Membros::class : User::class;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique($userClass)->ignore($user->id),
            ],
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Informe o nome.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'email.required' => 'Informe o e-mail.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está em uso.',
            'email.lowercase' => 'O e-mail precisa estar em letras minúsculas.',
            'profile_photo.image' => 'Envie uma imagem válida.',
            'profile_photo.mimes' => 'A foto precisa ser JPG, PNG, GIF ou WEBP.',
            'profile_photo.max' => 'A foto não pode ter mais de 2MB.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nome',
            'email' => 'e-mail',
            'profile_photo' => 'foto de perfil',
        ];
    }
}
