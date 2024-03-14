import {__} from '@wordpress/i18n';
import {format} from 'date-fns';

export default function EventTicketsHeader({title, startDateTime, endDateTime}) {
    const fullDate = format(startDateTime, 'EEEE, MMMM do, hh:mmaaa');
    const day = format(startDateTime, 'dd');
    const month = format(startDateTime, 'MMM');
    const hasEnded = endDateTime < new Date();

    console.log(endDateTime);

    return (
        <div className={'givewp-event-tickets__header'}>
            <div className={'givewp-event-tickets__header__date'}>
                {day} <span>{month}</span>
            </div>
            <h4 className={'givewp-event-tickets__header__title'}>{title}</h4>
            <p className={'givewp-event-tickets__header__full-date'}>{fullDate}</p>

            {hasEnded && (
                <div className={'givewp-event-tickets__header__ended'}>
                    <span>{__('Ended', 'give')}</span>
                </div>
            )}
        </div>
    );
}
