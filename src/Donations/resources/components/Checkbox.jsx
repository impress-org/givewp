import {forwardRef} from 'react';
import cx from 'classnames';
import PropTypes from 'prop-types';
import styles from './Checkbox.module.scss';

export const Checkbox = forwardRef(({className, type = 'checkbox', ...props}, ref) => (
    <input ref={ref} type={type} className={cx(styles.checkbox, className)} {...props} />
));

Checkbox.displayName = 'Checkbox';

Checkbox.propTypes = {
    className: PropTypes.string,
    type: PropTypes.oneOf(['checkbox']),
};
