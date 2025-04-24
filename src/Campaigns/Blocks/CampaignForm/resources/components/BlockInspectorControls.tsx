import {ExternalLink, PanelBody, PanelRow, SelectControl, TextControl, ToggleControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {InspectorControls} from '@wordpress/block-editor';
import type {FormOption} from '../../../shared/hooks/useFormOptions';
import useCampaigns from "../../../shared/hooks/useCampaigns";

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
    const {id, displayStyle, continueButtonTitle=__('Donate now', 'give'), showTitle, contentDisplay, showGoal, showContent} = attributes;
    const showOpenFormButton = ['newTab', 'modal', 'reveal', 'button'].includes(displayStyle);
    const {campaigns, hasResolved} = useCampaigns({status: ['active', 'draft']});

    const displayStyleOptions = (
        options: {label: string; value: string}[],
        legacy: {label: string; value: string}[],
        v3: {label: string; value: string}[]
    ) => {
        return  isLegacyTemplate ? options.concat(legacy) : (!isLegacyForm ? options.concat(v3) : options);
    };

    const campaignOptions = (() => {
        if (!hasResolved) {
            return [{label: __('Loading...', 'give'), value: ''}];
        }

        if (campaigns.length) {
            const campaignOptions = campaigns.map((campaign) => ({
                label: `${campaign.title} ${campaign.status === 'draft' ? `(${__('Draft', 'give')})` : ''}`.trim(),
                value: campaign.id.toString(),
            }));

            return [{label: __('Select...', 'give'), value: ''}, ...campaignOptions];
        }

        return [{label: __('No campaigns found.', 'give'), value: ''}];
    })();

    return (
        <InspectorControls>
            <PanelBody title={__('Form Settings', 'give')} initialOpen={true}>
                {attributes?.campaignId &&
                    <PanelRow>
                        <SelectControl
                            label={__('Select a campaign', 'give')}
                            value={attributes?.campaignId ?? ''}
                            options={[
                                ...campaignOptions.map((campaign) => ({
                                    label: campaign.label,
                                    value: String(campaign.value),
                                })),
                            ]}
                            onChange={(campaignId) => {
                                setAttributes({campaignId, id: null});
                            }}
                        />
                    </PanelRow>
                }
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
                    <PanelRow>
                        <SelectControl
                            label={__('Display style', 'give')}
                            value={displayStyle}
                            options={displayStyleOptions(
                                [
                                    {
                                        label: __('On page', 'give'),
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
