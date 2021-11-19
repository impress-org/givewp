const bannersContainer = document.querySelector('.give-sale-banners-container');
const dismissActions = document.querySelectorAll('.give-sale-banner-dismiss');
const pageTitle = document.querySelector('.page-title-action, .wp-heading-inline, #give-in-plugin-upsells h1');

const hideBanner = ({target: dismissAction}) => {
    const formData = new FormData();
    formData.append('id', dismissAction.dataset.id);

    document.getElementById(dismissAction.getAttribute('aria-controls')).remove();

    fetch(`${window.GiveSaleBanners.apiRoot}/hide`, {
        method: 'POST',
        headers: {
            'X-WP-Nonce': window.GiveSaleBanners.apiNonce,
        },
        body: formData,
    });

    if (bannersContainer.querySelectorAll('.give-sale-banner').length === 0) {
        bannersContainer.remove();
    }
};

if (pageTitle && bannersContainer) {
    pageTitle.parentNode.insertBefore(bannersContainer, pageTitle.nextSibling);

    bannersContainer.style.display = null;
}

dismissActions.forEach((action) => {
    action.addEventListener('click', hideBanner);
});
