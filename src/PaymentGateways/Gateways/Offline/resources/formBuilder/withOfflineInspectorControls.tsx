import {createHigherOrderComponent} from '@wordpress/compose';
import {InspectorControls} from '@wordpress/block-editor';
import {__} from '@wordpress/i18n';
import {PanelBody, PanelRow, ToggleControl} from '@wordpress/components';
import {createInterpolateElement} from '@wordpress/element';
import ControlForPopover from "@givewp/form-builder/components/settings/ControlForPopover";
import PopoverContentWithTemplateTags from "@givewp/form-builder/components/settings/PopoverContentWithTemplateTags";

import useSetDefaultAttributes from './useSetDefaultAttributes';
import {offlineAttributes} from './addOfflineAttributes';
import usePopoverState from "@givewp/form-builder/hooks/usePopoverState";

declare const window: {
    giveOfflineGatewaySettings: {
        offlineSettingsUrl: string;
    };
} & Window;

type OfflineAttributes = {
    offlineUseGlobalDefault?: boolean;
    offlineEnabled?: boolean;
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
]

function OfflineInspectorControls({
    attributes,
    setAttributes,
}: {
    setAttributes: OfflineSetAttributes;
    attributes: OfflineAttributes;
}) {
    const {
        isOpen: isInstructionsOpen,
        toggle: toggleInstructions,
        close: closeInstructions,
    } = usePopoverState();
    useSetDefaultAttributes(attributes, setAttributes, offlineAttributes);

    const windowSettings = window.giveOfflineGatewaySettings;
    const textWithLinkToOfflineSettings = (textTemplate: string) =>
        createInterpolateElement(textTemplate, {
            a: <a href={windowSettings.offlineSettingsUrl} target="_blank" />,
        });

    const globalDefaultHelper = textWithLinkToOfflineSettings(
        __(
            'When disabled, the <a>Global settings</a> will determine whether the Offline gateway is enabled and its instructions',
            'give'
        )
    );

    return (
        <InspectorControls>
            <PanelBody title={__('Offline Donations', 'give')}>
                <PanelRow>
                    <ToggleControl
                        label={__('Use global default', 'give')}
                        checked={attributes.offlineUseGlobalDefault}
                        onChange={(value) => setAttributes({offlineUseGlobalDefault: value})}
                        help={globalDefaultHelper}
                    />
                </PanelRow>
                {!attributes.offlineUseGlobalDefault && (
                    <>
                        <PanelRow>
                            <ToggleControl
                                label={__('Enable Offline Gateway', 'give')}
                                checked={attributes.offlineEnabled}
                                onChange={(value) => setAttributes({offlineEnabled: value})}
                                help={__('Enable or enable the Offline gateway for this form', 'give')}
                            />
                        </PanelRow>
                        <PanelRow>
                            <ControlForPopover
                                id="offline-donation-instructions"
                                help={__('The instructions provided to the donor on how to send their payment', 'give')}
                                heading={__('Donation Instructions', 'give')}
                                buttonCaption={__('Edit', 'give')}
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
                                        useEditor
                                    />
                                )}
                            </ControlForPopover>
                        </PanelRow>
                    </>
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
