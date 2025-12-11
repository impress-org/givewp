import classNames from 'classnames';
import styles from './style.module.scss';

/**
 * @since 4.4.0
 */
type SpinnerSize = 'tiny' | 'small' | 'medium' | 'large';

/**
 * @since 4.4.0
 */
type SpinnerProps = React.HTMLAttributes<HTMLDivElement> & {
    size?: SpinnerSize;
};

/**
 * @since 4.4.0
 */
const Spinner = ({size = 'small', ...rest}: SpinnerProps) => {
    const spinnerClasses = classNames({
        [styles.spinner]: true,
        [styles.large]: size === 'large',
        [styles.medium]: size === 'medium',
        [styles.small]: size === 'small',
        [styles.tiny]: size === 'tiny',
    });

    return (
        <div className={spinnerClasses} {...rest}>
            {' '}
        </div>
    );
};

export default Spinner;
