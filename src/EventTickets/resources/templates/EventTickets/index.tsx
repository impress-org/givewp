import moment from 'moment';
import {Icon} from '@wordpress/components';
import {plus, reset as minus} from '@wordpress/icons';
import {__} from '@wordpress/i18n';

export default function EventTicketsField({id, title, date, description, tickets}) {
    const {ticketsLabel, soldOutMessage} = window.eventTicketsBlockSettings;
    const fullDate = moment(date).format('dddd, MMMM Do, h:mma z');
    const [day, month] = moment(date).format('DD MMM');
    const {useWatch, useCurrencyFormatter, useDonationSummary} = window.givewp.form.hooks;

    const currency = useWatch({name: 'currency'});
    const formatter = useCurrencyFormatter(currency);

    const classNamePrefix = 'givewp-event-tickets';

    return (
        <div className={`${classNamePrefix}`}>
            <div className={`${classNamePrefix}__header`}>
                <div className={`${classNamePrefix}__header__date`}>
                    {day} <span>{month}</span>
                </div>
                <h4 className={`${classNamePrefix}__header__title`}>{title}</h4>
                <p className={`${classNamePrefix}__header__full-date`}>{fullDate}</p>
            </div>

            {description && (
                <div className={`${classNamePrefix}__description`}>
                    <p>{description}</p>
                </div>
            )}

            <div className={`${classNamePrefix}__tickets`}>
                <h4>{ticketsLabel}</h4>
                {tickets.map((ticket) => {
                    const ticketPrice = formatter.format(Number(ticket.price));

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
    );
}
