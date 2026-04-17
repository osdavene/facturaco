<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Empresa extends Model
{
    use HasFactory;
    protected $table = 'empresa';

    protected $fillable = [
        'razon_social', 'nombre_comercial', 'nit', 'digito_verificacion',
        'tipo_persona', 'regimen',
        'email', 'telefono', 'celular', 'sitio_web',
        'pais', 'departamento', 'municipio', 'direccion',
        'prefijo_factura', 'resolucion_numero', 'resolucion_fecha',
        'resolucion_vencimiento', 'consecutivo_desde', 'consecutivo_hasta',
        'consecutivo_actual', 'clave_tecnica', 'factura_electronica',
        'logo', 'moneda', 'decimales', 'pie_factura', 'terminos_condiciones',
        'iva_defecto', 'retefuente_defecto', 'reteica_defecto',
        'mail_mailer', 'mail_host', 'mail_port', 'mail_username',
        'mail_password', 'mail_encryption', 'mail_from_address', 'mail_from_name',
        'wompi_public_key', 'wompi_currency', 'wompi_events_key',
        'empresa_padre_id', 'timezone',
    ];

    protected $casts = [
        'resolucion_fecha'       => 'date',
        'resolucion_vencimiento' => 'date',
        'factura_electronica'    => 'boolean',
    ];

    protected $hidden = [
        'mail_password',
        'wompi_events_key',
    ];

    // ── Relaciones ────────────────────────────────────────────────

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'empresa_user')
                    ->withPivot('rol', 'activo')
                    ->withTimestamps();
    }

    public function modulos(): BelongsToMany
    {
        return $this->belongsToMany(Modulo::class, 'empresa_modulo')
            ->withPivot(['activo'])
            ->withTimestamps();
    }

    public function tieneModulo(string $slug): bool
    {
        return $this->modulos()
            ->where('slug', $slug)
            ->wherePivot('activo', true)
            ->exists();
    }

    /** Empresa padre (null si es matriz raíz) */
    public function padre(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_padre_id');
    }

    /** Filiales directas de esta empresa */
    public function filiales(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Empresa::class, 'empresa_padre_id');
    }

    /** ¿Esta empresa es una filial? */
    public function esFilial(): bool
    {
        return !is_null($this->empresa_padre_id);
    }

    /**
     * Devuelve la empresa raíz (matriz) del grupo.
     * Si ya es raíz, se devuelve a sí misma.
     */
    public function raiz(): static
    {
        if (is_null($this->empresa_padre_id)) {
            return $this;
        }
        return $this->padre->raiz();
    }

    /**
     * Devuelve todos los IDs del grupo (raíz + filiales recursivas).
     * Útil para filtrar catálogos compartidos.
     */
    public function idsGrupo(): array
    {
        $raiz = $this->raiz();
        return $raiz->idsDescendientes([$raiz->id]);
    }

    private function idsDescendientes(array $ids): array
    {
        foreach ($this->filiales as $filial) {
            $ids[] = $filial->id;
            $ids = $filial->idsDescendientes($ids);
        }
        return $ids;
    }

    // ── Accessors ─────────────────────────────────────────────────

    public function getNitFormateadoAttribute(): string
    {
        if ($this->digito_verificacion) {
            return $this->nit . '-' . $this->digito_verificacion;
        }
        return $this->nit;
    }

    public function getResolucionVigenteAttribute(): bool
    {
        if (!$this->resolucion_vencimiento) return false;
        return $this->resolucion_vencimiento >= now()->startOfDay();
    }

    public function getDiasParaVencerAttribute(): int
    {
        if (!$this->resolucion_vencimiento) return 0;
        return max(0, now()->startOfDay()->diffInDays($this->resolucion_vencimiento, false));
    }

    public function getMailConfiguradoAttribute(): bool
    {
        return !empty($this->mail_host) && !empty($this->mail_username) && !empty($this->mail_password);
    }

    public function getWompiConfiguradoAttribute(): bool
    {
        return !empty($this->wompi_public_key);
    }

    public function getWompiWebhookConfiguradoAttribute(): bool
    {
        return !empty($this->wompi_events_key);
    }

    // ── Helpers estáticos ─────────────────────────────────────────

    /**
     * Devuelve la empresa activa en la sesión actual.
     * Retrocompatible: todos los controladores que usan Empresa::obtener()
     * seguirán funcionando sin cambios.
     */
    public static function obtener(): static
    {
        if ($id = session('empresa_activa_id')) {
            return static::findOrFail($id);
        }

        // Fallback para contextos sin sesión (webhooks, consola, tests)
        return static::firstOrCreate(
            ['id' => 1],
            [
                'razon_social'    => 'MI EMPRESA S.A.S',
                'nit'             => '900000000',
                'prefijo_factura' => 'FE',
                'moneda'          => 'COP',
                'iva_defecto'     => 19,
            ]
        );
    }
}
