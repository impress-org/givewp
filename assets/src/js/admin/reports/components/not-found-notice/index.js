// Dependencies
const { __ } = wp.i18n;

// Styles
import './style.scss';

const NotFoundNotice = () => {
	return (
		<div className="givewp-not-found-notice">
			<h2>{ __( 'No donations were found for this period.', 'give' ) }</h2>
		</div>
	);
};

export default NotFoundNotice;
