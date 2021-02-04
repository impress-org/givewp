// Components
import Spinner from 'GiveComponents/Spinner';

// Styles
import styles from './style.module.scss';

const LoadingOverlay = () => {
	return (
		<div className={ styles.overlay }>
			<Spinner />
		</div>
	);
};

export default LoadingOverlay;
