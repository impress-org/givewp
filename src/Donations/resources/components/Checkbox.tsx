import {forwardRef, InputHTMLAttributes} from 'react';
import cx from 'classnames';
import styles from './Checkbox.module.scss';

type CheckboxProps = Omit<InputHTMLAttributes<HTMLInputElement>, 'type'>;

export const Checkbox = forwardRef<HTMLInputElement, CheckboxProps>(({className, ...props}, ref) => (
    <input ref={ref} type="checkbox" className={cx(styles.checkbox, className)} {...props} />
));

Checkbox.displayName = 'Checkbox';
