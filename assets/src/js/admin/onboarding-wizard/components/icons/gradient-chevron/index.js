const GradientChevronIcon = ( { index } ) => {
	const id = `paint${ index }_linear`;
	return (
		<svg width="7" height="12" viewBox="0 0 7 12" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path fill={ `url(#${ id })` } d="M0.859375 0.615234L0.351562 1.09766C0.25 1.22461 0.25 1.42773 0.351562 1.5293L4.94727 6.125L0.351562 10.7461C0.25 10.8477 0.25 11.0508 0.351562 11.1777L0.859375 11.6602C0.986328 11.7871 1.16406 11.7871 1.29102 11.6602L6.62305 6.35352C6.72461 6.22656 6.72461 6.04883 6.62305 5.92188L1.29102 0.615234C1.16406 0.488281 0.986328 0.488281 0.859375 0.615234Z" />
			<defs>
				<linearGradient id={ id } x1="-4.878" y1="18" x2="10.7527" y2="-3.13915" gradientUnits="userSpaceOnUse">
					<stop stopColor="#556E79" />
					<stop offset="1" stopColor="#2E90BB" />
				</linearGradient>
			</defs>
		</svg>
	);
};

export default GradientChevronIcon;
