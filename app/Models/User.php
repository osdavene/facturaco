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
        'name',
        'email',
        'password',
        'activo',
        'avatar', 
        'telefono', 
        'cargo',
        'tema',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return Storage::url($this->avatar);
        }
        return 'https://ui-avatars.com/api/?name='.urlencode($this->name)
            .'&background=f59e0b&color=000&bold=true&size=128';
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