import {forwardRef, MouseEventHandler, ReactNode} from 'react';

import cx from 'classnames';

import styles from './style.module.scss';

/**
 *
 * @unreleased
 */

export type ButtonProps = {
    variant?: 'primary' | 'secondary' | 'danger';
    size?: 'small' | 'large';
    type?: 'button' | 'reset' | 'submit';
    children: ReactNode;
    onClick?: MouseEventHandler;
    disabled?: boolean;
    [x: string]: any;
};

const Button = forwardRef<HTMLButtonElement, ButtonProps>(
    ({children, type = 'button', variant = 'primary', size = 'small', disabled = false, ...props}, ref) => (
        <button
            ref={ref}
            disabled={disabled}
            type={type}
            className={cx(styles.button, styles[variant], styles[size])}
            {...props}
        >
            {children}
        </button>
    )
);

export default Button;
