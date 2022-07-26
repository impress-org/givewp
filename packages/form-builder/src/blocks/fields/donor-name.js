import {Icon} from '@wordpress/icons';
import {__} from "@wordpress/i18n";
import {InspectorControls} from "@wordpress/block-editor";
import {FormTokenField, PanelBody, PanelRow, ToggleControl} from "@wordpress/components";
import settings from "./settings";

const donorName = {
    name: 'custom-block-editor/donor-name',
    category: 'input',
    settings: {
        ...settings,
        title: __('Donor Name', 'custom-block-editor'),
        supports: {
            multiple: false,
        },
        attributes: {
            lock: {remove: true},
            showHonorific: {
                type: 'boolean',
                default: true,
            },
            honoriphics: {
                type: 'array',
                default: ['Mr', 'Ms', 'Mrs'],
            },
            requireLastName: {
                type: 'boolean',
                default: false,
            },
        },
        edit: (props) => {

            const {
                attributes: {showHonorific, honoriphics, requireLastName},
                setAttributes,
            } = props;

            const requiredText = (text, isRequired = true) => {
                if (isRequired) {
                    return text + ' (' + __('required', 'give') + ')';
                }
                return text;
            };

            const titleLabelTransform = (token = '') => token.charAt(0).toUpperCase() + token.slice(1);
            const titleValueTransform = (token = '') => token.trim().toLowerCase();

            return (
                <>
                    <div style={{display: 'flex', gap: '15px'}}>
                        {!!showHonorific && (<select style={{width: '80px'}}>
                            {honoriphics.map(title => <option
                                value={titleLabelTransform(title)}>{titleLabelTransform(title)}</option>)}
                        </select>)}
                        <input type="text" placeholder={requiredText(__('First Name', 'give'))} />
                        <input type="text" placeholder={requiredText(__('Last Name', 'give'), requireLastName)} />
                    </div>

                    <InspectorControls>
                        <PanelBody title={__('Name Title Prefix', 'give')} initialOpen={true}>
                            <PanelRow>
                                <div style={{display: 'flex', flexDirection: 'column', gap: '10px'}}>
                                    <div>{/* Wrapper added to control spacing between control and help text. */}
                                        <ToggleControl
                                            label={__('Show Name Title Prefix', 'give')}
                                            checked={showHonorific}
                                            onChange={() => setAttributes({showHonorific: !showHonorific})}
                                            help={__('Do you want to add a name title prefix dropdown field before the donor\'s first name field? This will display a dropdown with options such as Mrs, Miss, Ms, Sir, and Dr for the donor to choose from.', 'give')}
                                        />
                                    </div>
                                    {!!showHonorific && (<FormTokenField
                                        tokenizeOnSpace={true}
                                        label={__('Title Prefixes', 'give')}
                                        value={honoriphics}
                                        suggestions={['Mr', 'Ms', 'Mrs']}
                                        placeholder={__('Select some options', 'give')}
                                        onChange={(tokens) => setAttributes({honoriphics: tokens})}
                                        displayTransform={titleLabelTransform}
                                        saveTransform={titleValueTransform}
                                    />)}
                                </div>
                            </PanelRow>
                        </PanelBody>
                        <PanelBody title={__('Last Name', 'give')} initialOpen={true}>
                            <PanelRow>
                                <ToggleControl
                                    label={__('Require Last Name', 'give')}
                                    checked={requireLastName}
                                    onChange={() => setAttributes({requireLastName: !requireLastName})}
                                    help={__('Do you want to force the Last Name field to be required?', 'give')}
                                />
                            </PanelRow>
                        </PanelBody>
                    </InspectorControls>
                </>
            );

        },
        icon: () => <Icon icon={
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M14.2736 13.4026C14.1721 13.3682 13.5308 13.0689 13.9315 11.8076H13.9258C14.9704 10.6936 15.7686 8.90101 15.7686 7.13619C15.7686 4.42256 14.026 3 12.0006 3C9.97402 3 8.24093 4.4219 8.24093 7.13619C8.24093 8.90827 9.03473 10.7081 10.0857 11.8195C10.4954 12.9321 9.76281 13.3451 9.60966 13.4032C7.48861 14.1974 5 15.6451 5 17.0743V17.6101C5 19.5573 8.64613 20 12.0204 20C15.3998 20 19 19.5573 19 17.6101V17.0743C19 15.6022 16.4993 14.1657 14.2736 13.4026Z"
                    fill="#000C00" />
            </svg>
        } />,
    },
};

export default donorName;
