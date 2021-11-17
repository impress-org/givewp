document.addEventListener( 'DOMContentLoaded', function() {
    const header = document.querySelector( '.wp-heading-inline' );
    const titleAction = document.querySelector( '.page-title-action' );
    const bannersContainer = document.querySelector( '.give-sale-banners-container' );
    const dismissActions = document.querySelectorAll( '.give-sale-banner-dismiss' );
    const previousSibling = titleAction ?? header;

    const hideBanner = ( e ) => {
        const formData = new FormData();
        formData.append( 'id', e.target.dataset.id );

        e.target.parentNode.remove();

        fetch( `${ window.GiveSaleBanners.apiRoot }/hide`, {
            method: 'POST',
            headers: {
                'X-WP-Nonce': window.GiveSaleBanners.apiNonce,
            },
            body: formData,
        } );

        if ( ! bannersContainer.children.length ) {
            bannersContainer.remove();
        }
    };

    if ( previousSibling && bannersContainer ) {
        previousSibling.parentNode.insertBefore( bannersContainer, previousSibling.nextSibling );
    }

    if ( dismissActions ) {
        dismissActions.forEach( function( action ) {
            action.addEventListener( 'click', hideBanner );
        } );
    }
} );
