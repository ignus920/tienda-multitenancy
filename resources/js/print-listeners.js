
// -----------------------------
// 🖨️ FUNCIÓN GLOBAL DE IMPRESIÓN
// -----------------------------
window.openPrintWindow = function(eventData) {
    // console.log('🖨️ openPrintWindow ejecutada:', eventData);

    const data = Array.isArray(eventData) ? eventData[0] : eventData;

    const url = data.url;
    const format = data.format;

    // console.log('🔗 URL a imprimir:', url, '📄 Formato:', format);

    // Tamaño de ventana según formato
    const features =
        format === 'pos'
            ? 'width=400,height=600,scrollbars=yes,resizable=yes,menubar=no,toolbar=no'
            : 'width=800,height=900,scrollbars=yes,resizable=yes,menubar=no,toolbar=no';

    // Abrir ventana
    const win = window.open(url, 'printWindow_' + Date.now(), features);

    if (!win) {
        alert('⚠️ No se pudo abrir la ventana. Activa las ventanas emergentes.');
        return;
    }

    console.log('✅ Ventana abierta correctamente');
    win.focus();

    // Auto impresión
    win.onload = function () {
        setTimeout(() => {
            win.print();
        }, 800);
    };
};


// ------------------------------------------------------
// 📌 CONFIGURACIÓN DE LISTENERS PARA EVENTOS DE IMPRESIÓN
// ------------------------------------------------------
(function initializePrintListeners() {

    let initialized = false;

    function setup() {

        if (initialized) {
            console.log('⚠️ Listeners ya estaban configurados');
            return;
        }

        initialized = true;

        console.log('🔧 Configurando listeners de impresión...');

        // Listener Livewire
        if (window.Livewire) {
            Livewire.on('open-print-window', window.openPrintWindow);
            console.log('✅ Listener Livewire configurado');
        }

        // Listener DOM personalizado
        document.addEventListener('print-quote', (event) => {
            console.log('🎯 Evento DOM print-quote recibido');
            window.openPrintWindow(event.detail);
        });

        console.log('🎉 Listeners listos');
    }

    // 🔥 Ejecución inicial
    setup();

    // Eventos importantes de Livewire 3
    document.addEventListener('livewire:initialized', setup);
    document.addEventListener('livewire:navigated', setup);

    // Soporte Alpine
    document.addEventListener('alpine:init', setup);

})();
