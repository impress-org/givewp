import {createHigherOrderComponent} from '@wordpress/compose';
import {InspectorControls} from '@wordpress/block-editor';
import {__} from '@wordpress/i18n';
import {PanelBody, PanelRow, ToggleControl} from '@wordpress/components';
import {createInterpolateElement} from '@wordpress/element';
import ControlForPopover from '@givewp/form-builder/components/settings/ControlForPopover';
import PopoverContentWithTemplateTags from '@givewp/form-builder/components/settings/PopoverContentWithTemplateTags';

import useSetDefaultAttributes from './useSetDefaultAttributes';
import {offlineAttributes} from './addOfflineAttributes';
import usePopoverState from '@givewp/form-builder/hooks/usePopoverState';

declare const window: {
    giveOfflineGatewaySettings: {
        offlineSettingsUrl: string;
    };
} & Window;

type OfflineAttributes = {
    offlineEnabled?: boolean;
    offlineUseGlobalInstructions?: boolean;
    offlineDonationInstructions?: string;
};

type OfflineSetAttributes = (attributes: OfflineAttributes) => void;

const offlineInstructionTags = [
    {
        id: 'offline_mailing_address',
        description: __('The mailing address provided to the donor to send their payment to.', 'give'),
    },
    {
        id: 'sitename',
        description: __('The name of this website.', 'give'),
    },
];

function OfflineInspectorControls({
    attributes,
    setAttributes,
}: {
    setAttributes: OfflineSetAttributes;
    attributes: OfflineAttributes;
}) {
    const {isOpen: isInstructionsOpen, toggle: toggleInstructions, close: closeInstructions} = usePopoverState();
    useSetDefaultAttributes(attributes, setAttributes, offlineAttributes);

    const windowSettings = window.giveOfflineGatewaySettings;
    const textWithLinkToOfflineSettings = (textTemplate: string) =>
        createInterpolateElement(textTemplate, {
            a: <a href={windowSettings.offlineSettingsUrl} target="_blank" />,
        });

    const globalDefaultHelper = textWithLinkToOfflineSettings(
        __(
            'Global instructions are defined in the <a>Global Settings</a>. When disabled, custom instructions can be written for this form',
            'give'
        )
    );

    return (
        <InspectorControls>
            <PanelBody title={__('Offline Donations', 'give')}>
                <PanelRow>
                    <ToggleControl
                        label={__('Enable offline donations', 'give')}
                        checked={attributes.offlineEnabled}
                        onChange={(value) => setAttributes({offlineEnabled: value})}
                    />
                </PanelRow>
                {attributes.offlineEnabled && (
                    <PanelRow>
                        <ToggleControl
                            label={__('Use global instructions', 'give')}
                            checked={attributes.offlineUseGlobalInstructions}
                            onChange={(value) => setAttributes({offlineUseGlobalInstructions: value})}
                            help={globalDefaultHelper}
                        />
                    </PanelRow>
                )}
                {attributes.offlineEnabled && !attributes.offlineUseGlobalInstructions && (
                    <PanelRow>
                        <ControlForPopover
                            id="offline-donation-instructions"
                            help={__(
                                'Enter the instructions you want to display to the donor during the donation process. Most likely this would include important information like mailing address and who to make the check out to',
                                'give'
                            )}
                            heading={__('Donation Instructions', 'give')}
                            onButtonClick={toggleInstructions}
                            isButtonActive={isInstructionsOpen}
                        >
                            {isInstructionsOpen && (
                                <PopoverContentWithTemplateTags
                                    onContentChange={(content) => setAttributes({offlineDonationInstructions: content})}
                                    heading={__('Donation Instructions', 'give')}
                                    content={attributes.offlineDonationInstructions}
                                    templateTags={offlineInstructionTags}
                                    onClose={closeInstructions}
                                    richText
                                />
                            )}
                        </ControlForPopover>
                    </PanelRow>
                )}
            </PanelBody>
        </InspectorControls>
    );
}

const withInspectorControls = createHigherOrderComponent((BlockEdit) => {
    return (props) => {
        if (props.name === 'givewp/payment-gateways') {
            return (
                <>
                    <BlockEdit {...props} />
                    <OfflineInspectorControls {...props} />
                </>
            );
        }
        return <BlockEdit {...props} />;
    };
}, 'withInspectorControl');

export default withInspectorControls;
