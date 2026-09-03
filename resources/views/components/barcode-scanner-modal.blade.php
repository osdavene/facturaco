{{-- Modal Lector de Código de Barras / QR con Cámara para Celulares y Laptops --}}
<div id="modal-escaner" class="fixed inset-0 z-[100] bg-black/85 backdrop-blur-md hidden flex items-center justify-center p-3 sm:p-4">
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl max-w-md w-full overflow-hidden shadow-2xl animate-in fade-in zoom-in duration-200 flex flex-col max-h-[92vh]">

        {{-- Header --}}
        <div class="px-4 py-3.5 border-b border-[#1e2d47] flex items-center justify-between flex-shrink-0 bg-[#0d1526]">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400">
                    <i class="fas fa-barcode text-base"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-200 text-sm">Lector de Código de Barras & QR</h3>
                    <p class="text-[11px] text-slate-400">Apunta la cámara a las barras o al código QR</p>
                </div>
            </div>
            <button type="button" onclick="cerrarEscaner()"
                    class="w-8 h-8 rounded-lg bg-[#1a2235] hover:bg-[#243049] text-slate-400 hover:text-white flex items-center justify-center transition-colors">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>

        {{-- Selector de Cámara (si el dispositivo tiene varias) --}}
        <div id="cam-selector-container" class="px-4 py-2 bg-[#101827] border-b border-[#1e2d47] flex items-center gap-2 text-xs hidden flex-shrink-0">
            <i class="fas fa-video text-slate-500 text-[10px]"></i>
            <span class="text-slate-400 text-[11px] font-medium">Cámara:</span>
            <select id="camera-select" onchange="cambiarCamara(this.value)"
                    class="bg-[#1a2235] border border-[#1e2d47] rounded-lg px-2 py-1 text-slate-200 text-xs flex-1 truncate focus:outline-none focus:border-amber-500">
            </select>
        </div>

        {{-- Visor de Cámara --}}
        <div class="p-3 sm:p-4 space-y-3 overflow-y-auto flex-1">
            <div class="relative bg-black rounded-xl overflow-hidden aspect-[4/3] sm:aspect-square flex items-center justify-center border border-[#1e2d47] shadow-inner">
                <div id="qr-reader" class="w-full h-full flex items-center justify-center"></div>

                {{-- Guía visual / Marco de escaneo horizontal amplio --}}
                <div id="scanner-overlay" class="absolute inset-x-6 inset-y-12 pointer-events-none border-2 border-dashed border-amber-400/80 rounded-xl flex items-center justify-center hidden">
                    <div class="w-full h-0.5 bg-red-500 shadow-[0_0_12px_rgba(239,68,68,1)] animate-pulse"></div>
                </div>

                <div id="scanner-loading" class="absolute inset-0 bg-[#0d1526] flex flex-col items-center justify-center text-slate-400 gap-3">
                    <i class="fas fa-circle-notch fa-spin text-3xl text-amber-500"></i>
                    <span class="text-xs font-medium">Iniciando cámara y sensor...</span>
                </div>
            </div>

            {{-- Slider de Zoom (si el dispositivo lo soporta) --}}
            <div id="zoom-container" class="items-center gap-2 text-xs px-2 hidden">
                <i class="fas fa-search-minus text-slate-500"></i>
                <input type="range" id="zoom-slider" min="1" max="5" step="0.1" value="1" oninput="aplicarZoom(this.value)"
                       class="flex-1 accent-amber-500 cursor-pointer h-1.5 bg-[#1a2235] rounded-lg">
                <i class="fas fa-search-plus text-slate-500"></i>
                <span id="zoom-value" class="text-[10px] text-slate-400 font-mono w-8 text-right">1.0x</span>
            </div>

            {{-- Estado / Resultado detectado --}}
            <div id="scanner-status" class="text-center text-xs text-slate-300 py-1 font-mono min-h-[22px] flex items-center justify-center">
                Alinea las barras del producto dentro del recuadro
            </div>

            {{-- Controles inferiores --}}
            <div class="flex items-center justify-between gap-2 pt-2 border-t border-[#1e2d47] flex-wrap">
                <div class="flex items-center gap-2">
                    <button type="button" id="btn-torch" onclick="toggleLinterna()"
                            class="text-xs bg-[#1a2235] hover:bg-[#243049] text-slate-300 border border-[#1e2d47] px-3 py-1.5 rounded-xl transition-colors flex items-center gap-1.5 hidden">
                        <i class="fas fa-bolt text-amber-400"></i> Linterna
                    </button>
                    <label class="text-xs bg-[#1a2235] hover:bg-[#243049] text-slate-300 border border-[#1e2d47] px-3 py-1.5 rounded-xl transition-colors flex items-center gap-1.5 cursor-pointer">
                        <i class="fas fa-image text-blue-400"></i> Subir foto de código
                        <input type="file" id="file-scanner" accept="image/*" class="hidden" onchange="escanearDesdeArchivo(this)">
                    </label>
                </div>
                <button type="button" onclick="cerrarEscaner()"
                        class="text-xs bg-[#1a2235] hover:bg-[#243049] text-slate-300 border border-[#1e2d47] px-4 py-1.5 rounded-xl transition-colors ml-auto">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Biblioteca Universal HTML5-QRCode --}}
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
let html5QrCode = null;
let campoDestinoId = 'codigo_barras';
let audioCtx = null;
let linternaEncendida = false;
let camarasDisponibles = [];
let camaraActivaId = null;

const FORMATOS_SOPORTADOS = [
    Html5QrcodeSupportedFormats.EAN_13,
    Html5QrcodeSupportedFormats.EAN_8,
    Html5QrcodeSupportedFormats.CODE_128,
    Html5QrcodeSupportedFormats.CODE_39,
    Html5QrcodeSupportedFormats.CODE_93,
    Html5QrcodeSupportedFormats.UPC_A,
    Html5QrcodeSupportedFormats.UPC_E,
    Html5QrcodeSupportedFormats.UPC_EAN_EXTENSION,
    Html5QrcodeSupportedFormats.ITF,
    Html5QrcodeSupportedFormats.QR_CODE,
    Html5QrcodeSupportedFormats.DATA_MATRIX,
];

function reproducirBeep() {
    try {
        if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(2000, audioCtx.currentTime);
        gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.12);
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.start();
        osc.stop(audioCtx.currentTime + 0.12);
    } catch(e) {}

    if (navigator.vibrate) {
        navigator.vibrate([80, 40, 80]);
    }
}

async function abrirEscaner(targetInputId = 'codigo_barras') {
    campoDestinoId = targetInputId;
    const modal = document.getElementById('modal-escaner');
    const loading = document.getElementById('scanner-loading');
    const status = document.getElementById('scanner-status');
    const overlay = document.getElementById('scanner-overlay');

    modal.classList.remove('hidden');
    loading.classList.remove('hidden');
    overlay.classList.add('hidden');
    status.textContent = 'Iniciando sensor óptico...';

    // Instanciar con aceleración de BarcodeDetector nativo de hardware
    if (html5QrCode) {
        try { await html5QrCode.stop(); } catch(e) {}
        try { await html5QrCode.clear(); } catch(e) {}
    }

    html5QrCode = new Html5Qrcode("qr-reader", {
        formatsToSupport: FORMATOS_SOPORTADOS,
        verbose: false,
        experimentalFeatures: {
            useBarCodeDetectorIfSupported: true // Activa decodificador nativo ultra-rápido en Android/iOS
        }
    });

    try {
        // Obtener cámaras disponibles
        camarasDisponibles = await Html5Qrcode.getCameras();
        const select = document.getElementById('camera-select');
        const container = document.getElementById('cam-selector-container');

        if (camarasDisponibles && camarasDisponibles.length > 0) {
            select.innerHTML = camarasDisponibles.map((cam, idx) =>
                `<option value="${cam.id}">${cam.label || ('Cámara ' + (idx + 1))}</option>`
            ).join('');

            // Preferir cámara trasera
            const camTrasera = camarasDisponibles.find(c =>
                c.label.toLowerCase().includes('back') ||
                c.label.toLowerCase().includes('trasera') ||
                c.label.toLowerCase().includes('environment') ||
                c.label.toLowerCase().includes('0')
            );

            camaraActivaId = camTrasera ? camTrasera.id : camarasDisponibles[0].id;
            select.value = camaraActivaId;

            if (camarasDisponibles.length > 1) {
                container.classList.remove('hidden');
            }
        }

        await iniciarCamara(camaraActivaId);

    } catch (err) {
        loading.classList.add('hidden');
        status.innerHTML = `<span class="text-red-400">Error al iniciar cámara: ${err}. Asegúrate de dar permisos de cámara.</span>`;
    }
}

async function iniciarCamara(cameraId = null) {
    const loading = document.getElementById('scanner-loading');
    const status = document.getElementById('scanner-status');
    const overlay = document.getElementById('scanner-overlay');

    loading.classList.remove('hidden');

    const config = {
        fps: 20,
        qrbox: function(viewfinderWidth, viewfinderHeight) {
            // Rectángulo amplio horizontal optimizado para códigos de barra largos EAN-13 / Code-128
            const ancho = Math.floor(viewfinderWidth * 0.9);
            const alto = Math.floor(Math.min(viewfinderHeight * 0.65, 200));
            return { width: Math.max(260, ancho), height: Math.max(140, alto) };
        },
        aspectRatio: 1.0,
        disableFlip: false
    };

    const cameraConfig = cameraId
        ? { deviceId: { exact: cameraId } }
        : { facingMode: "environment" };

    try {
        await html5QrCode.start(
            cameraConfig,
            config,
            (decodedText, decodedResult) => {
                onCodigoDetectado(decodedText);
            },
            (errorMessage) => {
                // scanning frame
            }
        );

        loading.classList.add('hidden');
        overlay.classList.remove('hidden');
        status.textContent = 'Enfoca el código de barras o QR de cerca';

        // Comprobar capabilities (Linterna / Zoom)
        setTimeout(() => {
            try {
                const capabilities = html5QrCode.getRunningTrackCapabilities();
                if (capabilities) {
                    if (capabilities.torch) {
                        document.getElementById('btn-torch').classList.remove('hidden');
                    }
                    if (capabilities.zoom) {
                        const zoomContainer = document.getElementById('zoom-container');
                        const zoomSlider = document.getElementById('zoom-slider');
                        zoomSlider.min = capabilities.zoom.min || 1;
                        zoomSlider.max = capabilities.zoom.max || 5;
                        zoomSlider.step = capabilities.zoom.step || 0.1;
                        zoomSlider.value = capabilities.zoom.min || 1;
                        zoomContainer.classList.remove('hidden');
                        zoomContainer.classList.add('flex');
                    }
                }
            } catch(e) {}
        }, 500);

    } catch(e) {
        loading.classList.add('hidden');
        status.innerHTML = `<span class="text-red-400">No se pudo iniciar el sensor: ${e}</span>`;
    }
}

async function cambiarCamara(newCameraId) {
    if (html5QrCode && html5QrCode.isScanning) {
        await html5QrCode.stop();
    }
    camaraActivaId = newCameraId;
    await iniciarCamara(newCameraId);
}

async function aplicarZoom(val) {
    if (!html5QrCode) return;
    try {
        await html5QrCode.applyVideoConstraints({
            advanced: [{ zoom: parseFloat(val) }]
        });
        document.getElementById('zoom-value').textContent = parseFloat(val).toFixed(1) + 'x';
    } catch(e) {}
}

function onCodigoDetectado(codigo) {
    reproducirBeep();
    const status = document.getElementById('scanner-status');
    status.innerHTML = `<span class="text-emerald-400 font-bold bg-emerald-500/10 px-3 py-1 rounded-lg">✓ Código leído: ${codigo}</span>`;

    const input = document.getElementById(campoDestinoId);
    if (input) {
        input.value = codigo.trim().toUpperCase();
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
        input.focus();
    }

    setTimeout(() => {
        cerrarEscaner();
    }, 450);
}

async function escanearDesdeArchivo(input) {
    if (!input.files || !input.files[0]) return;
    const archivo = input.files[0];
    const status = document.getElementById('scanner-status');
    status.textContent = 'Analizando imagen...';

    try {
        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("qr-reader", { formatsToSupport: FORMATOS_SOPORTADOS });
        }
        const resultado = await html5QrCode.scanFile(archivo, true);
        onCodigoDetectado(resultado);
    } catch(err) {
        status.innerHTML = `<span class="text-red-400">No se detectó ningún código claro en la imagen.</span>`;
    }
}

async function toggleLinterna() {
    if (!html5QrCode) return;
    try {
        linternaEncendida = !linternaEncendida;
        await html5QrCode.applyVideoConstraints({
            advanced: [{ torch: linternaEncendida }]
        });
        const btn = document.getElementById('btn-torch');
        btn.classList.toggle('text-amber-400', linternaEncendida);
        btn.classList.toggle('bg-amber-500/20', linternaEncendida);
    } catch(e) {}
}

async function cerrarEscaner() {
    if (html5QrCode && html5QrCode.isScanning) {
        try {
            await html5QrCode.stop();
        } catch(e) {}
    }
    document.getElementById('modal-escaner').classList.add('hidden');
    document.getElementById('btn-torch').classList.add('hidden');
    document.getElementById('zoom-container').classList.add('hidden');
}
</script>
