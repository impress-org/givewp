import {useSelect} from '@wordpress/data';

export default function useCampaignId(attributes, setAttributes) {
    const campaignIdFromContext = useSelect((select) => {
        const postType = select('core/editor').getCurrentPostType();

        if (postType === 'give_campaign_page') {
            return select('core/editor').getEditedPostAttribute('campaignId');
        }
        return null;
    }, []);

    if (campaignIdFromContext && campaignIdFromContext !== attributes?.campaignId) {
        setAttributes({campaignId: campaignIdFromContext});
    }

    return campaignIdFromContext;
}
