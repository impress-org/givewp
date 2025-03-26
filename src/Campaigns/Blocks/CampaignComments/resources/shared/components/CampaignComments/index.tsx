import apiFetch from '@wordpress/api-fetch';
import {addQueryArgs} from '@wordpress/url';
import {__} from '@wordpress/i18n';
import useSWR from 'swr';
import CampaignCommentCard from '../CommentCard';
import EmptyState from '../EmptyState';
import {Attributes, CommentData} from '../../../types';

import './styles.scss';

export default function CampaignComments({
    attributes,
    secondaryColor,
}: {
    attributes: Attributes;
    secondaryColor: string;
}) {
    const {title = __('Share your support', 'give')} = attributes;

    const {data, isLoading} = useSWR<CommentData[]>(
        addQueryArgs(`/givewp/v3/campaigns/${attributes?.campaignId}/comments`, {
            id: attributes?.campaignId,
            perPage: attributes?.commentsPerPage,
            anonymous: attributes?.showAnonymous,
        }),
        (url) => apiFetch({path: url})
    );

    if (isLoading) {
        return null;
    }

    if (data && data?.length === 0) {
        return <EmptyState secondaryColor={secondaryColor} />;
    }

    return (
        <div className={'givewp-campaign-comment-block'}>
            <h4 className={'givewp-campaign-comment-block__title'}>{title}</h4>
            <p className={'givewp-campaign-comment-block__cta'}>
                {__('Leave a supportive message by donating to the campaign.', 'give')}
            </p>
            {data?.map((comment: CommentData, index: number) => (
                <CampaignCommentCard key={`givewp-campaign-comment-${index}`} attributes={attributes} data={comment} />
            ))}
        </div>
    );
}
