import {render} from '@wordpress/element';
import {withTemplateWrapper} from '@givewp/forms/app/templates';
import amountFormatter from '@givewp/forms/app/utilities/amountFormatter';
import {ReceiptDetail} from '@givewp/forms/types';

const formTemplates = window.givewp.form.templates;
const DonationReceiptTemplate = withTemplateWrapper(formTemplates.layouts.receipt);

/**
 * Get data from the server
 */
const {receipt} = window.givewpDonationConfirmationReceiptExports;

/**
 * Return readable value
 *
 * @unreleased
 */
const getDetailValue = (value) => {
    if (typeof value === 'string') {
        return value;
    }

    return value?.amount ? amountFormatter(receipt.settings.currency, {}).format(value.amount) : JSON.stringify(value);
};

/**
 * Prepare detail values before render
 *
 * @unreleased
 */
const prepareDetails = (details: ReceiptDetail[]) => {
    return details?.map(({label, value}) => ({
        label,
        value: getDetailValue(value),
    }));
};

/**
 *
 * @unreleased
 */
function DonationConfirmationReceiptApp() {
    return (
        <DonationReceiptTemplate
            heading={receipt.settings.heading}
            description={receipt.settings.description}
            donorDashboardUrl={receipt.settings.donorDashboardUrl}
            donorDetails={receipt.donorDetails}
            donationDetails={prepareDetails(receipt.donationDetails)}
            subscriptionDetails={prepareDetails(receipt.subscriptionDetails)}
            additionalDetails={prepareDetails(receipt.additionalDetails)}
        />
    );
}

const root = document.getElementById('root-givewp-donation-confirmation-receipt');

render(<DonationConfirmationReceiptApp />, root);

root.scrollIntoView({
    behavior: 'smooth',
});
