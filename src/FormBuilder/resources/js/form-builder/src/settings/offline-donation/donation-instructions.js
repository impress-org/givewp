import {useState} from 'react';
import {useToggleState} from '../../hooks';
import {BaseControl, Button, Modal, PanelRow} from '@wordpress/components';
import {__} from "@wordpress/i18n";
import Editor from "@givewp/form-builder/settings/email/template-options/components/editor";
import {MenuIcon} from "@givewp/form-builder/blocks/fields/terms-and-conditions/Icon";
import {setFormSettings, useFormState, useFormStateDispatch} from "@givewp/form-builder/stores/form-state";

const DonationInstructions = () => {

    const {
        settings: {offlineDonationsInstructions},
    } = useFormState();
    const dispatch = useFormStateDispatch();

    const {state: showPopout, toggle: toggleShowPopout} = useToggleState();

    return (
        <>
            <PanelRow>
                <BaseControl
                    id={'offline-donation-instructions-text'}
                    help={__('This is the actual text which the user will follow to make a donation.', 'give')}
                >
                    <div
                        style={{
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'space-between',
                        }}
                    >
                        {__('Donation Instructions', 'give')}
                        <Button
                            style={{background: 'transparent', verticalAlign: 'center'}}
                            variant={'primary'}
                            onClick={toggleShowPopout}
                        >
                            <MenuIcon />
                        </Button>
                    </div>
                </BaseControl>
            </PanelRow>
            {showPopout && (
                <Modal
                    title={__('Donation Instructions', 'give')}
                    onRequestClose={toggleShowPopout}
                    style={{maxWidth: '35rem'}}
                >
                    <Editor
                        value={offlineDonationsInstructions ?? `
                            <p>You can customize instructions in the form settings.</p>
                            <p>Please make checks payable to <strong>"{sitename}"</strong>.</p>
                            <p>Your donation is greatly appreciated!</p>
                        `}
                        onChange={(offlineDonationsInstructions) => dispatch(setFormSettings({offlineDonationsInstructions}))}
                    />
                </Modal>
            )}
        </>
    );
};

export default DonationInstructions;
