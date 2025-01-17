import {useSelect} from '@wordpress/data';

export default function useCampaignId(attributes, setAttributes) {
    const campaignIdFromContext = useSelect((select) => {
        // @ts-ignore
        const postType = select('core/editor').getCurrentPostType();

        if (postType === 'give_campaign_page') {
            // @ts-ignore
            return select('core/editor').getEditedPostAttribute('campaignId');
        }
        return null;
    }, []);

    if (campaignIdFromContext && campaignIdFromContext !== attributes?.campaignId) {
        setAttributes({campaignId: campaignIdFromContext});
    }

    return campaignIdFromContext;
}
