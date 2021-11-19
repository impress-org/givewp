import {Link} from 'react-router-dom';
import {FontAwesomeIcon} from '@fortawesome/react-fontawesome';
import {__} from '@wordpress/i18n';
import {useWindowSize} from '../../hooks';

const DonationRow = ({donation}) => {
    const {id, form, payment} = donation;
    const {width} = useWindowSize();

    return (
        <div className="give-donor-dashboard-table__row">
            <div className="give-donor-dashboard-table__column">
                {width < 920 && <div className="give-donor-dashboard-table__mobile-header">{__('Amount', 'give')}</div>}
                <div className="give-donor-dashboard-table__donation-amount">{payment.amount}</div>
            </div>
            <div className="give-donor-dashboard-table__column">
                {width < 920 && <div className="give-donor-dashboard-table__mobile-header">{__('Form', 'give')}</div>}
                {form.title}
            </div>
            <div className="give-donor-dashboard-table__column">
                {width < 920 && <div className="give-donor-dashboard-table__mobile-header">{__('Date', 'give')}</div>}
                <div className="give-donor-dashboard-table__donation-date">{payment.date}</div>
                <div className="give-donor-dashboard-table__donation-time">{payment.time}</div>
            </div>
            <div className="give-donor-dashboard-table__column">
                {width < 920 && <div className="give-donor-dashboard-table__mobile-header">{__('Status', 'give')}</div>}
                <div className="give-donor-dashboard-table__donation-status">
                    <div
                        className="give-donor-dashboard-table__donation-status-indicator"
                        style={{background: payment.status.color}}
                    />
                    <div className="give-donor-dashboard-table__donation-status-label">{payment.status.label}</div>
                </div>
                {payment.mode === 'test' && (
                    <div className="give-donor-dashboard-table__donation-test-tag">{__('Test Donation', 'give')}</div>
                )}
            </div>
            <div className="give-donor-dashboard-table__pill">
                <div className="give-donor-dashboard-table__donation-id">
                    {__('ID', 'give')}: {payment.serialCode}
                </div>
                <div className="give-donor-dashboard-table__donation-receipt">
                    <Link to={`/donation-history/${id}`}>
                        {__('View Receipt', 'give')} <FontAwesomeIcon icon="arrow-right" />
                    </Link>
                </div>
            </div>
        </div>
    );
};

export default DonationRow;
