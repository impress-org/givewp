import {Icon} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {plus, reset as minus} from '@wordpress/icons';
import useCurrencyFormatter from '@givewp/forms/app/hooks/useCurrencyFormatter';

export default function EventTicketsListItem({ticketType, currency, selectedTickets, handleSelect}) {
    const formatter = useCurrencyFormatter(currency);
    const ticketPrice = formatter.format(Number(ticketType.price));

    const handleButtonClick = (quantity) => (e) => {
        e.preventDefault();
        handleSelect(quantity);
    };

    return (
        <div className={'givewp-event-tickets__tickets__ticket'} key={ticketType.id}>
            <div className={'givewp-event-tickets__tickets__ticket__description'}>
                <h5>{ticketType.label}</h5>
                <p>{ticketPrice}</p>
                <p>{ticketType.description}</p>
            </div>
            <div className={'givewp-event-tickets__tickets__ticket__quantity'}>
                <div className={'givewp-event-tickets__tickets__ticket__quantity__input'}>
                    <button onClick={handleButtonClick(selectedTickets - 1)}>
                        <Icon icon={minus} />
                    </button>
                    <input type="text" value={selectedTickets} />
                    <button onClick={handleButtonClick(selectedTickets + 1)}>
                        <Icon icon={plus} />
                    </button>
                </div>
                <p className={'givewp-event-tickets__tickets__ticket__quantity__availability'}>
                    {ticketType.max_available - selectedTickets} {__('remaining', 'give')}
                </p>
            </div>
        </div>
    );
}
