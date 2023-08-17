import {useToggleState} from '../../hooks';
import {Modal, PanelRow} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import Editor from '@givewp/form-builder/settings/email/template-options/components/editor';
import {setFormSettings, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';
import ControlForPopover from '@givewp/form-builder/components/settings/ControlForPopover';

const DonationInstructions = () => {
    const {
        settings: {offlineDonationsInstructions},
    } = useFormState();
    const dispatch = useFormStateDispatch();

    const {state: showPopout, toggle: toggleShowPopout} = useToggleState();

    return (
        <>
            <PanelRow>
                <ControlForPopover
                    id="offline-donation-instructions-text"
                    help={__('This is the actual text which the user will follow to make a donation.', 'give')}
                    heading={__('Donation Instructions', 'give')}
                    onButtonClick={toggleShowPopout}
                    isButtonActive={showPopout}
                >
                    {showPopout && (
                        <Modal
                            title={__('Donation Instructions', 'give')}
                            onRequestClose={toggleShowPopout}
                            style={{maxWidth: '35rem'}}
                        >
                            <Editor
                                value={
                                    offlineDonationsInstructions?.length > 0
                                        ? offlineDonationsInstructions
                                        : `
                            <p>You can customize instructions in the form settings.</p>
                            <p>Please make checks payable to <strong>"{sitename}"</strong>.</p>
                            <p>Your donation is greatly appreciated!</p>
                        `
                                }
                                onChange={(offlineDonationsInstructions) =>
                                    dispatch(setFormSettings({offlineDonationsInstructions}))
                                }
                            />
                        </Modal>
                    )}
                </ControlForPopover>
            </PanelRow>
        </>
    );
};

export default DonationInstructions;
