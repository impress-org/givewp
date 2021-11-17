document.addEventListener( 'DOMContentLoaded', function() {
    const header = document.querySelector( '.wp-heading-inline' );
    const titleAction = document.querySelector( '.page-title-action' );
    const bannersContainer = document.querySelector( '.give-sale-banners-container' );
    const dismissActions = document.querySelectorAll( '.give-sale-banner-dismiss' );
    const previousSibling = titleAction ?? header;

    const hideBanner = ( event ) => {
        const button = event.target;
        const formData = new FormData();
        formData.append( 'id', button.dataset.id );

        document.querySelector(`#${button.getAttribute( 'aria-controls' )}`).remove();

        fetch( `${ window.GiveSaleBanners.apiRoot }/hide`, {
            method: 'POST',
            headers: {
                'X-WP-Nonce': window.GiveSaleBanners.apiNonce,
            },
            body: formData,
        } );

        if ( bannersContainer.children.length <= 1 ) {
            bannersContainer.remove();
        }
    };

    if ( previousSibling && bannersContainer ) {
        previousSibling.parentNode.insertBefore( bannersContainer, previousSibling.nextSibling );
    }

    dismissActions?.forEach( ( action ) => {
        action.addEventListener( 'click', hideBanner );
    } );
} );
