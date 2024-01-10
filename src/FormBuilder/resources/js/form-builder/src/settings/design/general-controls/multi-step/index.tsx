import {PanelBody, PanelRow, TextControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {setFormSettings, useFormState} from '@givewp/form-builder/stores/form-state';

export default function MultiStep({dispatch, publishSettings}) {
    const {
        settings: {multiStepNextButtonText, multiStepFirstButtonText},
    } = useFormState();

    return (
        <PanelBody title={__('Multi-Step', 'give')} initialOpen={false}>
            <PanelRow>
                <TextControl
                    label={__('First Step Button Text', 'give')}
                    value={multiStepFirstButtonText}
                    onChange={(multiStepFirstButtonText) => {
                        dispatch(setFormSettings({multiStepFirstButtonText}));
                        publishSettings({multiStepFirstButtonText});
                    }}
                    help={__(
                        'Customize the text that appears in the first step, prompting the user to go to the next step.',
                        'give'
                    )}
                />
            </PanelRow>
            <PanelRow>
                <TextControl
                    label={__('Next Step Button Text', 'give')}
                    value={multiStepNextButtonText}
                    onChange={(multiStepNextButtonText) => {
                        dispatch(setFormSettings({multiStepNextButtonText}));
                        publishSettings({multiStepNextButtonText});
                    }}
                    help={__('Customize the text that appears prompting the user to go to the next step.', 'give')}
                />
            </PanelRow>
        </PanelBody>
    );
}
