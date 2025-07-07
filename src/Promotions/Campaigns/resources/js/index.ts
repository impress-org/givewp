document.addEventListener('DOMContentLoaded', () => {
  const pluginHeader = document.querySelector('.wp-header-end');
  if (pluginHeader) {
    const container = document.createElement('div');
    container.id = 'givewp-campaigns-welcome-banner';
    pluginHeader.insertAdjacentElement('afterend', container);

    const banner = document.querySelector('[data-stellarwp-givewp-notice-id="givewp-campaigns-welcome-banner-2025"]');

    if (banner) {
      container.appendChild(banner);
    }
  }
});
  