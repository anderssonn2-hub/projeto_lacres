<style type="text/css">
.overlay-processando-global-lite {
    position: fixed;
    inset: 0;
    display: none;
    align-items: center;
    justify-content: center;
    background: rgba(15, 23, 42, 0.34);
    z-index: 30000;
}
.overlay-processando-global-lite.ativo {
    display: flex;
}
.overlay-processando-global-lite-box {
    min-width: 220px;
    padding: 18px 22px;
    border-radius: 12px;
    background: #ffffff;
    color: #1f2937;
    text-align: center;
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.25);
    font-size: 15px;
    font-weight: 700;
}
.overlay-processando-global-lite-box:before {
    content: '';
    display: block;
    width: 28px;
    height: 28px;
    margin: 0 auto 10px;
    border-radius: 50%;
    border: 3px solid #dbeafe;
    border-top-color: #2563eb;
    animation: giro-processando-lite 0.9s linear infinite;
}
@keyframes giro-processando-lite {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
@media print {
    .overlay-processando-global-lite {
        display: none !important;
    }
}
</style>

<div id="overlay-processando-global-lite" class="overlay-processando-global-lite" aria-hidden="true">
    <div id="overlay-processando-global-lite-texto" class="overlay-processando-global-lite-box">Processando...</div>
</div>

<script type="text/javascript">
(function() {
    if (window.__overlayProcessandoLiteInicializado) {
        return;
    }
    window.__overlayProcessandoLiteInicializado = true;

    var overlay = document.getElementById('overlay-processando-global-lite');
    var overlayTexto = document.getElementById('overlay-processando-global-lite-texto');

    function exibirProcessando(texto) {
        if (!overlay) return;
        if (overlayTexto) {
            overlayTexto.textContent = texto || 'Processando...';
        }
        overlay.className = 'overlay-processando-global-lite ativo';
        overlay.setAttribute('aria-hidden', 'false');
    }

    function ocultarProcessando() {
        if (!overlay) return;
        overlay.className = 'overlay-processando-global-lite';
        overlay.setAttribute('aria-hidden', 'true');
    }

    function encontrarLinkAlvo(node) {
        while (node && node !== document) {
            if (node.tagName && node.tagName.toLowerCase() === 'a') {
                return node;
            }
            node = node.parentNode;
        }
        return null;
    }

    window.exibirProcessandoGlobal = exibirProcessando;
    window.ocultarProcessandoGlobal = ocultarProcessando;

    document.addEventListener('submit', function(evento) {
        var form = evento.target;
        if (!form || form.getAttribute('target') === '_blank' || form.getAttribute('data-sem-processando') === '1') {
            return;
        }
        exibirProcessando('Processando...');
    }, true);

    document.addEventListener('click', function(evento) {
        var link = encontrarLinkAlvo(evento.target);
        var href;
        if (!link) return;
        if (evento.defaultPrevented) return;
        if (evento.metaKey || evento.ctrlKey || evento.shiftKey || evento.altKey) return;
        if (link.getAttribute('target') === '_blank' || link.getAttribute('download') !== null || link.getAttribute('data-sem-processando') === '1') return;
        href = link.getAttribute('href') || '';
        href = String(href).replace(/^\s+|\s+$/g, '');
        if (href === '' || href === '#' || href.indexOf('javascript:') === 0 || href.indexOf('mailto:') === 0 || href.indexOf('tel:') === 0) return;
        exibirProcessando('Processando...');
    }, true);

    window.addEventListener('beforeunload', function() {
        exibirProcessando('Processando...');
    });

    window.addEventListener('pageshow', function() {
        ocultarProcessando();
    });
})();
</script>