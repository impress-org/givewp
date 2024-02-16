import moment from 'moment';

export default function EventTicketsHeader({title, startDateTime}) {
    const fullDate = moment(startDateTime).format('dddd, MMMM Do, h:mma z');
    const day = moment(startDateTime).format('DD');
    const month = moment(startDateTime).format('MMM');

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
