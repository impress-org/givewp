window.addEventListener(
	'load', function () {
		let readMoreLinks = document.querySelectorAll( '.give-donor__read-more' );

		if( readMoreLinks.length ) {
			readMoreLinks.forEach(
				function (readMoreLink) {
					readMoreLink.addEventListener(
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
			);
		}
	}, false
);
