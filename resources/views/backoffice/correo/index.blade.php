@extends('backoffice.layout')

@section('title', 'Servidor de Correo & Notificaciones')

@section('content')
<div class="space-y-6" x-data="correoConfigManager()">

    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-display font-black text-2xl text-white flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-500/10 rounded-xl flex items-center justify-center border border-blue-500/20 text-blue-400">
                    <i class="fas fa-envelope-open-text text-lg"></i>
                </div>
                Servidor de Correo & Notificaciones
            </h1>
            <p class="text-slate-400 text-sm mt-1">
                Configura tu cuenta de correo SMTP (Gmail o empresarial) para enviar facturas, cotizaciones y avisos automáticos de vencimiento de planes.
            </p>
        </div>
    </div>

    {{-- Alertas --}}
    @if(session('success'))
    <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-xl px-5 py-4 text-sm flex items-center gap-3">
        <i class="fas fa-check-circle text-base"></i>
        <div>{{ session('success') }}</div>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-rose-500/10 border border-rose-500/30 text-rose-400 rounded-xl px-5 py-4 text-sm flex items-center gap-3">
        <i class="fas fa-circle-exclamation text-base"></i>
        <div>{{ session('error') }}</div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Formulario Principal SMTP (2 columnas) --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Presets Rápidos --}}
            <div class="card p-5">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">
                    Configuración Rápida con 1 Clic (Presets)
                </p>
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="aplicarPreset('gmail')"
                            class="px-3.5 py-2 bg-[#1a2235] hover:bg-rose-500/10 hover:text-rose-400 border border-[#1e2d47] hover:border-rose-500/30 rounded-xl text-xs font-semibold text-slate-300 transition-colors flex items-center gap-2">
                        <i class="fab fa-google text-rose-400"></i>
                        <span>Gmail / Workspace</span>
                    </button>
                    <button type="button" @click="aplicarPreset('zoho')"
                            class="px-3.5 py-2 bg-[#1a2235] hover:bg-amber-500/10 hover:text-amber-400 border border-[#1e2d47] hover:border-amber-500/30 rounded-xl text-xs font-semibold text-slate-300 transition-colors flex items-center gap-2">
                        <i class="fas fa-briefcase text-amber-400"></i>
                        <span>Zoho Mail</span>
                    </button>
                    <button type="button" @click="aplicarPreset('outlook')"
                            class="px-3.5 py-2 bg-[#1a2235] hover:bg-blue-500/10 hover:text-blue-400 border border-[#1e2d47] hover:border-blue-500/30 rounded-xl text-xs font-semibold text-slate-300 transition-colors flex items-center gap-2">
                        <i class="fab fa-microsoft text-blue-400"></i>
                        <span>Outlook / Office 365</span>
                    </button>
                    <button type="button" @click="aplicarPreset('cpanel')"
                            class="px-3.5 py-2 bg-[#1a2235] hover:bg-purple-500/10 hover:text-purple-400 border border-[#1e2d47] hover:border-purple-500/30 rounded-xl text-xs font-semibold text-slate-300 transition-colors flex items-center gap-2">
                        <i class="fas fa-server text-purple-400"></i>
                        <span>cPanel / Hostinger</span>
                    </button>
                </div>
            </div>

            {{-- Formulario SMTP --}}
            <div class="card p-6">
                <div class="border-b border-[#1e2d47] pb-4 mb-5">
                    <h2 class="font-display font-bold text-lg text-white flex items-center gap-2">
                        <i class="fas fa-sliders text-amber-500"></i>
                        Parámetros del Servidor SMTP
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Si usas Gmail, recuerda activar la verificación en dos pasos y generar una <strong>Contraseña de Aplicación de 16 caracteres</strong>.
                    </p>
                </div>

                <form method="POST" action="{{ route('backoffice.correo.guardar') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="mail_mailer" value="smtp">

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label class="form-label">Servidor Host SMTP *</label>
                            <input type="text" name="mail_host" x-model="form.host" required
                                   placeholder="smtp.gmail.com" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Puerto *</label>
                            <input type="number" name="mail_port" x-model="form.port" required
                                   placeholder="587" class="form-input">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Tipo de Cifrado *</label>
                            <select name="mail_encryption" x-model="form.encryption" class="form-input">
                                <option value="tls">TLS (Puerto 587)</option>
                                <option value="ssl">SSL (Puerto 465)</option>
                                <option value="null">Sin Cifrado (Puerto 25)</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Usuario / Correo Electrónico *</label>
                            <input type="text" name="mail_username" x-model="form.username" required
                                   placeholder="tucuenta@gmail.com" class="form-input">
                        </div>
                    </div>

                    <div>
                        <label class="form-label flex items-center justify-between">
                            <span>Contraseña / Contraseña de Aplicación *</span>
                            <span class="text-[11px] text-slate-500">Dejar en blanco para mantener la clave actual</span>
                        </label>
                        <input type="password" name="mail_password" placeholder="••••••••••••••••"
                               class="form-input font-mono">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-[#1e2d47]">
                        <div>
                            <label class="form-label">Correo Remitente (From Address) *</label>
                            <input type="email" name="mail_from_address" x-model="form.from_address" required
                                   placeholder="notificaciones@facturaco.co" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Nombre Remitente (From Name) *</label>
                            <input type="text" name="mail_from_name" x-model="form.from_name" required
                                   placeholder="FacturaCO Notificaciones" class="form-input">
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-[#1e2d47]">
                        <button type="submit" class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-black font-semibold text-sm rounded-xl transition-colors flex items-center gap-2">
                            <i class="fas fa-save text-xs"></i>
                            <span>Guardar Configuración SMTP</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>

        {{-- Panel Lateral (Prueba y Alertas de Vencimiento) --}}
        <div class="space-y-6">

            {{-- Tarjeta: Probar Envío --}}
            <div class="card p-6">
                <h3 class="font-display font-bold text-base text-white flex items-center gap-2 mb-2">
                    <i class="fas fa-paper-plane text-emerald-400"></i>
                    Probar Envío de Correo
                </h3>
                <p class="text-xs text-slate-400 mb-4">
                    Envía un correo de prueba a tu bandeja de entrada para verificar que los datos SMTP estén correctos.
                </p>

                <form method="POST" action="{{ route('backoffice.correo.probar') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="form-label">Correo Destino para la prueba</label>
                        <input type="email" name="email_destino" required
                               value="{{ auth()->user()->email }}"
                               placeholder="tu@correo.com" class="form-input text-xs">
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-[#1a2235] hover:bg-emerald-500 hover:text-black border border-[#1e2d47] text-emerald-400 text-xs font-semibold rounded-xl transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-paper-plane text-[10px]"></i>
                        <span>Enviar Correo de Prueba</span>
                    </button>
                </form>
            </div>

            {{-- Tarjeta: Próximos Vencimientos de Empresas --}}
            <div class="card p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-display font-bold text-base text-white flex items-center gap-2">
                            <i class="fas fa-bell text-amber-500"></i>
                            Avisos de Vencimiento
                        </h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">Empresas con suscripción por vencer</p>
                    </div>
                </div>

                <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
                    @forelse($empresasPorVencer as $emp)
                    <div class="p-3.5 bg-[#1a2235]/60 border border-[#1e2d47] rounded-xl flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-white truncate">{{ $emp->razon_social }}</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">
                                Plan: <span class="text-amber-400">{{ $emp->plan?->nombre ?? 'Sin plan' }}</span>
                            </p>
                            <p class="text-[10px] mt-1 font-medium text-{{ $emp->dias_restantes <= 0 ? 'rose-400 font-bold' : ($emp->dias_restantes <= 5 ? 'amber-400' : 'emerald-400') }}">
                                {{ $emp->dias_restantes <= 0 ? '⚠️ Vencido' : ($emp->dias_restantes == 1 ? '⚡ Vence mañana' : '📅 Vence en ' . $emp->dias_restantes . ' días') }}
                                ({{ $emp->plan_vencimiento?->format('d/m/Y') }})
                            </p>
                        </div>

                        <form method="POST" action="{{ route('backoffice.empresas.notificar', $emp) }}">
                            @csrf
                            <button type="submit" title="Enviar recordatorio por correo"
                                    class="w-8 h-8 rounded-lg bg-amber-500/10 hover:bg-amber-500 hover:text-black border border-amber-500/20 text-amber-500 flex items-center justify-center transition-all flex-shrink-0 text-xs">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                    @empty
                    <p class="text-xs text-slate-500 text-center py-4">No hay empresas con vencimientos registrados.</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>

<script>
function correoConfigManager() {
    return {
        form: {
            host: '{{ $mailHost }}',
            port: '{{ $mailPort }}',
            encryption: '{{ $mailEncryption ?: 'tls' }}',
            username: '{{ $mailUsername }}',
            from_address: '{{ $mailFromAddress }}',
            from_name: '{{ $mailFromName }}'
        },

        aplicarPreset(tipo) {
            if (tipo === 'gmail') {
                this.form.host = 'smtp.gmail.com';
                this.form.port = '587';
                this.form.encryption = 'tls';
            } else if (tipo === 'zoho') {
                this.form.host = 'smtppro.zoho.com';
                this.form.port = '465';
                this.form.encryption = 'ssl';
            } else if (tipo === 'outlook') {
                this.form.host = 'smtp.office365.com';
                this.form.port = '587';
                this.form.encryption = 'tls';
            } else if (tipo === 'cpanel') {
                this.form.host = 'mail.tudominio.com';
                this.form.port = '465';
                this.form.encryption = 'ssl';
            }
        }
    };
}
</script>
@endsection
