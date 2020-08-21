/**
 * Accessible Block Links
 *
 * Problem: Hyperlink a component while maintaining screen-reader accessibility and the ability to select text.
 * Solution: Use progressive enhancement to conditionally trigger the target anchor element.
 *
 * @link https://css-tricks.com/block-links-the-search-for-a-perfect-solution/
 */

Array.from( document.querySelectorAll( '.setup-item' ) ).forEach( ( setupItem ) => {
	const actionAnchor = setupItem.querySelector( '.js-action-link' );

	if ( actionAnchor ) {
		actionAnchor.addEventListener( 'click', ( e ) => e.stopPropagation() );
		setupItem.style.cursor = 'pointer';
		setupItem.addEventListener( 'click', ( event ) => { // eslint-disable-line no-unused-vars
			if ( ! window.getSelection().toString() ) {
				actionAnchor.click();
			}
		} );
	}
} );
