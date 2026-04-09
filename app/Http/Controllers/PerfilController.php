<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class PerfilController extends Controller
{
    public function index()
    {
        $usuario = auth()->user();
        return view('perfil.index', compact('usuario'));
    }

    public function update(Request $request)
    {
        $usuario = auth()->user();

        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email,'.$usuario->id,
            'telefono' => 'nullable|string|max:20',
            'cargo'    => 'nullable|string|max:100',
        ]);

        $usuario->update([
            'name'     => strtoupper($request->name),
            'email'    => strtolower($request->email),
            'telefono' => $request->telefono,
            'cargo'    => $request->cargo ? strtoupper($request->cargo) : null,
        ]);

        return back()->with('success', 'Perfil actualizado correctamente.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password_actual'  => 'required',
            'password'         => ['required', 'confirmed', Password::min(8)
                                    ->mixedCase()->numbers()],
        ]);

        $usuario = auth()->user();

        if (!Hash::check($request->password_actual, $usuario->password)) {
            return back()->withErrors([
                'password_actual' => 'La contraseña actual no es correcta.'
            ])->with('tab', 'password');
        }

        $usuario->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Contraseña actualizada correctamente.')
                     ->with('tab', 'password');
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|max:2048|mimes:jpg,jpeg,png,webp',
        ]);

        $usuario = auth()->user();

        if ($usuario->avatar) {
            Storage::disk('public')->delete($usuario->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $usuario->update(['avatar' => $path]);

        return back()->with('success', 'Foto de perfil actualizada.');
    }

    public function deleteAvatar()
    {
        $usuario = auth()->user();

        if ($usuario->avatar) {
            Storage::disk('public')->delete($usuario->avatar);
            $usuario->update(['avatar' => null]);
        }

        return back()->with('success', 'Foto de perfil eliminada.');
    }
}