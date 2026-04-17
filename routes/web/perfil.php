<?php

use App\Http\Controllers\PerfilController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Laravel Breeze profile
Route::get('/profile',    [ProfileController::class, 'edit'])   ->name('profile.edit');
Route::patch('/profile',  [ProfileController::class, 'update']) ->name('profile.update');
Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

// Perfil propio de FacturaCO
Route::get('/perfil',           [PerfilController::class, 'index'])         ->name('perfil.index');
Route::put('/perfil',           [PerfilController::class, 'update'])        ->name('perfil.update');
Route::put('/perfil/password',  [PerfilController::class, 'updatePassword'])->name('perfil.password');
Route::post('/perfil/avatar',   [PerfilController::class, 'updateAvatar'])  ->name('perfil.avatar');
Route::delete('/perfil/avatar', [PerfilController::class, 'deleteAvatar'])  ->name('perfil.avatar.delete');

// Tema
Route::post('/tema', function (Request $request) {
    $tema = $request->tema === 'light' ? 'light' : 'dark';
    /** @var \App\Models\User $user */
    $user = Auth::user();
    $user->update(['tema' => $tema]);
    return back();
})->name('tema.cambiar');
