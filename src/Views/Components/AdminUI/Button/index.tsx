import React from 'react';

import cx from 'classnames';

import styles from './style.module.scss';
import {ButtonProps} from '@givewp/components/AdminUI/Button/types';

/**
 *
 * @unreleased
 */

const Button = React.forwardRef<HTMLButtonElement, ButtonProps>(
    ({children, type = 'button', classname, variant = 'primary', size = 'normal', disabled, ...props}, ref) => (
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
