// Components
import Spinner from '../spinner';

// Styles
import './style.scss';

const LoadingOverlay = () => {
	return (
		<div className="givewp-loading-overlay">
			<Spinner />
		</div>
	);
};

export default LoadingOverlay;
