import cx from 'classnames';
import styles from './style.module.scss';

export default ({children, onDismiss, position = 'center'}) => {
    return (
        <div
            className={cx(styles.snackbar, styles[`position-${position}`])}
        >
            {children}
            <a href="#" onClick={onDismiss}>
                x
            </a>
        </div>
    );
}
