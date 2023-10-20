import {PanelBody, PanelRow, SelectControl, TextareaControl, TextControl, ToggleControl} from '@wordpress/components';
import {PanelColorSettings, SETTINGS_DEFAULTS} from '@wordpress/block-editor';
import {__} from '@wordpress/i18n';
import {setFormSettings, useFormState, useFormStateDispatch} from '../../stores/form-state';
import {getWindowData} from '@givewp/form-builder/common';
import debounce from 'lodash.debounce';
import Heading from './Heading';
import Description from '@givewp/form-builder/settings/design/Description';
import MultiStepFirstButtonText from '@givewp/form-builder/settings/design/MultiStepFirstButtonText';
import MultiStepNextButtonText from '@givewp/form-builder/settings/design/MultiStepNextButtonText';
import DonateButton from '@givewp/form-builder/settings/design/DonateButton';

import useIframeMessages from '@givewp/forms/app/utilities/IframeMessages';

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
    const {sendToIframe} = useIframeMessages();

    const dispatchSettings = (data: { [s: string]: any } | ArrayLike<string>) => {
        const [key, value] = Object.entries<string>(data).flat();
        sendToIframe('iFrameResizer0', 'previewSettings', {[key]: value});
        dispatch(setFormSettings({[key]: value}))
    }


    return (
        <>
            <PanelBody title={__('Donation Form', 'give')} initialOpen={true}>
                <PanelRow>
                    <SelectControl
                        label={__('Form design', 'give')}
                        value={designId}
                        onChange={(designId) => dispatch(setFormSettings({designId}))}
                        options={designOptions}
                    />
                </PanelRow>

                <PanelColorSettings
                    title={__('Colors', 'give')}
                    initialOpen={false}
                    colorSettings={[
                        {
                            value: primaryColor,
                            onChange: debounce((primaryColor) => dispatchSettings({primaryColor}), 100),
                            label: __('Primary Color', 'give'),
                            disableCustomColors: false,
                            colors: SETTINGS_DEFAULTS.colors,
                        },
                        {
                            value: secondaryColor,
                            onChange: debounce((secondaryColor) => dispatchSettings({secondaryColor}), 100),
                            label: __('Secondary Color', 'give'),
                            disableCustomColors: false,
                            colors: SETTINGS_DEFAULTS.colors,
                        },
                    ]}
                />
            </PanelBody>
            <PanelBody title={__('Donate Button', 'give')} initialOpen={false}>
                <PanelRow>
                    <DonateButton text={donateButtonCaption} />
                </PanelRow>
            </PanelBody>
            <PanelBody title={__('Header', 'give')} initialOpen={false}>
                <PanelRow>
                    <ToggleControl
                        label={__('Show Header', 'give')}
                        checked={showHeader}
                        onChange={() => dispatchSettings({showHeader: !showHeader})}
                    />
                </PanelRow>
                {showHeader && (
                    <>
                        <PanelRow>
                            <ToggleControl
                                label={__('Show Heading', 'give')}
                                checked={showHeading}
                                onChange={() => dispatchSettings({showHeading: !showHeading})}
                            />
                        </PanelRow>
                        <PanelRow>
                            <ToggleControl
                                label={__('Show Description', 'give')}
                                checked={showDescription}
                                onChange={() => dispatchSettings({showDescription: !showDescription})}
                            />
                        </PanelRow>
                        {showHeading && (
                            <PanelRow>
                                <Heading heading={heading} />
                            </PanelRow>
                        )}
                        {showDescription && (
                            <PanelRow>
                                <Description description={description} />
                            </PanelRow>
                        )}
                    </>
                )}
            </PanelBody>
            {design?.isMultiStep && (
                <PanelBody title={__('Multi-Step', 'give')} initialOpen={false}>
                    <PanelRow>
                        <MultiStepFirstButtonText text={multiStepFirstButtonText} />
                    </PanelRow>
                    <PanelRow>
                        <MultiStepNextButtonText text={multiStepNextButtonText} />
                    </PanelRow>
                </PanelBody>
            )}
        </>
    );
};

export default FormDesignSettings;
