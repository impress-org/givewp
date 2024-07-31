import {__, sprintf} from "@wordpress/i18n";
import {createInterpolateElement} from "@wordpress/element";
import {CheckVerified} from "@givewp/components/AdminUI/Icons";

/**
 * @unreleased
 */
export const UpgradeModalContent = () => {

    const {supportedAddons, supportedGateways} = window.GiveDonationForms;

    return <p style={{maxWidth: '400px'}}>
        {createInterpolateElement(
            sprintf(__('GiveWP 3.0 introduces an enhanced forms experience powered by the new Visual Donation Form Builder. The team is still working on add-on and gateway compatibility. If you need to use an add-on or gateway that isn\'t listed, use the "%sAdd form%s" option for now.', 'give'), '<b>', '</b>'),
            {
                b: <strong />,
            }
        )}
        <br />
        {supportedAddons.length > 0 && (
            <>
                <h3>{__('Supported add-ons', 'give')}</h3>
                <ul>
                    {supportedAddons.map((addon) => (
                        <li key={addon}><CheckVerified /> {addon}</li>
                    ))}
                </ul>
            </>
        )}
        <br />
        {supportedGateways.length > 0 && (
            <>
                <h3>{__('Supported gateways', 'give')}</h3>
                <ul>
                    {supportedGateways.map((gateway) => (
                        <li key={gateway}><CheckVerified /> {gateway}</li>
                    ))}
                </ul>
            </>
        )}
        <div>
            <a href="https://docs.givewp.com/compat-guide" rel="noopener noreferrer" target="_blank">
                {__('Read more on Add-ons and Gateways compatibility', 'give')}
            </a>
        </div>
    </p>
}
