// Code to handle showing/hiding admin notices based on time since they were dismissed
document.addEventListener( 'DOMContentLoaded', () => {
	// Select dismissable notices
	const notices = document.querySelectorAll( 'div[data-give-dismissible]' );

	notices.forEach( ( notice ) => {
		const storageId = `give-dismissed-${ notice.dataset.giveDismissible }`;
		const storedItem = window.localStorage.getItem( storageId );

		if ( ! storedItem ) {
			notice.classList.remove( 'hidden' );
			notice.addEventListener( 'click', ( e ) => {
				if ( e.target.classList.contains( 'notice-dismiss' ) ) {
					window.localStorage.setItem( storageId, Date.now() );
				}
			} );
		}
	} );
} );
