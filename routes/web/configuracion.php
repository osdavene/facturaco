<?php

use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BusquedaController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\SesionController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

// ── Empresa ───────────────────────────────────────────────────────────────
Route::get('/empresa',              [EmpresaController::class, 'index'])     ->name('empresa.index')       ->middleware('can:ver configuracion');
Route::put('/empresa',              [EmpresaController::class, 'update'])    ->name('empresa.update')      ->middleware('can:editar configuracion');
Route::delete('/empresa/logo',      [EmpresaController::class, 'deleteLogo'])->name('empresa.logo.delete') ->middleware('can:editar configuracion');
Route::post('/empresa/probar-mail', [EmpresaController::class, 'probarMail'])->name('empresa.probarMail')  ->middleware('can:editar configuracion');

// ── Usuarios ──────────────────────────────────────────────────────────────
Route::get('/usuarios',                    [UsuarioController::class, 'index'])       ->name('usuarios.index')  ->middleware('can:ver usuarios');
Route::get('/usuarios/crear',              [UsuarioController::class, 'create'])      ->name('usuarios.create') ->middleware('can:crear usuarios');
Route::post('/usuarios',                   [UsuarioController::class, 'store'])       ->name('usuarios.store')  ->middleware('can:crear usuarios');
Route::get('/usuarios/{usuario}/editar',   [UsuarioController::class, 'edit'])        ->name('usuarios.edit')   ->middleware('can:editar usuarios');
Route::put('/usuarios/{usuario}',          [UsuarioController::class, 'update'])      ->name('usuarios.update') ->middleware('can:editar usuarios');
Route::delete('/usuarios/{usuario}',       [UsuarioController::class, 'destroy'])     ->name('usuarios.destroy')->middleware('can:eliminar usuarios');
Route::patch('/usuarios/{usuario}/activo', [UsuarioController::class, 'toggleActivo'])->name('usuarios.activo') ->middleware('can:editar usuarios');

// ── Sesiones activas ──────────────────────────────────────────────────────
Route::get('/sesiones',         [SesionController::class, 'index'])     ->name('sesiones.index')      ->middleware('can:ver usuarios');
Route::delete('/sesiones/{id}', [SesionController::class, 'destroy'])   ->name('sesiones.destroy')    ->middleware('can:ver usuarios');
Route::delete('/sesiones',      [SesionController::class, 'destroyAll'])->name('sesiones.destroyAll') ->middleware('can:ver usuarios');

// ── Auditoría ─────────────────────────────────────────────────────────────
Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index')->middleware('can:ver usuarios');

// ── Backup ────────────────────────────────────────────────────────────────
Route::get('/backup',      [BackupController::class, 'index'])        ->name('backup.index')->middleware('can:ver usuarios');
Route::get('/backup/json', [BackupController::class, 'descargarJson'])->name('backup.json') ->middleware('can:ver usuarios');
Route::post('/backup/csv', [BackupController::class, 'descargarCsv']) ->name('backup.csv')  ->middleware('can:ver usuarios');

// ── Búsqueda global ───────────────────────────────────────────────────────
Route::get('/busqueda', [BusquedaController::class, 'buscar'])->name('busqueda');
