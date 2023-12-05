import {__} from '@wordpress/i18n';
import {BlockEditProps} from '@wordpress/blocks';
import {PanelBody, PanelRow, SelectControl, TextControl, ToggleControl} from '@wordpress/components';
import {InspectorControls} from '@wordpress/block-editor';
import {useState} from 'react';
import Options from '@givewp/form-builder-library/src/OptionsPanel';
import {OptionProps} from '@givewp/form-builder-library/src/OptionsPanel/types';

const titleLabelTransform = (token = '') => token.charAt(0).toUpperCase() + token.slice(1);
const titleValueTransform = (token = '') => token.trim().toLowerCase();

export default function Edit({
    attributes: {
        showHonorific,
        honorifics,
        firstNameLabel,
        firstNamePlaceholder,
        lastNameLabel,
        lastNamePlaceholder,
        requireLastName,
    },
    setAttributes,
}: BlockEditProps<any>) {
    const [selectedTitle, setSelectedTitle] = useState<string>((Object.values(honorifics)[0] as string) ?? '');
    const [honorificOptions, setHonorificOptions] = useState<OptionProps[]>(
        Object.values(honorifics).map((token: string) => {
            return {
                label: titleLabelTransform(token),
                value: titleValueTransform(token),
                checked: selectedTitle === token,
            } as OptionProps;
        })
    );

    const setOptions = (options: OptionProps[]) => {
        setHonorificOptions(options);

        const filtered = {};
        // Filter options
        Object.values(options).forEach((option) => {
            Object.assign(filtered, {[option.label]: option.label});
        });

        setAttributes({honorifics: filtered});
    };

    return (
        <>
            <div
                style={{
                    display: 'grid',
                    gridTemplateColumns: showHonorific ? '1fr 2fr 2fr' : '1fr 1fr',
                    gap: '15px',
                }}
            >
                {!!showHonorific && (
                    <SelectControl
                        label={__('Title', 'give')}
                        options={honorificOptions}
                        value={selectedTitle}
                        onChange={setSelectedTitle}
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
                        <div style={{display: 'flex', flexDirection: 'column', gap: '10px'}}>
                            <div>
                                {/* Wrapper added to control spacing between control and help text. */}
                                <ToggleControl
                                    label={__('Show Name Title Prefix', 'give')}
                                    checked={showHonorific}
                                    onChange={() => setAttributes({showHonorific: !showHonorific})}
                                    help={__(
                                        "Do you want to add a name title prefix dropdown field before the donor's first name field? This will display a dropdown with options such as Mrs, Miss, Ms, Sir, and Dr for the donor to choose from.",
                                        'give'
                                    )}
                                />
                            </div>
                            {!!showHonorific && (
                                <Options
                                    multiple={false}
                                    selectable={false}
                                    options={honorificOptions}
                                    setOptions={setOptions}
                                    defaultControlsTooltip={__('Title Prefixes', 'give')}
                                />
                            )}
                        </div>
                    </PanelRow>
                </PanelBody>
                <PanelBody title={__('First Name', 'give')} initialOpen={true}>
                    <PanelRow>
                        <TextControl
                            label={__('Label')}
                            value={firstNameLabel}
                            onChange={(value) => setAttributes({firstNameLabel: value})}
                        />
                    </PanelRow>
                    <PanelRow>
                        <TextControl
                            label={__('Placeholder')}
                            value={firstNamePlaceholder}
                            onChange={(value) => setAttributes({firstNamePlaceholder: value})}
                        />
                    </PanelRow>
                </PanelBody>
                <PanelBody title={__('Last Name', 'give')} initialOpen={true}>
                    <PanelRow>
                        <TextControl
                            label={__('Label')}
                            value={lastNameLabel}
                            onChange={(value) => setAttributes({lastNameLabel: value})}
                        />
                    </PanelRow>
                    <PanelRow>
                        <TextControl
                            label={__('Placeholder')}
                            value={lastNamePlaceholder}
                            onChange={(value) => setAttributes({lastNamePlaceholder: value})}
                        />
                    </PanelRow>
                    <PanelRow>
                        <ToggleControl
                            label={__('Required', 'give')}
                            checked={requireLastName}
                            onChange={() => setAttributes({requireLastName: !requireLastName})}
                            help={__('Do you want to force the Last Name field to be required?', 'give')}
                        />
                    </PanelRow>
                </PanelBody>
            </InspectorControls>
        </>
    );
}
