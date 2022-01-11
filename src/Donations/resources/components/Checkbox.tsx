import {forwardRef, InputHTMLAttributes} from 'react';
import cx from 'classnames';
import styles from './Checkbox.module.scss';

type CheckboxProps = InputHTMLAttributes<HTMLInputElement> & {
    type: 'checkbox';
};

export const Checkbox = forwardRef<HTMLInputElement, CheckboxProps>(({className, type = 'checkbox', ...props}, ref) => (
    <input ref={ref} type={type} className={cx(styles.checkbox, className)} {...props} />
));

Checkbox.displayName = 'Checkbox';
