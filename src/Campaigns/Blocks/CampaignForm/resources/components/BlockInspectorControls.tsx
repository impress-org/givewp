import {ExternalLink, PanelBody, PanelRow, SelectControl, TextControl, ToggleControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {InspectorControls} from '@wordpress/block-editor';
import type {FormOption} from '../../../shared/hooks/useFormOptions';
import type {EntityOption} from "../../../shared/components/EntitySelector/EntitySelector";

/**
 * @unreleasaed
 */
interface BlockInspectorControls {
    attributes: Readonly<any>;
    setAttributes: (newAttributes: Record<string, any>) => void;
    entityOptions: FormOption[];
    isResolving: boolean;
    isLegacyTemplate: boolean;
    isLegacyForm: boolean;
}

/**
 * @unreleased
 */
export default function DonationFormBlockControls({
    attributes,
    setAttributes,
    entityOptions,
    isResolving,
    isLegacyTemplate,
    isLegacyForm,
}: BlockInspectorControls) {
    const {id, displayStyle, continueButtonTitle, showTitle, contentDisplay, showGoal, showContent} = attributes;
    const hasFormFormat = isLegacyTemplate || !isLegacyForm;
    const showOpenFormButton = hasFormFormat && ['newTab', 'modal', 'reveal', 'button'].includes(displayStyle);

    const displayStyleOptions = (
        options: {label: string; value: string}[],
        legacy: {label: string; value: string}[],
        v3: {label: string; value: string}[]
    ) => {
        return isLegacyTemplate ? options.concat(legacy) : options.concat(v3);
    };

    return (
        <InspectorControls>
            <PanelBody title={__('Form Settings', 'give')} initialOpen={true}>
                <PanelRow>
                    {isResolving === false && entityOptions.length === 0 ? (
                        <p>{__('No forms were found using the GiveWP form builder.', 'give')}</p>
                    ) : (
                        <SelectControl
                            label={__('Choose a donation form', 'give')}
                            value={id ?? ''}
                            options={[...entityOptions.map((form) => ({label: form.label, value: String(form.value)}))]}
                            onChange={(newFormId) => {
                                setAttributes({id: Number(newFormId)});
                            }}
                        />
                    )}
                </PanelRow>
                {hasFormFormat && (
                    <PanelRow>
                        <SelectControl
                            label={__('Display style', 'give')}
                            value={displayStyle}
                            options={displayStyleOptions(
                                [
                                    {
                                        label: __('Full Form', 'give'),
                                        value: 'onpage',
                                    },
                                    {
                                        label: __('Modal', 'give'),
                                        value: 'modal',
                                    },
                                ],
                                [
                                    {
                                        label: __('Reveal', 'give'),
                                        value: 'reveal',
                                    },
                                    {
                                        value: 'button',
                                        label: __('One Button Launch', 'give'),
                                    },
                                ],
                                [
                                    {
                                        label: __('New Tab', 'give'),
                                        value: 'newTab',
                                    },
                                ]
                            )}
                            onChange={(value) => {
                                setAttributes({displayStyle: value});
                            }}
                        />
                    </PanelRow>
                )}

                {showOpenFormButton && (
                    <PanelRow>
                        <TextControl
                            label={__('Button text', 'give')}
                            value={continueButtonTitle}
                            onChange={(value) => {
                                setAttributes({continueButtonTitle: value});
                            }}
                        />
                    </PanelRow>
                )}

                {isLegacyTemplate && (
                    <>
                        <PanelRow>
                            <ToggleControl
                                label={__('Title', 'give')}
                                name="showTitle"
                                checked={!!showTitle}
                                onChange={(value) => {
                                    setAttributes({showTitle: value});
                                }}
                            />
                        </PanelRow>
                        <PanelRow>
                            <ToggleControl
                                label={__('Goal', 'give')}
                                name="showGoal"
                                checked={!!showGoal}
                                onChange={(value) => {
                                    setAttributes({showGoal: value});
                                }}
                            />
                        </PanelRow>
                        <PanelRow>
                            <ToggleControl
                                label={__('Content', 'give')}
                                name="contentDisplay"
                                checked={!!contentDisplay}
                                onChange={(value) => {
                                    setAttributes({contentDisplay: value});
                                }}
                            />
                        </PanelRow>

                        {contentDisplay && (
                            <PanelRow>
                                <SelectControl
                                    label={__('Content Position', 'give')}
                                    name="showContent"
                                    value={showContent}
                                    options={[
                                        {value: 'above', label: __('Above', 'give')},
                                        {value: 'below', label: __('Below', 'give')},
                                    ]}
                                    onChange={(value) => {
                                        setAttributes({showContent: value});
                                    }}
                                />
                            </PanelRow>
                        )}
                    </>
                )}

                {id && (
                    <PanelRow>
                        <ExternalLink
                            href={
                                isLegacyForm
                                    ? `/wp-admin/post.php?post=${id}&action=edit`
                                    : `/wp-admin/edit.php?post_type=give_forms&page=givewp-form-builder&donationFormID=${id}`
                            }
                        >
                            {__('Edit donation form', 'give')}
                        </ExternalLink>
                    </PanelRow>
                )}
            </PanelBody>
        </InspectorControls>
    );
}
