import {useFormContext} from 'react-hook-form';
import CircularExitIcon from '@givewp/components/AdminUI/Icons/CircularExitIcon';
import styles from './style.module.scss';
import {TimeActionProps} from '../types';
import {format, formatISO, parse, parseISO} from 'date-fns';

export function Actions({isOpen, closeFields, hours, minutes, ampm}: TimeActionProps) {
    const {setValue, getValues} = useFormContext();

    const createdAt = getValues('createdAt');

    const convertTo24hourTime = () => {
        let convertedHours = Number(hours);

        if (ampm === 'PM' && hours !== 12) {
            convertedHours = convertedHours + 12;
        }

        return Number(convertedHours);
    };

    const cancelFieldSelection = () => {
        closeFields();
    };

    const confirmFieldValues = () => {
        const formattedHours = convertTo24hourTime();

        const preservedDateValue = parseISO(createdAt);
        const formattedDateValue = format(preservedDateValue, 'yyyy-MM-dd');
        const preservedSecondsValue = new Date(createdAt).getSeconds();

        const dateString = String(`${formattedDateValue} ${formattedHours}:${minutes}:${preservedSecondsValue}`);
        const newDateObject = parse(dateString, 'yyyy-MM-dd HH:mm:ss', new Date());

        const validFormFieldValue = formatISO(newDateObject);

        setValue('createdAt', validFormFieldValue, {shouldDirty: true});

        closeFields();
    };

    return (
        <>
            <div role={'button'} aria-pressed={isOpen} onClick={cancelFieldSelection}>
                <CircularExitIcon color={'#0B72D9'} />
            </div>
            <div className={styles.confirmSelection} role={'button'} aria-pressed={isOpen} onClick={confirmFieldValues}>
                &#10003;
            </div>
        </>
    );
}
