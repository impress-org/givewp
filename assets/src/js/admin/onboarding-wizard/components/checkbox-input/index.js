// Import vendor dependencies
import PropTypes from 'prop-types';

// Import utilities
import {toKebabCase} from '../../utils';

// Import styles
import './style.scss';

const CheckboxInput = ({label, help, value, checked, testId, onChange}) => {
    return (
        <div className="give-obw-checkbox-input" data-givewp-test={testId}>
            {label && (
                <label className="give-obw-checkbox-input__label" htmlFor={toKebabCase(label)}>
                    {label}
                </label>
            )}
            {help && <p className="give-obw-checkbox-input__help">{help}</p>}
            <input
                type="checkbox"
                id={toKebabCase(label)}
                className="give-obw-checkbox-input__input"
                value={value}
                checked={checked}
                onChange={onChange}
            />
        </div>
    );
};

CheckboxInput.propTypes = {
    label: PropTypes.string,
    help: PropTypes.string,
    value: PropTypes.string.isRequired,
    checked: PropTypes.bool,
    onChange: PropTypes.func,
};

CheckboxInput.defaultProps = {
    label: null,
    help: null,
    value: null,
    checked: false,
    onChange: null,
};

export default CheckboxInput;
