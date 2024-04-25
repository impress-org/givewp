import {__} from '@wordpress/i18n';
import {useRef, useState} from 'react';
import {Button, DatePicker, DateTimePicker, PanelRow, Popover, TextControl} from '@wordpress/components';

import './styles.scss';
import {close} from '@wordpress/icons';

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
 * @unreleased
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

    const [date, setDate] = useState<string>(value);
    const [isVisible, setIsVisible] = useState<boolean>(false);
    const currentDate = date ? new Date(Date.parse(date)) : new Date();


    const convertJsDateToMySQLDate = (dateTime: string) => {
        // split the ISO string into date and time
        const [date, time] = new Date(dateTime).toISOString().split('T');

        return `${date} ${time.slice(0, 8)}`;
    };

    const toggleVisible = () => {
        setIsVisible((state) => !state);
    };

    const onSelectDate = () => {
        onSelect(convertJsDateToMySQLDate(date));
        setIsVisible(false);
    };

    const checkDate = (date: Date) => {
        if (invalidDateBefore) {
            return date < new Date(invalidDateBefore);
        }

        if (invalidDateAfter) {
            return date > new Date(invalidDateAfter);
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
                                currentDate={currentDate}
                                isInvalidDate={(date) => checkDate(date)}
                                onChange={(date) => {
                                    setDate(date);
                                }}
                            />
                        ) : (
                            <DatePicker
                                currentDate={currentDate}
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
