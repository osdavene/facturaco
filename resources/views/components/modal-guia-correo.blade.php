{{-- MODAL INTERACTIVO DE GUÍA PASO A PASO PARA CONFIGURAR CORREO SMTP --}}
<div x-show="guiaModal" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm"
     @click.self="guiaModal = false">
    
    <div class="bg-[#111827] border border-[#1e2d47] rounded-2xl w-full max-w-3xl shadow-2xl slide-up max-h-[90vh] flex flex-col overflow-hidden">
        
        {{-- Header del Modal --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-[#1e2d47] bg-[#141c2e]">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-blue-500/10 rounded-xl flex items-center justify-center border border-blue-500/20 text-blue-400">
                    <i class="fas fa-book-open text-sm"></i>
                </div>
                <div>
                    <h3 class="font-display font-black text-lg text-white">Guía de Configuración de Correo SMTP</h3>
                    <p class="text-xs text-slate-400">Instrucciones paso a paso para configurar tu correo según tu proveedor</p>
                </div>
            </div>
            <button @click="guiaModal = false" class="text-slate-400 hover:text-white w-8 h-8 flex items-center justify-center rounded-lg hover:bg-[#1a2235] transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Contenedor con Tabs --}}
        <div class="p-6 overflow-y-auto flex-1 space-y-6" x-data="{ tabGuia: 'gmail' }">

            {{-- Selector de Proveedores --}}
            <div class="flex flex-wrap gap-2 p-1.5 bg-[#1a2235] rounded-xl border border-[#1e2d47]">
                <button type="button" @click="tabGuia = 'gmail'"
                        :class="tabGuia === 'gmail' ? 'bg-rose-500/20 text-rose-400 border-rose-500/40' : 'text-slate-400 hover:text-white border-transparent'"
                        class="flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold border transition-all">
                    <i class="fab fa-google text-rose-400"></i>
                    <span>Gmail / Workspace</span>
                </button>
                <button type="button" @click="tabGuia = 'outlook'"
                        :class="tabGuia === 'outlook' ? 'bg-blue-500/20 text-blue-400 border-blue-500/40' : 'text-slate-400 hover:text-white border-transparent'"
                        class="flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold border transition-all">
                    <i class="fab fa-microsoft text-blue-400"></i>
                    <span>Outlook / Office 365</span>
                </button>
                <button type="button" @click="tabGuia = 'zoho'"
                        :class="tabGuia === 'zoho' ? 'bg-amber-500/20 text-amber-400 border-amber-500/40' : 'text-slate-400 hover:text-white border-transparent'"
                        class="flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold border transition-all">
                    <i class="fas fa-briefcase text-amber-400"></i>
                    <span>Zoho Mail</span>
                </button>
                <button type="button" @click="tabGuia = 'cpanel'"
                        :class="tabGuia === 'cpanel' ? 'bg-purple-500/20 text-purple-400 border-purple-500/40' : 'text-slate-400 hover:text-white border-transparent'"
                        class="flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold border transition-all">
                    <i class="fas fa-server text-purple-400"></i>
                    <span>Hostinger / cPanel (Empresarial)</span>
                </button>
            </div>

            {{-- 1. GUÍA GMAIL --}}
            <div x-show="tabGuia === 'gmail'" x-cloak class="space-y-4">
                <div class="bg-rose-500/10 border border-rose-500/20 rounded-xl p-4 text-xs text-rose-300">
                    <strong class="font-bold">⚠️ Importante para Gmail / Google Workspace:</strong><br>
                    Google ya no permite usar tu contraseña habitual. Debes generar una <strong>Contraseña de Aplicación de 16 caracteres</strong> siguiendo estos pasos:
                </div>

                <div class="space-y-3 text-xs text-slate-300">
                    <div class="flex gap-3 items-start bg-[#1a2235]/60 p-3.5 rounded-xl border border-[#1e2d47]">
                        <span class="w-6 h-6 rounded-full bg-rose-500/20 text-rose-400 font-bold flex items-center justify-center flex-shrink-0 text-xs">1</span>
                        <div>
                            <p class="font-semibold text-white">Activa la Verificación en 2 Pasos</p>
                            <p class="text-slate-400 mt-0.5">Entra a tu cuenta de Google en <a href="https://myaccount.google.com/security" target="_blank" class="text-rose-400 underline">myaccount.google.com/security</a> y asegúrate de que la <strong>Verificación en 2 pasos</strong> esté Activada.</p>
                        </div>
                    </div>

                    <div class="flex gap-3 items-start bg-[#1a2235]/60 p-3.5 rounded-xl border border-[#1e2d47]">
                        <span class="w-6 h-6 rounded-full bg-rose-500/20 text-rose-400 font-bold flex items-center justify-center flex-shrink-0 text-xs">2</span>
                        <div>
                            <p class="font-semibold text-white">Crea una Contraseña de Aplicación</p>
                            <p class="text-slate-400 mt-0.5">Ingresa al enlace directo <a href="https://myaccount.google.com/apppasswords" target="_blank" class="text-rose-400 underline">myaccount.google.com/apppasswords</a>. En el nombre de la app escribe <code>FacturaCO</code> y haz clic en <strong>Crear</strong>.</p>
                        </div>
                    </div>

                    <div class="flex gap-3 items-start bg-[#1a2235]/60 p-3.5 rounded-xl border border-[#1e2d47]">
                        <span class="w-6 h-6 rounded-full bg-rose-500/20 text-rose-400 font-bold flex items-center justify-center flex-shrink-0 text-xs">3</span>
                        <div>
                            <p class="font-semibold text-white">Copia los 16 caracteres e ingresa los datos</p>
                            <p class="text-slate-400 mt-0.5">Google te mostrará una clave amarilla de 16 letras (ejemplo: <code>abcd efgh ijkl mnop</code>). Pégala en el campo de contraseña en FacturaCO.</p>
                        </div>
                    </div>
                </div>

                {{-- Tabla de Parámetros Gmail --}}
                <div class="bg-[#141c2e] p-4 rounded-xl border border-[#1e2d47] text-xs">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <span class="text-slate-500 block text-[10px] uppercase">Host SMTP</span>
                            <code class="text-rose-400 font-mono font-bold">smtp.gmail.com</code>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[10px] uppercase">Puerto</span>
                            <code class="text-white font-mono font-bold">587</code>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[10px] uppercase">Cifrado</span>
                            <code class="text-emerald-400 font-mono font-bold">TLS</code>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[10px] uppercase">Usuario</span>
                            <code class="text-slate-300 font-mono">tucorreo@gmail.com</code>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. GUÍA OUTLOOK --}}
            <div x-show="tabGuia === 'outlook'" x-cloak class="space-y-4">
                <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl p-4 text-xs text-blue-300">
                    <strong class="font-bold">Para cuentas @outlook.com, @hotmail.com o Microsoft 365:</strong>
                </div>

                <div class="space-y-3 text-xs text-slate-300">
                    <div class="flex gap-3 items-start bg-[#1a2235]/60 p-3.5 rounded-xl border border-[#1e2d47]">
                        <span class="w-6 h-6 rounded-full bg-blue-500/20 text-blue-400 font-bold flex items-center justify-center flex-shrink-0 text-xs">1</span>
                        <div>
                            <p class="font-semibold text-white">Parámetros Estándar</p>
                            <p class="text-slate-400 mt-0.5">Usa el servidor <code>smtp-mail.outlook.com</code> (o <code>smtp.office365.com</code> para empresas), puerto <code>587</code> y cifrado <code>TLS</code>.</p>
                        </div>
                    </div>
                    <div class="flex gap-3 items-start bg-[#1a2235]/60 p-3.5 rounded-xl border border-[#1e2d47]">
                        <span class="w-6 h-6 rounded-full bg-blue-500/20 text-blue-400 font-bold flex items-center justify-center flex-shrink-0 text-xs">2</span>
                        <div>
                            <p class="font-semibold text-white">Si tienes verificación en 2 pasos</p>
                            <p class="text-slate-400 mt-0.5">Debes ir a la seguridad de tu cuenta Microsoft y generar una <strong>Contraseña de Aplicación</strong>.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-[#141c2e] p-4 rounded-xl border border-[#1e2d47] text-xs">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <span class="text-slate-500 block text-[10px] uppercase">Host SMTP</span>
                            <code class="text-blue-400 font-mono font-bold">smtp-mail.outlook.com</code>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[10px] uppercase">Puerto</span>
                            <code class="text-white font-mono font-bold">587</code>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[10px] uppercase">Cifrado</span>
                            <code class="text-emerald-400 font-mono font-bold">TLS</code>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[10px] uppercase">Usuario</span>
                            <code class="text-slate-300 font-mono">tucorreo@outlook.com</code>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. GUÍA ZOHO --}}
            <div x-show="tabGuia === 'zoho'" x-cloak class="space-y-4">
                <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl p-4 text-xs text-amber-300">
                    <strong class="font-bold">Para cuentas empresariales de Zoho Mail:</strong>
                </div>

                <div class="space-y-3 text-xs text-slate-300">
                    <div class="flex gap-3 items-start bg-[#1a2235]/60 p-3.5 rounded-xl border border-[#1e2d47]">
                        <span class="w-6 h-6 rounded-full bg-amber-500/20 text-amber-400 font-bold flex items-center justify-center flex-shrink-0 text-xs">1</span>
                        <div>
                            <p class="font-semibold text-white">Generar Contraseña de Aplicación en Zoho</p>
                            <p class="text-slate-400 mt-0.5">Ve a <a href="https://accounts.zoho.com" target="_blank" class="text-amber-400 underline">accounts.zoho.com</a> > <strong>Seguridad</strong> > <strong>Contraseñas específicas de la aplicación</strong> y crea una para FacturaCO.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-[#141c2e] p-4 rounded-xl border border-[#1e2d47] text-xs">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <span class="text-slate-500 block text-[10px] uppercase">Host SMTP</span>
                            <code class="text-amber-400 font-mono font-bold">smtppro.zoho.com</code>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[10px] uppercase">Puerto</span>
                            <code class="text-white font-mono font-bold">465 (SSL) o 587 (TLS)</code>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[10px] uppercase">Cifrado</span>
                            <code class="text-emerald-400 font-mono font-bold">SSL</code>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[10px] uppercase">Usuario</span>
                            <code class="text-slate-300 font-mono">contacto@tudominio.com</code>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. GUÍA CPANEL / HOSTINGER --}}
            <div x-show="tabGuia === 'cpanel'" x-cloak class="space-y-4">
                <div class="bg-purple-500/10 border border-purple-500/20 rounded-xl p-4 text-xs text-purple-300">
                    <strong class="font-bold">Para correos corporativos propios (@tudominio.com en Hostinger, cPanel, Plesk, etc.):</strong>
                </div>

                <div class="space-y-3 text-xs text-slate-300">
                    <div class="flex gap-3 items-start bg-[#1a2235]/60 p-3.5 rounded-xl border border-[#1e2d47]">
                        <span class="w-6 h-6 rounded-full bg-purple-500/20 text-purple-400 font-bold flex items-center justify-center flex-shrink-0 text-xs">1</span>
                        <div>
                            <p class="font-semibold text-white">Servidor Saliente SMTP</p>
                            <p class="text-slate-400 mt-0.5">Por lo general es <code>mail.tudominio.com</code> o el nombre de host de tu servidor (ej. <code>smtp.hostinger.com</code>).</p>
                        </div>
                    </div>

                    <div class="flex gap-3 items-start bg-[#1a2235]/60 p-3.5 rounded-xl border border-[#1e2d47]">
                        <span class="w-6 h-6 rounded-full bg-purple-500/20 text-purple-400 font-bold flex items-center justify-center flex-shrink-0 text-xs">2</span>
                        <div>
                            <p class="font-semibold text-white">Usuario y Contraseña</p>
                            <p class="text-slate-400 mt-0.5">El usuario es tu correo completo (ej: <code>facturacion@miempresa.com</code>) y la contraseña es la misma que usas para iniciar sesión en Webmail.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-[#141c2e] p-4 rounded-xl border border-[#1e2d47] text-xs">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <span class="text-slate-500 block text-[10px] uppercase">Host SMTP</span>
                            <code class="text-purple-400 font-mono font-bold">mail.tudominio.com</code>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[10px] uppercase">Puerto</span>
                            <code class="text-white font-mono font-bold">465 o 587</code>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[10px] uppercase">Cifrado</span>
                            <code class="text-emerald-400 font-mono font-bold">SSL (465) / TLS (587)</code>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[10px] uppercase">Usuario</span>
                            <code class="text-slate-300 font-mono">contacto@tudominio.com</code>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Footer del Modal --}}
        <div class="px-6 py-4 border-t border-[#1e2d47] bg-[#141c2e] flex justify-end">
            <button type="button" @click="guiaModal = false"
                    class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-black font-semibold text-xs rounded-xl transition-colors">
                Entendido, cerrar guía
            </button>
        </div>

    </div>
</div>
