import {PanelBody, PanelRow, TextControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {setFormSettings, useFormState} from '@givewp/form-builder/stores/form-state';

export default function MultiStep({dispatch, publishSettings}) {
    const {
        settings: {multiStepNextButtonText, multiStepFirstButtonText, donateButtonCaption},
    } = useFormState();

    return (
        <PanelBody title={__('Buttons', 'give')} initialOpen={false}>
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
            <PanelRow>
                <TextControl
                    label={__('Donate Button', 'give')}
                    value={donateButtonCaption}
                    onChange={(donateButtonCaption) => {
                        dispatch(
                            setFormSettings({
                                donateButtonCaption,
                            })
                        );
                        publishSettings({
                            donateButtonCaption,
                        });
                    }}
                    help={__('Enter the text you want to display on the donation button.', 'give')}
                />
            </PanelRow>
        </PanelBody>
    );
}
