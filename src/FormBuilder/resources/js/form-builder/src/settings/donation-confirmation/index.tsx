import {PanelBody, PanelRow} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {setFormSettings, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';
import PopoverContentWithTemplateTags from '@givewp/form-builder/components/settings/PopoverContentWithTemplateTags';
import usePopoverState from '@givewp/form-builder/hooks/usePopoverState';
import ControlForPopover from '@givewp/form-builder/components/settings/ControlForPopover';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';

const {donationConfirmationTemplateTags} = getFormBuilderWindowData();

const DonationConfirmation = () => {
    const {
        settings: {receiptHeading, receiptDescription},
    } = useFormState();
    const dispatch = useFormStateDispatch();

    const {
        isOpen: isHeaderMessageSettingsOpen,
        toggle: toggleHeaderMessageSettings,
        close: closeHeaderMessageSettings,
    } = usePopoverState();

    const {
        isOpen: isDescriptionMessageSettingsOpen,
        toggle: toggleDescriptionMessageSettings,
        close: closeDescriptionMessageSettings,
    } = usePopoverState();

    return (
        <PanelBody title={__('Donation Confirmation', 'give')} initialOpen={false}>
            <PanelRow>
                <ControlForPopover
                    id="donation-confirmation-settings-header"
                    help={__('This is the first message that displays in the donation confirmation.', 'give')}
                    heading={__('Header', 'give')}
                    onButtonClick={toggleHeaderMessageSettings}
                    isButtonActive={isHeaderMessageSettingsOpen}
                >
                    {isHeaderMessageSettingsOpen && (
                        <PopoverContentWithTemplateTags
                            onContentChange={(receiptHeading) => dispatch(setFormSettings({receiptHeading}))}
                            heading={__('Header', 'give')}
                            content={receiptHeading}
                            templateTags={donationConfirmationTemplateTags}
                            onClose={closeHeaderMessageSettings}
                        />
                    )}
                </ControlForPopover>
            </PanelRow>
            <PanelRow>
                <ControlForPopover
                    id="donation-confirmation-settings-description"
                    help={__('This is the second message that displays in the donation confirmation.', 'give')}
                    heading={__('Description', 'give')}
                    onButtonClick={toggleDescriptionMessageSettings}
                    isButtonActive={isDescriptionMessageSettingsOpen}
                >
                    {isDescriptionMessageSettingsOpen && (
                        <PopoverContentWithTemplateTags
                            onContentChange={(receiptDescription) => dispatch(setFormSettings({receiptDescription}))}
                            heading={__('Description', 'give')}
                            content={receiptDescription}
                            templateTags={donationConfirmationTemplateTags}
                            onClose={closeDescriptionMessageSettings}
                        />
                    )}
                </ControlForPopover>
            </PanelRow>
        </PanelBody>
    );
};

export default DonationConfirmation;
