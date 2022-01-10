import {forwardRef} from 'react';
import PropTypes from 'prop-types';
import cx from 'classnames';

import styles from './Button.module.scss';

export const Button = forwardRef(({className, type = 'button', ...props}, ref) => (
    <button ref={ref} className={cx(styles.button, className)} {...props} />
));

Button.propTypes = {
    className: PropTypes.string,
    type: PropTypes.oneOf(['button', 'submit', 'reset']),
};
