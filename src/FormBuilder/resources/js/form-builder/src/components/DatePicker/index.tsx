import {__} from '@wordpress/i18n';
import {useRef, useState} from 'react';
import {close} from '@wordpress/icons';
import {Button, DatePicker, DateTimePicker, PanelRow, Popover, TextControl} from '@wordpress/components';

import './styles.scss';

interface DatePickerProps {
    label: string;
    placeholder: string;
    date: string;
    onSelect: (date: string) => void;
    invalidDateBefore?: string;
    invalidDateAfter?: string;
    is12Hour?: boolean;
    showTimeSelector?: boolean;
}

/**
 * @since 3.12.0
 */
export default ({
    label,
    placeholder,
    date: value,
    onSelect,
    invalidDateBefore = null,
    invalidDateAfter = null,
    is12Hour = true,
    showTimeSelector = false,
}: DatePickerProps) => {

    const popoverRef = useRef();

    const [date, setDate] = useState<string>(value || new Date().toISOString().slice(0, 19));
    const [isVisible, setIsVisible] = useState<boolean>(false);

    const toggleVisible = () => {
        setIsVisible((state) => !state);
    };

    const onSelectDate = () => {
        onSelect(date.replace('T', ' '));
        setIsVisible(false);
    };

    const checkDate = (date: Date) => {
        // Check if the date is in range
        if (invalidDateBefore && invalidDateAfter) {
            return !(date > new Date(invalidDateBefore) && date < new Date(invalidDateAfter));
        }

        if (invalidDateBefore && date < new Date(invalidDateBefore)) {
            return true;
        }

        if (invalidDateAfter && date > new Date(invalidDateAfter)) {
            return true;
        }

        return false;
    };

    return (
        <PanelRow ref={popoverRef}>
            <TextControl
                type="text"
                value={value}
                label={label}
                placeholder={placeholder}
                className="givewp-date-picker_input"
                onChange={() => {}}
                onClick={toggleVisible}
            />
            {isVisible && (
                <>
                    <Popover
                        shift
                        anchor={popoverRef.current}
                        position="middle left bottom"
                        className="givewp-date-picker_popover"
                        //@ts-ignore
                        offset={window?.chrome ? 30 : 0}
                    >
                        <Button
                            className="givewp-date-picker_popover__close-button"
                            icon={close}
                            label={__('Close', 'give')}
                            onClick={toggleVisible}
                        />

                        <label>{label}</label>

                        {showTimeSelector ? (
                            <DateTimePicker
                                is12Hour={is12Hour}
                                currentDate={date}
                                isInvalidDate={(date) => checkDate(date)}
                                onChange={(date) => {
                                    setDate(date);
                                }}
                            />
                        ) : (
                            <DatePicker
                                currentDate={date}
                                isInvalidDate={(date) => checkDate(date)}
                                onChange={(date) => {
                                    setDate(date);
                                }}
                            />
                        )}

                        <div className="givewp-date-picker_popover__buttons">
                            <Button variant="primary" onClick={onSelectDate}>
                                {__('Set Date', 'give')}
                            </Button>

                            <Button variant="secondary" onClick={() => {
                                onSelect('');
                                setIsVisible(false);
                            }}>
                                {__('Reset', 'give')}
                            </Button>

                            <Button onClick={toggleVisible}>
                                {__('Cancel', 'give')}
                            </Button>
                        </div>
                    </Popover>
                </>
            )}
        </PanelRow>
    );
}
