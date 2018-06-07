window.addEventListener('load', function () {
	let readMoreLinks = document.querySelectorAll('.give-donor__read-more');

	readMoreLinks.forEach(function (readMoreLink) {
		readMoreLink.addEventListener('click', function () {
			jQuery.magnificPopup.open({
				items: {
					src: this.parentNode.parentNode.parentNode.parentNode.getElementsByClassName('give-donor__comment')[0].innerHTML,
					type: 'inline'
				},
				mainClass: 'give-modal',
				closeOnBgClick: false
			})
		});
	});
}, false);
