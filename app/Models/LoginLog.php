<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    public $timestamps = false;

    protected $table = 'login_logs';

    protected $fillable = [
        'user_id', 'ip_address', 'user_agent',
        'navegador', 'dispositivo', 'accion', 'fecha_hora',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}