const Selected = ( { index } ) => {
	const id = `filterSelected${ index }_d`;
	return (
		<svg className="card-input-selected" width="49" height="42" viewBox="0 0 49 42" fill="none" xmlns="http://www.w3.org/2000/svg">
			<g filter={ `url(#${ id })` }>
				<circle cx="24.5" cy="20.5" r="16.5" fill="#66BC6B" />
			</g>
			<g filter={ `url(#${ id })` }>
				<path d="M21.582 27.7188C21.9336 28.0703 22.5312 28.0703 22.8828 27.7188L33.2188 17.3828C33.5703 17.0312 33.5703 16.4336 33.2188 16.082L31.9531 14.8164C31.6016 14.4648 31.0391 14.4648 30.6875 14.8164L22.25 23.2539L18.2773 19.3164C17.9258 18.9648 17.3633 18.9648 17.0117 19.3164L15.7461 20.582C15.3945 20.9336 15.3945 21.5312 15.7461 21.8828L21.582 27.7188Z" fill="#F6F9FC" />
			</g>
			<defs>
				<filter id={ id } x="4" y="0" width="41" height="41" filterUnits="userSpaceOnUse" colorInterpolationFilters="sRGB">
					<feFlood floodOpacity="0" result="BackgroundImageFix" />
					<feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" />
					<feOffset />
					<feGaussianBlur stdDeviation="2" />
					<feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0" />
					<feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow" />
					<feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow" result="shape" />
				</filter>
				<filter id="filter1_d" x="11.4824" y="10.5527" width="26" height="21.4297" filterUnits="userSpaceOnUse" colorInterpolationFilters="sRGB">
					<feFlood floodOpacity="0" result="BackgroundImageFix" />
					<feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" />
					<feOffset />
					<feGaussianBlur stdDeviation="2" />
					<feColorMatrix type="matrix" values="0 0 0 0 0.247059 0 0 0 0 0.670588 0 0 0 0 0.435294 0 0 0 1 0" />
					<feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow" />
					<feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow" result="shape" />
				</filter>
			</defs>
		</svg>
	);
};

export default Selected;
