// Import vendor dependencies
import PropTypes from 'prop-types';
import Select from 'react-select';

// Import utilities
import {toUniqueId} from '../../utils';
import {useAccentColor} from '../../hooks';

// Import styles
import './style.scss';

import {__} from '@wordpress/i18n';

const SelectControl = ({label, value, isLoading, onChange, options, placeholder, width, isClearable}) => {
    if (options && options.length < 2) {
        return null;
    }

    const accentColor = useAccentColor();

    const id = toUniqueId(label);

    const selectedOptionValue = options !== null ? options.filter((option) => option.value === value) : null;
    const selectStyles = {
        control: (provided) => ({
            ...provided,
            fontSize: '14px',
            fontFamily: 'Montserrat, Arial, Helvetica, sans-serif',
            fontWeight: '500',
            color: '#828382',
            lineHeight: '1.2',
            boxSizing: 'border-box',
            marginTop: '8px',
            border: '1px solid #b8b8b8',
            borderRadius: '4px',
        }),
        input: (provided) => ({
            ...provided,
            fontSize: '14px',
            fontFamily: 'Montserrat, Arial, Helvetica, sans-serif',
            fontWeight: '500',
            color: '#828382',
            lineHeight: '1.2',
        }),
        valueContainer: (provided) => ({
            ...provided,
            padding: '7px 12px',
        }),
        clearIndicator: (provided) => ({
            ...provided,
            padding: '0px',
        }),
        dropdownIndicator: (provided) => ({
            ...provided,
            padding: '0 8px 0 0',
        }),
        option: (provided, state) => ({
            ...provided,
            fontSize: '14px',
            fontFamily: 'Montserrat, Arial, Helvetica, sans-serif',
            fontWeight: '500',
            color: state.isSelected ? '#fff' : '#333',
            lineHeight: '1.2',
        }),
        indicatorSeparator: () => ({
            display: 'none',
        }),
    };

    return (
        <div className="give-donor-dashboard-select-control" style={width ? {maxWidth: width} : null}>
            {label && (
                <label className="give-donor-dashboard-select-control__label" htmlFor={id}>
                    {label}
                </label>
            )}
            <Select
                placeholder={placeholder}
                isLoading={isLoading}
                inputId={id}
                value={selectedOptionValue}
                onChange={(selectedOption) => onChange(selectedOption ? selectedOption.value : '')}
                options={options}
                styles={selectStyles}
                maxMenuHeight="200px"
                isDisabled={isLoading}
                isClearable={isClearable}
                theme={(theme) => ({
                    ...theme,
                    colors: {
                        ...theme.colors,
                        primary: accentColor,
                    },
                })}
            />
        </div>
    );
};

SelectControl.propTypes = {
    label: PropTypes.string,
    value: PropTypes.string.isRequired,
    onChange: PropTypes.func,
    options: PropTypes.array.isRequired,
    placeholder: PropTypes.string,
    width: PropTypes.string,
    isClearable: PropTypes.bool,
};

SelectControl.defaultProps = {
    label: null,
    value: null,
    onChange: null,
    options: null,
    placeholder: __('Select...', 'give'),
    width: null,
    isClearable: false,
};

export default SelectControl;
