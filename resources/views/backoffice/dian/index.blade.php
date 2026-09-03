@extends('backoffice.layout')

@section('title', 'Integración DIAN & Folios API')

@section('content')
<div class="space-y-6">

    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-display font-black text-2xl text-white flex items-center gap-3">
                <div class="w-10 h-10 bg-amber-500/10 rounded-xl flex items-center justify-center border border-amber-500/20 text-amber-500">
                    <i class="fas fa-file-invoice-dollar text-lg"></i>
                </div>
                Integración DIAN & API Gateway
            </h1>
            <p class="text-slate-400 text-sm mt-1">
                Configura la conexión maestra con el proveedor tecnológico (Factus) para emitir facturación electrónica en todas las empresas.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <form method="POST" action="{{ route('backoffice.dian.probar') }}">
                @csrf
                <button type="submit" class="btn-secondary text-sm flex items-center gap-2 hover:border-amber-500/50">
                    <i class="fas fa-plug text-amber-500"></i>
                    <span>Probar Conexión API</span>
                </button>
            </form>
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

    {{-- Métricas de Plataforma --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-slate-500 text-xs uppercase tracking-wider font-semibold">Proveedor Activo</span>
                <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-400">
                    <i class="fas fa-server text-xs"></i>
                </div>
            </div>
            <p class="font-display font-black text-2xl text-white uppercase">{{ $proveedorActivo }}</p>
            <p class="text-xs text-slate-500 mt-1">Motor de facturación</p>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-slate-500 text-xs uppercase tracking-wider font-semibold">Ambiente DIAN</span>
                <div class="w-8 h-8 rounded-lg bg-{{ $factusAmbiente === 'produccion' ? 'emerald' : 'amber' }}-500/10 flex items-center justify-center text-{{ $factusAmbiente === 'produccion' ? 'emerald' : 'amber' }}-400">
                    <i class="fas fa-globe text-xs"></i>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-{{ $factusAmbiente === 'produccion' ? 'emerald' : 'amber' }}-500 animate-pulse"></span>
                <p class="font-display font-black text-2xl text-white capitalize">{{ $factusAmbiente }}</p>
            </div>
            <p class="text-xs text-slate-500 mt-1">{{ $factusAmbiente === 'produccion' ? 'Emisión real legal' : 'Set de pruebas / Sandbox' }}</p>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-slate-500 text-xs uppercase tracking-wider font-semibold">Emitidas Este Mes</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-400">
                    <i class="fas fa-calendar-check text-xs"></i>
                </div>
            </div>
            <p class="font-display font-black text-2xl text-white">{{ number_format($totalFacturasMes) }}</p>
            <p class="text-xs text-slate-500 mt-1">En el mes actual</p>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-slate-500 text-xs uppercase tracking-wider font-semibold">Total Histórico DIAN</span>
                <div class="w-8 h-8 rounded-lg bg-purple-500/10 flex items-center justify-center text-purple-400">
                    <i class="fas fa-receipt text-xs"></i>
                </div>
            </div>
            <p class="font-display font-black text-2xl text-white">{{ number_format($totalFacturasDian) }}</p>
            <p class="text-xs text-slate-500 mt-1">Facturas con CUFE oficial</p>
        </div>
    </div>

    {{-- Formulario de Configuración Maestra --}}
    <div class="card p-6">
        <div class="flex items-center justify-between border-b border-[#1e2d47] pb-4 mb-6">
            <div>
                <h2 class="font-display font-bold text-lg text-white flex items-center gap-2">
                    <i class="fas fa-key text-amber-500"></i>
                    Credenciales Maestras del Proveedor (Factus)
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">
                    Estas credenciales serán usadas automáticamente por todas las empresas creadas en FacCol.
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('backoffice.dian.guardar') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

                <div>
                    <label class="form-label font-semibold text-slate-300">Proveedor de Facturación *</label>
                    <select name="dian_proveedor" class="form-input" required>
                        <option value="factus" {{ $proveedorActivo === 'factus' ? 'selected' : '' }}>Factus API REST (Recomendado SaaS)</option>
                        <option value="directo" {{ $proveedorActivo === 'directo' ? 'selected' : '' }}>Directo SOAP (Software Propio .p12)</option>
                    </select>
                    <p class="text-[11px] text-slate-500 mt-1">Usa Factus para emitir facturas multitenant sin certificados individuales.</p>
                </div>

                <div>
                    <label class="form-label font-semibold text-slate-300">Ambiente de Operación *</label>
                    <select name="dian_factus_ambiente" class="form-input" required>
                        <option value="sandbox" {{ $factusAmbiente === 'sandbox' ? 'selected' : '' }}>Sandbox (Pruebas Ilimitadas)</option>
                        <option value="produccion" {{ $factusAmbiente === 'produccion' ? 'selected' : '' }}>Producción (DIAN Real Oficial)</option>
                    </select>
                    <p class="text-[11px] text-slate-500 mt-1">Cambia a producción cuando tu cuenta Factus esté aprobada.</p>
                </div>

                <div>
                    <label class="form-label font-semibold text-slate-300">ID Rango Numeración Factus</label>
                    <input type="number" name="dian_factus_range_id" value="{{ old('dian_factus_range_id', $factusRangeId) }}"
                           placeholder="1" class="form-input">
                    <p class="text-[11px] text-slate-500 mt-1">ID de resolución configurado en tu panel de Factus.</p>
                </div>

                <div>
                    <label class="form-label font-semibold text-slate-300">Client ID (OAuth2)</label>
                    <input type="text" name="dian_factus_client_id" value="{{ old('dian_factus_client_id', $factusClientId) }}"
                           placeholder="tu_client_id" class="form-input">
                </div>

                <div>
                    <label class="form-label font-semibold text-slate-300">Client Secret (OAuth2)</label>
                    <input type="password" name="dian_factus_client_secret" placeholder="••••••••••••••••"
                           class="form-input">
                    <p class="text-[11px] text-slate-500 mt-1">Déjalo en blanco para mantener el secreto actual.</p>
                </div>

                <div>
                    <label class="form-label font-semibold text-slate-300">Correo / Username Factus</label>
                    <input type="email" name="dian_factus_username" value="{{ old('dian_factus_username', $factusUsername) }}"
                           placeholder="sandboxv2@factus.com.co" class="form-input">
                </div>

                <div>
                    <label class="form-label font-semibold text-slate-300">Contraseña Factus</label>
                    <input type="password" name="dian_factus_password" placeholder="••••••••••••••••"
                           class="form-input">
                    <p class="text-[11px] text-slate-500 mt-1">Déjalo en blanco para mantener la contraseña actual.</p>
                </div>

                <div class="sm:col-span-2 lg:col-span-2">
                    <label class="form-label font-semibold text-slate-300">Token de Acceso Directo / API Key (Opcional)</label>
                    <input type="password" name="dian_factus_token" value="{{ old('dian_factus_token', $factusToken) }}"
                           placeholder="Bearer token o API Key directa" class="form-input font-mono text-xs">
                    <p class="text-[11px] text-slate-500 mt-1">Si dispones de un Bearer Token permanente, puedes pegarlo aquí.</p>
                </div>

            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-[#1e2d47]">
                <button type="submit" class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-black font-semibold text-sm rounded-xl transition-colors flex items-center gap-2">
                    <i class="fas fa-save text-xs"></i>
                    <span>Guardar Configuración Master</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Consumo por Empresa --}}
    <div class="card p-6">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="font-display font-bold text-lg text-white flex items-center gap-2">
                    <i class="fas fa-chart-pie text-amber-500"></i>
                    Consumo y Emisión DIAN por Empresa
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">
                    Historial de facturas electrónicas enviadas y validadas por cada cliente.
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider border-b border-[#1e2d47] pb-3">
                        <th class="pb-3">Empresa</th>
                        <th class="pb-3">NIT</th>
                        <th class="pb-3">Municipio</th>
                        <th class="pb-3 text-center">Facturas Mes</th>
                        <th class="pb-3 text-center">Total Histórico</th>
                        <th class="pb-3 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1e2d47]">
                    @forelse($empresas as $emp)
                    <tr class="hover:bg-[#1a2235]/40 transition-colors">
                        <td class="py-3.5 font-semibold text-white">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 bg-amber-500/10 rounded-lg flex items-center justify-center text-amber-500 text-xs font-bold">
                                    {{ substr($emp->razon_social, 0, 2) }}
                                </div>
                                <span>{{ $emp->razon_social }}</span>
                            </div>
                        </td>
                        <td class="py-3.5 text-slate-300 font-mono text-xs">
                            {{ $emp->nit }}{{ $emp->digito_verificacion ? '-'.$emp->digito_verificacion : '' }}
                        </td>
                        <td class="py-3.5 text-slate-400 text-xs">
                            {{ $emp->municipio ?: 'No especificado' }}
                        </td>
                        <td class="py-3.5 text-center font-bold text-emerald-400">
                            {{ number_format($emp->facturas_dian_mes) }}
                        </td>
                        <td class="py-3.5 text-center font-bold text-slate-200">
                            {{ number_format($emp->facturas_dian_total) }}
                        </td>
                        <td class="py-3.5 text-right">
                            <a href="{{ route('backoffice.empresas.editar', $emp) }}"
                               class="text-xs px-3 py-1.5 bg-[#1a2235] hover:bg-[#24314d] text-slate-300 rounded-lg transition-colors inline-flex items-center gap-1.5">
                                <i class="fas fa-sliders text-[10px] text-amber-500"></i>
                                <span>Ver Empresa</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-500">
                            No hay empresas registradas aún.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
