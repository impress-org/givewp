import styles from './Select.module.scss';
import cx from 'classnames';

export default function Select({children, className = '', ...rest}) {
    return (
        <select className={cx(styles.select, className)} {...rest}>
            {children}
        </select>
    );
}
