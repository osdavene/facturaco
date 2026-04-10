<?php
namespace App\Models;

use App\Traits\PertenecerEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class ReciboCaja extends Model
{
    use HasFactory, SoftDeletes, PertenecerEmpresa;

    protected $table = 'recibos_caja';

    protected $fillable = [
        'empresa_id',
        'numero', 'consecutivo',
        'cliente_id', 'cliente_nombre', 'cliente_documento',
        'factura_id', 'fecha', 'valor',
        'forma_pago', 'banco', 'num_referencia',
        'concepto', 'observaciones', 'estado', 'user_id',
    ];

    protected $casts = [
        'fecha' => 'date',
        'valor' => 'decimal:2',
    ];

    public function cliente() { return $this->belongsTo(Cliente::class); }
    public function factura() { return $this->belongsTo(Factura::class); }
    public function usuario() { return $this->belongsTo(User::class, 'user_id'); }

    public static function siguienteConsecutivo(): array
    {
        $ultimo      = static::withTrashed()->max('consecutivo') ?? 0;
        $consecutivo = $ultimo + 1;
        $numero      = 'RC-' . date('Y') . '-' . str_pad($consecutivo, 4, '0', STR_PAD_LEFT);
        return compact('consecutivo', 'numero');
    }
}
