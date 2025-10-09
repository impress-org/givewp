import './css/bfcm2025.scss';

document.addEventListener('DOMContentLoaded', () => {
    const adminRoot = document.querySelector('#give-admin-campaigns-root, #give-admin-donations-root, #give-admin-donors-root, #give-admin-subscriptions-root');
    const bfcmBanner2025 = document.querySelector('#givewp-bfcm-2025-banner');

    if (adminRoot) {
        const header = adminRoot.querySelector('article > header');

        if (header) {
            header.insertAdjacentElement('afterend', bfcmBanner2025);
        }
    }
});
