const bannersContainer = document.querySelector('.givewp-sale-banners-container');
const dismissActions = document.querySelectorAll('.givewp-sale-banner__dismiss');
const pageTitle = document.querySelector('.page-title-action, .wp-heading-inline, #give-in-plugin-upsells h1');
const listTable = document.querySelector('#give-admin-donations-root, #give-admin-donation-forms-root, #give-admin-donors-root');

/**
 * @since 3.1.0 show banner on ListTable pages.
 */
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

    if (bannersContainer.querySelectorAll('.givewp-sale-banner').length === 0) {
        bannersContainer.remove();
    }
};

if((pageTitle || listTable) && bannersContainer ){
    bannersContainer.style.display = null;

    if (pageTitle) {
        pageTitle.parentNode.insertBefore(bannersContainer, pageTitle.nextSibling);
    } else if (listTable){
        listTable.querySelector('header').insertAdjacentElement('afterend', bannersContainer);
    }
}

dismissActions.forEach((action) => {
    action.addEventListener('click', hideBanner);
});
