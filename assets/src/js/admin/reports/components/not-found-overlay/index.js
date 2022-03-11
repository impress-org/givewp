// Dependencies
import { __ } from '@wordpress/i18n'

// Styles
import './style.scss';

const NotFoundOverlay = () => {
	return (
		<div className="givewp-not-found-overlay">
			<div className="notice-text">
				{ __( 'No data found.', 'give' ) }
			</div>
		</div>
	);
};

export default NotFoundOverlay;
