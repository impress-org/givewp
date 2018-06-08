/**
 * This class handles donor wall shortcode related features
 *
 * @since 2.2.0
 *
 */
class GiveDonorWall{
	constructor(){
		window.addEventListener(
			'load', function () {
				let readMoreLinks = GiveDonorWall.readMoreLinks();

				if( readMoreLinks.length ) {
					readMoreLinks.forEach(
						function (readMoreLink) {
							GiveDonorWall.readMoreBtnEvent( readMoreLink );
						}
					);
				}
			}, false
		);
	}

	/**
	 * Get all read more links
	 *
	 * @since  2.2.0
	 */
	static readMoreLinks(){
		return document.querySelectorAll( '.give-donor__read-more' );
	}

	/**
	 * Add click event to read more link
	 *
	 * @since  2.2.0
	 *
	 * @param {object} el
	 */
	static readMoreBtnEvent( el ){
		el.addEventListener(
			'click', function () {
				jQuery.magnificPopup.open(
					{
						items: {
							src: this.parentNode.parentNode.parentNode.parentNode.getElementsByClassName( 'give-donor__comment' )[0].innerHTML,
							type: 'inline',
						},
						mainClass: 'give-modal give-donor-wall-modal',
						closeOnBgClick: false,
					}
				)
			}
		);
	}
}

let giveDonorWall = new GiveDonorWall();
