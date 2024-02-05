import moment from 'moment';
import {plus, reset as minus} from '@wordpress/icons';
import {Icon} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {getWindowData} from '@givewp/form-builder/common';

/**
 * @unreleased
 */
export default function BlockPlaceholder({attributes}) {
    const {events, ticketsLabel, soldOutMessage} = window.eventTicketsBlockSettings;
    const event = events.find((event) => event.id === attributes.eventId);

    if (!event || !event.tickets.length) {
        return null;
    }

    const fullDate = moment(event.date).format('dddd, MMMM Do, h:mma z');
    const [day, month] = moment(event.date).format('DD MMM').split(' ');
    const locale = document.querySelector('html').getAttribute('lang');
    const {currency} = getWindowData();

    const classNamePrefix = 'givewp-event-tickets';
    return (
        <div className={`${classNamePrefix}-block__placeholder`}>
            <div className={`${classNamePrefix}`}>
                <div className={`${classNamePrefix}__header`}>
                    <div className={`${classNamePrefix}__header__date`}>
                        {day} <span>{month}</span>
                    </div>
                    <h4 className={`${classNamePrefix}__header__title`}>{event.title}</h4>
                    <p className={`${classNamePrefix}__header__full-date`}>{fullDate}</p>
                </div>

                {event.description && (
                    <div className={`${classNamePrefix}__description`}>
                        <p>{event.description}</p>
                    </div>
                )}

                <div className={`${classNamePrefix}__tickets`}>
                    <h4>{ticketsLabel}</h4>
                    {event.tickets.map((ticket) => {
                        const ticketPrice = new Intl.NumberFormat(locale, {
                            style: 'currency',
                            currency,
                        }).format(ticket.price);

                        return (
                            <div className={`${classNamePrefix}__tickets__ticket`} key={ticket.id}>
                                <div className={`${classNamePrefix}__tickets__ticket__description`}>
                                    <h5>{ticket.name}</h5>
                                    <p>{ticketPrice}</p>
                                    <p>{ticket.description}</p>
                                </div>
                                <div className={`${classNamePrefix}__tickets__ticket__quantity`}>
                                    <div className={`${classNamePrefix}__tickets__ticket__quantity__input`}>
                                        <button>
                                            <Icon icon={minus} />
                                        </button>
                                        <input type="text" value="0" />
                                        <button>
                                            <Icon icon={plus} />
                                        </button>
                                    </div>
                                    <p className={`${classNamePrefix}__tickets__ticket__quantity__availability`}>
                                        {ticket.quantity} {__('remaining', 'give')}
                                    </p>
                                </div>
                            </div>
                        );
                    })}
                </div>
            </div>
        </div>
    );
}
