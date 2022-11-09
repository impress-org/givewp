import styles from './Input.module.scss';
import cx from 'classnames';

export default function Input({className = '', ...rest}) {
    return <input className={cx(styles.input, className)} {...rest} />;
}
