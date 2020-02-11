// Components
import Spinner from '../spinner';

// Styles
import './style.scss';

const LoadingNotice = () => {
	return (
		<div className="givewp-loading-notice">
			<Spinner />
		</div>
	);
};

export default LoadingNotice;
