import Field from '../Field';
import {__} from '@wordpress/i18n';
import {useState} from 'react';
import styles from './style.module.scss';
import {useWatch} from 'react-hook-form';
import {format} from 'date-fns';
import {AmpmField, NumberField} from './inputFields';
import {Actions} from './Actions';

export default function TimePickerField() {
    const watchedTime = useWatch({name: 'createdAt'});
    const timeObject = new Date(watchedTime);

    const [showFields, setShowFields] = useState<boolean>(false);

    return (
        <Field label={__('Donation time', 'give')} editable onEdit={() => setShowFields(!showFields)}>
            {showFields ? (
                <div className={styles.timePickerPosition}>
                    <TimeFields isOpen={showFields} timeObject={timeObject} closeFields={() => setShowFields(false)} />
                </div>
            ) : (
                <span>{format(timeObject, 'h:mm a')}</span>
            )}
        </Field>
    );
}

export function TimeFields({isOpen, timeObject, closeFields}) {
    const [hours, setHours] = useState<number>(Number(format(timeObject, timeObject.getHours() >= 12 ? 'h' : 'h')));
    const [minutes, setMinutes] = useState<number>(Number(format(timeObject, timeObject.getMinutes())));
    const [ampm, setAmpm] = useState<string>(format(timeObject, 'h:mm a').split(' ')[1]);

    return (
        <>
            <NumberField
                id={'give-payment-time-hour'}
                label={__('Payment time by the minute')}
                setState={setHours}
                state={hours}
                min={0}
                max={12}
            />
            <>&#x3A;</>
            <NumberField
                id={'give-payment-time-minute'}
                label={__('Payment time by the minute')}
                setState={setMinutes}
                state={minutes}
                min={0}
                max={59}
            />
            <AmpmField state={ampm} setState={setAmpm} />
            <Actions
                isOpen={isOpen}
                closeFields={() => closeFields(false)}
                hours={hours}
                minutes={minutes}
                ampm={ampm}
            />
        </>
    );
}
