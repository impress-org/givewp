/**
 * Vendor dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
const {useInstanceId} = wp.compose;
const {DateTimePicker, BaseControl, Button, Dropdown} = wp.components;
const {__experimentalGetSettings, date} = wp.date;
import { __ } from '@wordpress/i18n'

const DateTimeControl = ({name, label, help, className, value, onChange = null}) => {
    const instanceId = useInstanceId(DateTimeControl);
    const id = `give-date-time-control-${name}-${instanceId}`;
    const settings = __experimentalGetSettings(); // eslint-disable-line no-restricted-syntax

    // To know if the current timezone is a 12 hour time with look for an "a" in the time format.
    // We also make sure this a is not escaped by a "/".
    const is12HourTime = /a(?!\\)/i.test(
        settings.formats.time
            .toLowerCase() // Test only the lower case a
            .replace(/\\\\/g, '') // Replace "//" with empty strings
            .split('')
            .reverse()
            .join('') // Reverse the string and test for "a" not followed by a slash
    );
    return (
        <BaseControl label={label} hideLabelFromVision={true} id={id} help={help} className={className}>
            <div style={{display: 'flex', alignItems: 'center', justifyContent: 'space-between'}}>
                <span>{label}</span>
                <Dropdown
                    position="bottom right"
                    renderToggle={({isOpen, onToggle}) => (
                        <Button isSecondary onClick={onToggle} aria-expanded={isOpen}>
                            {value !== '' ? date('F j, Y', value) : __('Set date', 'give')}
                        </Button>
                    )}
                    renderContent={() => (
                        <DateTimePicker
                            currentDate={value}
                            onChange={(newValue) => onChange(newValue)}
                            is12Hour={is12HourTime}
                        />
                    )}
                />
            </div>
        </BaseControl>
    );
};

DateTimeControl.propTypes = {
    label: PropTypes.string,
    value: PropTypes.any.isRequired,
    onChange: PropTypes.func,
    name: PropTypes.string.isRequired,
    help: PropTypes.string,
    className: PropTypes.string,
};

export default DateTimeControl;
