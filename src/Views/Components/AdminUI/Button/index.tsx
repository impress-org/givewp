import React from 'react';

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
    children: React.ReactNode;

    onClick?: React.MouseEventHandler<HTMLButtonElement>;
    disabled?: boolean;
    classname?: 'string';
};

const Button = React.forwardRef<HTMLButtonElement, ButtonProps>(
    ({children, type = 'button', classname, variant = 'primary', size = 'normal', disabled = false, ...props}, ref) => (
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
