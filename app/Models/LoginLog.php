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

    public static function parsearUserAgent(?string $ua): array
    {
        $ua = (string) $ua;

        // Navegador
        $navegador = 'Navegador Web';
        if (str_contains($ua, 'Edg'))            $navegador = 'Microsoft Edge';
        elseif (str_contains($ua, 'OPR'))        $navegador = 'Opera';
        elseif (str_contains($ua, 'Brave'))      $navegador = 'Brave';
        elseif (str_contains($ua, 'Chrome'))     $navegador = 'Google Chrome';
        elseif (str_contains($ua, 'Firefox'))    $navegador = 'Mozilla Firefox';
        elseif (str_contains($ua, 'Safari'))     $navegador = 'Apple Safari';

        // Dispositivo / S.O.
        $dispositivo = 'Escritorio (PC)';
        if (str_contains($ua, 'iPhone'))              $dispositivo = 'iPhone (iOS)';
        elseif (str_contains($ua, 'iPad'))            $dispositivo = 'iPad (iPadOS)';
        elseif (str_contains($ua, 'Android'))         $dispositivo = str_contains($ua, 'Mobile') ? 'Móvil Android' : 'Tablet Android';
        elseif (str_contains($ua, 'Windows NT 10.0')) $dispositivo = 'Windows PC (10/11)';
        elseif (str_contains($ua, 'Windows'))         $dispositivo = 'Windows PC';
        elseif (str_contains($ua, 'Macintosh'))       $dispositivo = 'Mac (macOS)';
        elseif (str_contains($ua, 'Linux'))           $dispositivo = 'Linux PC';

        return [$navegador, $dispositivo];
    }
}