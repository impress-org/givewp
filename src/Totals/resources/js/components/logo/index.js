/**
 * Give Logo
 * @param {*} props Logo properties
 * @returns {Element} SVG Icon
 */
function GiveLogo( { size = '24px', color, className } ) {
	let colorCode;

	switch ( color ) {
		case 'white':
			colorCode = '#FFFFFF';
			break;

		case 'grey':
			colorCode = '#555d66';
			break;

		default:
			colorCode = '#66BB6A';
			break;
	}

	return (
		<svg id="Layer_1" width={ size } height={ size } className={ className } xmlns="http://www.w3.org/2000/svg" xmlnsXlink="http://www.w3.org/1999/xlink"
			viewBox="100 0 404 400" >
			<g id="Layer_2">
				<circle fill={ colorCode } cx="300" cy="200" r="200" />
				<defs>
					<circle id="SVGID_1_" cx="300" cy="200" r="200" />
				</defs>
				<clippath id="SVGID_2_">
					<use xlinkHref="#SVGID_1_" overflow="visible" />
				</clippath>
				<path clipPath="url(#SVGID_2_)" fill="#FFF" d="M328.5,214.2c0.8,1.8,2.5,3.3,2.5,3.3c35.4,4.3,85.5-0.5,123.7-5.6 c-21.9,47.1-61.1,78.4-96.9,78.4c-67.4,0-119.3-81.7-119.3-81.7c20.9-18.3,55.2-78.4,104.8-78.4s71.2,27.2,71.2,27.2l5.6-8.9 c0,0-23.2-81.2-88.8-81.2S195.9,175.1,155.2,199.7c0,0,56,132.8,178.6,132.8c102.8,0,128.8-98.2,133.6-122.6 c13.7-2,25.2-4.1,32.6-5.3c2.5-5.6,5.3-15.5,3.3-28.8c-41,15.8-103.1,33.6-175.8,33.6C327.2,209.4,327.5,212,328.5,214.2z"
				/>
			</g>
		</svg>
	);
}

export default GiveLogo;
