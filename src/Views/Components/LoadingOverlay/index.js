// Components
import Spinner from 'GiveComponents/Spinner';

// Styles
import styles from './style.module.scss';

const LoadingOverlay = ( { spinnerSize } ) => {
	return (
		<div className={ styles.overlay }>
			<Spinner size={ spinnerSize } />
		</div>
	);
};

export default LoadingOverlay;
