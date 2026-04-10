<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password',
        'activo', 'avatar', 'telefono', 'cargo', 'tema',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ── Relaciones ────────────────────────────────────────────────

    public function empresas()
    {
        return $this->belongsToMany(Empresa::class, 'empresa_user')
                    ->withPivot('rol', 'activo')
                    ->withTimestamps();
    }

    public function loginLogs()
    {
        return $this->hasMany(LoginLog::class);
    }

    // ── Helpers ───────────────────────────────────────────────────

    /** Empresa activa en la sesión actual */
    public function empresaActiva(): ?Empresa
    {
        $id = session('empresa_activa_id');
        if (!$id) return null;

        return $this->empresas()
                    ->wherePivot('activo', true)
                    ->where('empresa_id', $id)
                    ->first();
    }

    /** Rol del usuario en la empresa activa */
    public function rolEnEmpresaActiva(): ?string
    {
        $id = session('empresa_activa_id');
        if (!$id) return null;

        $pivot = $this->empresas()
                      ->wherePivot('activo', true)
                      ->where('empresa_id', $id)
                      ->first()?->pivot;

        return $pivot?->rol;
    }

    /** Verifica si el usuario es admin de la empresa activa */
    public function esAdminEmpresa(): bool
    {
        return $this->rolEnEmpresaActiva() === 'admin';
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return Storage::url($this->avatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name)
             . '&background=f59e0b&color=000&bold=true&size=128';
    }

    public function getIniciales(): string
    {
        $partes = explode(' ', $this->name);
        if (count($partes) >= 2) {
            return strtoupper($partes[0][0] . $partes[1][0]);
        }
        return strtoupper(substr($this->name, 0, 2));
    }
}
