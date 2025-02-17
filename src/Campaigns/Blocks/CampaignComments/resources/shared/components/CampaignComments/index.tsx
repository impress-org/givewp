import CampaignCommentCard, {AttributeProps} from '../CommentCard';
import useSWR from 'swr';
import apiFetch from '@wordpress/api-fetch';
import {addQueryArgs} from '@wordpress/url';

import './styles.scss';
import EmptyState from '../EmptyState';
import {__} from '@wordpress/i18n';

type CampaignCommentsProps = {
    attributes: AttributeProps;
};

export type CommentData = {
    comment: string;
    date: string;
    donorName: string;
    avatar: string;
};

export default function CampaignComments({attributes}: CampaignCommentsProps) {
    const {data, isLoading} = useSWR<CommentData[]>(
        addQueryArgs(`/give-api/v2/campaigns/${attributes?.campaignId}/comments`, {
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
        return <EmptyState />;
    }

    return (
        <div className={'givewp-campaign-comment-block'}>
            <h4 className={'givewp-campaign-comment-block__title'}>{attributes?.title}</h4>
            <p className={'givewp-campaign-comment-block__cta'}>
                {__('Leave a supportive message by donating to the campaign.', 'give')}
            </p>
            {data?.map((comment: CommentData, index: number) => (
                <CampaignCommentCard key={`givewp-campaign-comment-${index}`} attributes={attributes} data={comment} />
            ))}
        </div>
    );
}
