const { __ } = wp.i18n;

import './style.scss';

const LoadingNotice = () => {
	return (
		<div className="givewp-loading-notice">
			<h2>{ __( 'Loading...', 'give' ) }</h2>
		</div>
	);
};

export default LoadingNotice;
