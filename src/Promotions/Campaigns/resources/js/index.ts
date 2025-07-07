document.addEventListener('DOMContentLoaded', () => {
  const wrap = document.querySelector('.wrap');

  if (!wrap) return;

  const pageTitleAction = wrap.querySelector('.page-title-action');

  const canInsertBanner = pageTitleAction && 
  pageTitleAction.textContent.includes('Add Plugin') &&
  pageTitleAction.nextElementSibling?.classList.contains('wp-header-end');

  if (canInsertBanner) {
    const container = document.createElement('div');
    container.id = 'givewp-campaigns-welcome-banner';

    pageTitleAction.insertAdjacentElement('afterend', container);
   
    const banner = document.querySelector('[data-stellarwp-givewp-notice-id="givewp-campaigns-welcome-banner-2025"]');
    if (banner) {
      container.appendChild(banner);
    }
  }
});