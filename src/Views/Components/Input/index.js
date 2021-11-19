import PropTypes from 'prop-types';
import classNames from 'classnames';
import styles from './style.module.scss';

const Input = ({type, name, onChange, value, className, ...rest}) => {
    return (
        <input
            key={value}
            className={classNames(styles.input, className)}
            type={type}
            name={name}
            onChange={onChange}
            value={value}
            {...rest}
        />
    );
};

Input.propTypes = {
    // Input type
    type: PropTypes.string.isRequired,
    // On change event
    onChange: PropTypes.func,
    // Input value
    value: PropTypes.string,
};

export default Input;
