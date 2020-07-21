import PropTypes from 'prop-types';
import './style.scss';

import { useStoreValue } from '../../app/store';
import { goToStep } from '../../app/store/actions';

const StepLink = ( { title, step } ) => {
	const [ { currentStep }, dispatch ] = useStoreValue();

	const check = <svg width="27" height="22" viewBox="0 0 27 22" fill="none" xmlns="http://www.w3.org/2000/svg">
		<g filter="url(#filter0_d)">
			<path d="M10.582 17.7188C10.9336 18.0703 11.5312 18.0703 11.8828 17.7188L22.2188 7.38281C22.5703 7.03125 22.5703 6.43359 22.2188 6.08203L20.9531 4.81641C20.6016 4.46484 20.0391 4.46484 19.6875 4.81641L11.25 13.2539L7.27734 9.31641C6.92578 8.96484 6.36328 8.96484 6.01172 9.31641L4.74609 10.582C4.39453 10.9336 4.39453 11.5312 4.74609 11.8828L10.582 17.7188Z" fill="#F6F9FC" />
		</g>
		<defs>
			<filter id="filter0_d" x="0.482422" y="0.552734" width="26" height="21.4297" filterUnits="userSpaceOnUse" colorInterpolationFilters="sRGB">
				<feFlood floodOpacity="0" result="BackgroundImageFix" />
				<feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" />
				<feOffset />
				<feGaussianBlur stdDeviation="2" />
				<feColorMatrix type="matrix" values="0 0 0 0 0.247059 0 0 0 0 0.670588 0 0 0 0 0.435294 0 0 0 1 0" />
				<feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow" />
				<feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow" result="shape" />
			</filter>
		</defs>
	</svg>;

	return (
		<div className="give-obw-step-link">
			<button className="give-obw-step-button" onClick={ () => dispatch( goToStep( step ) ) }>
				<div className={ `give-obw-step-icon${ currentStep >= step ? ' give-obw-step-icon--green' : '' }` }>
					{ currentStep <= step ? step : check }
				</div>
				<div className="give-obw-step-title">
					{ title }
				</div>
			</button>
			<div className="give-obw-step-progress">
				<div className="give-obw-step-progress-bar"></div>
			</div>
		</div>
	);
};

StepLink.propTypes = {
	title: PropTypes.string.isRequired,
	step: PropTypes.number.isRequired,
};

StepLink.defaultProps = {
	title: null,
	step: null,
};

export default StepLink;
