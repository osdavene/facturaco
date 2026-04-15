@extends('layouts.app')
@section('title', 'Configuración de Empresa')
@section('page-title', 'Empresa · Configuración')

@section('content')

{{-- Alerta resolución DIAN --}}
@if($empresa->resolucion_vencimiento)
    @if(!$empresa->resolucion_vigente)
    <div class="bg-red-500/10 border border-red-500/30 text-red-400
                rounded-xl px-5 py-4 mb-5 flex items-center gap-3">
        <i class="fas fa-exclamation-triangle text-lg"></i>
        <div>
            <div class="font-semibold">¡Resolución DIAN vencida!</div>
            <div class="text-sm opacity-80">Venció el {{ $empresa->resolucion_vencimiento->format('d/m/Y') }}. Renueva urgentemente para poder facturar.</div>
        </div>
    </div>
    @elseif($empresa->dias_para_vencer <= 30)
    <div class="bg-amber-500/10 border border-amber-500/30 text-amber-400
                rounded-xl px-5 py-4 mb-5 flex items-center gap-3">
        <i class="fas fa-clock text-lg"></i>
        <div>
            <div class="font-semibold">Resolución DIAN próxima a vencer</div>
            <div class="text-sm opacity-80">Vence en {{ $empresa->dias_para_vencer }} días ({{ $empresa->resolucion_vencimiento->format('d/m/Y') }}). Renueva pronto.</div>
        </div>
    </div>
    @endif
@endif

<form method="POST" action="{{ route('empresa.update') }}" enctype="multipart/form-data">
    @csrf @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- COLUMNA IZQUIERDA --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- SECCIÓN 1: Identificación --}}
            <div class="card p-6">
                <h2 class="font-display font-bold text-base mb-4 flex items-center gap-2">
                    <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center
                                 text-black text-xs font-black">1</span>
                    Identificación de la Empresa
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="form-label">
                            Razón Social *
                        </label>
                        <input type="text" name="razon_social"
                               value="{{ old('razon_social', $empresa->razon_social) }}"
                               data-uppercase
                               class="form-input @error('razon_social') border-red-500 @enderror"
                               style="color:#e2e8f0">
                        @error('razon_social') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">
                            Nombre Comercial
                        </label>
                        <input type="text" name="nombre_comercial"
                               value="{{ old('nombre_comercial', $empresa->nombre_comercial) }}"
                               data-uppercase
                               placeholder="NOMBRE COMERCIAL"
                               class="form-input"
                               style="color:#e2e8f0">
                    </div>
                    <div>
                        <label class="form-label">
                            Tipo de Persona
                        </label>
                        <select name="tipo_persona"
                                class="form-input"
                                style="color:#e2e8f0">
                            <option value="juridica" {{ old('tipo_persona',$empresa->tipo_persona)=='juridica' ? 'selected':'' }}>Persona Jurídica</option>
                            <option value="natural"  {{ old('tipo_persona',$empresa->tipo_persona)=='natural'  ? 'selected':'' }}>Persona Natural</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="col-span-2">
                            <label class="form-label">NIT *</label>
                            <input type="text" name="nit"
                                   value="{{ old('nit', $empresa->nit) }}"
                                   placeholder="900000000"
                                   data-numeric
                                   class="form-input @error('nit') border-red-500 @enderror"
                                   style="color:#e2e8f0">
                            @error('nit') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">DV</label>
                            <input type="text" name="digito_verificacion" maxlength="1"
                                   value="{{ old('digito_verificacion', $empresa->digito_verificacion) }}"
                                   placeholder="0"
                                   data-numeric
                                   class="form-input"
                                   style="color:#e2e8f0">
                        </div>
                    </div>
                    <div>
                        <label class="form-label">
                            Régimen Tributario
                        </label>
                        <select name="regimen"
                                class="form-input"
                                style="color:#e2e8f0">
                            <option value="responsable_iva" {{ old('regimen',$empresa->regimen)=='responsable_iva' ? 'selected':'' }}>Responsable de IVA</option>
                            <option value="simple"          {{ old('regimen',$empresa->regimen)=='simple'          ? 'selected':'' }}>Régimen Simple</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- SECCIÓN 2: Contacto --}}
            <div class="card p-6">
                <h2 class="font-display font-bold text-base mb-4 flex items-center gap-2">
                    <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center
                                 text-black text-xs font-black">2</span>
                    Contacto y Ubicación
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" name="email"
                               value="{{ old('email', $empresa->email) }}"
                               placeholder="info@empresa.com"
                               class="form-input"
                               style="color:#e2e8f0">
                    </div>
                    <div>
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono"
                               value="{{ old('telefono', $empresa->telefono) }}"
                               placeholder="601 1234567"
                               data-numeric
                               class="form-input"
                               style="color:#e2e8f0">
                    </div>
                    <div>
                        <label class="form-label">Celular</label>
                        <input type="text" name="celular"
                               value="{{ old('celular', $empresa->celular) }}"
                               placeholder="300 1234567"
                               data-numeric
                               class="form-input"
                               style="color:#e2e8f0">
                    </div>
                    <div>
                        <label class="form-label">Sitio Web</label>
                        <input type="text" name="sitio_web"
                               value="{{ old('sitio_web', $empresa->sitio_web) }}"
                               placeholder="www.empresa.com"
                               class="form-input"
                               style="color:#e2e8f0">
                    </div>
                    <div>
                        <label class="form-label">Departamento</label>
                        <input type="text" name="departamento"
                               value="{{ old('departamento', $empresa->departamento) }}"
                               placeholder="CÓRDOBA"
                               data-uppercase
                               class="form-input"
                               style="color:#e2e8f0">
                    </div>
                    <div>
                        <label class="form-label">Municipio</label>
                        <input type="text" name="municipio"
                               value="{{ old('municipio', $empresa->municipio) }}"
                               placeholder="MONTERÍA"
                               data-uppercase
                               class="form-input"
                               style="color:#e2e8f0">
                    </div>
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="direccion"
                               value="{{ old('direccion', $empresa->direccion) }}"
                               placeholder="CRA 5 # 10-20"
                               data-uppercase
                               class="form-input"
                               style="color:#e2e8f0">
                    </div>
                </div>
            </div>

            {{-- SECCIÓN 3: DIAN --}}
            <div class="card p-6">
                <h2 class="font-display font-bold text-base mb-4 flex items-center gap-2">
                    <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center
                                 text-black text-xs font-black">3</span>
                    Resolución DIAN — Factura Electrónica
                </h2>

                <div class="flex items-center gap-3 mb-5 p-3 bg-[#1a2235] rounded-xl">
                    <input type="checkbox" name="factura_electronica" value="1"
                           id="factura_electronica"
                           class="w-4 h-4 accent-amber-500"
                           {{ old('factura_electronica', $empresa->factura_electronica) ? 'checked':'' }}>
                    <label for="factura_electronica" class="text-sm text-slate-300 cursor-pointer">
                        Habilitado para <strong>Factura Electrónica DIAN</strong>
                    </label>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="form-label">
                            Prefijo Factura
                        </label>
                        <input type="text" name="prefijo_factura"
                               value="{{ old('prefijo_factura', $empresa->prefijo_factura) }}"
                               placeholder="FE"
                               data-uppercase
                               class="form-input"
                               style="color:#e2e8f0">
                    </div>
                    <div>
                        <label class="form-label">
                            N° Resolución
                        </label>
                        <input type="text" name="resolucion_numero"
                               value="{{ old('resolucion_numero', $empresa->resolucion_numero) }}"
                               placeholder="18764030"
                               data-numeric
                               class="form-input"
                               style="color:#e2e8f0">
                    </div>
                    <div>
                        <label class="form-label">
                            Fecha Resolución
                        </label>
                        <input type="date" name="resolucion_fecha"
                               value="{{ old('resolucion_fecha', $empresa->resolucion_fecha?->format('Y-m-d')) }}"
                               class="form-input"
                               style="color:#e2e8f0">
                    </div>
                    <div>
                        <label class="form-label">
                            Fecha Vencimiento
                        </label>
                        <input type="date" name="resolucion_vencimiento"
                               value="{{ old('resolucion_vencimiento', $empresa->resolucion_vencimiento?->format('Y-m-d')) }}"
                               class="form-input"
                               style="color:#e2e8f0">
                    </div>
                    <div>
                        <label class="form-label">
                            Desde Consecutivo
                        </label>
                        <input type="text" name="consecutivo_desde"
                               value="{{ old('consecutivo_desde', $empresa->consecutivo_desde) }}"
                               data-numeric
                               class="form-input"
                               style="color:#e2e8f0">
                    </div>
                    <div>
                        <label class="form-label">
                            Hasta Consecutivo
                        </label>
                        <input type="text" name="consecutivo_hasta"
                               value="{{ old('consecutivo_hasta', $empresa->consecutivo_hasta) }}"
                               data-numeric
                               class="form-input"
                               style="color:#e2e8f0">
                    </div>
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="form-label">
                            Clave Técnica DIAN
                        </label>
                        <input type="text" name="clave_tecnica"
                               value="{{ old('clave_tecnica', $empresa->clave_tecnica) }}"
                               placeholder="CLAVE TÉCNICA PROPORCIONADA POR LA DIAN"
                               data-uppercase
                               class="form-input"
                               style="color:#e2e8f0">
                    </div>
                </div>
            </div>

            {{-- SECCIÓN 4: Impuestos por defecto --}}
            <div class="card p-6">
                <h2 class="font-display font-bold text-base mb-4 flex items-center gap-2">
                    <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center
                                 text-black text-xs font-black">4</span>
                    Impuestos por Defecto
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="form-label">
                            % IVA por Defecto
                        </label>
                        <select name="iva_defecto"
                                class="form-input"
                                style="color:#e2e8f0">
                            <option value="0"  {{ old('iva_defecto',$empresa->iva_defecto)==0  ? 'selected':'' }}>0% - Excluido</option>
                            <option value="5"  {{ old('iva_defecto',$empresa->iva_defecto)==5  ? 'selected':'' }}>5%</option>
                            <option value="19" {{ old('iva_defecto',$empresa->iva_defecto)==19 ? 'selected':'' }}>19% - General</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">
                            % ReteFuente por Defecto
                        </label>
                        <input type="text" inputmode="decimal" name="retefuente_defecto"
                               value="{{ old('retefuente_defecto', $empresa->retefuente_defecto) }}"
                               data-numeric
                               class="form-input"
                               style="color:#e2e8f0">
                    </div>
                    <div>
                        <label class="form-label">
                            % ReteICA por Defecto
                        </label>
                        <input type="text" inputmode="decimal" name="reteica_defecto"
                               value="{{ old('reteica_defecto', $empresa->reteica_defecto) }}"
                               data-numeric
                               class="form-input"
                               style="color:#e2e8f0">
                    </div>
                </div>
            </div>

            {{-- SECCIÓN 5: Textos --}}
            <div class="card p-6">
                <h2 class="font-display font-bold text-base mb-4 flex items-center gap-2">
                    <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center
                                 text-black text-xs font-black">5</span>
                    Textos de Factura
                </h2>
                <div class="space-y-4">
                    <div>
                        <label class="form-label">
                            Pie de Página de Factura
                        </label>
                        <textarea name="pie_factura" rows="2"
                                  placeholder="TEXTO QUE APARECE AL PIE DE CADA FACTURA..."
                                  data-uppercase
                                  class="form-input resize-none"
                                  style="color:#e2e8f0">{{ old('pie_factura', $empresa->pie_factura) }}</textarea>
                    </div>
                    <div>
                        <label class="form-label">
                            Términos y Condiciones
                        </label>
                        <textarea name="terminos_condiciones" rows="3"
                                  placeholder="TÉRMINOS Y CONDICIONES DE VENTA..."
                                  data-uppercase
                                  class="form-input resize-none"
                                  style="color:#e2e8f0">{{ old('terminos_condiciones', $empresa->terminos_condiciones) }}</textarea>
                    </div>
                </div>
            </div>

        </div>

        {{-- COLUMNA DERECHA --}}
        <div class="space-y-4">

            {{-- Logo --}}
            <div class="card p-6">
                <h3 class="font-display font-bold text-base mb-4 flex items-center gap-2">
                    <i class="fas fa-image text-amber-500 text-sm"></i>
                    Logo de la Empresa
                </h3>

                {{-- Preview actual --}}
                <div class="flex flex-col items-center mb-4">
                    @if($empresa->logo)
                    <div class="relative mb-3">
                        <img src="{{ Storage::url($empresa->logo) }}"
                             alt="Logo"
                             class="w-32 h-32 object-contain rounded-xl bg-white p-2">
                        <form method="POST" action="{{ route('empresa.logo.delete') }}"
                              class="absolute -top-2 -right-2"
                              onsubmit="return confirm('¿Eliminar el logo?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="w-6 h-6 bg-red-500 text-white rounded-full
                                           flex items-center justify-center text-xs
                                           hover:bg-red-600 transition-colors">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                    </div>
                    @else
                    <div class="w-32 h-32 bg-[#1a2235] border-2 border-dashed border-[#1e2d47]
                                rounded-xl flex flex-col items-center justify-center mb-3
                                text-slate-600">
                        <i class="fas fa-image text-3xl mb-2"></i>
                        <span class="text-xs">Sin logo</span>
                    </div>
                    @endif
                </div>

                <label class="form-label">
                    Subir Logo <span class="text-slate-600">(PNG, JPG máx 2MB)</span>
                </label>
                <input type="file" name="logo" accept="image/*"
                       class="form-input text-slate-400 focus:outline-none focus:border-amber-500
                              file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0
                              file:text-xs file:font-semibold file:bg-amber-500 file:text-black
                              hover:file:bg-amber-600 cursor-pointer">
            </div>

            {{-- Info resolución --}}
            <div class="card p-5">
                <h3 class="font-display font-bold text-sm mb-4 flex items-center gap-2">
                    <i class="fas fa-file-contract text-amber-500"></i>
                    Estado Resolución DIAN
                </h3>
                @if($empresa->resolucion_numero)
                <div class="space-y-3">
                    <div>
                        <div class="text-xs text-slate-500 uppercase tracking-wider mb-1">Resolución</div>
                        <div class="text-sm font-semibold" style="color:#e2e8f0">
                            {{ number_format($empresa->resolucion_numero, 0, ',', '.') }}
                        </div>
                    </div>
                    @if($empresa->resolucion_vencimiento)
                    <div>
                        <div class="text-xs text-slate-500 uppercase tracking-wider mb-1">Vencimiento</div>
                        <div class="text-sm font-semibold
                            {{ $empresa->resolucion_vigente ? 'text-emerald-500' : 'text-red-400' }}">
                            {{ $empresa->resolucion_vencimiento->format('d/m/Y') }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 uppercase tracking-wider mb-1">Estado</div>
                        @if($empresa->resolucion_vigente)
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold
                                     px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-500">
                            <span class="w-1.5 h-1.5 rounded-full bg-current animate-pulse"></span>
                            Vigente · {{ $empresa->dias_para_vencer }} días
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold
                                     px-2.5 py-1 rounded-full bg-red-500/10 text-red-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            Vencida
                        </span>
                        @endif
                    </div>
                    @endif
                    <div>
                        <div class="text-xs text-slate-500 uppercase tracking-wider mb-1">Consecutivos</div>
                        <div class="text-sm font-mono" style="color:#e2e8f0">
                            {{ number_format($empresa->consecutivo_desde) }}
                            — {{ number_format($empresa->consecutivo_hasta) }}
                        </div>
                        <div class="text-xs text-slate-500 mt-1">
                            Actual: {{ $empresa->consecutivo_actual }}
                        </div>
                    </div>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-exclamation-circle text-amber-500 text-2xl mb-2 block"></i>
                    <div class="text-xs text-slate-500">No hay resolución configurada</div>
                </div>
                @endif
            </div>

            {{-- Resumen empresa --}}
            <div class="card p-5">
                <h3 class="font-display font-bold text-sm mb-4 flex items-center gap-2">
                    <i class="fas fa-building text-amber-500"></i>
                    Resumen
                </h3>
                <div class="space-y-3 text-sm">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center
                                    font-black text-black text-xs flex-shrink-0">
                            {{ strtoupper(substr($empresa->razon_social, 0, 2)) }}
                        </div>
                        <div>
                            <div class="font-semibold text-xs" style="color:#e2e8f0">
                                {{ $empresa->razon_social }}
                            </div>
                            <div class="text-xs text-slate-500">NIT: {{ $empresa->nit_formateado }}</div>
                        </div>
                    </div>
                    @if($empresa->municipio)
                    <div class="flex items-center gap-2 text-xs text-slate-400">
                        <i class="fas fa-map-marker-alt w-4 text-slate-600"></i>
                        {{ $empresa->municipio }}{{ $empresa->departamento ? ', '.$empresa->departamento : '' }}
                    </div>
                    @endif
                    @if($empresa->email)
                    <div class="flex items-center gap-2 text-xs text-slate-400">
                        <i class="fas fa-envelope w-4 text-slate-600"></i>
                        {{ $empresa->email }}
                    </div>
                    @endif
                    @if($empresa->telefono)
                    <div class="flex items-center gap-2 text-xs text-slate-400">
                        <i class="fas fa-phone w-4 text-slate-600"></i>
                        {{ $empresa->telefono }}
                    </div>
                    @endif
                </div>
            </div>

            {{-- SECCIÓN: Configuración de Correo --}}
            <div class="card p-6">
                <h2 class="font-display font-bold text-base mb-1 flex items-center gap-2">
                    <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center
                                text-black text-xs font-black">
                        <i class="fas fa-envelope text-xs"></i>
                    </span>
                    Configuración de Correo
                </h2>
                <p class="text-xs text-slate-500 mb-5">
                    Configura el servidor SMTP para enviar facturas por email directamente desde el sistema.
                    Cada empresa puede tener su propio correo de envío.
                </p>

                {{-- Estado actual --}}
                @if($empresa->mail_configurado)
                <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400
                            rounded-xl px-4 py-3 mb-4 flex items-center gap-2 text-sm">
                    <i class="fas fa-check-circle"></i>
                    Correo configurado · Servidor: <strong>{{ $empresa->mail_host }}</strong>
                    · Remitente: <strong>{{ $empresa->mail_from_address ?: $empresa->email }}</strong>
                </div>
                @else
                <div class="bg-amber-500/10 border border-amber-500/30 text-amber-400
                            rounded-xl px-4 py-3 mb-4 flex items-center gap-2 text-sm">
                    <i class="fas fa-exclamation-triangle"></i>
                    Correo no configurado. Sin esto no podrás enviar facturas por email.
                </div>
                @endif

                {{-- Selector de proveedor --}}
                <div class="bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-4 mb-4">
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">
                        Preconfigurar proveedor
                    </div>
                    <div class="flex flex-wrap gap-2 mb-3">
                        <button type="button" onclick="precargarProveedor('resend')"
                                class="proveedor-btn text-xs bg-[#141c2e] border border-[#1e2d47] hover:border-amber-500/60
                                    text-slate-400 hover:text-amber-400 px-3 py-1.5 rounded-lg transition-colors">
                            <i class="fas fa-bolt mr-1 text-amber-400"></i> Resend
                        </button>
                        <button type="button" onclick="precargarProveedor('gmail')"
                                class="proveedor-btn text-xs bg-[#141c2e] border border-[#1e2d47] hover:border-amber-500/60
                                    text-slate-400 hover:text-amber-400 px-3 py-1.5 rounded-lg transition-colors">
                            <i class="fab fa-google mr-1 text-red-400"></i> Gmail
                        </button>
                        <button type="button" onclick="precargarProveedor('outlook')"
                                class="proveedor-btn text-xs bg-[#141c2e] border border-[#1e2d47] hover:border-amber-500/60
                                    text-slate-400 hover:text-amber-400 px-3 py-1.5 rounded-lg transition-colors">
                            <i class="fab fa-microsoft mr-1 text-blue-300"></i> Outlook / Hotmail
                        </button>
                        <button type="button" onclick="precargarProveedor('yahoo')"
                                class="proveedor-btn text-xs bg-[#141c2e] border border-[#1e2d47] hover:border-amber-500/60
                                    text-slate-400 hover:text-amber-400 px-3 py-1.5 rounded-lg transition-colors">
                            <i class="fab fa-yahoo mr-1 text-purple-400"></i> Yahoo
                        </button>
                        <button type="button" onclick="precargarProveedor('mailgun')"
                                class="proveedor-btn text-xs bg-[#141c2e] border border-[#1e2d47] hover:border-amber-500/60
                                    text-slate-400 hover:text-amber-400 px-3 py-1.5 rounded-lg transition-colors">
                            <i class="fas fa-envelope mr-1 text-slate-400"></i> Mailgun
                        </button>
                        <button type="button" onclick="precargarProveedor('sendgrid')"
                                class="proveedor-btn text-xs bg-[#141c2e] border border-[#1e2d47] hover:border-amber-500/60
                                    text-slate-400 hover:text-amber-400 px-3 py-1.5 rounded-lg transition-colors">
                            <i class="fas fa-paper-plane mr-1 text-blue-400"></i> SendGrid
                        </button>
                    </div>

                    {{-- Panel de instrucciones dinámico --}}
                    <div id="mail-instrucciones" class="hidden text-xs leading-relaxed"></div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    {{-- Host --}}
                    <div>
                        <label class="form-label">Servidor SMTP (Host)</label>
                        <input type="text" name="mail_host" id="mail_host"
                            value="{{ old('mail_host', $empresa->mail_host) }}"
                            placeholder="smtp.resend.com"
                            class="form-input">
                    </div>

                    {{-- Puerto --}}
                    <div>
                        <label class="form-label">Puerto</label>
                        <select name="mail_port" id="mail_port" class="form-input">
                            <option value="587" {{ old('mail_port', $empresa->mail_port ?? 587) == 587 ? 'selected' : '' }}>587 — TLS / STARTTLS (recomendado)</option>
                            <option value="465" {{ old('mail_port', $empresa->mail_port) == 465 ? 'selected' : '' }}>465 — SSL</option>
                            <option value="25"  {{ old('mail_port', $empresa->mail_port) == 25  ? 'selected' : '' }}>25 — Sin cifrado</option>
                        </select>
                    </div>

                    {{-- Cifrado --}}
                    <div>
                        <label class="form-label">Cifrado</label>
                        <select name="mail_encryption" id="mail_encryption" class="form-input">
                            <option value="tls" {{ old('mail_encryption', $empresa->mail_encryption ?? 'tls') == 'tls' ? 'selected' : '' }}>TLS / STARTTLS</option>
                            <option value="ssl" {{ old('mail_encryption', $empresa->mail_encryption) == 'ssl' ? 'selected' : '' }}>SSL</option>
                            <option value=""    {{ old('mail_encryption', $empresa->mail_encryption) == ''    ? 'selected' : '' }}>Ninguno</option>
                        </select>
                    </div>

                    {{-- Usuario --}}
                    <div>
                        <label class="form-label">Usuario SMTP</label>
                        <input type="text" name="mail_username" id="mail_username"
                            value="{{ old('mail_username', $empresa->mail_username) }}"
                            placeholder="tucorreo@gmail.com"
                            autocomplete="off"
                            class="form-input">
                    </div>

                    {{-- Contraseña --}}
                    <div class="sm:col-span-2">
                        <label class="form-label" id="mail_password_label">Contraseña / API Key</label>
                        <div class="relative">
                            <input type="password" name="mail_password" id="mail_password"
                                placeholder="{{ $empresa->mail_password ? '••••••••••••••• (guardada — deja vacío para no cambiar)' : 'Contraseña de aplicación o API key' }}"
                                autocomplete="new-password"
                                class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5 pr-10
                                        text-sm text-slate-200 placeholder-slate-600
                                        focus:outline-none focus:border-amber-500 transition-colors">
                            <button type="button" onclick="togglePassword()"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300">
                                <i class="fas fa-eye text-xs" id="eye-icon"></i>
                            </button>
                        </div>
                        <p class="text-xs text-slate-600 mt-1" id="mail_password_hint">
                            Deja vacío para conservar la contraseña guardada.
                        </p>
                    </div>

                    {{-- From address --}}
                    <div>
                        <label class="form-label">Correo remitente (From)</label>
                        <input type="email" name="mail_from_address" id="mail_from_address"
                            value="{{ old('mail_from_address', $empresa->mail_from_address ?: $empresa->email) }}"
                            placeholder="facturacion@miempresa.com"
                            class="form-input">
                        <p class="text-xs text-slate-600 mt-1">El correo que verá el cliente como remitente.</p>
                    </div>

                    {{-- From name --}}
                    <div>
                        <label class="form-label">Nombre remitente</label>
                        <input type="text" name="mail_from_name" id="mail_from_name"
                            value="{{ old('mail_from_name', $empresa->mail_from_name ?: $empresa->razon_social) }}"
                            placeholder="Mi Empresa S.A.S."
                            class="form-input">
                        <p class="text-xs text-slate-600 mt-1">El nombre que verá el cliente al recibir el correo.</p>
                    </div>

                </div>

                {{-- Probar correo --}}
                <div class="mt-5 pt-4 border-t border-[#1e2d47]">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">
                        Probar configuración
                    </p>
                    @if($empresa->mail_configurado)
                    <form method="POST" action="{{ route('empresa.probarMail') }}" class="flex gap-3 flex-wrap">
                        @csrf
                        <input type="email" name="email_prueba"
                            placeholder="correo@prueba.com"
                            class="flex-1 min-w-48 bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2
                                    text-sm text-slate-200 placeholder-slate-600
                                    focus:outline-none focus:border-amber-500 transition-colors">
                        <button type="submit"
                                class="flex items-center gap-2 bg-emerald-500/20 border border-emerald-500/30
                                    text-emerald-400 hover:bg-emerald-500/30 font-semibold text-sm
                                    px-4 py-2 rounded-xl transition-colors">
                            <i class="fas fa-paper-plane text-xs"></i> Enviar correo de prueba
                        </button>
                    </form>
                    @else
                    <p class="text-xs text-slate-600">
                        Guarda la configuración SMTP primero para poder probar el envío.
                    </p>
                    @endif
                </div>

            </div>

            {{-- SECCIÓN: Pagos en Línea (Wompi) --}}
            <div class="card p-6">
                <h2 class="font-display font-bold text-base mb-1 flex items-center gap-2">
                    <span class="w-6 h-6 bg-amber-500 rounded-lg flex items-center justify-center
                                text-black text-xs font-black">
                        <i class="fas fa-credit-card text-xs"></i>
                    </span>
                    Pagos en Línea — Wompi
                </h2>
                <p class="text-xs text-slate-500 mb-5">
                    Permite a tus clientes pagar facturas en línea con tarjeta, PSE o Nequi.
                    Obtén tu llave pública en
                    <a href="https://comercios.wompi.co" target="_blank"
                    class="text-amber-400 hover:underline">comercios.wompi.co</a>
                </p>

                @if($empresa->wompi_configurado)
                <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400
                            rounded-xl px-4 py-3 mb-4 flex items-center gap-2 text-sm">
                    <i class="fas fa-check-circle"></i>
                    Wompi configurado · El botón de pago aparece en las facturas emitidas.
                </div>
                @else
                <div class="bg-amber-500/10 border border-amber-500/30 text-amber-400
                            rounded-xl px-4 py-3 mb-4 flex items-center gap-2 text-sm">
                    <i class="fas fa-exclamation-triangle"></i>
                    Sin configurar · Los clientes no podrán pagar en línea desde las facturas.
                </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">
                            Llave Pública Wompi
                        </label>
                        <div class="relative">
                            <input type="password" name="wompi_public_key" id="wompi_key"
                                placeholder="{{ $empresa->wompi_public_key ? '••••••••••••••• (guardada)' : 'pub_prod_xxxxxxxxxx' }}"
                                autocomplete="new-password"
                                class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5 pr-10
                                        text-sm text-slate-200 placeholder-slate-600
                                        focus:outline-none focus:border-amber-500 transition-colors">
                            <button type="button" onclick="toggleWompiKey()"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300">
                                <i class="fas fa-eye text-xs" id="eye-wompi"></i>
                            </button>
                        </div>
                        <p class="text-xs text-slate-600 mt-1">
                            Empieza con <code class="bg-[#1a2235] px-1 rounded text-amber-400">pub_prod_</code>
                            para producción o
                            <code class="bg-[#1a2235] px-1 rounded text-blue-400">pub_test_</code>
                            para pruebas.
                            Déjalo vacío para conservar la key guardada.
                        </p>
                    </div>

                    <div>
                        <label class="form-label">
                            Moneda
                        </label>
                        <select name="wompi_currency"
                                class="form-input">
                            <option value="COP" {{ ($empresa->wompi_currency ?? 'COP') === 'COP' ? 'selected' : '' }}>
                                COP — Peso Colombiano
                            </option>
                            <option value="USD" {{ ($empresa->wompi_currency ?? 'COP') === 'USD' ? 'selected' : '' }}>
                                USD — Dólar
                            </option>
                        </select>
                    </div>
                </div>

                {{-- Events Key para verificación de webhooks --}}
                <div class="mt-4">
                    <label class="form-label">
                        Llave de Eventos (Webhook)
                        <span class="ml-1 text-slate-600 font-normal normal-case tracking-normal">— opcional, para verificar firmas</span>
                    </label>
                    <div class="relative">
                        <input type="password" name="wompi_events_key" id="wompi_events_key"
                            placeholder="{{ $empresa->wompi_webhook_configurado ? '••••••••••••••• (guardada)' : 'test_events_xxx / prod_events_xxx' }}"
                            autocomplete="new-password"
                            class="w-full bg-[#1a2235] border border-[#1e2d47] rounded-xl px-4 py-2.5 pr-10
                                    text-sm text-slate-200 placeholder-slate-600
                                    focus:outline-none focus:border-amber-500 transition-colors">
                        <button type="button" onclick="toggleEventsKey()"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300">
                            <i class="fas fa-eye text-xs" id="eye-events"></i>
                        </button>
                    </div>
                    <p class="text-xs text-slate-600 mt-1">
                        Encuéntrala en <span class="text-slate-400">Desarrolladores → Llaves de API → Llave de eventos</span>.
                        Permite verificar que los webhooks provienen realmente de Wompi.
                    </p>
                </div>

                {{-- Webhook URL --}}
                <div class="mt-4 bg-[#1a2235] border border-[#1e2d47] rounded-xl p-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">
                        URL del Webhook (configúrala en Wompi)
                    </p>
                    <div class="flex items-center gap-2">
                        <code class="flex-1 text-xs text-emerald-400 bg-[#141c2e] px-3 py-2 rounded-lg font-mono break-all">
                            {{ route('wompi.webhook') }}
                        </code>
                        <button type="button"
                                onclick="navigator.clipboard.writeText('{{ route('wompi.webhook') }}').then(() => this.textContent='¡Copiado!')"
                                class="text-xs text-slate-500 hover:text-slate-300 whitespace-nowrap px-2 py-1">
                            Copiar
                        </button>
                    </div>
                    <p class="text-xs text-slate-600 mt-2">
                        Ve a <span class="text-slate-400">comercios.wompi.co → Desarrolladores → Webhooks</span> y registra esta URL
                        para que los pagos se confirmen automáticamente.
                    </p>
                </div>

                {{-- Cómo obtener las llaves --}}
                <div class="mt-4 bg-[#1a2235] border border-[#1e2d47] rounded-xl p-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">
                        Cómo configurar Wompi paso a paso
                    </p>
                    <ol class="text-xs text-slate-500 space-y-1 list-none">
                        <li><span class="text-amber-500 font-bold mr-2">1.</span>Ve a <span class="text-slate-300">comercios.wompi.co</span> y crea tu cuenta</li>
                        <li><span class="text-amber-500 font-bold mr-2">2.</span>Entra a <span class="text-slate-300">Desarrolladores → Llaves de API</span></li>
                        <li><span class="text-amber-500 font-bold mr-2">3.</span>Copia la <span class="text-slate-300">Llave pública</span> (pub_prod_...) y la <span class="text-slate-300">Llave de eventos</span></li>
                        <li><span class="text-amber-500 font-bold mr-2">4.</span>En <span class="text-slate-300">Desarrolladores → Webhooks</span>, registra la URL de webhook de arriba</li>
                        <li><span class="text-amber-500 font-bold mr-2">5.</span>Pega las llaves aquí y guarda la configuración</li>
                    </ol>
                </div>
            </div>

            {{-- Botón guardar --}}
            <button type="submit"
                    class="w-full py-3 bg-amber-500 hover:bg-amber-600 text-black
                           font-bold rounded-xl transition-colors flex items-center justify-center gap-2">
                <i class="fas fa-save"></i> Guardar Configuración
            </button>

        </div>
    </div>
</form>

@push('scripts')
<script>
function precargarResend() {
    document.getElementById('mail_host').value       = 'smtp.resend.com';
    document.getElementById('mail_port').value       = '465';
    document.getElementById('mail_encryption').value = 'ssl';
    document.getElementById('mail_username').value   = 'resend';
    document.getElementById('mail_password').focus();
}
function precargarGmail() {
    document.getElementById('mail_host').value       = 'smtp.gmail.com';
    document.getElementById('mail_port').value       = '587';
    document.getElementById('mail_encryption').value = 'tls';
    document.getElementById('mail_username').value   = '';
    document.getElementById('mail_username').placeholder = 'tucorreo@gmail.com';
    document.getElementById('mail_username').focus();
}
function precargarOutlook() {
    document.getElementById('mail_host').value       = 'smtp-mail.outlook.com';
    document.getElementById('mail_port').value       = '587';
    document.getElementById('mail_encryption').value = 'tls';
    document.getElementById('mail_username').value   = '';
    document.getElementById('mail_username').placeholder = 'tucorreo@hotmail.com';
    document.getElementById('mail_username').focus();
}
function togglePassword() {
    const input = document.getElementById('mail_password');
    const icon  = document.getElementById('eye-icon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

function toggleWompiKey() {
    const input = document.getElementById('wompi_key');
    const icon  = document.getElementById('eye-wompi');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

function toggleEventsKey() {
    const input = document.getElementById('wompi_events_key');
    const icon  = document.getElementById('eye-events');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

</script>
@endpush

@endsection

@push('scripts')
<script>
// Mayúsculas
document.querySelectorAll('[data-uppercase]').forEach(el => {
    el.addEventListener('input', function() {
        const pos = this.selectionStart;
        this.value = this.value.toUpperCase();
        this.setSelectionRange(pos, pos);
    });
});

// Numérico
document.querySelectorAll('[data-numeric]').forEach(el => {
    el.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9.,]/g, '');
    });
});

// Preview logo al seleccionar archivo
document.querySelector('input[name="logo"]').addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        // Buscar si hay preview existente
        let img = document.querySelector('.logo-preview');
        if (!img) {
            img = document.createElement('img');
            img.className = 'logo-preview w-32 h-32 object-contain rounded-xl bg-white p-2 mx-auto mb-3';
            this.closest('.bg-\\[\\#141c2e\\]').querySelector('.flex.flex-col').prepend(img);
        }
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
});
</script>
@endpush