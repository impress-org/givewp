import {forwardRef, MouseEventHandler, ReactNode} from 'react';
import cx from 'classnames';
import './style.scss';

/**
 *
 * @since 3.0.0
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
            className={cx('givewp-button', variant, size)}
            {...props}
        >
            {children}
        </button>
    )
);

export default Button;
