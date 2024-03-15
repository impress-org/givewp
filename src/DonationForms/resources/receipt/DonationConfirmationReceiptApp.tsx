import {createRoot, render} from '@wordpress/element';
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
 * This function is used to format the amount value.  It also handles cased when there are additional details within the string.  For example "$25.00 / month"
 *
 * @since 3.0.0
 */
const getAmountFormatted = (value) => {
    const amount = parseFloat(value);
    const amountFormatted = amountFormatter(receipt.settings.currency, {}).format(amount);
    const additionalDetails = value.replace(/^[\d|.,]+/, '').trim();

    if (!additionalDetails) {
        return amountFormatted;
    } else {
        return `${amountFormatted} ${additionalDetails}`;
    }
};

/**
 * Return readable value
 *
 * @since 3.0.0
 */
const getDetailValue = (value) => {
    if (typeof value === 'string') {
        return value;
    }

    if (value?.amount) {
        return getAmountFormatted(value.amount);
    }

    return JSON.stringify(value);
};

/**
 * Prepare detail values before render
 *
 * @since 3.0.0
 */
const prepareDetails = (details: ReceiptDetail[]) => {
    return details?.map(({label, value}) => ({
        label,
        value: getDetailValue(value),
    }));
};

/**
 *
 * @since 3.0.0
 */
function DonationConfirmationReceiptApp() {
    return (
        <DonationReceiptTemplate
            heading={receipt.settings.heading}
            description={receipt.settings.description}
            donorDashboardUrl={receipt.settings.donorDashboardUrl}
            pdfReceiptLink={receipt.settings.pdfReceiptLink}
            donorDetails={receipt.donorDetails}
            donationDetails={prepareDetails(receipt.donationDetails)}
            subscriptionDetails={prepareDetails(receipt.subscriptionDetails)}
            eventTicketsDetails={prepareDetails(receipt.eventTicketsDetails)}
            additionalDetails={prepareDetails(receipt.additionalDetails)}
        />
    );
}

const root = document.getElementById('root-givewp-donation-confirmation-receipt');

if (createRoot) {
    createRoot(root).render(<DonationConfirmationReceiptApp />);
} else {
    render(<DonationConfirmationReceiptApp />, root);
}

root.scrollIntoView({
    behavior: 'smooth',
});
