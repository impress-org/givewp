import {__} from '@wordpress/i18n';
import CampaignCommentCard, {AttributeProps} from '../CommentCard';
import useSWR from 'swr';
import apiFetch from '@wordpress/api-fetch';
import {addQueryArgs} from '@wordpress/url';

import './styles.scss';

type CampaignCommentsProps = {
    attributes: AttributeProps;
};

export type CommentData = {
    comment: string;
    date: string;
    campaignTitle: string;
    donorName: string;
    avatar: string;
};

export default function CampaignComments({attributes}: CampaignCommentsProps) {
    const {data} = useSWR<CommentData[]>(
        addQueryArgs(`/give-api/v2/campaigns/${attributes?.campaignId}/comments`, {
            id: attributes?.campaignId,
            perPage: attributes?.commentsPerPage,
            anonymous: attributes?.showAnonymous ? 1 : 0,
        }),
        (url) => apiFetch({path: url})
    );

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
