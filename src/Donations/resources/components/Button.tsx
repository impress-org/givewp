import {ButtonHTMLAttributes, forwardRef} from 'react';
import cx from 'classnames';

import styles from './Button.module.scss';

type ButtonProps = ButtonHTMLAttributes<HTMLButtonElement>;

export const Button = forwardRef<HTMLButtonElement, ButtonProps>(({className, type = 'button', ...props}, ref) => (
    <button ref={ref} className={cx(styles.button, className)} {...props} />
));
