// Copiar chave Pix
document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-copy]');
    if (!btn) return;
    const sel = btn.getAttribute('data-copy');
    const el = document.querySelector(sel);
    if (!el) return;
    const text = el.textContent.trim();
    const done = () => {
        const orig = btn.textContent;
        btn.textContent = 'Copiado!';
        setTimeout(() => (btn.textContent = orig), 1500);
    };
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(done).catch(done);
    } else {
        const ta = document.createElement('textarea');
        ta.value = text; document.body.appendChild(ta); ta.select();
        try { document.execCommand('copy'); } catch (_) { }
        document.body.removeChild(ta); done();
    }
});