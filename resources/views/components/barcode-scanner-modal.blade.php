{{-- Modal Lector de Código de Barras / QR con Cámara para Celulares y Laptops --}}
<div id="modal-escaner" class="fixed inset-0 z-[100] bg-black/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-[#141c2e] border border-[#1e2d47] rounded-2xl max-w-md w-full overflow-hidden shadow-2xl animate-in fade-in zoom-in duration-200">
        {{-- Header --}}
        <div class="px-5 py-4 border-b border-[#1e2d47] flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400">
                    <i class="fas fa-barcode"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-200 text-sm">Lector de Código / QR</h3>
                    <p class="text-[11px] text-slate-500">Apunta la cámara al código del producto</p>
                </div>
            </div>
            <button type="button" onclick="cerrarEscaner()"
                    class="w-8 h-8 rounded-lg bg-[#1a2235] hover:bg-[#243049] text-slate-400 hover:text-white flex items-center justify-center transition-colors">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>

        {{-- Visor de Cámara --}}
        <div class="p-4 space-y-3">
            <div class="relative bg-black rounded-xl overflow-hidden aspect-square flex items-center justify-center border border-[#1e2d47]">
                <div id="qr-reader" class="w-full h-full"></div>

                {{-- Guía visual / Marco de escaneo --}}
                <div id="scanner-overlay" class="absolute inset-8 pointer-events-none border-2 border-dashed border-amber-400/70 rounded-xl flex items-center justify-center">
                    <div class="w-full h-0.5 bg-amber-400 shadow-[0_0_12px_rgba(251,191,36,1)] animate-pulse"></div>
                </div>

                <div id="scanner-loading" class="absolute inset-0 bg-[#0d1526] flex flex-col items-center justify-center text-slate-400 gap-3">
                    <i class="fas fa-spinner fa-spin text-2xl text-amber-500"></i>
                    <span class="text-xs">Iniciando cámara...</span>
                </div>
            </div>

            {{-- Estado / Resultado detectado --}}
            <div id="scanner-status" class="text-center text-xs text-slate-400 py-1 font-mono">
                Buscando código de barras o QR...
            </div>

            {{-- Controles (Linterna / Cambiar Cámara) --}}
            <div class="flex items-center justify-between gap-2 pt-1 border-t border-[#1e2d47]">
                <button type="button" id="btn-torch" onclick="toggleLinterna()"
                        class="text-xs bg-[#1a2235] hover:bg-[#243049] text-slate-300 border border-[#1e2d47] px-3 py-2 rounded-xl transition-colors flex items-center gap-1.5 hidden">
                    <i class="fas fa-bolt text-amber-400"></i> Linterna
                </button>
                <button type="button" onclick="cerrarEscaner()"
                        class="text-xs bg-[#1a2235] hover:bg-[#243049] text-slate-300 border border-[#1e2d47] px-4 py-2 rounded-xl transition-colors ml-auto">
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

function reproducirBeep() {
    try {
        if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(1800, audioCtx.currentTime); // Tono agudo de scanner
        gain.gain.setValueAtTime(0.15, audioCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.12);
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.start();
        osc.stop(audioCtx.currentTime + 0.12);
    } catch(e) {}

    if (navigator.vibrate) {
        navigator.vibrate(100);
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
    status.textContent = 'Solicitando acceso a la cámara...';

    if (!html5QrCode) {
        html5QrCode = new Html5Qrcode("qr-reader");
    }

    const config = {
        fps: 15,
        qrbox: { width: 250, height: 180 },
        aspectRatio: 1.0,
        formatsToSupport: [
            Html5QrcodeSupportedFormats.EAN_13,
            Html5QrcodeSupportedFormats.EAN_8,
            Html5QrcodeSupportedFormats.CODE_128,
            Html5QrcodeSupportedFormats.CODE_39,
            Html5QrcodeSupportedFormats.UPC_A,
            Html5QrcodeSupportedFormats.UPC_E,
            Html5QrcodeSupportedFormats.QR_CODE,
            Html5QrcodeSupportedFormats.DATA_MATRIX,
        ]
    };

    try {
        await html5QrCode.start(
            { facingMode: "environment" }, // Cámara trasera en celulares
            config,
            (decodedText, decodedResult) => {
                // Código escaneado con éxito
                reproducirBeep();
                status.innerHTML = `<span class="text-emerald-400 font-bold">✓ Detectado: ${decodedText}</span>`;

                const input = document.getElementById(campoDestinoId);
                if (input) {
                    input.value = decodedText.trim().toUpperCase();
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                    input.focus();
                }

                setTimeout(() => {
                    cerrarEscaner();
                }, 400);
            },
            (errorMessage) => {
                // Parse error continuo, se ignora
            }
        );

        loading.classList.add('hidden');
        overlay.classList.remove('hidden');
        status.textContent = 'Enfoca el código de barras o QR';

        // Comprobar soporte de linterna
        try {
            const capabilities = html5QrCode.getRunningTrackCapabilities();
            if (capabilities && capabilities.torch) {
                document.getElementById('btn-torch').classList.remove('hidden');
            }
        } catch(e) {}

    } catch (err) {
        loading.classList.add('hidden');
        status.innerHTML = `<span class="text-red-400">Error de cámara: Asegúrate de dar permisos de cámara en tu navegador. (${err})</span>`;
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
}
</script>
