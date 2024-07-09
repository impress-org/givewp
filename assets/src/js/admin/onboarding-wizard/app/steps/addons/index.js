// Import vendor dependencies
import { __ } from '@wordpress/i18n'

// Import store dependencies
import { useStoreValue } from '../../store';
import { setAddons } from '../../store/actions';

// Import components
import Card from '../../../components/card';
import CardInput from '../../../components/card-input';
import ContinueButton from '../../../components/continue-button';
import RecurringDonationsIcon from '../../../components/icons/recurring-donations';
import DonorsCoverFeesIcon from '../../../components/icons/donors-cover-fees';
import PDFReceiptsIcon from '../../../components/icons/pdf-receipts';
import CustomFormFieldsIcon from '../../../components/icons/custom-form-fields';
import MultipleCurrenciesIcon from '../../../components/icons/multiple-currencies';
import DedicateDonationsIcon from '../../../components/icons/dedicate-donations';

// Import styles
import './style.scss';

const Addons = () => {
	const [ { configuration }, dispatch ] = useStoreValue();
	const addons = configuration.addons;

    return (
        <div className="give-obw-fundraising-needs">
            <h1>{__('What else do you need to support your cause?', 'give')}</h1>
            <p>{__('Take your fundraising to the next level with these premium add-ons.', 'give')}</p>
            <CardInput values={addons} onChange={(value) => dispatch(setAddons(value))}>
                <Card value="recurring-donations">
                    <RecurringDonationsIcon />
                    <h2>{__('Recurring Donations', 'give')}</h2>
                    <p>{__('Allow donors to make donations on a recurring basis.', 'give')}</p>
                </Card>
                <Card value="donors-cover-fees">
                    <DonorsCoverFeesIcon />
                    <h2>{__('Donors Cover Fees', 'give')}</h2>
                    <p>{__('Enable donors to cover payment processing fees.', 'give')}</p>
                </Card>
                <Card value="pdf-receipts">
                    <PDFReceiptsIcon />
                    <h2>{__('PDF Receipts', 'give')}</h2>
                    <p>{__('Provide custom donation receipts in PDF format.', 'give')}</p>
                </Card>
                <Card value="custom-form-fields">
                    <CustomFormFieldsIcon />
                    <h2>{__('Custom Form Fields', 'give')}</h2>
                    <p>{__('Add custom fields to your donation forms.', 'give')}</p>
                </Card>
                <Card value="multiple-currencies">
                    <MultipleCurrenciesIcon />
                    <h2>{__('Multiple Currencies', 'give')}</h2>
                    <p>{__('Accept donations in your preferred currencies.', 'give')}</p>
                </Card>
                <Card value="dedicate-donations">
                    <DedicateDonationsIcon />
                    <h2>{__('Dedicate Donations', 'give')}</h2>
                    <p>{__('Allow donors to dedicate their donation to someone special.', 'give')}</p>
                </Card>
            </CardInput>
            <ContinueButton testId="addons-continue-button" />
        </div>
    );
};

export default Addons;
