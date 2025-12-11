import {Icon} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {plus, reset as minus} from '@wordpress/icons';
import useCurrencyFormatter from '@givewp/forms/app/hooks/useCurrencyFormatter';

/**
 * @since 4.3.2 include proper labeling for accessibility. update title to <p> tag.
 */
export default function EventTicketsListItem({ticketType, currency, currencyRate, selectedTickets, handleSelect}) {
    const formatter = useCurrencyFormatter(currency);
    const ticketPrice =
        ticketType.price > 0 ? formatter.format((Number(ticketType.price) * currencyRate) / 100) : __('Free', 'give');
    const remainingTickets = ticketType.ticketsAvailable - selectedTickets;

    const handleButtonClick = (quantity) => (e) => {
        e.preventDefault();
        handleSelect(quantity);
    };

    return (
        <div className={'givewp-event-tickets__tickets__ticket'} key={ticketType.id}>
            <div className={'givewp-event-tickets__tickets__ticket__description'}>
                <p>{ticketType.title}</p>
                <p>{ticketPrice}</p>
                <p>{ticketType.description}</p>
            </div>
            <div className={'givewp-event-tickets__tickets__ticket__quantity'}>
                {remainingTickets > 0 ? (
                    <>
                        <div className={'givewp-event-tickets__tickets__ticket__quantity__input'}>
                            <button
                                onClick={handleButtonClick(selectedTickets - 1)}
                                aria-label={__('Decrease quantity of', 'give') + ' ' + ticketType.title}
                            >
                                <Icon icon={minus} />
                            </button>
                            <input
                                type="text"
                                value={selectedTickets}
                                aria-label={__('Ticket quantity', 'give')}
                            />
                            <button
                                onClick={handleButtonClick(selectedTickets + 1)}
                                aria-label={__('Increase quantity of', 'give') + ' ' + ticketType.title}
                            >
                                <Icon icon={plus} />
                            </button>
                        </div>
                        <p
                            className={'givewp-event-tickets__tickets__ticket__quantity__availability'}
                            role="status"
                            aria-live="polite"
                            aria-label={`${remainingTickets} ${__('tickets remaining', 'give')}`}
                        >
                            {remainingTickets} {__('remaining', 'give')}
                        </p>
                    </>
                ) : (
                    <span
                        className={'givewp-event-tickets__tickets__ticket__quantity__sold-out'}
                        role="status"
                        aria-live="polite"
                    >
                        {__('Sold out', 'give')}
                    </span>
                )}
            </div>
        </div>
    );
}
