import {__, sprintf} from "@wordpress/i18n";
import {createInterpolateElement} from "@wordpress/element";
import {CheckVerified} from "@givewp/components/AdminUI/Icons";

/**
 * @since 3.16.0
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
            <SupportedItemsList title={__('Supported add-ons', 'give')} items={supportedAddons} />
        )}
        <br />
        {supportedGateways.length > 0 && (
            <SupportedItemsList title={__('Supported gateways', 'give')} items={supportedGateways} />
        )}
        <div>
            <a href="https://docs.givewp.com/compat-guide" rel="noopener noreferrer" target="_blank">
                {__('Read more on Add-ons and Gateways compatibility', 'give')}
            </a>
        </div>
    </p>
}

const SupportedItemsList = ({title, items}) => {
    return (
        <>
            <h3>{title}</h3>
            <ul>
                {items.map((item) => (
                    <li key={item}>
                        <CheckVerified /> {item}
                    </li>
                ))}
            </ul>
        </>
    )
}
