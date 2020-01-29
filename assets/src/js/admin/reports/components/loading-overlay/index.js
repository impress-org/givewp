const { __ } = wp.i18n;

import './style.scss';

const LoadingOverlay = () => {
	return (
		<div className="givewp-loading-overlay">
			<div className="notice-text">
				{ __( 'Loading...', 'give' ) }
			</div>
		</div>
	);
};

export default LoadingOverlay;
