import { __ } from '@wordpress/i18n';

// Components
import Spinner from '../spinner';

// Styles
import './style.scss';

const LoadingNotice = () => {
	return (
		<div className="givewp-loading-notice">
			<div className="givewp-loading-notice__card">
				<Spinner />
				<h2>{ __( 'Loading your latest', 'give' ) }<br />{ __( 'donation activity', 'give' ) }</h2>
			</div>
		</div>
	);
};

export default LoadingNotice;
