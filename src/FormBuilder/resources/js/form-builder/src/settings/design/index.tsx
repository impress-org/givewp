import {PanelBody, PanelRow, SelectControl, TextareaControl, TextControl, ToggleControl} from '@wordpress/components';
import {PanelColorSettings, SETTINGS_DEFAULTS} from '@wordpress/block-editor';
import {__} from '@wordpress/i18n';
import {setFormSettings, useFormState, useFormStateDispatch} from '../../stores/form-state';
import {getWindowData} from '@givewp/form-builder/common';

import useDonationFormPubSub from '@givewp/forms/app/utilities/useDonationFormPubSub';

const {formDesigns} = getWindowData();

const designOptions = Object.values(formDesigns).map(({id, name}) => ({value: id, label: name}));
const getDesign = (designId) => formDesigns[designId];

const FormDesignSettings = () => {
    const {
        settings: {
            designId,
            showHeader,
            showHeading,
            heading,
            showDescription,
            description,
            primaryColor,
            secondaryColor,
            multiStepNextButtonText,
            multiStepFirstButtonText,
            donateButtonCaption,
        },
    } = useFormState();
    const dispatch = useFormStateDispatch();
    const design = getDesign(designId);
    const {publishSettings, publishColors} = useDonationFormPubSub();

    return (
        <>
            <PanelBody title={__('Donation Form', 'give')} initialOpen={true}>
                <PanelRow>
                    <SelectControl
                        label={__('Form design', 'give')}
                        value={designId}
                        onChange={(designId: string) => dispatch(setFormSettings({designId}))}
                        options={designOptions}
                    />
                </PanelRow>

                <PanelColorSettings
                    title={__('Colors', 'give')}
                    initialOpen={false}
                    colorSettings={[
                        {
                            value: primaryColor,
                            onChange: (primaryColor: string) => {
                                dispatch(setFormSettings({primaryColor}));
                                publishColors({primaryColor});
                            },
                            label: __('Primary Color', 'give'),
                            disableCustomColors: false,
                            colors: SETTINGS_DEFAULTS.colors,
                        },
                        {
                            value: secondaryColor,
                            onChange: (secondaryColor: string) => {
                                dispatch(setFormSettings({secondaryColor}));
                                publishColors({secondaryColor});
                            },
                            label: __('Secondary Color', 'give'),
                            disableCustomColors: false,
                            colors: SETTINGS_DEFAULTS.colors,
                        },
                    ]}
                />
            </PanelBody>
            <PanelBody title={__('Donate Button', 'give')} initialOpen={false}>
                <PanelRow>
                    <TextControl
                        label={__('Button caption', 'give')}
                        value={donateButtonCaption}
                        onChange={(donateButtonCaption) => {
                            dispatch(setFormSettings({donateButtonCaption}));
                            publishSettings({donateButtonCaption});
                        }}
                        help={__('Enter the text you want to display on the donation button.', 'give')}
                    />
                </PanelRow>
            </PanelBody>
            <PanelBody title={__('Header', 'give')} initialOpen={false}>
                <PanelRow>
                    <ToggleControl
                        label={__('Show Header', 'give')}
                        checked={showHeader}
                        onChange={() => {
                            dispatch(setFormSettings({showHeader: !showHeader}));
                            publishSettings({showHeading: !showHeader});
                        }}
                    />
                </PanelRow>
                {showHeader && (
                    <>
                        <PanelRow>
                            <ToggleControl
                                label={__('Show Heading', 'give')}
                                checked={showHeading}
                                onChange={() => {
                                    dispatch(setFormSettings({showHeading: !showHeading}));
                                    publishSettings({showHeading: !showHeading});
                                }}
                            />
                        </PanelRow>
                        <PanelRow>
                            <ToggleControl
                                label={__('Show Description', 'give')}
                                checked={showDescription}
                                onChange={() => {
                                    dispatch(setFormSettings({showDescription: !showDescription}));
                                    publishSettings({showDescription: !showDescription});
                                }}
                            />
                        </PanelRow>
                        {showHeading && (
                            <PanelRow>
                                <TextControl
                                    label={__('Heading', 'give')}
                                    value={heading}
                                    onChange={(heading) => {
                                        dispatch(setFormSettings({heading}));
                                        publishSettings({heading});
                                    }}
                                />
                            </PanelRow>
                        )}
                        {showDescription && (
                            <PanelRow>
                                <TextareaControl
                                    label={__('Description', 'give')}
                                    value={description}
                                    onChange={(description) => {
                                        dispatch(setFormSettings({description}));
                                        publishSettings({description});
                                    }}
                                />
                            </PanelRow>
                        )}
                    </>
                )}
            </PanelBody>
            {design?.isMultiStep && (
                <PanelBody title={__('Multi-Step', 'give')} initialOpen={false}>
                    <PanelRow>
                        <TextControl
                            label={__('First Step Button Text', 'give')}
                            value={multiStepFirstButtonText}
                            onChange={(multiStepFirstButtonText) => {
                                dispatch(setFormSettings({multiStepFirstButtonText}));
                                publishSettings({multiStepFirstButtonText});
                            }}
                            help={__('Customize the text that appears in the first step, prompting the user to go to the next step.')}
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
                            help={__('Customize the text that appears prompting the user to go to the next step.')}
                        />
                    </PanelRow>
                </PanelBody>
            )}
        </>
    );
};

export default FormDesignSettings;
