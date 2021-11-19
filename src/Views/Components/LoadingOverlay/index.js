// Components
import Spinner from '@givewp/components/Spinner';

// Styles
import styles from './style.module.scss';

const LoadingOverlay = ({spinnerSize}) => {
    return (
        <div className={styles.overlay}>
            <Spinner size={spinnerSize} />
        </div>
    );
};

export default LoadingOverlay;
