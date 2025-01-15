import {
    AlignmentControl,
    BlockControls,
    HeadingLevelDropdown,
    InspectorControls,
    useBlockProps,
} from '@wordpress/block-editor';
import {BaseControl, Icon, PanelBody, TextareaControl} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import {CampaignSelector} from '../shared/components/CampaignSelector';
import useCampaign from '../shared/hooks/useCampaign';
import {__} from '@wordpress/i18n';
import {useSelect} from '@wordpress/data';
import {external} from '@wordpress/icons';

import './editor.scss';

export default function Edit({attributes, setAttributes}) {
    const blockProps = useBlockProps();
    const {campaign, hasResolved} = useCampaign(attributes.campaignId);

    const adminBaseUrl = useSelect(
        // @ts-ignore
        (select) => select('core').getSite()?.url + '/wp-admin/edit.php?post_type=give_forms&page=give-campaigns',
        []
    );

    const editCampaignUrl = `${adminBaseUrl}&id=${attributes.campaignId}&tab=settings`;

    return (
        <div {...blockProps}>
            <CampaignSelector attributes={attributes} setAttributes={setAttributes}>
                <ServerSideRender block="givewp/campaign-title-block" attributes={attributes} />
            </CampaignSelector>

            {hasResolved && campaign && (
                <InspectorControls>
                    <PanelBody title="Settings" initialOpen={true}>
                        <BaseControl label="Title" id="givewp-campaign-title-block__title-field">
                            <TextareaControl
                                value={campaign.title}
                                readOnly={true}
                                onChange={() => null}
                                help={
                                    <a
                                        href={editCampaignUrl}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="givewp-campaign-title-block__edit-campaign-link"
                                        aria-label={__('Edit campaign settings in a new tab', 'give')}
                                    >
                                        {__('Edit campaign title', 'give')}
                                        <Icon icon={external} />
                                    </a>
                                }
                            />
                        </BaseControl>
                    </PanelBody>
                </InspectorControls>
            )}

            <BlockControls>
                <HeadingLevelDropdown
                    value={attributes.headingLevel}
                    onChange={(newLevel: string) => setAttributes({headingLevel: newLevel})}
                />
                <AlignmentControl
                    value={attributes.textAlign}
                    onChange={(nextAlign) => {
                        setAttributes({textAlign: nextAlign});
                    }}
                />
            </BlockControls>
        </div>
    );
}
