const pluginHeader = document.querySelector('.wp-header-end');
// Place banner underneath Plugin header & add new button
const element = document.querySelector('[data-stellarwp-givewp-notice-id="givewp-campaigns-welcome-banner-2025"]');
pluginHeader.insertAdjacentElement('afterend', element);
