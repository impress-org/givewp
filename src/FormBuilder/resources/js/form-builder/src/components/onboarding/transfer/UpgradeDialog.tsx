import {__} from '@wordpress/i18n';
import {setTransferState, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import ButtonGroup from '@givewp/components/AdminUI/ButtonGroup';
import Button from '@givewp/components/AdminUI/Button';

export default function UpgradeSuccessDialog() {

    const {transfer, settings} = useFormState();
    const dispatch = useFormStateDispatch();

    const {migrationActionUrl, formId} = window.migrationOnboardingData;

    function handleClose() {
        dispatch(setTransferState({showUpgradeModal: false}))
        fetch(migrationActionUrl + `&formId=${formId}`, {method: 'POST'})
    }
    const getImage = (name: string) => `${window.migrationOnboardingData.pluginUrl}assets/dist/images/form-migration/${name}`;

    if(!transfer.showUpgradeModal) {
        return null;
    }

    return (
        <ModalDialog
            showHeader={false}
            handleClose={handleClose}
            title={__('Your form has been upgraded', 'give')}
        >
            <>
                <div className="givewp-dialog-image-container">
                    <img src={getImage('form.jpg')} alt={__('Upgraded form', 'give')} />
                </div>

                <div className="givewp-dialog-title">
                    {__('Your form has been upgraded', 'give')}
                </div>

                <div className="givewp-dialog-info">
                    {__('Make sure to check the settings for each section and block, and maybe even run some test donations to ensure your new form is good to go.', 'give')}
                </div>

                <ButtonGroup align="right">
                    <Button
                        size="large"
                        onClick={handleClose}
                    >
                        {__('Got it', 'give')}
                    </Button>
                </ButtonGroup>
            </>
        </ModalDialog>
    );
}
