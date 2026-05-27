<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = Auth::guard('web')->user() ?: Auth::guard('membro')->user();

        return view('profile.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = Auth::guard('web')->user() ?: Auth::guard('membro')->user();
        $data = $request->validated();
        $profilePhoto = $request->file('profile_photo');

        unset($data['profile_photo']);

        // Se for membro, mapeamos name -> nome explicitamente para evitar
        // qualquer tentativa de gravar uma coluna "name" inexistente.
        if ($user instanceof \App\Models\Membros && isset($data['name'])) {
            $data['nome'] = $data['name'];
            unset($data['name']);
        }

        if ($profilePhoto && $profilePhoto->isValid()) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            $data['profile_photo_path'] = $profilePhoto->store('perfis', 'public');
        }

        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('sucesso', 'Perfil atualizado com sucesso.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::guard('web')->user() ?: Auth::guard('membro')->user();
        $guard = $user instanceof \App\Models\Membros ? 'membro' : 'web';

        $request->validateWithBag('userDeletion', [
            'password' => ['required', "current_password:{$guard}"],
        ], [
            'password.required' => 'Informe sua senha para excluir a conta.',
            'password.current_password' => 'A senha informada está incorreta.',
        ], [
            'password' => 'senha',
        ]);

        Auth::guard($guard)->logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
