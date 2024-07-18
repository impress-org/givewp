// Import vendor dependencies
import PropTypes from 'prop-types';
import cx from 'classnames';

// Import styles
import './style.scss';

const Button = ({className, onClick, testId, children}) => {
    return (
        <button className={cx('give-obw-button', className)} data-givewp-test={testId} onClick={onClick}>
            {children}
        </button>
    );
};

Button.propTypes = {
    className: PropTypes.string,
    onClick: PropTypes.func,
    testId: PropTypes.string,
    children: PropTypes.node,
};

Button.defaultProps = {
    className: null,
    onClick: null,
    testId: null,
    children: null,
};

export default Button;
