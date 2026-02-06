/**
 * Opens/closes the TGB popup notice modal (Learn more) below the donation button.
 * In the block editor iframe, the modal is shown in the parent document so it appears above the block toolbar.
 *
 * @unreleased
 */
(function () {
    function isInIframe(doc) {
        return doc && doc.defaultView && doc.defaultView !== doc.defaultView.top;
    }

    const parentModalStyleId = 'give-tgb-notice-modal-parent-styles';

    function ensureParentModalStyles(parentDoc) {
        if (parentDoc.getElementById(parentModalStyleId)) return;
        const style = parentDoc.createElement('style');
        style.id = parentModalStyleId;
        style.textContent =
            '.give-tgb-notice-modal{position:fixed;inset:0;z-index:1000000;display:flex;align-items:center;justify-content:center;padding:1rem;box-sizing:border-box}' +
            '.give-tgb-notice-modal-overlay{position:absolute;inset:0;background:rgba(0,0,0,0.5)}' +
            '.give-tgb-notice-modal-content{position:relative;width:100%;max-width:30rem;max-height:85vh;background:#fff;border-radius:4px;overflow:auto;padding:1.25rem 1.5rem;box-sizing:border-box;box-shadow:0 4px 20px rgba(0,0,0,0.15)}' +
            '.give-tgb-notice-modal-body{margin-bottom:1rem;font-size:14px!important;line-height:1.5;text-align:left}' +
            '.give-tgb-notice-modal-body p:first-child{margin-top:0}' +
            '.give-tgb-notice-modal-body p:last-child{margin-bottom:0}' +
            '.give-tgb-notice-modal-close{display:block;width:100%;padding:0.5rem 1rem;background:#1e1e1e;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:0.9375rem}' +
            '.give-tgb-notice-modal-close:hover{background:#2c2c2c}';
        (parentDoc.head || parentDoc.documentElement).appendChild(style);
    }

    function stripInlineFontSize(html, doc) {
        const wrap = doc.createElement('div');
        wrap.innerHTML = html;
        wrap.querySelectorAll('[style]').forEach(function (el) {
            const style = el.getAttribute('style');
            if (!style) return;
            const cleaned = style
                .split(';')
                .filter(function (part) {
                    const prop = part.split(':')[0].trim().toLowerCase();
                    return prop && prop !== 'font-size' && prop !== 'line-height';
                })
                .join('; ')
                .trim();
            if (cleaned) el.setAttribute('style', cleaned);
            else el.removeAttribute('style');
        });
        return wrap.innerHTML;
    }

    function showModalInParent(iframeModal) {
        const bodyEl = iframeModal.querySelector('.give-tgb-notice-modal-body');
        const closeEl = iframeModal.querySelector('.give-tgb-notice-modal-close');
        if (!bodyEl) return;

        const parentDoc = document;
        if (!parentDoc.body) return;

        ensureParentModalStyles(parentDoc);

        const bodyHtml = stripInlineFontSize(bodyEl.innerHTML, parentDoc);

        const wrap = parentDoc.createElement('div');
        wrap.className = 'give-tgb-notice-modal';
        wrap.setAttribute('role', 'dialog');
        wrap.setAttribute('aria-modal', 'true');
        wrap.setAttribute('aria-label', 'Information');
        wrap.innerHTML =
            '<div class="give-tgb-notice-modal-overlay"></div>' +
            '<div class="give-tgb-notice-modal-content">' +
            '<div class="give-tgb-notice-modal-body">' + bodyHtml + '</div>' +
            '<button type="button" class="give-tgb-notice-modal-close" aria-label="Close">' +
            (closeEl ? (closeEl.textContent || 'Close').replace(/</g, '&lt;') : 'Close') + '</button>' +
            '</div>';

        parentDoc.body.appendChild(wrap);

        // Force 14px in editor parent so no admin CSS can override (inline wins)
        const modalBody = wrap.querySelector('.give-tgb-notice-modal-body');
        if (modalBody) {
            modalBody.style.fontSize = '14px';
            modalBody.style.lineHeight = '1.5';
            modalBody.style.textAlign = 'left';
            modalBody.querySelectorAll('*').forEach(function (el) {
                el.style.fontSize = '14px';
                el.style.lineHeight = '1.5';
            });
        }

        function close() {
            if (wrap.parentNode) wrap.parentNode.removeChild(wrap);
            parentDoc.removeEventListener('keydown', onEscape);
        }

        function onEscape(e) {
            if (e.key === 'Escape') close();
        }

        wrap.querySelector('.give-tgb-notice-modal-overlay').addEventListener('click', close);
        wrap.querySelector('.give-tgb-notice-modal-close').addEventListener('click', close);
        parentDoc.addEventListener('keydown', onEscape);
    }

    function init(doc) {
        if (!doc || !doc.querySelectorAll) return;
        const inIframe = isInIframe(doc);

        doc.querySelectorAll('.give-tgb-notice-cta').forEach(function (cta) {
            if (cta.dataset.tgbNoticeBound) return;
            cta.dataset.tgbNoticeBound = '1';

            const container = cta.closest('.give-tgb-popup-notice-container');
            if (!container) return;

            const modal = container.querySelector('.give-tgb-notice-modal');
            const overlay = container.querySelector('.give-tgb-notice-modal-overlay');
            const closeBtn = container.querySelector('.give-tgb-notice-modal-close');

            function openModal() {
                if (!modal) return;
                if (inIframe) {
                    showModalInParent(modal);
                } else {
                    modal.removeAttribute('hidden');
                    cta.setAttribute('aria-expanded', 'true');
                }
            }

            function closeModal() {
                if (modal) {
                    modal.setAttribute('hidden', '');
                    cta.setAttribute('aria-expanded', 'false');
                }
            }

            cta.addEventListener('click', function (e) {
                e.preventDefault();
                openModal();
            });

            if (!inIframe) {
                if (overlay) overlay.addEventListener('click', closeModal);
                if (closeBtn) closeBtn.addEventListener('click', closeModal);
                doc.addEventListener('keydown', function onKey(e) {
                    if (e.key !== 'Escape' || !modal || modal.hasAttribute('hidden')) return;
                    closeModal();
                });
            }
        });
    }

    function runInEditorIframe() {
        const iframe = document.querySelector('.editor-canvas__iframe, iframe[name="editor-canvas"], .block-editor-iframe');
        if (iframe && iframe.contentDocument) {
            init(iframe.contentDocument);
        }
    }

    function runAll() {
        init(document);
        runInEditorIframe();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            runAll();
            setTimeout(runAll, 500);
            setTimeout(runAll, 1500);
            setTimeout(runAll, 3000);
        });
    } else {
        runAll();
        setTimeout(runAll, 500);
        setTimeout(runAll, 1500);
        setTimeout(runAll, 3000);
    }

    const editorObserver = new MutationObserver(function () {
        runInEditorIframe();
    });
    if (document.body) {
        editorObserver.observe(document.body, { childList: true, subtree: true });
    }

    const iframe = document.querySelector('.editor-canvas__iframe, iframe[name="editor-canvas"], .block-editor-iframe');
    if (iframe) {
        iframe.addEventListener('load', function () {
            setTimeout(runInEditorIframe, 100);
            setTimeout(runInEditorIframe, 800);
        });
    }
})();
