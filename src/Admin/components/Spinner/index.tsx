import classNames from 'classnames';

import styles from './Spinner.module.scss';

type SpinnerSize = 'tiny' | 'small' | 'medium' | 'large';

interface SpinnerProps extends React.HTMLAttributes<HTMLDivElement> {
    size?: SpinnerSize;
}

/**
 * @unreleased
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
