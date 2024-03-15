import {format} from 'date-fns';

export default function EventTicketsHeader({title, startDateTime}) {
    const fullDate = format(startDateTime, 'EEEE, MMMM do, hh:mmaaa');
    const day = format(startDateTime, 'dd');
    const month = format(startDateTime, 'MMM');

    return (
        <div className={'givewp-event-tickets__header'}>
            <div className={'givewp-event-tickets__header__date'}>
                {day} <span>{month}</span>
            </div>
            <h4 className={'givewp-event-tickets__header__title'}>{title}</h4>
            <p className={'givewp-event-tickets__header__full-date'}>{fullDate}</p>
        </div>
    );
}
