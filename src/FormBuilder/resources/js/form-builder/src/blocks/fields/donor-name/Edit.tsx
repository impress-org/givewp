import {__} from '@wordpress/i18n';
import {BlockEditProps} from '@wordpress/blocks';
import {PanelBody, PanelRow, SelectControl, TextControl, ToggleControl} from '@wordpress/components';
import {InspectorControls} from '@wordpress/block-editor';
import {useState} from 'react';
import {OptionsPanel} from '@givewp/form-builder-library';
import type {OptionProps} from '@givewp/form-builder-library/build/OptionsPanel/types';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';

const titleLabelTransform = (token = '') => token.charAt(0).toUpperCase() + token.slice(1);
const titleValueTransform = (token = '') => token.trim().toLowerCase();
const convertHonorificsToOptions = (honorifics: string[], defaultValue?: string) =>
    honorifics?.filter(label => label.length > 0).map((honorific: string) => ({
            label: titleLabelTransform(honorific),
            value: titleValueTransform(honorific),
            checked: defaultValue ? defaultValue === honorific : honorifics[0] === honorific
        }) as OptionProps
    );

const convertOptionsToHonorifics = (options: OptionProps[]) => {
    const honorifics = [];
    Object.values(options).forEach((option) => {
        if (option.label.length > 0) {
            honorifics.push(option.label);
        }
    });

    return honorifics;
}

type Attributes = {
    showHonorific: boolean;
    useGlobalSettings: boolean;
    honorifics: string[];
    firstNameLabel: string;
    firstNamePlaceholder: string;
    lastNameLabel: string;
    lastNamePlaceholder: string;
    requireLastName: boolean;
}

export default function Edit({
                                 attributes,
                                 setAttributes
                             }: BlockEditProps<any>) {
    const {
        showHonorific,
        useGlobalSettings,
        honorifics,
        firstNameLabel,
        firstNamePlaceholder,
        lastNameLabel,
        lastNamePlaceholder,
        requireLastName
    } = attributes as Attributes;
    const globalHonorifics = getFormBuilderWindowData().nameTitlePrefixes;
    const [selectedTitle, setSelectedTitle] = useState<string>((Object.values(honorifics)[0] as string) ?? '');
    const [honorificOptions, setHonorificOptions] = useState<OptionProps[]>(
        convertHonorificsToOptions(Object.values(honorifics), selectedTitle)
    );

    const setOptions = (options: OptionProps[]) => {
        setHonorificOptions(options);

        setAttributes({ honorifics: convertOptionsToHonorifics(options) });
    };

    if (typeof useGlobalSettings === 'undefined') {
        setAttributes({ useGlobalSettings: true });
    }

    return (
        <>
            <div
                style={{
                    display: 'grid',
                    gridTemplateColumns: showHonorific && honorificOptions.length > 0 ? '1fr 2fr 2fr' : '1fr 1fr',
                    gap: '15px'
                }}
            >
                {!!showHonorific && (
                    <SelectControl
                        label={__('Title', 'give')}
                        options={!useGlobalSettings ? honorificOptions : convertHonorificsToOptions(globalHonorifics)}
                        value={selectedTitle}
                        onChange={setSelectedTitle}
                        style={{ padding: '16px 38px 16px 16px' }}
                    />
                )}
                <TextControl
                    label={firstNameLabel}
                    placeholder={firstNamePlaceholder}
                    required={true}
                    className={'give-is-required'}
                    readOnly
                    value={firstNamePlaceholder}
                    onChange={null}
                />
                <TextControl
                    label={lastNameLabel}
                    placeholder={lastNamePlaceholder}
                    required={requireLastName}
                    className={`${requireLastName ? 'give-is-required' : ''}`}
                    value={lastNamePlaceholder}
                    onChange={null}
                    readOnly
                />
            </div>

            <InspectorControls>
                <PanelBody title={__('Name Title Prefix', 'give')} initialOpen={true}>
                    <PanelRow>
                        <ToggleControl
                            label={__('Show Name Title Prefix', 'give')}
                            checked={showHonorific}
                            onChange={() => setAttributes({ showHonorific: !showHonorific })}
                            help={__(
                                'Do you want to add a name title prefix dropdown field before the donor\'s first name field? This will display a dropdown with options such as Mrs, Miss, Ms, Sir, and Dr for the donor to choose from.',
                                'give'
                            )}
                        />
                    </PanelRow>
                    {!!showHonorific && (
                        <PanelRow>
                            <div style={{ width: '100%' }}>
                                <div>
                                    <SelectControl
                                        label={__('Options', 'give')}
                                        onChange={() => setAttributes({ useGlobalSettings: !useGlobalSettings })}
                                        value={useGlobalSettings ? 'true' : 'false'}
                                        options={[
                                            { label: __('Global', 'give'), value: 'true' },
                                            { label: __('Customize', 'give'), value: 'false' }
                                        ]}
                                    />
                                </div>
                                {useGlobalSettings && (
                                    <p
                                        style={{
                                            color: '#595959',
                                            fontStyle: 'SF Pro Text',
                                            fontSize: '0.75rem',
                                            lineHeight: '120%',
                                            fontWeight: 400,
                                            marginTop: '0.5rem'
                                        }}
                                    >
                                        {__(' Go to the settings to change the ')}
                                        <a
                                            href="/wp-admin/edit.php?post_type=give_forms&page=give-settings&tab=display&section=display-settings"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            {__('Global Title Prefixes options.')}
                                        </a>
                                    </p>
                                )}
                            </div>
                        </PanelRow>
                    )}

                    {!!showHonorific && !useGlobalSettings && (
                        <div style={{ marginTop: '1rem' }}>
                            <OptionsPanel
                                multiple={false}
                                selectable={false}
                                options={honorificOptions}
                                setOptions={setOptions}
                                toggleEnabled={false}
                                defaultControlsTooltip={__('Title Prefixes', 'give')}
                            />
                        </div>
                    )}
                </PanelBody>
                <PanelBody title={__('First Name', 'give')} initialOpen={true}>
                    <PanelRow>
                        <TextControl
                            label={__('Label')}
                            value={firstNameLabel}
                            onChange={(value) => setAttributes({ firstNameLabel: value })}
                        />
                    </PanelRow>
                    <PanelRow>
                        <TextControl
                            label={__('Placeholder')}
                            value={firstNamePlaceholder}
                            onChange={(value) => setAttributes({ firstNamePlaceholder: value })}
                        />
                    </PanelRow>
                </PanelBody>
                <PanelBody title={__('Last Name', 'give')} initialOpen={true}>
                    <PanelRow>
                        <TextControl
                            label={__('Label')}
                            value={lastNameLabel}
                            onChange={(value) => setAttributes({ lastNameLabel: value })}
                        />
                    </PanelRow>
                    <PanelRow>
                        <TextControl
                            label={__('Placeholder')}
                            value={lastNamePlaceholder}
                            onChange={(value) => setAttributes({ lastNamePlaceholder: value })}
                        />
                    </PanelRow>
                    <PanelRow>
                        <ToggleControl
                            label={__('Required', 'give')}
                            checked={requireLastName}
                            onChange={() => setAttributes({ requireLastName: !requireLastName })}
                            help={__('Do you want to force the Last Name field to be required?', 'give')}
                        />
                    </PanelRow>
                </PanelBody>
            </InspectorControls>
        </>
    );
}
