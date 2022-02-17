import cx from 'classnames';
import styles from './Button.module.css';

export const Button = ({as: Element = 'button', className, ...props}) => (
    <Element className={cx(styles.button, className)} {...props} />
);
